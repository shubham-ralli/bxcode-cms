<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize - {{ \App\Models\Setting::get('site_title') }}</title>
    <style>
        /* Layout Structure */
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            display: flex;
            height: 100vh;
        }

        #customize-controls {
            width: 300px;
            min-width: 300px;
            height: 100%;
            background: #f0f0f1;
            border-right: 1px solid #dcdcde;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            z-index: 10;
            display: flex;
            flex-direction: column;
            /* Shared Footer Layout */
        }

        .customize-panel-holder {
            width: 200%;
            flex: 1;
            /* Take remaining height */
            display: flex;
            transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            will-change: transform;
            overflow: hidden;
            /* Panels inside handle scroll */
        }

        #customize-main-panel,
        #customize-section-panel {
            width: 50%;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        #customize-preview {
            flex: 1;
            background: #f0f0f1;
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        .customize-header {
            background: #fff;
            border-bottom: 1px solid #dcdcde;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
            height: 60px;
        }

        /* Shared Footer */
        .customize-footer {
            padding: 12px 16px;
            background: #fff;
            border-top: 1px solid #dcdcde;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
            height: 50px;
            z-index: 20;
        }

        .customize-body {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }

        .customize-header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.3;
        }

        .customize-theme-name {
            font-size: 13px;
            color: #646970;
        }

        .close-button {
            text-decoration: none;
            color: #2271b1;
            font-size: 24px;
            line-height: 1;
            width: 24px;
            text-align: center;
        }

        .close-button:hover {
            color: #135e96;
        }

        .btn-primary {
            background: #2271b1;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-primary:active {
            transform: translateY(1px);
        }

        .btn-primary:disabled {
            background: #a7aaad;
            cursor: default;
            transform: none;
        }

        .customize-back-btn {
            background: #1d2327;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .customize-back-btn:hover {
            background: #3c434a;
        }

        .control-section-link {
            background: #fff;
            border-bottom: 1px solid #dcdcde;
            border-left: 4px solid transparent;
            padding: 15px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
            color: #3c434a;
        }

        .control-section-link:hover {
            background: #f6f7f7;
            color: #2271b1;
            border-left-color: #2271b1;
        }

        .control-section-link:after {
            content: '▸';
            color: #a0a5aa;
            font-size: 18px;
        }

        .customize-section-header {
            background: #fff;
            border-bottom: 1px solid #dcdcde;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            height: 50px;
        }

        .customize-section-title {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
        }

        #customize-section-content {
            padding: 20px;
        }

        .customize-control {
            margin-bottom: 24px;
        }

        .customize-control label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 13px;
            color: #1d2327;
        }

        .customize-control input[type="text"],
        .customize-control textarea,
        .customize-control select {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 13px;
            line-height: 1.5;
            box-sizing: border-box;
            color: #2c3338;
        }

        .customize-control input:focus,
        .customize-control textarea:focus,
        .customize-control select:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }

        .customize-control .description {
            font-size: 12px;
            color: #646970;
            margin-top: 6px;
            font-style: italic;
            line-height: 1.4;
        }

        /* Device Toolbar (Now in Footer) */
        .device-toolbar {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .device-btn {
            background: none;
            border: none;
            cursor: pointer;
            opacity: 0.5;
            font-size: 16px;
            padding: 4px;
            transition: opacity 0.2s, color 0.2s;
            color: #50575e;
        }

        .device-btn:hover {
            opacity: 0.8;
        }

        .device-btn.active {
            opacity: 1;
            color: #2271b1;
        }

        #iframe-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            padding: 0;
            transition: all 0.3s;
            background: #dcdcde;
        }

        #preview-frame {
            background: #fff;
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/hint/show-hint.min.css">
    <style>
        /* CodeMirror Customization */
        .CodeMirror {
            height: 400px;
            border: 1px solid #ddd;
            font-size: 13px;
        }

        .CodeMirror-hints {
            z-index: 99999;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Override Tailwind Preflight interactions with Customizer -->
    <style>
        /* Restore Customizer functionality if Tailwind resets mess it up */
        .customize-panel-holder {
            display: flex;
        }

        /* Tailwind preflight might change this */
        input[type="text"],
        textarea,
        select {
            border: 1px solid #8c8f94;
        }

        /* Restore borders */
    </style>
</head>

<body>

    <div id="customize-controls">
        <div class="customize-panel-holder">
            <!-- Main Panel (Section List) -->
            <div id="customize-main-panel">
                <div class="customize-header">
                    <div>
                        <h1>You are customizing</h1>
                        <div class="customize-theme-name">{{ ucfirst(get_active_theme()) }}</div>
                    </div>
                    <a href="{{ $closeUrl }}" class="close-button">&times;</a>
                </div>

                <div class="customize-body">
                    @foreach($customizer->getSections() as $section)
                        <div class="control-section-link"
                            onclick="openSection('{{ $section['id'] }}', '{{ addslashes($section['title']) }}')">
                            {{ $section['title'] }}
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Section Panel (Controls) -->
            <div id="customize-section-panel">
                <div class="customize-section-header">
                    <button class="customize-back-btn" onclick="closeSection()">❮ Back</button>
                    <h3 class="customize-section-title" id="active-section-title">Section Title</h3>
                </div>

                <div class="customize-body" id="customize-section-content">
                    <!-- Controls injected here via JS -->
                </div>
            </div>
        </div>

        <!-- Shared Footer -->
        <div class="customize-footer">
            <div class="device-toolbar">
                <button class="device-btn active" onclick="setDevice('desktop')" title="Desktop">🖥</button>
                <button class="device-btn" onclick="setDevice('tablet')" title="Tablet">📱</button>
                <button class="device-btn" onclick="setDevice('mobile')" title="Mobile">iphone</button>
            </div>

            <div style="display:flex; align-items:center; gap:10px;">
                <span id="save-status"
                    style="display:none; color: #2271b1; font-size: 11px; font-weight: 600;">Saved!</span>
                <button class="btn-primary" onclick="saveSettings()">Publish</button>
            </div>
        </div>
    </div>

    <div id="customize-preview">
        <div id="iframe-wrapper">
            <iframe src="{{ $url }}" id="preview-frame"></iframe>
        </div>
    </div>

    <!-- HIDDEN TEMPLATES FOR SECTIONS -->
    @foreach($customizer->getSections() as $section)
        <template id="tmpl-section-{{ $section['id'] }}">
            @if(!empty($section['description']))
                <p class="description" style="margin-bottom:20px;">{{ $section['description'] }}</p>
            @endif

            @foreach($customizer->getControls($section['id']) as $control)
                <div class="customize-control customize-control-{{ $control['type'] }}">
                    <label for="{{ $control['id'] }}">{{ $control['label'] }}</label>

                    @if($control['type'] === 'text')
                        <input type="text" id="{{ $control['id'] }}"
                            value="{{ \App\Models\Setting::get($control['id'], $control['default']) }}"
                            data-customize-setting-link="{{ $control['id'] }}">

                    @elseif($control['type'] === 'textarea')
                        <textarea id="{{ $control['id'] }}" rows="5"
                            data-customize-setting-link="{{ $control['id'] }}">{{ \App\Models\Setting::get($control['id'], $control['default']) }}</textarea>

                    @elseif($control['type'] === 'select')
                        <select id="{{ $control['id'] }}" data-customize-setting-link="{{ $control['id'] }}">
                            @foreach($control['choices'] as $val => $label)
                                <option value="{{ $val }}" {{ \App\Models\Setting::get($control['id'], $control['default']) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>

                    @elseif($control['type'] === 'checkbox')
                        <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
                            @php $isChecked = \App\Models\Setting::get($control['id'], $control['default']); @endphp
                            <input type="checkbox" id="{{ $control['id'] }}" {{ $isChecked ? 'checked' : '' }}
                                data-customize-setting-link="{{ $control['id'] }}" style="width: auto; margin: 0;">
                            <label for="{{ $control['id'] }}" style="margin:0; font-weight: 400;">{{ $control['label'] }}</label>
                        </div>

                    @elseif($control['type'] === 'range')
                        <div>
                            <input type="range" id="{{ $control['id'] }}" min="{{ $control['min'] ?? 0 }}"
                                max="{{ $control['max'] ?? 100 }}" step="{{ $control['step'] ?? 1 }}"
                                value="{{ \App\Models\Setting::get($control['id'], $control['default']) }}"
                                data-customize-setting-link="{{ $control['id'] }}"
                                style="width: 80%; display: inline-block; vertical-align: middle;">
                            <span style="width: 15%; display: inline-block; text-align: right; font-size: 12px; color: #555;">
                                {{ \App\Models\Setting::get($control['id'], $control['default']) }}
                            </span>
                            <script>
                                // Simple sync for the number display
                                document.getElementById('{{ $control['id'] }}').addEventListener('input', function (e) {
                                    e.target.nextElementSibling.textContent = e.target.value;
                                });
                            </script>
                        </div>

                    @elseif($control['type'] === 'image')
                        <div class="customize-image-control">
                            @php $imgVal = \App\Models\Setting::get($control['id'], $control['default']); @endphp

                            <input type="hidden" id="{{ $control['id'] }}" value="{{ $imgVal }}"
                                data-customize-setting-link="{{ $control['id'] }}">

                            <!-- Preview Area -->
                            <div class="image-preview-wrapper" style="{{ $imgVal ? '' : 'display:none;' }}">
                                <div class="image-preview" style="margin-bottom: 10px;">
                                    <img src="{{ $imgVal }}" id="preview-{{ $control['id'] }}"
                                        style="max-width: 100%; border: 1px solid #ddd; padding: 4px; border-radius: 4px; background: #eee;">
                                </div>
                                <div style="display:flex; gap: 10px;">
                                    <button class="btn-secondary"
                                        onclick="openMediaPicker('{{ $control['id'] }}', 'preview-{{ $control['id'] }}')">Change
                                        Image</button>
                                    <button class="btn-link-delete" onclick="removeImage('{{ $control['id'] }}')"
                                        style="color: #d63638; text-decoration: underline; border: none; background: none; cursor: pointer;">Remove</button>
                                </div>
                            </div>

                            <!-- Initial Select Button -->
                            <div class="image-select-wrapper" style="{{ $imgVal ? 'display:none;' : '' }}">
                                <button class="btn-secondary"
                                    style="background: #f6f7f7; color: #2271b1; border: 1px solid #2271b1; padding: 6px 12px; border-radius: 3px; cursor: pointer;"
                                    onclick="openMediaPicker('{{ $control['id'] }}', 'preview-{{ $control['id'] }}')">
                                    Select Image
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(!empty($control['description']))
                        <p class="description">{{ $control['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </template>
    @endforeach

    @include('admin.partials.media-picker-modal')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
    <!-- ... rest of scripts ... -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/hint/show-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/hint/css-hint.min.js"></script>

    <script>
        window.saveUrl = '{{ route('admin.customize.save') }}';
        window.csrfToken = '{{ csrf_token() }}';

        // Initial State
        window.customizerInitialState = {
            @foreach($customizer->getSections() as $section)
                @foreach($customizer->getControls($section['id']) as $control)
                    '{{ $control['id'] }}': `{!! addslashes(\App\Models\Setting::get($control['id'], $control['default'])) !!}`,
                @endforeach
            @endforeach
    };
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Panel Sliding Logic
            const panelHolder = document.querySelector('.customize-panel-holder');
            const sectionTitle = document.getElementById('active-section-title');
            const sectionsContainer = document.getElementById('customize-section-content');
            let activeEditor = null; // Store CodeMirror instance

            window.openSection = function (sectionId, title) {
                // Destroy existing editor if any
                if (activeEditor) {
                    activeEditor.toTextArea();
                    activeEditor = null;
                }

                // Find the section content template
                const template = document.getElementById('tmpl-section-' + sectionId);
                if (!template) return;

                // Update Title
                sectionTitle.textContent = title;

                // Clear previous content
                sectionsContainer.innerHTML = '';

                // Clone and append content
                const clone = template.content.cloneNode(true);
                sectionsContainer.appendChild(clone);

                // Re-bind listeners for new elements
                bindControlListeners();

                // Initialize CodeMirror for Custom CSS
                if (sectionId === 'custom_css') {
                    const textarea = document.getElementById('custom_css');
                    if (textarea) {
                        activeEditor = CodeMirror.fromTextArea(textarea, {
                            mode: 'css',
                            theme: 'dracula',
                            lineNumbers: true,
                            extraKeys: { "Ctrl-Space": "autocomplete" }
                        });

                        // Sync on change
                        activeEditor.on('change', function (cm) {
                            const val = cm.getValue();
                            textarea.value = val; // Sync to hidden textarea for save

                            // Trigger Live Preview Update
                            updateLivePreview('custom_css', val);

                            // Update State
                            window.customizerState['custom_css'] = val;
                        });

                        // Trigger autocomplete on typing
                        activeEditor.on('inputRead', function (cm, change) {
                            if (change.origin !== "+delete" && change.text[0] !== ";" && change.text[0] !== "}" && change.text[0] !== ")") {
                                cm.showHint({ hint: CodeMirror.hint.css, completeSingle: false });
                            }
                        });
                    }
                }

                // Slide
                panelHolder.style.transform = 'translateX(-50%)';
            };

            window.closeSection = function () {
                // Slide back
                panelHolder.style.transform = 'translateX(0)';
                setTimeout(() => {
                    // Cleanup editor after slide back
                    if (activeEditor) {
                        activeEditor.toTextArea();
                        activeEditor = null;
                    }
                }, 300);
            };

            window.saveSettings = async function () {
                const btn = document.querySelector('.btn-primary');
                const originalText = btn.textContent;
                btn.textContent = 'Publishing...';
                btn.disabled = true;

                // Ensure CodeMirror content is synced before save
                if (activeEditor) {
                    window.customizerState['custom_css'] = activeEditor.getValue();
                }

                // Initialize state if empty
                if (!window.customizerState) window.customizerState = {};

                // Sync current DOM inputs to state (excluding textarea as CodeMirror handles it)
                document.querySelectorAll('#customize-section-content input, #customize-section-content select').forEach(input => {
                    if (input.id) {
                        if (input.type === 'checkbox') {
                            window.customizerState[input.id] = input.checked ? "1" : "0";
                        } else {
                            window.customizerState[input.id] = input.value;
                        }
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
                // Bind live preview listeners for standard inputs
                document.querySelectorAll('[data-customize-setting-link]').forEach(input => {
                    // Skip custom_css as it's handled by CodeMirror
                    if (input.id === 'custom_css') return;

                    input.addEventListener('input', function () {
                        const settingId = this.getAttribute('data-customize-setting-link');
                        const val = this.value;

                        // Update State
                        window.customizerState[settingId] = val;

                        // Transport to Iframe
                        updateLivePreview(settingId, val);
                    });
                });
            }

            function updateLivePreview(settingId, val) {
                const iframe = document.getElementById('preview-frame');
                const targetWindow = iframe.contentWindow;
                const doc = targetWindow.document;

                // Specific handling for common types
                if (settingId === 'custom_css') {
                    // Direct manipulation for CSS
                    let style = doc.getElementById('bx-custom-css');
                    if (!style) {
                        style = doc.createElement('style');
                        style.id = 'bx-custom-css';
                        doc.head.appendChild(style);
                    }
                    style.textContent = val;
                }
                else if (settingId === 'site_title') {
                    const el = doc.querySelector('.logo-text');
                    if (el) el.textContent = val;
                }
                else if (settingId === 'tagline') {
                    const el = doc.querySelector('.page-subtitle');
                    if (el) el.textContent = val;
                }
                else if (settingId === 'logo_width') {
                    const logo = doc.querySelector('.custom-logo');
                    if (logo) logo.style.width = val + 'px';
                }
                else if (settingId === 'site_logo_alt') {
                    const logo = doc.querySelector('.custom-logo');
                    if (logo) logo.alt = val;
                }
                else if (settingId === 'site_logo_title') {
                    const logo = doc.querySelector('.custom-logo');
                    if (logo) logo.title = val;
                }
                else if (settingId === 'site_icon') {
                    let link = doc.querySelector('link[rel="icon"]');
                    if (!link) {
                        link = doc.createElement('link');
                        link.rel = 'icon';
                        doc.head.appendChild(link);
                    }
                    link.href = val;
                }
                else {
                    // Generic handling: Try to find element with matching class or ID
                    // This is a "best effort" for other generic settings if they match class names
                    // e.g. setting 'footer_text' -> tries to find .footer-text
                    const genericClass = '.' + settingId.replace(/_/g, '-');
                    const el = doc.querySelector(genericClass);
                    if (el) el.textContent = val;
                }
            }

            // Initialize State
            window.customizerState = window.customizerInitialState || {};

            // Restore device switching
            window.setDevice = function (device) {
                const iframe = document.getElementById('preview-frame');
                const btns = document.querySelectorAll('.device-btn');

                btns.forEach(b => b.classList.remove('active'));
                // Map device to title for selection
                let titleMap = {
                    'desktop': 'Desktop',
                    'tablet': 'Tablet',
                    'mobile': 'Mobile'
                };

                btns.forEach(b => {
                    if (b.title === titleMap[device]) {
                        b.classList.add('active');
                    }
                });

                if (device === 'desktop') {
                    iframe.style.width = '100%';
                } else if (device === 'tablet') {
                    iframe.style.width = '768px';
                } else if (device === 'mobile') {
                    iframe.style.width = '375px';
                }
            };

            // Set Default Device
            setDevice('desktop');

            // --- Media Picker Integration ---
            window.mediaPickerCallback = function (inputId, previewId) {
                // The modal sets the input value to the ID. We want the URL for the frontend usually.
                // Or we can store the ID and resolve it. But typically Customizers store URLs for simplicity or both.
                // Let's grab the URL from the global variable in the modal script 'pickerSelectedUrl' if accessible.
                // Since the modal script is just included, its vars are global.

                if (typeof pickerSelectedUrl !== 'undefined' && pickerSelectedUrl) {
                    const input = document.getElementById(inputId);
                    input.value = pickerSelectedUrl; // Store URL instead of ID

                    // Update UI (Switch to preview mode)
                    const wrapper = input.closest('.customize-image-control');
                    wrapper.querySelector('.image-preview-wrapper').style.display = 'block';
                    wrapper.querySelector('.image-select-wrapper').style.display = 'none';

                    // Trigger Input Event for Live Preview
                    input.dispatchEvent(new Event('input'));
                }
            };

            window.removeImage = function (controlId) {
                const input = document.getElementById(controlId);
                input.value = '';

                // Update UI (Switch to select mode)
                const wrapper = input.closest('.customize-image-control');
                wrapper.querySelector('.image-preview-wrapper').style.display = 'none';
                wrapper.querySelector('.image-select-wrapper').style.display = 'block';

                // Trigger Input Event for Live Preview
                input.dispatchEvent(new Event('input'));
            };
        });
    </script>

</body>

</html>