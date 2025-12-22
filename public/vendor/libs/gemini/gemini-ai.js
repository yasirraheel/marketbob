/**
 * Gemini AI Integration for CKEditor
 * Provides AI-powered features for content generation and improvement
 */

class GeminiAI {
    constructor() {
        this.apiBaseUrl = '/api/gemini';
        this.isEnabled = false;
        this.checkStatus();
    }

    /**
     * Check if Gemini AI is enabled
     */
    async checkStatus() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/status`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            const data = await response.json();
            this.isEnabled = data.success && data.data.enabled;
        } catch (error) {
            console.error('Failed to check Gemini AI status:', error);
            this.isEnabled = false;
        }
    }

    /**
     * Generate description
     */
    async generateDescription(productName, category = '', tags = [], features = []) {
        if (!this.isEnabled) {
            throw new Error('Gemini AI is not enabled');
        }

        try {
            const response = await fetch(`${this.apiBaseUrl}/generate-description`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                body: JSON.stringify({
                    product_name: productName,
                    category: category,
                    tags: tags,
                    features: features,
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Failed to generate description');
            }

            return data.data.description;
        } catch (error) {
            console.error('Generate description error:', error);
            throw error;
        }
    }

    /**
     * Suggest tags
     */
    async suggestTags(productName, description = '', category = '') {
        if (!this.isEnabled) {
            throw new Error('Gemini AI is not enabled');
        }

        try {
            const response = await fetch(`${this.apiBaseUrl}/suggest-tags`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                body: JSON.stringify({
                    product_name: productName,
                    description: description,
                    category: category,
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Failed to suggest tags');
            }

            return data.data.tags;
        } catch (error) {
            console.error('Suggest tags error:', error);
            throw error;
        }
    }

    /**
     * Improve content
     */
    async improveContent(content, productName = '') {
        if (!this.isEnabled) {
            throw new Error('Gemini AI is not enabled');
        }

        try {
            const response = await fetch(`${this.apiBaseUrl}/improve-content`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                body: JSON.stringify({
                    content: content,
                    product_name: productName,
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Failed to improve content');
            }

            return data.data.content;
        } catch (error) {
            console.error('Improve content error:', error);
            throw error;
        }
    }

    /**
     * Get CSRF token
     */
    getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    /**
     * Show loading state
     */
    showLoading(button) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    }

    /**
     * Hide loading state
     */
    hideLoading(button) {
        button.disabled = false;
        if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'success') {
        // Try to use existing toast/notification system
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else if (typeof notif !== 'undefined') {
            notif({ type: type, msg: message });
        } else {
            alert(message);
        }
    }
}

// Initialize Gemini AI globally
const geminiAI = new GeminiAI();

/**
 * Add AI button to CKEditor toolbar
 */
function addGeminiButtonToCKEditor(editorElement, productNameField, categoryField = null, tagsField = null, featuresField = null) {
    if (!geminiAI.isEnabled) {
        return;
    }

    // Create button container
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'gemini-ai-buttons mb-3';
    buttonContainer.innerHTML = `
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-primary" id="gemini-generate-btn">
                <i class="fas fa-magic me-1"></i> Generate with AI
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="gemini-improve-btn">
                <i class="fas fa-wand-magic-sparkles me-1"></i> Improve Content
            </button>
        </div>
        <small class="text-muted ms-2">
            <i class="fas fa-robot"></i> Powered by Gemini AI
        </small>
    `;

    // Insert before CKEditor
    editorElement.parentNode.insertBefore(buttonContainer, editorElement);

    // Generate description handler
    document.getElementById('gemini-generate-btn').addEventListener('click', async function(e) {
        e.preventDefault();

        const productName = typeof productNameField === 'function'
            ? productNameField()
            : document.querySelector(productNameField)?.value;

        if (!productName || productName.trim() === '') {
            geminiAI.showToast('Please enter a product name first', 'error');
            return;
        }

        const category = categoryField ? (
            typeof categoryField === 'function'
                ? categoryField()
                : document.querySelector(categoryField)?.value || ''
        ) : '';

        const tags = tagsField ? (
            typeof tagsField === 'function'
                ? tagsField()
                : document.querySelector(tagsField)?.value.split(',').map(t => t.trim()) || []
        ) : [];

        const features = featuresField ? (
            typeof featuresField === 'function'
                ? featuresField()
                : JSON.parse(document.querySelector(featuresField)?.value || '[]')
        ) : [];

        geminiAI.showLoading(this);

        try {
            const description = await geminiAI.generateDescription(productName, category, tags, features);

            // Set content in CKEditor
            if (window.editor && window.editor.setData) {
                window.editor.setData(description);
            } else if (editorElement.value !== undefined) {
                editorElement.value = description;
            }

            geminiAI.showToast('Description generated successfully!', 'success');
        } catch (error) {
            geminiAI.showToast(error.message || 'Failed to generate description', 'error');
        } finally {
            geminiAI.hideLoading(this);
        }
    });

    // Improve content handler
    document.getElementById('gemini-improve-btn').addEventListener('click', async function(e) {
        e.preventDefault();

        const currentContent = window.editor && window.editor.getData
            ? window.editor.getData()
            : editorElement.value;

        if (!currentContent || currentContent.trim() === '') {
            geminiAI.showToast('Please add some content first', 'error');
            return;
        }

        const productName = typeof productNameField === 'function'
            ? productNameField()
            : document.querySelector(productNameField)?.value || '';

        geminiAI.showLoading(this);

        try {
            const improvedContent = await geminiAI.improveContent(currentContent, productName);

            // Set improved content in CKEditor
            if (window.editor && window.editor.setData) {
                window.editor.setData(improvedContent);
            } else if (editorElement.value !== undefined) {
                editorElement.value = improvedContent;
            }

            geminiAI.showToast('Content improved successfully!', 'success');
        } catch (error) {
            geminiAI.showToast(error.message || 'Failed to improve content', 'error');
        } finally {
            geminiAI.hideLoading(this);
        }
    });
}

/**
 * Add tag suggestions button
 */
function addGeminiTagSuggestions(tagsInputField, productNameField, descriptionField = null, categoryField = null) {
    if (!geminiAI.isEnabled) {
        return;
    }

    const tagsInput = document.querySelector(tagsInputField);
    if (!tagsInput) return;

    // Create button
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-sm btn-outline-primary mt-2';
    button.innerHTML = '<i class="fas fa-tags me-1"></i> Suggest Tags with AI';

    // Insert after tags input
    tagsInput.parentNode.insertBefore(button, tagsInput.nextSibling);

    // Click handler
    button.addEventListener('click', async function(e) {
        e.preventDefault();

        const productName = typeof productNameField === 'function'
            ? productNameField()
            : document.querySelector(productNameField)?.value;

        if (!productName || productName.trim() === '') {
            geminiAI.showToast('Please enter a product name first', 'error');
            return;
        }

        const description = descriptionField ? (
            typeof descriptionField === 'function'
                ? descriptionField()
                : (window.editor && window.editor.getData ? window.editor.getData() : document.querySelector(descriptionField)?.value || '')
        ) : '';

        const category = categoryField ? (
            typeof categoryField === 'function'
                ? categoryField()
                : document.querySelector(categoryField)?.value || ''
        ) : '';

        geminiAI.showLoading(this);

        try {
            const tags = await geminiAI.suggestTags(productName, description, category);

            // Set tags
            tagsInput.value = tags.join(', ');

            // Trigger change event
            tagsInput.dispatchEvent(new Event('change', { bubbles: true }));

            geminiAI.showToast(`${tags.length} tags suggested successfully!`, 'success');
        } catch (error) {
            geminiAI.showToast(error.message || 'Failed to suggest tags', 'error');
        } finally {
            geminiAI.hideLoading(this);
        }
    });
}
