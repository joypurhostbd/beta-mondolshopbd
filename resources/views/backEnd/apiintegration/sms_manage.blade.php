@extends('backEnd.layouts.master') 
@section('title', 'SMS Gateway Management')
@section('css')
<link href="{{asset('backEnd/assets/libs/switchery/switchery.min.css')}}" rel="stylesheet" type="text/css" />
<style>
  .sms-header-card {
    border-left: 4px solid #727cf5;
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
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
            <li class="breadcrumb-item active">SMS Gateway</li>
          </ol>
        </div>
        <h4 class="page-title">SMS Gateway Configuration</h4>
      </div>
    </div>
  </div>
  <!-- end page title -->

  <!-- KPI Summary Cards -->
  <div class="row">
    <div class="col-md-6 col-xl-3">
      <div class="widget-rounded-circle card">
        <div class="card-body">
          <div class="row">
            <div class="col-6">
              <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                <i class="fe-message-square font-22 avatar-title text-primary"></i>
              </div>
            </div>
            <div class="col-6">
              <div class="text-end">
                <h4 class="mt-1">
                  @if($sms && $sms->status == 1)
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Inactive</span>
                  @endif
                </h4>
                <p class="text-muted mb-1 text-truncate" id="kpi-balance-wrapper">
                  @if(isset($sms_balance) && $sms_balance !== null)
                    Balance: <span class="fw-bold text-dark" id="kpi-balance-value">{{ $sms_balance }} SMS</span>
                  @else
                    Master SMS ({{ ($sms->provider ?? 'joypurhost') === 'custom' ? 'Custom' : 'JoypurHost' }})
                  @endif
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="widget-rounded-circle card">
        <div class="card-body">
          <div class="row">
            <div class="col-6">
              <div class="avatar-lg rounded-circle bg-soft-success border-success border">
                <i class="fe-shopping-bag font-22 avatar-title text-success"></i>
              </div>
            </div>
            <div class="col-6">
              <div class="text-end">
                <h4 class="mt-1">
                  @if($sms && $sms->order == 1)
                    <span class="badge bg-success">Enabled</span>
                  @else
                    <span class="badge bg-secondary">Disabled</span>
                  @endif
                </h4>
                <p class="text-muted mb-1 text-truncate">Order Alert</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="widget-rounded-circle card">
        <div class="card-body">
          <div class="row">
            <div class="col-6">
              <div class="avatar-lg rounded-circle bg-soft-warning border-warning border">
                <i class="fe-key font-22 avatar-title text-warning"></i>
              </div>
            </div>
            <div class="col-6">
              <div class="text-end">
                <h4 class="mt-1">
                  @if($sms && $sms->forget_pass == 1)
                    <span class="badge bg-success">Enabled</span>
                  @else
                    <span class="badge bg-secondary">Disabled</span>
                  @endif
                </h4>
                <p class="text-muted mb-1 text-truncate">OTP / Forgot Pass</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-xl-3">
      <div class="widget-rounded-circle card">
        <div class="card-body">
          <div class="row">
            <div class="col-6">
              <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                <i class="fe-lock font-22 avatar-title text-info"></i>
              </div>
            </div>
            <div class="col-6">
              <div class="text-end">
                <h4 class="mt-1">
                  @if($sms && $sms->password_g == 1)
                    <span class="badge bg-success">Enabled</span>
                  @else
                    <span class="badge bg-secondary">Disabled</span>
                  @endif
                </h4>
                <p class="text-muted mb-1 text-truncate">Auto-Password</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- end KPI Summary Cards -->

  <!-- SMS Gateway Configuration Form -->
  <div class="row">
    <div class="col-12">
      <div class="card sms-header-card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <div>
            <h4 class="header-title mb-0 text-dark"><i class="fe-send me-1 text-primary"></i> SMS Gateway Provider Settings</h4>
            <p class="text-muted font-13 mb-0">Configure HTTP API endpoint, authentication keys, and notification triggers</p>
          </div>
          <div>
            @if($sms && $sms->status == 1)
              <span class="badge bg-soft-success text-success px-2 py-1"><i class="fe-check-circle me-1"></i> Active ({{ ($sms->provider ?? 'joypurhost') === 'custom' ? 'Custom Provider' : 'JoypurHost SMS' }})</span>
            @else
              <span class="badge bg-soft-secondary text-secondary px-2 py-1"><i class="fe-slash me-1"></i> Inactive Gateway</span>
            @endif
          </div>
        </div>
        <div class="card-body">
          <form action="{{ route('smsgeteway.update') }}" method="POST" data-parsley-validate="">
            @csrf
            <input type="hidden" name="id" id="gateway_id" value="{{ $sms->id }}">

            <!-- Provider Selection & Endpoint -->
            <h5 class="text-uppercase bg-light p-2 mt-0 mb-3 font-14"><i class="fe-globe me-1"></i> Provider Configuration & API Credentials</h5>
            
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="form-group mb-2">
                  <label for="provider_select" class="form-label fw-bold">SMS Provider <span class="text-danger">*</span></label>
                  <select name="provider" id="provider_select" class="form-select @error('provider') is-invalid @enderror" required>
                    @foreach($providers ?? \App\Models\SmsGateway::getAvailableProviders() as $key => $label)
                      <option value="{{ $key }}" {{ old('provider', $sms->provider ?? 'joypurhost') == $key ? 'selected' : '' }}>
                        {{ $label }} {{ $key === 'joypurhost' ? '(Default / Recommended)' : '' }}
                      </option>
                    @endforeach
                  </select>
                  @error('provider')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                  <small id="provider_help" class="form-text text-muted mt-1 d-block">
                    {{ old('provider', $sms->provider ?? 'joypurhost') === 'custom' ? 'Configured with custom SMS HTTP API endpoint and parameters.' : 'Fast, reliable JoypurHost SMS Engine for OTP and notifications in Bangladesh.' }}
                  </small>
                </div>
              </div>

              <div class="col-md-6">
                <div id="joypurhost_info_banner" class="alert alert-info py-2 px-3 mb-0 mt-md-4 {{ old('provider', $sms->provider ?? 'joypurhost') === 'custom' ? 'd-none' : '' }}">
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                      <i class="fe-zap font-20 me-2 text-primary"></i>
                      <div>
                        <span class="fw-bold d-block text-dark">JoypurHost SMS Gateway Engine</span>
                        <small class="text-muted">Direct BDIX carrier routes for OTP, notifications & promotional SMS.</small>
                      </div>
                    </div>
                    <div class="text-end ms-2" id="banner-balance-wrapper" style="{{ isset($sms_balance) && $sms_balance !== null ? '' : 'display:none;' }}">
                      <span class="badge bg-primary font-12 py-1 px-2">
                        <i class="fe-credit-card me-1"></i> Balance: <span id="banner-balance-value">{{ $sms_balance ?? '0.00' }}</span> SMS
                      </span>
                    </div>
                  </div>
                </div>
                <div id="custom_info_banner" class="alert alert-warning py-2 px-3 mb-0 mt-md-4 {{ old('provider', $sms->provider ?? 'joypurhost') === 'custom' ? '' : 'd-none' }}">
                  <div class="d-flex align-items-center">
                    <i class="fe-settings font-20 me-2 text-warning"></i>
                    <div>
                      <span class="fw-bold d-block text-dark">Custom SMS Provider</span>
                      <small class="text-muted">Specify custom HTTP API URL, API Key/Token, and Sender ID.</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-5">
                <div class="form-group mb-3">
                  <label for="url" class="form-label">Gateway API URL <span class="text-danger">*</span></label>
                  <input type="url" class="form-control @error('url') is-invalid @enderror" name="url" value="{{ old('url', $sms->url ?? 'http://sms.joypurhost.com/api/smsapi') }}" id="url" placeholder="http://sms.joypurhost.com/api/smsapi" required="" />
                  @error('url')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group mb-3">
                  <label for="api_key" class="form-label">API Key <span class="text-danger">*</span></label>
                  <input type="password" class="form-control @error('api_key') is-invalid @enderror" name="api_key" value="{{ old('api_key', $sms->api_key) }}" id="api_key" required="" />
                  @error('api_key')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group mb-3">
                  <label for="sender_id" class="form-label">Sender ID <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('sender_id') is-invalid @enderror" name="sender_id" value="{{ old('sender_id', $sms->sender_id) }}" id="sender_id" placeholder="8809612xxxxxx" required="" />
                  @error('sender_id')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>
            </div>

            <!-- Trigger Toggles -->
            <h5 class="text-uppercase bg-light p-2 mt-2 mb-3 font-14"><i class="fe-bell me-1"></i> Notification Trigger Toggles</h5>
            <div class="row">
              <div class="col-md-3 mb-3">
                <div class="card border border-light p-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <label for="sms_status" class="form-label fw-bold mb-0">Master SMS Status</label>
                      <small class="text-muted d-block">Enable all outgoing SMS</small>
                    </div>
                    <div>
                      <input type="checkbox" value="1" name="status" id="sms_status" data-plugin="switchery" data-color="#1abc9c" data-size="small" @if(old('status', $sms->status) == 1) checked @endif />
                    </div>
                  </div>
                  @error('status')
                  <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-3 mb-3">
                <div class="card border border-light p-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <label for="order_status" class="form-label fw-bold mb-0">Order Confirmation</label>
                      <small class="text-muted d-block">SMS upon placed order</small>
                    </div>
                    <div>
                      <input type="checkbox" value="1" name="order" id="order_status" data-plugin="switchery" data-color="#1abc9c" data-size="small" @if(old('order', $sms->order) == 1) checked @endif />
                    </div>
                  </div>
                  @error('order')
                  <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-3 mb-3">
                <div class="card border border-light p-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <label for="forget_pass_status" class="form-label fw-bold mb-0">Forgot Password / OTP</label>
                      <small class="text-muted d-block">SMS with reset OTP code</small>
                    </div>
                    <div>
                      <input type="checkbox" value="1" name="forget_pass" id="forget_pass_status" data-plugin="switchery" data-color="#1abc9c" data-size="small" @if(old('forget_pass', $sms->forget_pass) == 1) checked @endif />
                    </div>
                  </div>
                  @error('forget_pass')
                  <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-3 mb-3">
                <div class="card border border-light p-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <label for="password_g_status" class="form-label fw-bold mb-0">Password Generator</label>
                      <small class="text-muted d-block">SMS auto-generated pass</small>
                    </div>
                    <div>
                      <input type="checkbox" value="1" name="password_g" id="password_g_status" data-plugin="switchery" data-color="#1abc9c" data-size="small" @if(old('password_g', $sms->password_g) == 1) checked @endif />
                    </div>
                  </div>
                  @error('password_g')
                  <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>
            </div>

            <!-- Admin Order Notification Numbers -->
            <div class="row mb-2">
              <div class="col-12">
                <div class="card border border-light p-3 bg-light-subtle">
                  <div class="row align-items-center">
                    <div class="col-md-4">
                      <label for="admin_phone" class="form-label fw-bold mb-1">
                        <i class="fe-phone-call me-1 text-primary"></i> Admin Order Alert Mobile Number(s)
                      </label>
                      <small class="text-muted d-block font-12">
                        Instant SMS notification sent upon customer order submission.
                      </small>
                    </div>
                    <div class="col-md-8">
                      <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fe-smartphone text-primary"></i></span>
                        <input type="text" 
                               name="admin_phone" 
                               id="admin_phone" 
                               class="form-control @error('admin_phone') is-invalid @enderror" 
                               placeholder="e.g. 01711XXXXXX, 01811XXXXXX (comma separated for multiple numbers)" 
                               value="{{ old('admin_phone', $sms->admin_phone ?? ($joypurhost->admin_phone ?? ($custom->admin_phone ?? ''))) }}">
                      </div>
                      <small class="form-text text-muted mt-1 d-block font-12">
                        <i class="fe-info me-1"></i> You can enter multiple Bangladeshi numbers separated by comma (e.g., <code>017XXXXXXXX, 018XXXXXXXX</code>). SMS will be sent to both client and all admin numbers.
                      </small>
                      @error('admin_phone')
                      <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                      </span>
                      @enderror
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
              <button type="submit" class="btn btn-primary waves-effect waves-light">
                <i class="fe-save me-1"></i> Save SMS Gateway Settings
              </button>
              <button type="button" id="btn-test-sms" class="btn btn-outline-info waves-effect waves-light">
                <i class="fe-activity me-1"></i> Test Connection & Check Balance
              </button>
              <button type="button" id="btn-send-test-sms-modal" class="btn btn-outline-secondary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#testSmsModal">
                <i class="fe-send me-1"></i> Send Test SMS
              </button>
              <span id="sms-test-result" class="font-13 ms-2"></span>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Test SMS Modal -->
<div class="modal fade" id="testSmsModal" tabindex="-1" aria-labelledby="testSmsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title font-16" id="testSmsModalLabel">
          <i class="fe-send me-1 text-primary"></i> Send Test SMS
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="test_recipient_phone" class="form-label fw-bold">Recipient Mobile Number <span class="text-danger">*</span></label>
          <input type="text" id="test_recipient_phone" class="form-control" placeholder="017xxxxxxxx" value="" required>
          <small class="text-muted">Enter a valid 11-digit Bangladeshi mobile number.</small>
        </div>
        <div class="mb-3">
          <label for="test_sms_message" class="form-label fw-bold">Test Message</label>
          <textarea id="test_sms_message" class="form-control" rows="3" placeholder="This is a test SMS from MondolShopBD.">This is a test SMS from MondolShopBD.</textarea>
        </div>
        <div id="test-sms-modal-alert"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
        <button type="button" id="btn-submit-test-sms" class="btn btn-primary waves-effect waves-light">
          <i class="fe-send me-1"></i> Send Test SMS Now
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="{{asset('backEnd/assets/libs/parsleyjs/parsley.min.js')}}"></script>
<script src="{{asset('backEnd/assets/js/pages/form-validation.init.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/switchery/switchery.min.js')}}"></script>
<script>
  $(document).ready(function () {
    $('[data-plugin="switchery"]').each(function (idx, obj) {
      new Switchery($(this)[0], $(this).data());
    });

    var currentProvider = "{{ old('provider', $active_provider ?? ($sms->provider ?? 'joypurhost')) }}";
    var providerConfigs = {
      joypurhost: {
        id: "{{ $joypurhost->id ?? '' }}",
        url: "{{ old('url', $joypurhost->url ?? 'https://sms.joypurhost.com/api/smsapi') }}",
        api_key: "{{ old('api_key', $joypurhost->api_key ?? '') }}",
        sender_id: "{{ old('sender_id', $joypurhost->sender_id ?? '') }}",
        admin_phone: "{{ old('admin_phone', $joypurhost->admin_phone ?? '') }}",
        balance: "{{ $sms_balance ?? '' }}"
      },
      custom: {
        id: "{{ $custom->id ?? '' }}",
        url: "{{ old('url', $custom->url ?? '') }}",
        api_key: "{{ old('api_key', $custom->api_key ?? '') }}",
        sender_id: "{{ old('sender_id', $custom->sender_id ?? '') }}",
        admin_phone: "{{ old('admin_phone', $custom->admin_phone ?? '') }}",
        balance: null
      }
    };

    $('#provider_select').on('change', function () {
      var newProvider = $(this).val();

      // Cache current inputs in memory for current provider
      if (providerConfigs[currentProvider]) {
        providerConfigs[currentProvider].url = $('#url').val();
        providerConfigs[currentProvider].api_key = $('#api_key').val();
        providerConfigs[currentProvider].sender_id = $('#sender_id').val();
        providerConfigs[currentProvider].admin_phone = $('#admin_phone').val();
      }

      currentProvider = newProvider;

      // Swap to target provider configuration
      var target = providerConfigs[newProvider] || { id: '', url: '', api_key: '', sender_id: '', admin_phone: '', balance: null };
      if (target.id) {
        $('#gateway_id').val(target.id);
      }
      $('#url').val(target.url);
      $('#api_key').val(target.api_key);
      $('#sender_id').val(target.sender_id);
      if (target.admin_phone) {
        $('#admin_phone').val(target.admin_phone);
      }

      if (newProvider === 'joypurhost') {
        $('#joypurhost_info_banner').removeClass('d-none');
        $('#custom_info_banner').addClass('d-none');
        $('#provider_help').text('Fast, reliable JoypurHost SMS Engine for OTP and notifications in Bangladesh.');
        if (!$('#url').val()) {
          $('#url').val('https://sms.joypurhost.com/api/smsapi');
        }
        if (target.balance) {
          $('#banner-balance-wrapper').show();
          $('#banner-balance-value').text(target.balance);
        }
      } else {
        $('#joypurhost_info_banner').addClass('d-none');
        $('#custom_info_banner').removeClass('d-none');
        $('#banner-balance-wrapper').hide();
        $('#provider_help').text('Configured with custom SMS HTTP API endpoint and parameters.');
        $('#url').attr('placeholder', 'https://your-custom-sms-api.com/api/send');
      }
    });

    // Test Connection & Check Balance
    $(document).on('click', '#btn-test-sms', function(e) {
      e.preventDefault();
      var $btn = $(this);
      var $result = $('#sms-test-result');
      var provider = $('#provider_select').val();
      var apiKey = $('#api_key').val();
      var senderId = $('#sender_id').val();
      var url = $('#url').val();

      if (!apiKey && provider === 'joypurhost') {
        if (typeof toastr !== 'undefined') toastr.warning('Please enter an API Key to test connection.');
        $result.html('<span class="text-danger font-12"><i class="fe-alert-triangle me-1"></i>Please enter an API Key</span>');
        return;
      }

      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Testing Connection...');
      $result.html('<span class="text-muted font-12"><i class="fa fa-spinner fa-spin me-1"></i>Connecting to SMS Gateway...</span>');

      $.ajax({
        type: 'POST',
        url: "{{ route('smsgeteway.test') }}",
        data: {
          _token: "{{ csrf_token() }}",
          provider: provider,
          api_key: apiKey,
          sender_id: senderId,
          url: url
        },
        success: function(res) {
          $btn.prop('disabled', false).html('<i class="fe-activity me-1"></i> Test Connection & Check Balance');
          if (res && res.success) {
            var bal = (res.current_balance !== null && typeof res.current_balance !== 'undefined') ? ' (Balance: ' + res.current_balance + ' SMS)' : '';
            $result.html('<span class="badge bg-soft-success text-success p-1"><i class="fe-check-circle me-1"></i>' + (res.message || 'Connected successfully!') + '</span>');
            
            // Live update KPI and Banner balance
            if (res.current_balance !== null && typeof res.current_balance !== 'undefined') {
              $('#kpi-balance-value').text(res.current_balance + ' SMS');
              $('#banner-balance-value').text(res.current_balance);
              $('#banner-balance-wrapper').show();
            }

            if (typeof toastr !== 'undefined') toastr.success(res.message || 'Connected successfully!');
          } else {
            var errMsg = (res && res.message) ? res.message : 'Connection failed';
            $result.html('<span class="badge bg-soft-danger text-danger p-1"><i class="fe-x-circle me-1"></i>' + errMsg + '</span>');
            if (typeof toastr !== 'undefined') toastr.error(errMsg);
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).html('<i class="fe-activity me-1"></i> Test Connection & Check Balance');
          var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Request failed. Please verify credentials.';
          $result.html('<span class="badge bg-soft-danger text-danger p-1"><i class="fe-x-circle me-1"></i>' + msg + '</span>');
          if (typeof toastr !== 'undefined') toastr.error(msg);
        }
      });
    });

    // Send Test SMS from Modal
    $(document).on('click', '#btn-submit-test-sms', function(e) {
      e.preventDefault();
      var $btn = $(this);
      var $alert = $('#test-sms-modal-alert');
      var phone = $('#test_recipient_phone').val();
      var message = $('#test_sms_message').val();
      var provider = $('#provider_select').val();
      var apiKey = $('#api_key').val();
      var senderId = $('#sender_id').val();
      var url = $('#url').val();

      if (!phone) {
        $alert.html('<div class="alert alert-warning py-1 px-2 font-13"><i class="fe-alert-triangle me-1"></i>Recipient mobile number is required.</div>');
        return;
      }

      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Sending SMS...');
      $alert.html('<div class="alert alert-info py-1 px-2 font-13"><i class="fa fa-spinner fa-spin me-1"></i>Sending test SMS...</div>');

      $.ajax({
        type: 'POST',
        url: "{{ route('smsgeteway.send_test') }}",
        data: {
          _token: "{{ csrf_token() }}",
          phone: phone,
          message: message,
          provider: provider,
          api_key: apiKey,
          sender_id: senderId,
          url: url
        },
        success: function(res) {
          $btn.prop('disabled', false).html('<i class="fe-send me-1"></i> Send Test SMS Now');
          if (res && res.success) {
            $alert.html('<div class="alert alert-success py-1 px-2 font-13"><i class="fe-check-circle me-1"></i>' + (res.message || 'SMS sent successfully!') + '</div>');
            if (typeof toastr !== 'undefined') toastr.success(res.message);
          } else {
            var errMsg = (res && res.message) ? res.message : 'Failed to send SMS';
            $alert.html('<div class="alert alert-danger py-1 px-2 font-13"><i class="fe-x-circle me-1"></i>' + errMsg + '</div>');
            if (typeof toastr !== 'undefined') toastr.error(errMsg);
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).html('<i class="fe-send me-1"></i> Send Test SMS Now');
          var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to send test SMS.';
          $alert.html('<div class="alert alert-danger py-1 px-2 font-13"><i class="fe-x-circle me-1"></i>' + msg + '</div>');
          if (typeof toastr !== 'undefined') toastr.error(msg);
        }
      });
    });
  });
</script>
@endsection