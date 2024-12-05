<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/theme.js') }}"></script>
    <script>
        document.documentElement.setAttribute('data-theme', '{{ session("theme", "dark") }}');
    </script>
    <title>Sentirex - @yield('title', 'Sentiment Analysis')</title>
    
    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@3.9.0/dist/full.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tippy.js@6.3.7/dist/tippy.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tippy.js@6.3.7/dist/tippy.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-item {
            transition: all 0.3s ease;
        }

        .nav-item:hover {
            transform: translateX(5px);
        }

        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .loading-overlay {
            backdrop-filter: blur(5px);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--fallback-base-200,oklch(var(--b2)));
        }

        ::-webkit-scrollbar-thumb {
            background: var(--fallback-primary,oklch(var(--p)));
            border-radius: 4px;
        }

        /* Custom animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .float {
            animation: float 3s ease-in-out infinite;
        }

        /* Gradient borders */
        .gradient-border {
            position: relative;
            background: linear-gradient(var(--fallback-base-100,oklch(var(--b1))), var(--fallback-base-100,oklch(var(--b1)))) padding-box,
                        linear-gradient(135deg, #6366f1, #8b5cf6) border-box;
            border: 2px solid transparent;
            border-radius: 0.5rem;
        }

        /* Glass effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Custom badge animation */
        .badge-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-base-300 to-base-200">
    @include('components.report-modal')
    <div class="drawer lg:drawer-open">
        <input id="drawer" type="checkbox" class="drawer-toggle" />
        
        <!-- Main Content -->
        <div class="drawer-content flex flex-col">
            <!-- Navbar -->
            <div class="navbar bg-base-200/50 backdrop-blur-lg sticky top-0 z-50 border-b border-base-300">
                <div class="flex-none lg:hidden">
                    <label for="drawer" class="btn btn-square btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </label>
                </div>
                <div class="flex-1 px-4">
                    <h1 class="text-xl font-semibold gradient-text">@yield('header', 'Dashboard')</h1>
                </div>
                <div class="flex-none gap-4">
                    <button class="btn btn-ghost btn-circle" id="notificationBtn" data-tippy-content="Notifications">
                        <div class="indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="badge badge-xs badge-primary badge-pulse indicator-item"></span>
                        </div>
                    </button>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-circle avatar">
                            <div class="w-10 rounded-full bg-primary/10">
                                <span class="text-lg font-semibold">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                            </div>
                        </label>
                        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-200 rounded-box w-52">
                            <li><a>Profile</a></li>
                            <li><a>Settings</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <label class="swap swap-rotate btn btn-ghost btn-circle">
                        <input type="checkbox" class="theme-controller" value="light" />
                        <svg class="swap-on fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z"/>
                        </svg>
                        <svg class="swap-off fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z"/>
                        </svg>
                    </label>
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="footer footer-center p-4 bg-base-200/50 text-base-content border-t border-base-300">
                <div>
                    <p>Copyright © {{ date('Y') }} - All rights reserved by Sentirex</p>
                </div>
            </footer>
        </div>

        <!-- Sidebar -->
        <div class="drawer-side">
            <label for="drawer" aria-label="close sidebar" class="drawer-overlay"></label>
            <div class="menu min-h-screen w-80 bg-base-200/50 backdrop-blur-lg text-base-content border-r border-base-300">
                <div class="p-4">
                    <div class="flex items-center gap-4 p-4 mb-6">
                        <!-- Replace avatar placeholder with logo image -->
                        <div class="w-12 h-12">
                            <img 
                                src="{{ asset('images/sentirex.png') }}" 
                                alt="Sentirex Logo"
                                class="w-full h-full object-contain rounded-xl float"
                            />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold gradient-text">Sentirex</h2>
                            <p class="text-sm opacity-70">AI-Powered Analysis</p>
                        </div>
                    </div>

                    <ul class="menu menu-lg gap-2">
                        <li>
                            <a href="{{ route('home') }}" class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('analyze') }}" class="nav-item {{ request()->routeIs('analyze') ? 'active' : '' }}">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                Analyze
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('history') }}" class="nav-item {{ request()->routeIs('history') ? 'active' : '' }}">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                History
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('settings') }}" class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </a>
                        </li>
                    </ul>

                    <!-- Stats Summary -->
                    <div class="mt-8 p-4">
                        <h3 class="font-semibold mb-4 opacity-70">Quick Stats</h3>
                        <div class="stats stats-vertical shadow bg-base-100 w-full">
                            <div class="stat px-4 py-2">
                                <div class="stat-title">Today's Analysis</div>
                                <div class="stat-value text-primary text-2xl">{{ $todayStats['count'] ?? 0 }}</div>
                            </div>
                            <div class="stat px-4 py-2">
                                <div class="stat-title">Positive Rate</div>
                                <div class="stat-value text-success text-2xl">{{ $todayStats['positive_rate'] ?? '0%' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="absolute bottom-0 left-0 right-0 p-4">
                        <div class="bg-primary/10 rounded-xl p-4">
                            <h3 class="font-semibold mb-2">Need Help?</h3>
                            <p class="text-sm opacity-70 mb-4">Check our documentation for detailed guides and examples.</p>
                            <a href="#" class="btn btn-primary btn-sm w-full">View Documentation</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Panel -->
    <div id="notificationPanel" class="hidden fixed right-4 top-20 w-80 bg-base-200 shadow-lg rounded-lg z-50">
        <div class="p-4 border-b border-base-300">
            <h3 class="font-bold">Notifications</h3>
        </div>
        <div class="p-4 max-h-96 overflow-y-auto">
            <!-- Notification items will be inserted here -->
        </div>
    </div>

    @stack('modals')
    @stack('scripts')

    <script src="{{ asset('js/theme.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            tippy('[data-tippy-content]', {
                theme: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'
            });

            // Initialize theme
            initTheme();

            // Notification panel toggle
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationPanel = document.getElementById('notificationPanel');
            
            notificationBtn?.addEventListener('click', () => {
                notificationPanel.classList.toggle('hidden');
                if (!notificationPanel.classList.contains('hidden')) {
                    gsap.from(notificationPanel, {
                        y: -20,
                        opacity: 0,
                        duration: 0.3,
                        ease: 'power2.out'
                    });
                }
            });

            // Close notification panel when clicking outside
            document.addEventListener('click', (e) => {
                if (!notificationPanel.contains(e.target) && !notificationBtn.contains(e.target)) {
                    notificationPanel.classList.add('hidden');
                }
            });

            // Add smooth scrolling
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Initialize custom animations
            gsap.fromTo('.float', 
                { y: 0 }, 
                { 
                    y: -10,
                    duration: 2,
                    repeat: -1,
                    yoyo: true,
                    ease: "power1.inOut"
                }
            );
        });

        // Flash message handling
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false,
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1f2937' : '#ffffff',
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1f2937' : '#ffffff',
            });
        @endif
    </script>
</body>
</html>

@vite(['resources/css/app.css', 'resources/js/app.js'])