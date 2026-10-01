@extends('backEnd.layouts.master')
@section('title', 'Edit Facebook Pixel & CAPI')

@section('css')
<link href="{{ asset('backEnd/assets/libs/switchery/switchery.min.css') }}" rel="stylesheet" type="text/css" />
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
                        <li class="breadcrumb-item"><a href="{{ route('pixels.index') }}">Pixels</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                    <a href="{{ route('pixels.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-list me-1"></i> Manage Pixels
                    </a>
                </div>
                <h4 class="page-title">Edit Facebook Pixel & Conversions API</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info border-0 mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-information-outline font-22 me-2"></i>
                            <div>
                                <strong>Full Advanced Server-Side Tracking (Meta Conversions API)</strong>
                                <p class="mb-0 font-12 text-muted">Configure both Browser Meta Pixel and Conversions API (CAPI) for 100% accurate tracking, event deduplication, and bypassing ad-blockers/iOS restrictions.</p>
                            </div>
                        </div>
                    </div>

                    <h5 class="card-title text-uppercase font-14 mb-3"><i class="mdi mdi-facebook me-1 text-primary"></i> Edit Facebook Pixel & CAPI</h5>
                    <form action="{{ route('pixels.update') }}" method="POST" class="row" data-parsley-validate="" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" value="{{ $edit_data->id }}" name="id">
                        <input type="hidden" value="{{ $edit_data->id }}" name="hidden_id">

                        <!-- Pixel ID -->
                        <div class="col-sm-12">
                            <div class="form-group mb-3">
                                <label for="code" class="form-label font-weight-bold">Facebook Pixel ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $edit_data->code) }}" id="code" placeholder="e.g. 123456789012345" required="">
                                <small class="text-muted d-block mt-1">Enter your Meta Pixel / Dataset ID from Meta Events Manager (Settings &gt; Dataset ID / Pixel ID).</small>
                                @error('code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- CAPI Access Token -->
                        <div class="col-sm-12">
                            <div class="form-group mb-3">
                                <label for="access_token" class="form-label font-weight-bold">Conversions API (CAPI) Permanent Access Token</label>
                                <textarea class="form-control @error('access_token') is-invalid @enderror" name="access_token" id="access_token" rows="3" placeholder="EAAB... (System User Permanent Token or Generate Access Token in Events Manager)">{{ old('access_token', $edit_data->access_token) }}</textarea>
                                <small class="text-muted d-block mt-1">
                                    <i class="fe-help-circle me-1"></i>
                                    Generate in Meta Events Manager &gt; Settings &gt; Conversions API &gt; <strong>Generate access token</strong> or via Business System User.
                                </small>
                                @error('access_token')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Test Event Code -->
                        <div class="col-sm-12">
                            <div class="form-group mb-3">
                                <label for="test_event_code" class="form-label font-weight-bold">Meta Test Event Code <span class="badge bg-soft-info text-info">Optional / Testing</span></label>
                                <input type="text" class="form-control @error('test_event_code') is-invalid @enderror" name="test_event_code" value="{{ old('test_event_code', $edit_data->test_event_code) }}" id="test_event_code" placeholder="e.g. TEST12345">
                                <small class="text-muted d-block mt-1">Enter Test Code from Meta Events Manager &gt; <strong>Test Events</strong> tab to verify server-side events in real-time. (Leave empty in live production).</small>
                                @error('test_event_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row col-sm-12 mb-3">
                            <!-- Browser Pixel Toggle -->
                            <div class="col-md-6 mb-2">
                                <div class="form-group">
                                    <label for="status" class="d-block form-label font-weight-bold">Browser Pixel Tracking</label>
                                    <input type="checkbox" value="1" name="status" id="status" class="js-switch" data-color="#28a745" {{ ($edit_data->status ?? 1) == 1 ? 'checked' : '' }} />
                                    <small class="text-muted d-block mt-1">Toggle to activate or deactivate browser-side JavaScript Meta Pixel.</small>
                                    @error('status')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Server CAPI Toggle -->
                            <div class="col-md-6 mb-2">
                                <div class="form-group">
                                    <label for="capi_status" class="d-block form-label font-weight-bold">Conversions API (Server-Side)</label>
                                    <input type="checkbox" value="1" name="capi_status" id="capi_status" class="js-switch" data-color="#0d6efd" {{ ($edit_data->capi_status ?? 1) == 1 ? 'checked' : '' }} />
                                    <small class="text-muted d-block mt-1">Toggle to enable or disable server-side Conversions API dispatching.</small>
                                    @error('capi_status')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-success waves-effect waves-light me-1">
                                <i class="fe-check-circle me-1"></i> Update Pixel &amp; CAPI
                            </button>
                            <a href="{{ route('pixels.index') }}" class="btn btn-secondary waves-effect">
                                <i class="fe-x me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->
    </div>
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
                new Switchery(html, { size: 'small', color: html.getAttribute('data-color') || '#28a745' });
            });
        }
    });
</script>
@endsection