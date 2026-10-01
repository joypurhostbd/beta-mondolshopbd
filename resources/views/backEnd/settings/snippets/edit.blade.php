@extends('backEnd.layouts.master')
@section('title', 'Edit Code Snippet')

@section('css')
<!-- CodeMirror CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/monokai.min.css" />
<style>
    .CodeMirror {
        height: 340px;
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
                <h4 class="page-title">Edit Code Snippet: {{ $snippet->title }}</h4>
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

                    <form action="{{route('snippets.update')}}" method="POST" id="snippet-form">
                        @csrf
                        <input type="hidden" name="hidden_id" value="{{ $snippet->id }}">
                        <input type="hidden" name="id" value="{{ $snippet->id }}">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Snippet Title / Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" id="title" value="{{ old('title', $snippet->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="type" class="form-label">Code Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="html" {{ old('type', $snippet->type->value) == 'html' ? 'selected' : '' }}>HTML / Mixed Script</option>
                                    <option value="javascript" {{ old('type', $snippet->type->value) == 'javascript' ? 'selected' : '' }}>JavaScript (&lt;script&gt;)</option>
                                    <option value="css" {{ old('type', $snippet->type->value) == 'css' ? 'selected' : '' }}>CSS (&lt;style&gt;)</option>
                                    <option value="text" {{ old('type', $snippet->type->value) == 'text' ? 'selected' : '' }}>Plain Text</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="location" class="form-label">Insertion Location <span class="text-danger">*</span></label>
                                <select name="location" id="location" class="form-select @error('location') is-invalid @enderror" required>
                                    <option value="head" {{ old('location', $snippet->location->value) == 'head' ? 'selected' : '' }}>Header (&lt;head&gt;...&lt;/head&gt;)</option>
                                    <option value="body_open" {{ old('location', $snippet->location->value) == 'body_open' ? 'selected' : '' }}>Body Open (Immediately after &lt;body&gt;)</option>
                                    <option value="footer" {{ old('location', $snippet->location->value) == 'footer' ? 'selected' : '' }}>Footer (Immediately before &lt;/body&gt;)</option>
                                </select>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Code editor area -->
                        <div class="mb-3">
                            <label for="code" class="form-label">Code / Script Payload <span class="text-danger">*</span></label>
                            <textarea name="code" id="code" class="form-control @error('code') is-invalid @enderror" rows="10">{{ old('code', $snippet->code) }}</textarea>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Conditional Logic Section -->
                        <div class="card bg-light border p-3 mb-3">
                            <h5 class="card-title font-16 mb-2"><i class="fe-filter me-1 text-primary"></i> Smart Conditional Logic (Execution Rules)</h5>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label for="target_pages" class="form-label">Target Pages</label>
                                    <select name="target_pages" id="target_pages" class="form-select">
                                        <option value="all" {{ old('target_pages', $snippet->targetPages) == 'all' ? 'selected' : '' }}>All Pages (Sitewide)</option>
                                        <option value="homepage" {{ old('target_pages', $snippet->targetPages) == 'homepage' ? 'selected' : '' }}>Homepage Only</option>
                                        <option value="checkout" {{ old('target_pages', $snippet->targetPages) == 'checkout' ? 'selected' : '' }}>Checkout Page Only</option>
                                        <option value="thank_you" {{ old('target_pages', $snippet->targetPages) == 'thank_you' ? 'selected' : '' }}>Order Success / Thank You Page</option>
                                        <option value="custom" {{ old('target_pages', $snippet->targetPages) == 'custom' ? 'selected' : '' }}>Custom Specific URLs</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label for="device_target" class="form-label">Device Target</label>
                                    <select name="device_target" id="device_target" class="form-select">
                                        <option value="all" {{ old('device_target', $snippet->deviceTarget->value) == 'all' ? 'selected' : '' }}>All Devices (Desktop & Mobile)</option>
                                        <option value="desktop" {{ old('device_target', $snippet->deviceTarget->value) == 'desktop' ? 'selected' : '' }}>Desktop Only</option>
                                        <option value="mobile" {{ old('device_target', $snippet->deviceTarget->value) == 'mobile' ? 'selected' : '' }}>Mobile Only</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label for="auth_condition" class="form-label">User Authentication</label>
                                    <select name="auth_condition" id="auth_condition" class="form-select">
                                        <option value="all" {{ old('auth_condition', $snippet->authCondition) == 'all' ? 'selected' : '' }}>All Visitors (Guests & Logged-in)</option>
                                        <option value="logged_in" {{ old('auth_condition', $snippet->authCondition) == 'logged_in' ? 'selected' : '' }}>Logged-in Users Only</option>
                                        <option value="guest" {{ old('auth_condition', $snippet->authCondition) == 'guest' ? 'selected' : '' }}>Guests / Non-logged-in Only</option>
                                    </select>
                                </div>

                                <div class="col-12 mt-2 {{ old('target_pages', $snippet->targetPages) == 'custom' ? '' : 'd-none' }}" id="custom-urls-wrapper">
                                    <label for="custom_page_urls" class="form-label">Custom URL Paths (comma-separated, wildcards allowed like <code>product/*</code>)</label>
                                    <input type="text" name="custom_page_urls" id="custom_page_urls" class="form-control" value="{{ old('custom_page_urls', $snippet->customPageUrls) }}" placeholder="e.g. checkout, product/*, category/electronics">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="priority" class="form-label">Priority Order</label>
                                <input type="number" name="priority" id="priority" class="form-control" value="{{ old('priority', $snippet->priority) }}" min="1" max="9999">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" class="form-check-input" id="status" name="status" value="1" {{ old('status', $snippet->status) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status">Active (Enabled)</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="description" class="form-label">Internal Description / Notes</label>
                                <input type="text" name="description" id="description" class="form-control" value="{{ old('description', $snippet->description) }}">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success"><i class="fe-check me-1"></i> Update Snippet</button>
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
        var initialType = "{{ $snippet->type->value }}";
        var initialMode = "htmlmixed";
        if (initialType === 'javascript') initialMode = 'javascript';
        if (initialType === 'css') initialMode = 'css';

        var editor = CodeMirror.fromTextArea(textarea, {
            lineNumbers: true,
            mode: initialMode,
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
