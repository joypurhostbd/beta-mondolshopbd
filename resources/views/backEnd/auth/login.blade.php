<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Log In | {{$generalsetting->name ?? 'MondolShopBD'}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="{{$generalsetting->meta_description ?? 'MondolShopBD Admin Panel'}}" name="description" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <!-- App favicon -->
    @if(!empty($generalsetting->favicon))
        <link rel="shortcut icon" href="{{asset($generalsetting->favicon)}}">
    @endif

    <!-- Bootstrap css -->
    <link href="{{asset('backEnd/assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <!-- App css -->
    <link href="{{asset('backEnd/assets/css/app.min.css')}}" rel="stylesheet" type="text/css" id="app-style"/>
    <!-- icons -->
    <link href="{{asset('backEnd/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
    <!-- Head js -->
    <script src="{{asset('backEnd/assets/js/head.js')}}"></script>

    <style>
        body.auth-redesign-bg {
            background: radial-gradient(circle at center, #1e1e38 0%, #0d0d1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            position: relative;
            overflow: hidden;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        /* Particles Container */
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
        }

        /* Glassmorphism Card Style */
        .auth-card-modern {
            position: relative;
            z-index: 2;
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            overflow: hidden;
            width: 100%;
        }

        .auth-card-modern .card-body {
            padding: 2.5rem !important;
        }

        .auth-logo-box {
            margin-bottom: 1.5rem;
        }

        .auth-logo-box img {
            max-height: 48px;
            object-fit: contain;
        }

        .form-control-modern {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            transition: all 0.2s ease-in-out;
        }

        .form-control-modern:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        }

        .btn-modern {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            color: #ffffff;
            transition: all 0.3s ease;
        }

        .btn-modern:hover {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            box-shadow: 0 8px 20px rgba(29, 78, 216, 0.4);
            transform: translateY(-1px);
            color: #ffffff;
        }

        .input-group-text-modern {
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
            border: 1px solid #cbd5e1;
            border-left: none;
            background: #f8fafc;
            cursor: pointer;
        }

        .form-label-custom {
            font-weight: 600;
            color: #334155;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body class="auth-redesign-bg">
    <!-- Animated Particle Background -->
    <div id="particles-js"></div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5 col-xl-4">
                <div class="card auth-card-modern">
                    <div class="card-body">
                        <!-- Logo & Title Header -->
                        <div class="text-center auth-logo-box">
                            <a href="{{url('/')}}" class="logo text-center">
                                @if(!empty($generalsetting->white_logo))
                                    <img src="{{asset($generalsetting->white_logo)}}" alt="Logo">
                                @else
                                    <h3 class="text-dark font-weight-bold m-0" style="letter-spacing: -0.5px;">
                                        {{$generalsetting->name ?? 'MondolShopBD'}}
                                    </h3>
                                @endif
                            </a>
                            <h4 class="fw-bold text-dark mt-3 mb-1">Welcome Back, Administrator</h4>
                            <p class="text-muted fs-14">Sign in to access your admin panel</p>
                        </div>

                        <!-- Login Form -->
                        <form method="POST" action="{{route('admin.login')}}">
                            @csrf
                            
                            <!-- Email Input -->
                            <div class="mb-3">
                                <label for="emailaddress" class="form-label form-label-custom">Email address</label>
                                <input type="email" id="emailaddress" 
                                    class="form-control form-control-modern @error('email') is-invalid @enderror" 
                                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus 
                                    placeholder="Enter your email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Password Input -->
                            <div class="mb-3">
                                <label for="password" class="form-label form-label-custom">Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" 
                                        class="form-control form-control-modern @error('password') is-invalid @enderror" 
                                        style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                        name="password" required autocomplete="password" placeholder="Enter password">
                                    <div class="input-group-text input-group-text-modern" data-password="false">
                                        <span class="password-eye"></span>
                                    </div>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Remember Me Checkbox -->
                            <div class="mb-4 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkbox-signin" value="1" name="remember" checked style="cursor: pointer;">
                                    <label class="form-check-label text-muted fs-14" for="checkbox-signin" style="cursor: pointer;">
                                        Remember me
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button class="btn btn-modern" type="submit"> Log In </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="{{asset('backEnd/assets/js/vendor.min.js')}}"></script>
    <!-- App js -->
    <script src="{{asset('backEnd/assets/js/app.min.js')}}"></script>

    <!-- Particles.js Animation Script -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        particlesJS("particles-js", {
            "particles": {
                "number": { "value": 110, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": "#ffffff" },
                "shape": { "type": "circle" },
                "opacity": { "value": 0.5, "random": true },
                "size": { "value": 3, "random": true },
                "line_linked": { "enable": false },
                "move": {
                    "enable": true,
                    "speed": 1,
                    "direction": "none",
                    "random": true,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": { "enable": true, "mode": "bubble" },
                    "onclick": { "enable": true, "mode": "repulse" }
                },
                "modes": {
                    "bubble": { "distance": 200, "size": 4, "duration": 2, "opacity": 0.8 },
                    "repulse": { "distance": 200, "duration": 0.4 }
                }
            },
            "retina_detect": true
        });
    </script>
</body>
</html>