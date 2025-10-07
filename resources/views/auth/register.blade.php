<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Register - AbsTrack Fitness</title>
    
    <!-- Google Fonts - Rubik -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('template/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom-fonts.css') }}">
    
    <!-- Inline Style Override for Maximum Priority -->
    <style>
        * {
            font-family: 'Rubik', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
    </style>
    
    <link rel="shortcut icon" href="{{ asset('template/assets/images/favicon.png') }}" />
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="row w-100 m-0">
                <div class="content-wrapper full-page-wrapper d-flex align-items-center auth login-bg">
                    <div class="card col-lg-4 mx-auto">
                        <div class="card-body px-5 py-5">
                            <h3 class="card-title text-left mb-3">Register</h3>
                            
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register') }}">
                                @csrf
                                
                                <div class="form-group">
                                    <label>Name *</label>
                                    <input type="text" 
                                           name="name" 
                                           class="form-control p_input @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" 
                                           required 
                                           autofocus
                                           placeholder="Enter your full name">
                                    @error('name')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" 
                                           name="email" 
                                           class="form-control p_input @error('email') is-invalid @enderror" 
                                           value="{{ old('email') }}" 
                                           required
                                           placeholder="Enter your email">
                                    @error('email')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label>Password *</label>
                                    <input type="password" 
                                           name="password" 
                                           class="form-control p_input @error('password') is-invalid @enderror" 
                                           required
                                           placeholder="Enter your password">
                                    @error('password')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label>Confirm Password *</label>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           class="form-control p_input" 
                                           required
                                           placeholder="Confirm your password">
                                </div>
                                
                                <div class="form-group d-flex align-items-center justify-content-between">
                                    <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" class="form-check-input"> 
                                            I agree to all Terms & Conditions
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-block enter-btn">Sign Up</button>
                                </div>
                                
                                <p class="sign-up text-center mt-3">Already have an Account? <a href="{{ route('login') }}">Sign In</a></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('template/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('template/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('template/assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('template/assets/js/misc.js') }}"></script>
    <script src="{{ asset('template/assets/js/settings.js') }}"></script>
    <script src="{{ asset('template/assets/js/todolist.js') }}"></script>
</body>
</html>
