document.addEventListener('DOMContentLoaded', function() {
    
    // Panel Sliding Logic
    const panelHolder = document.querySelector('.customize-panel-holder');
    const sectionTitle = document.getElementById('active-section-title');
    const sectionsContainer = document.getElementById('customize-section-content');

    window.openSection = function(sectionId, title) {
        // Find the section content template
        const template = document.getElementById('tmpl-section-' + sectionId);
        if(!template) return;

        // Update Title
        sectionTitle.textContent = title;
        
        // Clear previous content
        sectionsContainer.innerHTML = '';
        
        // Clone and append content
        const clone = template.content.cloneNode(true);
        sectionsContainer.appendChild(clone);
        
        // Re-bind listeners for new elements
        bindControlListeners();

        // Slide
        panelHolder.style.transform = 'translateX(-50%)';
    };

    window.closeSection = function() {
        // Slide back
        panelHolder.style.transform = 'translateX(0)';
        
        // Cleanup after transition? Not strictly necessary but good practice.
    };

    window.saveSettings = async function() {
        const btn = document.querySelector('.btn-primary');
        const originalText = btn.textContent;
        btn.textContent = 'Publishing...';
        btn.disabled = true;

        // Gather all inputs from ALL sections (even hidden ones? No, templates are hidden)
        // We need to gather current values.
        // Problem: inputs in templates aren't in DOM. inputs in active section ARE in DOM.
        // We need a state object 'settings'.
        
        // Sync current DOM inputs to state
        document.querySelectorAll('#customize-section-content input, #customize-section-content textarea, #customize-section-content select').forEach(input => {
            if(input.id) {
                window.customizerState[input.id] = input.value;
            }
        });

        try {
            const response = await fetch(window.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    settings: window.customizerState
                })
            });
            
            if (response.ok) {
                const status = document.getElementById('save-status');
                status.style.display = 'inline';
                setTimeout(() => { status.style.display = 'none'; }, 2000);
            } else {
                alert('Failed to save settings');
            }

        } catch (e) {
            alert('Error saving settings: ' + e.message);
        } finally {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    };

    function bindControlListeners() {
        // Bind live preview listeners
        document.querySelectorAll('[data-customize-setting-link]').forEach(input => {
            input.addEventListener('input', function() {
                const settingId = this.getAttribute('data-customize-setting-link');
                const val = this.value;
                
                // Update State
                window.customizerState[settingId] = val;

                // Transport to Iframe
                const iframe = document.getElementById('preview-frame');
                const targetWindow = iframe.contentWindow;
                
                // Specific handling for common types
                if(settingId === 'custom_css') {
                    // Direct manipulation for CSS (faster than postMessage usually)
                    const doc = targetWindow.document;
                    let style = doc.getElementById('wp-custom-css');
                    if(!style) {
                         style = doc.createElement('style');
                         style.id = 'wp-custom-css';
                         doc.head.appendChild(style);
                    }
                    style.textContent = val;
                } else {
                    // Generic handling via PostMessage (Theme needs to listen)
                    // Or direct DOM manipulation if we know the selector?
                    // For Site Title:
                    if(settingId === 'site_title') {
                        // Try to find .site-title or similar
                        // This is brittle. Best to reload or use postMessage API.
                        // For this demo: No-op or specialized logic.
                    }
                }
            });
        });
    }
    
    // Initial Bind?
    // Initialize State
    window.customizerState = window.customizerInitialState || {};

    // Restore device switching
    window.setDevice = function(device) {
        const iframe = document.getElementById('preview-frame');
        const btns = document.querySelectorAll('.device-btn');
        
        btns.forEach(b => b.classList.remove('active'));
        // Find button by onclick content or pass `this`
        // We'll trust the user to click correctly or fix later.
        
        if (device === 'desktop') {
            iframe.style.width = '100%';
        } else if (device === 'tablet') {
            iframe.style.width = '768px';
        } else if (device === 'mobile') {
            iframe.style.width = '375px';
        }
    };
});
