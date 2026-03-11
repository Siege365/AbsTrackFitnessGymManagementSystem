<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Register - AbsTrack Fitness Gym</title>
    
    @include('partials.fonts')
    
    <link rel="stylesheet" href="{{ asset('template/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    @vite(['resources/css/auth-register.css'])
    <link rel="shortcut icon" href="{{ asset('template/assets/images/favicon.png') }}" />
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body>
    <div class="auth-container">
        <!-- Left side - Background Image -->
        <div class="auth-image-section" id="authBackground"></div>
        
        <!-- Right side - Register Form -->
        <div class="auth-form-section">
            <div class="auth-form-wrapper">
                <!-- Logo -->
                <div class="auth-logo">
                    <img src="{{ asset('template/assets/images/navbar logo.png') }}" alt="AbsTrack Fitness">
                </div>
                
                <!-- Title -->
                <p class="auth-title">Sign up your account</p>
                
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
                
                <!-- Register Form -->
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <!-- First Name & Last Name -->
                    <div class="form-row">
                        <div>
                            <label class="form-label">First Name</label>
                            <input type="text" 
                                   name="first_name" 
                                   class="form-control" 
                                   value="{{ old('first_name') }}" 
                                   placeholder="First name">
                        </div>
                        <div>
                            <label class="form-label">Last Name</label>
                            <input type="text" 
                                   name="last_name" 
                                   class="form-control" 
                                   value="{{ old('last_name') }}" 
                                   placeholder="Last name">
                        </div>
                    </div>
                    
                    <!-- Contact & Confirmatory Contact -->
                    <div class="form-row">
                        <div>
                            <label class="form-label">Contact#</label>
                            <input type="text" 
                                   name="contact" 
                                   class="form-control" 
                                   value="{{ old('contact') }}" 
                                   placeholder="Contact number">
                        </div>
                        <div>
                            <label class="form-label">Confirmatory Contact#</label>
                            <input type="text" 
                                   name="contact_confirmation" 
                                   class="form-control" 
                                   placeholder="Confirm contact">
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" 
                               name="address" 
                               class="form-control" 
                               value="{{ old('address') }}" 
                               placeholder="Complete address">
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" 
                               name="email" 
                               class="form-control" 
                               value="{{ old('email') }}" 
                               required
                               placeholder="email@domain.com">
                    </div>
                    
                    <!-- Password -->
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" 
                               name="password" 
                               class="form-control" 
                               required
                               placeholder="••••••••••••••">
                    </div>
                    
                    <!-- Cloudflare Turnstile -->
                    <div class="turnstile-wrapper">
                        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="dark" data-size="flexible"></div>
                        @error('cf-turnstile-response')
                            <span class="text-danger" style="font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Register Button -->
                    <button type="submit" class="btn-register">Register</button>
                </form>
            </div>
        </div>
    </div>
    
    @vite(['resources/js/auth-register.js'])
</body>
</html>
