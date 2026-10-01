@extends('backEnd.layouts.master')
@section('title', 'Add New Code Snippet')

@section('css')
<!-- CodeMirror CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/monokai.min.css" />
<style>
    .CodeMirror {
        height: 320px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-family: 'Fira Code', 'Courier New', Courier, monospace;
        font-size: 14px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('snippets.index')}}" class="btn btn-primary rounded-pill"><i class="fe-list me-1"></i> Manage Snippets</a>
                </div>
                <h4 class="page-title">Add New Code Snippet</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{route('snippets.store')}}" method="POST" id="snippet-form">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Snippet Title / Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" id="title" value="{{ old('title') }}" placeholder="e.g. Google Analytics 4 (GA4), TikTok Pixel, Custom Theme Styles" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="type" class="form-label">Code Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="html" {{ old('type') == 'html' ? 'selected' : '' }}>HTML / Mixed Script</option>
                                    <option value="javascript" {{ old('type') == 'javascript' ? 'selected' : '' }}>JavaScript (&lt;script&gt;)</option>
                                    <option value="css" {{ old('type') == 'css' ? 'selected' : '' }}>CSS (&lt;style&gt;)</option>
                                    <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>Plain Text</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="location" class="form-label">Insertion Location <span class="text-danger">*</span></label>
                                <select name="location" id="location" class="form-select @error('location') is-invalid @enderror" required>
                                    <option value="head" {{ old('location') == 'head' ? 'selected' : '' }}>Header (&lt;head&gt;...&lt;/head&gt;)</option>
                                    <option value="body_open" {{ old('location') == 'body_open' ? 'selected' : '' }}>Body Open (Immediately after &lt;body&gt;)</option>
                                    <option value="footer" {{ old('location') == 'footer' ? 'selected' : '' }}>Footer (Immediately before &lt;/body&gt;)</option>
                                </select>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Code editor area -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="code" class="form-label mb-0">Code / Script Payload <span class="text-danger">*</span></label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-sample-ga4">Preset: GA4</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-sample-clarity">Preset: Clarity</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-sample-css">Preset: Custom CSS</button>
                                </div>
                            </div>
                            <textarea name="code" id="code" class="form-control @error('code') is-invalid @enderror" rows="10">{{ old('code') }}</textarea>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Enter valid HTML, CSS (wrapped in &lt;style&gt; or auto-wrapped), or JS (wrapped in &lt;script&gt; or auto-wrapped).</small>
                        </div>

                        <!-- Conditional Logic Section -->
                        <div class="card bg-light border p-3 mb-3">
                            <h5 class="card-title font-16 mb-2"><i class="fe-filter me-1 text-primary"></i> Smart Conditional Logic (Execution Rules)</h5>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label for="target_pages" class="form-label">Target Pages</label>
                                    <select name="target_pages" id="target_pages" class="form-select">
                                        <option value="all" {{ old('target_pages') == 'all' ? 'selected' : '' }}>All Pages (Sitewide)</option>
                                        <option value="homepage" {{ old('target_pages') == 'homepage' ? 'selected' : '' }}>Homepage Only</option>
                                        <option value="checkout" {{ old('target_pages') == 'checkout' ? 'selected' : '' }}>Checkout Page Only</option>
                                        <option value="thank_you" {{ old('target_pages') == 'thank_you' ? 'selected' : '' }}>Order Success / Thank You Page</option>
                                        <option value="custom" {{ old('target_pages') == 'custom' ? 'selected' : '' }}>Custom Specific URLs</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label for="device_target" class="form-label">Device Target</label>
                                    <select name="device_target" id="device_target" class="form-select">
                                        <option value="all" {{ old('device_target') == 'all' ? 'selected' : '' }}>All Devices (Desktop & Mobile)</option>
                                        <option value="desktop" {{ old('device_target') == 'desktop' ? 'selected' : '' }}>Desktop Only</option>
                                        <option value="mobile" {{ old('device_target') == 'mobile' ? 'selected' : '' }}>Mobile Only</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label for="auth_condition" class="form-label">User Authentication</label>
                                    <select name="auth_condition" id="auth_condition" class="form-select">
                                        <option value="all" {{ old('auth_condition') == 'all' ? 'selected' : '' }}>All Visitors (Guests & Logged-in)</option>
                                        <option value="logged_in" {{ old('auth_condition') == 'logged_in' ? 'selected' : '' }}>Logged-in Users Only</option>
                                        <option value="guest" {{ old('auth_condition') == 'guest' ? 'selected' : '' }}>Guests / Non-logged-in Only</option>
                                    </select>
                                </div>

                                <div class="col-12 mt-2 {{ old('target_pages') == 'custom' ? '' : 'd-none' }}" id="custom-urls-wrapper">
                                    <label for="custom_page_urls" class="form-label">Custom URL Paths (comma-separated, wildcards allowed like <code>product/*</code>)</label>
                                    <input type="text" name="custom_page_urls" id="custom_page_urls" class="form-control" value="{{ old('custom_page_urls') }}" placeholder="e.g. checkout, product/*, category/electronics">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="priority" class="form-label">Priority Order <small class="text-muted">(Lower = runs earlier)</small></label>
                                <input type="number" name="priority" id="priority" class="form-control" value="{{ old('priority', 10) }}" min="1" max="9999">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" class="form-check-input" id="status" name="status" value="1" {{ old('status', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status">Active (Enabled)</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="description" class="form-label">Internal Description / Notes</label>
                                <input type="text" name="description" id="description" class="form-control" value="{{ old('description') }}" placeholder="e.g. Added by Marketing team for Q3 campaign tracking">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success"><i class="fe-check me-1"></i> Save Snippet</button>
                            <a href="{{route('snippets.index')}}" class="btn btn-secondary ms-1">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<!-- CodeMirror JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var textarea = document.getElementById('code');
        var editor = CodeMirror.fromTextArea(textarea, {
            lineNumbers: true,
            mode: "htmlmixed",
            theme: "monokai",
            matchBrackets: true,
            indentUnit: 4,
            tabSize: 4,
            lineWrapping: true
        });

        // Sync CodeMirror content back to hidden textarea in real-time
        editor.on('change', function () {
            editor.save();
        });

        // Sync mode on type select
        var typeSelect = document.getElementById('type');
        typeSelect.addEventListener('change', function () {
            var val = this.value;
            if (val === 'javascript') {
                editor.setOption('mode', 'javascript');
            } else if (val === 'css') {
                editor.setOption('mode', 'css');
            } else {
                editor.setOption('mode', 'htmlmixed');
            }
        });

        // Custom URLs toggle
        var targetPagesSelect = document.getElementById('target_pages');
        var customUrlsWrapper = document.getElementById('custom-urls-wrapper');
        targetPagesSelect.addEventListener('change', function () {
            if (this.value === 'custom') {
                customUrlsWrapper.classList.remove('d-none');
            } else {
                customUrlsWrapper.classList.add('d-none');
            }
        });

        // Presets
        document.getElementById('btn-sample-ga4').addEventListener('click', function () {
            document.getElementById('title').value = 'Google Analytics 4 (GA4)';
            document.getElementById('type').value = 'html';
            document.getElementById('location').value = 'head';
            editor.setValue("<!-- Google tag (gtag.js) -->\n<script async src=\"https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX\"><\/script>\n<script>\n  window.dataLayer = window.dataLayer || [];\n  function gtag(){dataLayer.push(arguments);}\n  gtag('js', new Date());\n  gtag('config', 'G-XXXXXXXXXX');\n<\/script>");
            editor.save();
        });

        document.getElementById('btn-sample-clarity').addEventListener('click', function () {
            document.getElementById('title').value = 'Microsoft Clarity';
            document.getElementById('type').value = 'javascript';
            document.getElementById('location').value = 'head';
            editor.setValue("(function(c,l,a,r,i,t,y){\n    c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};\n    t=l.createElement(r);t.async=1;t.src=\"https://www.clarity.ms/tag/\"+i;\n    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);\n})(window, document, \"clarity\", \"script\", \"YOUR_CLARITY_PROJECT_ID\");");
            editor.save();
        });

        document.getElementById('btn-sample-css').addEventListener('click', function () {
            document.getElementById('title').value = 'Custom Theme CSS Adjustments';
            document.getElementById('type').value = 'css';
            document.getElementById('location').value = 'head';
            editor.setValue("/* Custom Styles */\n.btn-primary {\n    background-color: #ff6600 !important;\n    border-color: #ff6600 !important;\n}");
            editor.save();
        });

        // Ensure editor saves to textarea before form submit and validate code presence
        var form = document.getElementById('snippet-form');
        form.addEventListener('submit', function (e) {
            editor.save();
            var content = editor.getValue().trim();
            if (!content) {
                e.preventDefault();
                alert('Please enter your code or script payload.');
                editor.focus();
                return false;
            }
        });
    });
</script>
@endsection
