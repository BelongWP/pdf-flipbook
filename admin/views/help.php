<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap pdf-flipbook-help">
    <h1>PDF Flipbook Documentation</h1>

    <div class="pdf-flipbook-help-section">
        <h2>Creating a New Flipbook</h2>
        <ol>
            <li>
                <strong>Create New Flipbook:</strong>
                <ul>
                    <li>Click "Add New" in the PDF Flipbooks menu</li>
                    <li>Enter a title for your flipbook</li>
                    <li>Upload your PDF file in the "PDF Document" section</li>
                </ul>
            </li>
            <li>
                <strong>Configure Document Organization:</strong>
                <ul>
                    <li>Choose a document type (e.g., newsletter, report)</li>
                    <li>For newsletters: Enter year, month, and issue number</li>
                    <li>For custom paths: Enter your desired directory structure</li>
                </ul>
            </li>
            <li>
                <strong>Adjust Display Settings:</strong>
                <ul>
                    <li>Set page turn animation speed (500ms to 2000ms)</li>
                    <li>Enable/disable thumbnail navigation</li>
                </ul>
            </li>
            <li>
                <strong>Publish:</strong>
                <ul>
                    <li>Click "Publish" to save your flipbook</li>
                    <li>Copy the shortcode provided in the Usage Instructions section</li>
                </ul>
            </li>
        </ol>
    </div>

    <div class="pdf-flipbook-help-section">
        <h2>Organizing Your Documents</h2>
        <p>The plugin provides two ways to organize your PDF files:</p>

        <h3>1. Predefined Document Types</h3>
        <ul>
            <li><strong>Newsletters:</strong> Automatically organized by year/month/issue
                <br><em>Example path: newsletter/2024/01/issue-123</em>
            </li>
            <li><strong>Reports:</strong> Organized by year and type
                <br><em>Example path: reports/annual/2024</em>
            </li>
            <li><strong>Custom Types:</strong> Add your own document types in Settings</li>
        </ul>

        <h3>2. Custom Paths</h3>
        <p>Create your own directory structure using these variables:</p>
        <ul>
            <li><code>{year}</code> - Document year (YYYY)</li>
            <li><code>{month}</code> - Document month (MM)</li>
            <li><code>{issue}</code> - Issue number</li>
            <li><code>{title}</code> - Document title</li>
            <li><code>{id}</code> - Document ID</li>
        </ul>
    </div>

    <div class="pdf-flipbook-help-section">
        <h2>Using Your Flipbook</h2>
        
        <h3>Shortcode Usage</h3>
        <p>Add your flipbook to any post or page using the shortcode:</p>
        <code>[flipbook id="X"]</code>
        <p>Replace "X" with your flipbook's ID number.</p>

        <h3>Examples:</h3>
        <ul>
            <li><strong>Basic usage:</strong><br>
                <code>[flipbook id="123"]</code>
            </li>
        </ul>

        <h3>Navigation Options</h3>
        <p>Readers can navigate your flipbook using:</p>
        <ul>
            <li>Previous/Next buttons</li>
            <li>Keyboard arrow keys</li>
            <li>Click and drag to turn pages</li>
            <li>Thumbnail navigation (if enabled)</li>
            <li>Touch swipe on mobile devices</li>
        </ul>
    </div>

    <div class="pdf-flipbook-help-section">
        <h2>Managing Flipbooks</h2>
        
        <h3>Editing Existing Flipbooks</h3>
        <ol>
            <li>Go to PDF Flipbooks → All Flipbooks</li>
            <li>Click on the flipbook title to edit</li>
            <li>Make your changes</li>
            <li>Click "Update" to save</li>
        </ol>

        <h3>Updating PDFs</h3>
        <p>When updating a PDF file:</p>
        <ol>
            <li>Edit the flipbook</li>
            <li>In the PDF Document section, remove the existing file</li>
            <li>Upload the new PDF</li>
            <li>Update the flipbook</li>
        </ol>
    </div>

    <div class="pdf-flipbook-help-section">
        <h2>Best Practices</h2>
        
        <h3>PDF Preparation</h3>
        <ul>
            <li>Optimize PDFs for web viewing before upload</li>
            <li>Keep file sizes under 10MB for best performance</li>
            <li>Use PDF version 1.4 or later</li>
            <li>Ensure text is properly embedded for accessibility</li>
        </ul>

        <h3>Organization Tips</h3>
        <ul>
            <li>Use consistent naming conventions for custom paths</li>
            <li>Include the year in paths for easier archival</li>
            <li>Use descriptive titles for better searchability</li>
        </ul>
    </div>

    <div class="pdf-flipbook-help-section">
        <h2>Troubleshooting</h2>
        
        <h3>Common Issues</h3>
        <dl>
            <dt>Flipbook not displaying:</dt>
            <dd>
                <ul>
                    <li>Verify the shortcode ID is correct</li>
                    <li>Check if the PDF file exists in the uploads directory</li>
                    <li>Ensure the PDF is properly uploaded and processed</li>
                </ul>
            </dd>

            <dt>Pages not turning smoothly:</dt>
            <dd>
                <ul>
                    <li>Try adjusting the animation speed in display settings</li>
                    <li>Optimize your PDF file size</li>
                    <li>Check for browser console errors</li>
                </ul>
            </dd>

            <dt>Upload errors:</dt>
            <dd>
                <ul>
                    <li>Verify your PDF file size is within WordPress limits</li>
                    <li>Check directory permissions in wp-content/uploads</li>
                    <li>Ensure your PDF isn't corrupted</li>
                </ul>
            </dd>
        </dl>
    </div>
</div>