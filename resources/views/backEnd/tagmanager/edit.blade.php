@extends('backEnd.layouts.master')
@section('title', 'Tag Manager Edit')

@section('css')
<link href="{{ asset('backEnd/assets/libs/switchery/switchery.min.css') }}" rel="stylesheet" type="text/css" />
<style>
    .tracking-matrix th { font-size: 12px; text-transform: uppercase; background: #f8f9fa; }
    .tracking-matrix td { vertical-align: middle; }
    .server-config-box { transition: all 0.3s ease; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0 me-2 d-none d-md-inline-flex">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tagmanagers.index') }}">Tag Manager</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                    <a href="{{ route('tagmanagers.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-list me-1"></i> Manage Tags
                    </a>
                </div>
                <h4 class="page-title">Google Tag Manager Edit</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <form action="{{ route('tagmanagers.update') }}" method="POST" id="gtm-edit-form" data-parsley-validate="">
        @csrf
        <input type="hidden" value="{{ $edit_data->id }}" name="id">
        <input type="hidden" value="{{ $edit_data->id }}" name="hidden_id">

        <div class="row">
            <!-- Left Column: Container & Server-Side & Events -->
            <div class="col-lg-8">
                <!-- 1. Container Info Card -->
                <div class="card mb-3">
                    <div class="card-header bg-light py-2">
                        <h5 class="card-title font-14 mb-0"><i class="fe-tag me-1 text-primary"></i> 1. GTM Web Container Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="title" class="form-label">Container Title / Label <span class="text-muted">(Optional)</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $edit_data->title) }}" id="title" placeholder="e.g. Primary GA4 & Ads Container">
                                    <small class="text-muted d-block mt-1">Identifies this container in your admin dashboard.</small>
                                    @error('title')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="code" class="form-label">Google Tag Manager ID <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fe-code"></i></span>
                                        <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $edit_data->code) }}" id="code" placeholder="e.g. GTM-XXXXXXX" required="">
                                    </div>
                                    <small class="text-muted d-block mt-1">Enter your GTM Container ID (e.g., <code>GTM-K89745P</code>).</small>
                                    @error('code')
                                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">Description / Notes <span class="text-muted">(Optional)</span></label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description" rows="2" placeholder="e.g. Production container tracking GA4, Meta CAPI, and Google Ads">{{ old('description', $edit_data->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-1">
                                    <label for="status" class="d-block form-label">Container Status</label>
                                    <input type="checkbox" value="1" name="status" id="status" class="js-switch" data-color="#28a745" {{ $edit_data->status == 1 ? 'checked' : '' }} />
                                    <small class="text-muted ms-2">Enable or disable this entire Tag Manager integration.</small>
                                    @error('status')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Server-Side GTM (sGTM) Configuration Card -->
                <div class="card mb-3 border-primary border">
                    <div class="card-header bg-soft-primary py-2 d-flex justify-content-between align-items-center">
                        <h5 class="card-title font-14 mb-0 text-primary">
                            <i class="fe-server me-1"></i> 2. Server-Side Google Tag Manager (sGTM) Configuration
                        </h5>
                        <span class="badge bg-primary">Advanced Tracking</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_server_side" name="is_server_side" value="1" {{ old('is_server_side', $edit_data->is_server_side) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="is_server_side">
                                        Enable Server-Side GTM Tracking Engine
                                    </label>
                                    <small class="text-muted d-block">Dispatches events directly from the server to your sGTM Tagging Server container via background queue.</small>
                                </div>
                            </div>

                            <div id="sgtm-fields-box" class="col-12" style="{{ old('is_server_side', $edit_data->is_server_side) ? '' : 'display: none;' }}">
                                <div class="row g-2">
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label for="server_container_url" class="form-label">Tagging Server URL <span class="text-danger">*</span></label>
                                            <input type="url" class="form-control @error('server_container_url') is-invalid @enderror" name="server_container_url" value="{{ old('server_container_url', $edit_data->server_container_url) }}" id="server_container_url" placeholder="https://gtm.mondolshopbd.com or https://xxxx.stape.io">
                                            <small class="text-muted d-block mt-1">The URL of your custom tagging server (GCP Cloud Run, Stape, or AWS sGTM).</small>
                                            @error('server_container_url')
                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="measurement_id" class="form-label">GA4 Measurement ID <span class="text-muted">(Optional)</span></label>
                                            <input type="text" class="form-control @error('measurement_id') is-invalid @enderror" name="measurement_id" value="{{ old('measurement_id', $edit_data->measurement_id) }}" id="measurement_id" placeholder="e.g. G-XXXXXXXXXX">
                                            <small class="text-muted d-block mt-1">Required if routing Measurement Protocol to GA4 client.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="api_secret" class="form-label">Measurement Protocol API Secret <span class="text-muted">(Optional)</span></label>
                                            <input type="text" class="form-control @error('api_secret') is-invalid @enderror" name="api_secret" value="{{ old('api_secret', $edit_data->api_secret) }}" id="api_secret" placeholder="e.g. 5xX_YyZz1234">
                                            <small class="text-muted d-block mt-1">Found in GA4 Admin &gt; Data Streams &gt; Measurement Protocol API Secrets.</small>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="custom_loader_domain" name="custom_loader_domain" value="1" {{ old('custom_loader_domain', $edit_data->custom_loader_domain) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-dark" for="custom_loader_domain">
                                                Load Web Script via Server Container URL (Bypass Ad-Blockers & Safari ITP)
                                            </label>
                                            <small class="text-muted d-block">Rewrites the <code>&lt;head&gt;</code> script URL to load from your custom tagging domain instead of <code>googletagmanager.com</code>.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Granular Dynamic Relational Event Tracking Matrix -->
                <div class="card mb-3">
                    <div class="card-header bg-light py-2">
                        <h5 class="card-title font-14 mb-0"><i class="fe-sliders me-1 text-success"></i> 3. Granular Event Tracking Matrix (Dual-Track)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 tracking-matrix">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">Event Name & Action</th>
                                        <th style="width: 25%;" class="text-center">Web DataLayer (Browser)</th>
                                        <th style="width: 25%;" class="text-center">Server-Side sGTM (HTTP)</th>
                                        <th style="width: 20%;">GA4 Event Key</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($standardEvents as $eventEnum)
                                    @php
                                        $key = $eventEnum->value;
                                        $existingConfig = $edit_data->eventConfigs ? $edit_data->eventConfigs->firstWhere('event_key', $key) : null;
                                        $isWeb = $existingConfig ? (bool) $existingConfig->is_web_enabled : true;
                                        $isServer = $existingConfig ? (bool) $existingConfig->is_server_enabled : true;
                                        $customName = $existingConfig && !empty($existingConfig->custom_event_name) ? $existingConfig->custom_event_name : $eventEnum->ga4EventName();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $eventEnum->label() }}</div>
                                            <small class="text-muted font-11 d-block">{{ $eventEnum->description() }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="events[{{ $key }}][is_web_enabled]" value="1" id="web_{{ $key }}" {{ $isWeb ? 'checked' : '' }}>
                                                <label class="form-check-label font-12" for="web_{{ $key }}">Web Push</label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="events[{{ $key }}][is_server_enabled]" value="1" id="server_{{ $key }}" {{ $isServer ? 'checked' : '' }}>
                                                <label class="form-check-label font-12" for="server_{{ $key }}">Server Post</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" name="events[{{ $key }}][custom_event_name]" value="{{ $customName }}" placeholder="{{ $eventEnum->ga4EventName() }}">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3 mb-4">
                    <button type="submit" class="btn btn-success btn-lg waves-effect waves-light me-2">
                        <i class="fe-check-circle me-1"></i> Update Tag Manager & Tracking Engine
                    </button>
                    <a href="{{ route('tagmanagers.index') }}" class="btn btn-secondary btn-lg waves-effect">
                        <i class="fe-x me-1"></i> Cancel
                    </a>
                </div>
            </div>

            <!-- Right Column: Live Injection Preview & Architecture Guide -->
            <div class="col-lg-4">
                <div class="card mb-3 sticky-top" style="top: 80px;">
                    <div class="card-header bg-light py-2">
                        <h5 class="card-title font-13 mb-0"><i class="fe-eye me-1 text-info"></i> Live Script Injection Preview</h5>
                    </div>
                    <div class="card-body">
                        <p class="font-12 text-muted mb-2">Dynamic script generated for your storefront <code>&lt;head&gt;</code>:</p>
                        <pre class="bg-dark text-light p-2 rounded font-11 mb-2" style="white-space: pre-wrap; word-break: break-all;" id="preview-head">
&lt;!-- Google Tag Manager --&gt;
&lt;script&gt;(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'<span id="preview-domain" class="text-info">{{ $edit_data->getEffectiveScriptBaseUrl() }}</span>/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<span class="text-warning fw-bold preview-code">{{ $edit_data->code }}</span>');&lt;/script&gt;
&lt;!-- End Google Tag Manager --&gt;</pre>
                        
                        <div class="alert alert-info py-2 px-3 font-11 mb-0 mt-2">
                            <i class="fe-shield me-1"></i>
                            <strong>Enhanced Conversions:</strong> When Server-Side GTM is active, customer phone numbers and emails are automatically formatted (e.g. <code>+88017XXXXXXXX</code>) and SHA-256 hashed on order placement for maximum conversion attribution.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script src="{{ asset('backEnd/assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('backEnd/assets/libs/switchery/switchery.min.js') }}"></script>
<script>
    $(document).ready(function() {
        if ($('.js-switch').length > 0) {
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
                new Switchery(html, { size: 'small', color: '#28a745' });
            });
        }

        $('#is_server_side').on('change', function() {
            if ($(this).is(':checked')) {
                $('#sgtm-fields-box').slideDown(200);
            } else {
                $('#sgtm-fields-box').slideUp(200);
            }
            updateGtmPreview();
        });

        $('#custom_loader_domain, #server_container_url').on('input change', function() {
            updateGtmPreview();
        });

        function updateGtmPreview() {
            var inputVal = $('#code').val().trim();
            var match = inputVal.match(/(GTM-[A-Z0-9_-]+)/i);
            var displayId = 'GTM-XXXXXXX';
            if (match) {
                var extracted = match[1].toUpperCase();
                if (inputVal !== extracted && inputVal.indexOf('<script') !== -1) {
                    $('#code').val(extracted);
                }
                displayId = extracted;
            } else if (inputVal.length > 0) {
                var clean = inputVal.toUpperCase().replace(/[^A-Z0-9_-]/g, '');
                if (!clean.startsWith('GTM-')) {
                    clean = 'GTM-' . clean;
                }
                displayId = clean;
            }

            $('.preview-code').text(displayId);

            var isServerSide = $('#is_server_side').is(':checked');
            var useCustomDomain = $('#custom_loader_domain').is(':checked');
            var serverUrl = $('#server_container_url').val().trim().replace(/\/$/, '');

            if (isServerSide && useCustomDomain && serverUrl.length > 5) {
                $('#preview-domain').text(serverUrl);
            } else {
                $('#preview-domain').text('https://www.googletagmanager.com');
            }
        }

        $('#code').on('input paste change blur', function() {
            setTimeout(updateGtmPreview, 50);
        });

        updateGtmPreview();
    });
</script>
@endsection