<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - AbsTrack Fitness Gym</title>
    
    @include('partials.fonts')
    
    <link rel="stylesheet" href="{{ asset('template/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    @vite(['resources/css/auth-login.css'])
    <link rel="shortcut icon" href="{{ asset('template/assets/images/favicon.png') }}" />
    @php($turnstileEnabled = filled(config('services.turnstile.site_key')) && filled(config('services.turnstile.secret_key')))
    @if ($turnstileEnabled)
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
</head>
<body>
    <div class="auth-container">
        <!-- Left side - Background Image -->
        <div class="auth-image-section" id="authBackground"></div>
        
        <!-- Right side - Login Form -->
        <div class="auth-form-section">
            <div class="auth-form-wrapper">
                <!-- Logo -->
                <div class="auth-logo">
                    <img src="{{ asset('template/assets/images/navbar logo.png') }}" alt="AbsTrack Fitness">
                </div>
                
                <!-- Title -->
                <p class="auth-title">Sign in your account</p>
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif
                
                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <!-- Email -->
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" 
                               name="email" 
                               class="form-control" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus
                               placeholder="email@domain.com">
                    </div>
                    
                    <!-- Password -->
                    <div>
                        <label class="form-label">Password</label>
                        <input type="password" 
                               name="password" 
                               class="form-control" 
                               required
                               placeholder="••••••••••••••">
                    </div>
                    
                    <!-- Forgot Password -->
                    <div class="forgot-password">
                        <a href="#">Forgot Password?</a>
                    </div>
                    
                    @if ($turnstileEnabled)
                        <!-- Cloudflare Turnstile -->
                        <div class="turnstile-wrapper">
                            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="dark" data-size="flexible"></div>
                            @error('cf-turnstile-response')
                                <span class="text-danger" style="font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <!-- Login Button -->
                    <button type="submit" class="btn-login">Login</button>
                </form>
            </div>
        </div>
    </div>
    
    @vite(['resources/js/auth-login.js'])
</body>
</html>
