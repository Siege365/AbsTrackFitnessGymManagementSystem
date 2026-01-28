<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'AbsTrack Fitness')</title>
    
    @include('partials.fonts')
    
    <!-- MDI Icons -->
    <link rel="stylesheet" href="{{ asset('template/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    
    <!-- Core CSS (Replaces Bootstrap + Template) -->
    <link rel="stylesheet" href="{{ asset('css/core.css') }}?v={{ time() }}">
    
    <!-- Sidebar Styles -->
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}?v={{ time() }}">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/custom-fonts.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/notification-bell.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/global-theme.css') }}?v={{ time() }}">
    
    <!-- Page-specific styles -->
    @stack('styles')
    
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
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
    
    <!-- Common JS Utilities -->
    <script src="{{ asset('js/common/sidebar.js') }}?v={{ time() }}"></script>
    
    @stack('scripts')
</body>
</html>
