@extends('backEnd.layouts.master') 
@section('title', 'Payment Gateway Management')
@section('css')
<link href="{{asset('backEnd/assets/libs/switchery/switchery.min.css')}}" rel="stylesheet" type="text/css" />
<style>
  .gateway-header-bkash {
    border-left: 4px solid #e2136e;
  }
  .gateway-header-shurjopay {
    border-left: 4px solid #0084ff;
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
            <li class="breadcrumb-item active">Payment Gateway</li>
          </ol>
        </div>
        <h4 class="page-title">Payment Gateway Configuration</h4>
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
                <i class="fe-layers font-22 avatar-title text-primary"></i>
              </div>
            </div>
            <div class="col-6">
              <div class="text-end">
                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $total_gateways ?? 2 }}</span></h3>
                <p class="text-muted mb-1 text-truncate">Total Gateways</p>
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
                <i class="fe-check-circle font-22 avatar-title text-success"></i>
              </div>
            </div>
            <div class="col-6">
              <div class="text-end">
                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $active_gateways ?? 0 }}</span></h3>
                <p class="text-muted mb-1 text-truncate">Active Gateways</p>
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
              <div class="avatar-lg rounded-circle bg-soft-danger border-danger border">
                <i class="fe-dollar-sign font-22 avatar-title text-danger"></i>
              </div>
            </div>
            <div class="col-6">
              <div class="text-end">
                <h4 class="mt-1">
                  @if($bkash && $bkash->status == 1)
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Inactive</span>
                  @endif
                </h4>
                <p class="text-muted mb-1 text-truncate">bKash Status</p>
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
                <i class="fe-credit-card font-22 avatar-title text-info"></i>
              </div>
            </div>
            <div class="col-6">
              <div class="text-end">
                <h4 class="mt-1">
                  @if($shurjopay && $shurjopay->status == 1)
                    <span class="badge bg-success">Active</span>
                  @else
                    <span class="badge bg-secondary">Inactive</span>
                  @endif
                </h4>
                <p class="text-muted mb-1 text-truncate">Shurjopay Status</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- end KPI Summary Cards -->

  <!-- bKash Configuration Section -->
  <div class="row">
    <div class="col-12">
      <div class="card gateway-header-bkash">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <div>
            <h4 class="header-title mb-0 text-dark"><i class="fe-dollar-sign me-1 text-danger"></i> bKash PGW (Tokenized API)</h4>
            <p class="text-muted font-13 mb-0">Configure bKash merchant credentials and endpoints</p>
          </div>
          <div>
            @if($bkash && $bkash->status == 1)
              <span class="badge bg-soft-success text-success px-2 py-1">Enabled</span>
            @else
              <span class="badge bg-soft-secondary text-secondary px-2 py-1">Disabled</span>
            @endif
          </div>
        </div>
        <div class="card-body">
          <form action="{{ route('paymentgeteway.update') }}" method="POST" data-parsley-validate="">
            @csrf
            <input type="hidden" name="id" value="{{ $bkash->id }}">
            <input type="hidden" name="type" value="bkash">

            <div class="row">
              <div class="col-md-4">
                <div class="form-group mb-3">
                  <label for="bkash_username" class="form-label">User Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username', $bkash->username) }}" id="bkash_username" required="" />
                  @error('username')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group mb-3">
                  <label for="bkash_app_key" class="form-label">App Key <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('app_key') is-invalid @enderror" name="app_key" value="{{ old('app_key', $bkash->app_key) }}" id="bkash_app_key" required="" />
                  @error('app_key')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group mb-3">
                  <label for="bkash_app_secret" class="form-label">App Secret <span class="text-danger">*</span></label>
                  <input type="password" class="form-control @error('app_secret') is-invalid @enderror" name="app_secret" value="{{ old('app_secret', $bkash->app_secret) }}" id="bkash_app_secret" required="" />
                  @error('app_secret')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group mb-3">
                  <label for="bkash_password" class="form-label">Password <span class="text-danger">*</span></label>
                  <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password', $bkash->password) }}" id="bkash_password" required="" />
                  @error('password')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-group mb-3">
                  <label for="bkash_base_url" class="form-label">Base URL <span class="text-danger">*</span></label>
                  <input type="url" class="form-control @error('base_url') is-invalid @enderror" name="base_url" value="{{ old('base_url', $bkash->base_url) }}" id="bkash_base_url" placeholder="https://tokenized.pay.bka.sh/v1.2.0-beta" required="" />
                  @error('base_url')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group mb-3">
                  <label for="bkash_status" class="form-label d-block">Gateway Status</label>
                  <input type="checkbox" value="1" name="status" id="bkash_status" data-plugin="switchery" data-color="#1abc9c" data-size="small" @if(old('status', $bkash->status) == 1) checked @endif />
                  <span class="ms-1 text-muted font-13 align-middle">{{ ($bkash->status == 1) ? 'Active' : 'Inactive' }}</span>
                  @error('status')
                  <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="mt-2">
              <button type="submit" class="btn btn-success waves-effect waves-light"><i class="fe-save me-1"></i> Save bKash Settings</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Shurjopay Configuration Section -->
  <div class="row">
    <div class="col-12">
      <div class="card gateway-header-shurjopay">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <div>
            <h4 class="header-title mb-0 text-dark"><i class="fe-credit-card me-1 text-info"></i> Shurjopay Payment Gateway</h4>
            <p class="text-muted font-13 mb-0">Configure Shurjopay merchant API credentials and return callbacks</p>
          </div>
          <div>
            @if($shurjopay && $shurjopay->status == 1)
              <span class="badge bg-soft-success text-success px-2 py-1">Enabled</span>
            @else
              <span class="badge bg-soft-secondary text-secondary px-2 py-1">Disabled</span>
            @endif
          </div>
        </div>
        <div class="card-body">
          <form action="{{ route('paymentgeteway.update') }}" method="POST" data-parsley-validate="">
            @csrf
            <input type="hidden" name="id" value="{{ $shurjopay->id }}">
            <input type="hidden" name="type" value="shurjopay">

            <div class="row">
              <div class="col-md-4">
                <div class="form-group mb-3">
                  <label for="shurjopay_username" class="form-label">User Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username', $shurjopay->username) }}" id="shurjopay_username" required="" />
                  @error('username')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group mb-3">
                  <label for="shurjopay_prefix" class="form-label">Prefix <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('prefix') is-invalid @enderror" name="prefix" value="{{ old('prefix', $shurjopay->prefix) }}" id="shurjopay_prefix" required="" />
                  @error('prefix')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group mb-3">
                  <label for="shurjopay_password" class="form-label">Password <span class="text-danger">*</span></label>
                  <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password', $shurjopay->password) }}" id="shurjopay_password" required="" />
                  @error('password')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group mb-3">
                  <label for="shurjopay_base_url" class="form-label">Base URL <span class="text-danger">*</span></label>
                  <input type="url" class="form-control @error('base_url') is-invalid @enderror" name="base_url" value="{{ old('base_url', $shurjopay->base_url) }}" id="shurjopay_base_url" placeholder="https://engine.shurjopayment.com" required="" />
                  @error('base_url')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group mb-3">
                  <label for="shurjopay_success_url" class="form-label">Success URL <span class="text-danger">*</span></label>
                  <input type="url" class="form-control @error('success_url') is-invalid @enderror" name="success_url" value="{{ old('success_url', $shurjopay->success_url) }}" id="shurjopay_success_url" required="" />
                  @error('success_url')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group mb-3">
                  <label for="shurjopay_return_url" class="form-label">Return URL <span class="text-danger">*</span></label>
                  <input type="url" class="form-control @error('return_url') is-invalid @enderror" name="return_url" value="{{ old('return_url', $shurjopay->return_url) }}" id="shurjopay_return_url" required="" />
                  @error('return_url')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group mb-3">
                  <label for="shurjopay_status" class="form-label d-block">Gateway Status</label>
                  <input type="checkbox" value="1" name="status" id="shurjopay_status" data-plugin="switchery" data-color="#1abc9c" data-size="small" @if(old('status', $shurjopay->status) == 1) checked @endif />
                  <span class="ms-1 text-muted font-13 align-middle">{{ ($shurjopay->status == 1) ? 'Active' : 'Inactive' }}</span>
                  @error('status')
                  <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
              </div>
            </div>

            <div class="mt-2">
              <button type="submit" class="btn btn-primary waves-effect waves-light"><i class="fe-save me-1"></i> Save Shurjopay Settings</button>
            </div>
          </form>
        </div>
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
  });
</script>
@endsection