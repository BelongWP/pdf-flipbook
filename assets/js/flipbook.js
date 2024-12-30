class FlipBook {
    constructor(container) {
        this.container = container;
        this.pdf = container.dataset.pdf;
        this.speed = parseInt(container.dataset.speed) || 1000;
        this.showThumbnails = container.dataset.thumbnails === "1";
        
        this.currentPage = 1;
        this.totalPages = 0;
        this.isAnimating = false;
        
        this.init();
    }
    
    async init() {
        // Set up Three.js scene
        this.scene = new THREE.Scene();
        this.camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        
        const viewport = this.container.querySelector('.flipbook-viewport');
        viewport.appendChild(this.renderer.domElement);
        
        // Initialize controls
        this.initializeControls();
        
        // Set up responsive canvas
        this.setupResponsive();
        
        // Load PDF
        await this.loadPDF();
        
        // Start animation loop
        this.animate();
    }
    
    initializeControls() {
        const prevButton = this.container.querySelector('.prev-page');
        const nextButton = this.container.querySelector('.next-page');
        
        prevButton.addEventListener('click', () => this.previousPage());
        nextButton.addEventListener('click', () => this.nextPage());
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.previousPage();
            if (e.key === 'ArrowRight') this.nextPage();
        });
        
        // Touch/swipe support
        let touchStartX = 0;
        this.container.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
        });
        
        this.container.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > 50) {
                if (diff > 0) this.nextPage();
                else this.previousPage();
            }
        });
    }
    
    setupResponsive() {
        const resize = () => {
            const viewport = this.container.querySelector('.flipbook-viewport');
            const width = viewport.clientWidth;
            const height = viewport.clientHeight;
            
            this.renderer.setSize(width, height);
            this.camera.aspect = width / height;
            this.camera.updateProjectionMatrix();
        };
        
        window.addEventListener('resize', resize);
        resize();
    }
    
    async loadPDF() {
        try {
            const response = await fetch(this.pdf);
            const arrayBuffer = await response.arrayBuffer();
            
            // Initialize PDF.js
            const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
            this.totalPages = pdf.numPages;
            
            // Load first page
            await this.loadPage(1);
            
            // Update page counter
            this.updatePageNumber();
            
            // Load thumbnails if enabled
            if (this.showThumbnails) {
                await this.loadThumbnails(pdf);
            }
        } catch (error) {
            console.error('Error loading PDF:', error);
            this.container.innerHTML = '<p>Error loading PDF. Please try again later.</p>';
        }
    }
    
    async loadPage(pageNumber) {
        const page = await this.pdf.getPage(pageNumber);
        const viewport = page.getViewport({ scale: 1.5 });
        
        // Create canvas for page
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        
        // Render PDF page to canvas
        await page.render({
            canvasContext: context,
            viewport: viewport
        }).promise;
        
        // Create texture from canvas
        const texture = new THREE.Texture(canvas);
        texture.needsUpdate = true;
        
        // Create page geometry
        const geometry = new THREE.PlaneGeometry(1, viewport.height / viewport.width);
        const material = new THREE.MeshBasicMaterial({
            map: texture,
            side: THREE.DoubleSide
        });
        
        // Create mesh and add to scene
        const mesh = new THREE.Mesh(geometry, material);
        this.scene.add(mesh);
        
        return mesh;
    }
    
    async loadThumbnails(pdf) {
        const thumbnailsContainer = this.container.querySelector('.flipbook-thumbnails');
        
        for (let i = 1; i <= this.totalPages; i++) {
            const page = await pdf.getPage(i);
            const viewport = page.getViewport({ scale: 0.2 });
            
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            
            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;

            const thumbnail = document.createElement('img');
            thumbnail.src = canvas.toDataURL();
            thumbnail.alt = `Page ${i}`;
            thumbnail.classList.add('thumbnail');
            if (i === this.currentPage) thumbnail.classList.add('active');
            
            thumbnail.addEventListener('click', () => this.goToPage(i));
            thumbnailsContainer.appendChild(thumbnail);
        }
    }
    
    updatePageNumber() {
        const pageNumberEl = this.container.querySelector('.page-number');
        if (pageNumberEl) {
            pageNumberEl.textContent = `Page ${this.currentPage} of ${this.totalPages}`;
        }
    }
    
    async nextPage() {
        if (this.isAnimating || this.currentPage >= this.totalPages) return;
        
        this.isAnimating = true;
        const oldPage = this.currentPage;
        this.currentPage++;
        
        await this.animatePageTurn(oldPage, this.currentPage);
        this.updatePageNumber();
        this.updateThumbnails();
        
        this.isAnimating = false;
    }
    
    async previousPage() {
        if (this.isAnimating || this.currentPage <= 1) return;
        
        this.isAnimating = true;
        const oldPage = this.currentPage;
        this.currentPage--;
        
        await this.animatePageTurn(oldPage, this.currentPage);
        this.updatePageNumber();
        this.updateThumbnails();
        
        this.isAnimating = false;
    }
    
    async goToPage(pageNumber) {
        if (this.isAnimating || pageNumber === this.currentPage) return;
        if (pageNumber < 1 || pageNumber > this.totalPages) return;
        
        this.isAnimating = true;
        const oldPage = this.currentPage;
        this.currentPage = pageNumber;
        
        await this.animatePageTurn(oldPage, this.currentPage);
        this.updatePageNumber();
        this.updateThumbnails();
        
        this.isAnimating = false;
    }
    
    updateThumbnails() {
        if (!this.showThumbnails) return;
        
        const thumbnails = this.container.querySelectorAll('.thumbnail');
        thumbnails.forEach((thumb, index) => {
            if (index + 1 === this.currentPage) {
                thumb.classList.add('active');
            } else {
                thumb.classList.remove('active');
            }
        });
    }
    
    async animatePageTurn(fromPage, toPage) {
        // Remove old page
        while(this.scene.children.length > 0) {
            this.scene.remove(this.scene.children[0]);
        }
        
        // Load new page
        const newPageMesh = await this.loadPage(toPage);
        
        // Animation setup
        const duration = this.speed;
        const startTime = Date.now();
        
        return new Promise((resolve) => {
            const animate = () => {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Rotate the page
                if (toPage > fromPage) {
                    newPageMesh.rotation.y = Math.PI * (1 - progress);
                } else {
                    newPageMesh.rotation.y = Math.PI * progress;
                }
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    resolve();
                }
            };
            
            animate();
        });
    }
    
    animate() {
        requestAnimationFrame(() => this.animate());
        this.renderer.render(this.scene, this.camera);
    }
}

// Initialize all flipbooks on the page
document.addEventListener('DOMContentLoaded', () => {
    const flipbooks = document.querySelectorAll('.flipbook-container');
    flipbooks.forEach(container => new FlipBook(container));
});
