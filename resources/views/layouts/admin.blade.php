<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AbsTrack Fitness')</title>
    
    @include('partials.fonts')
    
    <!-- MDI Icons -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    
    <!-- Vite-managed CSS (hot reload enabled) -->
    @vite([
        'resources/css/core.css',
        'resources/css/sidebar.css',
        'resources/css/custom-fonts.css',
        'resources/css/notification-bell.css',
        'resources/css/global-theme.css',
        'resources/css/pagination.css'
    ])
    
    <!-- Page-specific styles -->
    @stack('styles')
    
    <link rel="shortcut icon" href="{{ asset('template/assets/images/abstractlogotransparent.svg') }}" />
</head>
<body>
    <div class="container-scroller">
        @include('partials.sidebar')
        <div class="container-fluid page-body-wrapper">
            @include('partials.navbar')
            <div class="main-panel">
                <div class="content-wrapper">
                    @yield('content')
                </div>
                @include('partials.footer')
            </div>
        </div>
    </div>
    
    <script src="{{ asset('template/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{ asset('template/assets/vendors/chart.js/Chart.min.js') }}"></script>
    <script src="{{ asset('template/assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
    <script src="{{ asset('template/assets/vendors/jvectormap/jquery-jvectormap.min.js') }}"></script>
    <script src="{{ asset('template/assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script src="{{ asset('template/assets/vendors/owl-carousel-2/owl.carousel.min.js') }}"></script>
    <!-- End plugin js for this page -->
    
    <!-- Common JS Utilities (Vite-managed) -->
    @vite(['resources/js/common/kpi-utils.js'])
    @vite(['resources/js/common/toast-utils.js'])
    @vite(['resources/js/common/sidebar.js'])
    <script src="{{ asset('template/assets/js/misc.js') }}?v={{ time() }}"></script>
    
    <!-- Session Flash Messages as Toasts -->
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ToastUtils.showSuccess('{{ session('success') }}', 'Success');
        });
    </script>
    @endif
    
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ToastUtils.showError('{{ session('error') }}', 'Error');
        });
    </script>
    @endif
    
    @if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ToastUtils.showWarning('{{ session('warning') }}', 'Warning');
        });
    </script>
    @endif
    
    @if(session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ToastUtils.showInfo('{{ session('info') }}', 'Info');
        });
    </script>
    @endif
    
    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($errors->all() as $error)
                ToastUtils.showError('{{ addslashes($error) }}', 'Validation Error');
            @endforeach
        });
    </script>
    @endif
    
    @stack('scripts')
</body>
</html>
