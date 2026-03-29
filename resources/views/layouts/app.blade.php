<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- PRIMARY SEO --}}
    <title>@yield('title', 'eHealthFinder | Find Specialist Doctors & Medicine in Bangladesh')</title>
    <meta name="description" content="@yield('meta_description', 'Search verified specialist doctors, book appointments, and find medicine information across Bangladesh on eHealthFinder.')">
    <meta name="keywords" content="@yield('meta_keywords', 'doctor Bangladesh, specialist doctor, medicine price Bangladesh, ehealthfinder, find doctor online')">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- OPEN GRAPH (Facebook / LinkedIn) --}}
    <meta property="og:type"        content="@yield('og_type', 'website')">
    <meta property="og:title"       content="@yield('og_title', 'eHealthFinder | Find Specialist Doctors & Medicine in Bangladesh')">
    <meta property="og:description" content="@yield('og_description', 'Search verified specialist doctors and medicine information across Bangladesh.')">
    <meta property="og:url"         content="{{ url()->current() }}">
    <meta property="og:image"       content="@yield('og_image', asset('images/og-default.png'))">
    <meta property="og:site_name"   content="eHealthFinder">

    {{-- TWITTER CARD --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="@yield('og_title', 'eHealthFinder')">
    <meta name="twitter:description" content="@yield('og_description', 'Find specialist doctors and medicines in Bangladesh.')">
    <meta name="twitter:image"       content="@yield('og_image', asset('images/og-default.png'))">

    {{-- JSON-LD STRUCTURED DATA (per-page) --}}
    @yield('schema')

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="{{ route('home') }}" class="logo">
                <span style="font-size:1.8rem">🩺</span> eHealthFinder
            </a>
            
            <button class="mobile-toggle" onclick="document.querySelector('.nav-links').classList.toggle('active')">
                ☰
            </button>

            <nav class="nav-links">
                <a href="{{ route('doctors.index') }}" class="{{ request()->routeIs('doctors.*', 'doctor.*') ? 'active' : '' }}">
                    <i>👨‍⚕️</i> Find Doctors
                </a>
                <a href="{{ route('medicines.index') }}" class="{{ request()->routeIs('medicines.*', 'medicine.*') ? 'active' : '' }}">
                    <i>💊</i> Medicine Index
                </a>
            </nav>
        </div>
    </header>

    <main class="animate-fade">
        @yield('content')
    </main>

    <footer class="premium-footer">
        <div class="footer-grid">
            <div class="footer-col footer-about">
                <a href="{{ route('home') }}" class="footer-logo">
                    <span style="font-size:1.8rem">🩺</span> eHealthFinder
                </a>
                <p>Leading healthcare portal in Bangladesh. Find expert doctors, discover accurate medicine information, and make informed medical decisions.</p>
            </div>
            
            <div class="footer-col">
                <h3>For Patients</h3>
                <ul class="footer-links">
                    <li><a href="{{ route('doctors.index') }}">Find a Doctor</a></li>
                    <li><a href="{{ route('medicines.index') }}">Medicine Index</a></li>
                    <li><a href="#">Health Blog</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h3>For Providers</h3>
                <ul class="footer-links">
                    <li><a href="#">Join eHealthFinder</a></li>
                    <li><a href="#">Claim Profile</a></li>
                    <li><a href="#">Provider Support</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h3>Company</h3>
                <ul class="footer-links">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact Support</a></li>
                    <li><a href="#">Terms & Privacy</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} <strong>eHealthFinder</strong> Portal. Built with Laravel. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
