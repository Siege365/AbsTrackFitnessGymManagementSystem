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
    <!-- inject:js -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle (collapse/expand)
            const sidebarToggler = document.querySelector('.navbar-toggler');
            const body = document.body;
            
            // Check if sidebar state is saved in localStorage
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                body.classList.add('sidebar-icon-only');
            }
            
            if (sidebarToggler) {
                sidebarToggler.addEventListener('click', function(e) {
                    e.preventDefault();
                    body.classList.toggle('sidebar-icon-only');
                    localStorage.setItem('sidebarCollapsed', body.classList.contains('sidebar-icon-only'));
                });
            }
            
            // Sidebar submenu toggle
            const toggleLinks = document.querySelectorAll('.sidebar .nav-link[data-toggle="collapse"]');
            
            toggleLinks.forEach(function(element) {
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const targetSelector = this.getAttribute('href');
                    const target = document.querySelector(targetSelector);
                    const parent = this.closest('.menu-items');
                    
                    if (!target) return;
                    
                    // Toggle collapse
                    if (target.classList.contains('show')) {
                        target.classList.remove('show');
                        parent.classList.remove('active');
                    } else {
                        // Close other open menus first
                        document.querySelectorAll('.sidebar .collapse.show').forEach(function(openMenu) {
                            openMenu.classList.remove('show');
                            const parentItem = openMenu.closest('.menu-items');
                            if (parentItem && !parentItem.querySelector('.sub-menu .nav-item.active')) {
                                parentItem.classList.remove('active');
                            }
                        });
                        
                        target.classList.add('show');
                        parent.classList.add('active');
                    }
                });
            });
            
            // Set active menu based on current URL
            const currentPath = window.location.pathname;
            
            // Find and mark the active page
            document.querySelectorAll('.sidebar .nav-link:not([data-toggle="collapse"])').forEach(function(link) {
                const href = link.getAttribute('href');
                if (!href || href === '#') return;
                
                try {
                    const linkPath = new URL(href, window.location.origin).pathname;
                    const isActive = currentPath === linkPath || 
                                    (linkPath !== '/' && currentPath.startsWith(linkPath));
                    
                    if (isActive) {
                        const navItem = link.closest('.nav-item');
                        if (navItem) {
                            navItem.classList.add('active');
                        }
                        
                        // If it's inside a submenu, open the parent collapse (but don't mark parent as active)
                        const subMenu = link.closest('.sub-menu');
                        if (subMenu) {
                            const collapse = subMenu.closest('.collapse');
                            if (collapse) {
                                collapse.classList.add('show');
                            }
                        }
                    }
                } catch (e) {
                    // Skip invalid URLs
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
