<!DOCTYPE html>
<html lang="en">
<head>
<meta name="google-site-verification" content="BpJUnDdIY6XZAzAfXkH9vJNQiS72qTEnI2xN9l3-3oc" />
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
    <meta property="og:image"       content="@yield('og_image', asset('logo.png'))">
    <meta property="og:image:secure_url" content="@yield('og_image', asset('logo.png'))">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt"    content="@yield('og_title', 'eHealthFinder')">
    <meta property="og:site_name"   content="eHealthFinder">
    <meta property="og:locale"      content="en_US">

    {{-- TWITTER CARD --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="@yield('og_title', 'eHealthFinder')">
    <meta name="twitter:description" content="@yield('og_description', 'Find specialist doctors and medicines in Bangladesh.')">
    <meta name="twitter:image"       content="@yield('og_image', asset('logo.png'))">
    <meta name="twitter:image:alt"   content="@yield('og_title', 'eHealthFinder')">
    <meta name="twitter:site"        content="@ehealthfinder">

    {{-- JSON-LD STRUCTURED DATA (per-page) --}}
    @yield('schema')
    @yield('custom_meta')

    {{-- FAVICON --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-85JZV06S65"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-85JZV06S65');
</script>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8135122441154292"
     crossorigin="anonymous"></script>
     <script async custom-element="amp-auto-ads"
        src="https://cdn.ampproject.org/v0/amp-auto-ads-0.1.js">
</script>
</head>
<body>
<amp-auto-ads type="adsense"
        data-ad-client="ca-pub-8135122441154292">
</amp-auto-ads>
    <header>
        <div class="nav-container">
            <a href="{{ route('home') }}" class="logo" style="-webkit-text-fill-color:initial; background:none;">
                <img onerror="this.outerHTML='💊'" src="{{ asset('logo.png') }}" alt="eHealthFinder" style="height:42px; width:auto; display:block;">
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
                <a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.index') || request()->routeIs('blog.show') ? 'active' : '' }}">
                    <i>📰</i> Blog
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
                    <img onerror="this.outerHTML='💊'" src="{{ asset('logo.png') }}" alt="eHealthFinder" style="height:40px; width:auto; filter:brightness(0) invert(1); display:block;">
                </a>
                <p>Bangladesh's leading healthcare portal. Find expert doctors, discover accurate medicine information, and make informed medical decisions — all in one place.</p>
                <div style="display:flex; gap:0.75rem; margin-top:0.5rem;">
                    <span style="background:rgba(255,255,255,0.08); border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; font-size:1rem;">🌐</span>
                    <span style="background:rgba(255,255,255,0.08); border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; font-size:1rem;">📧</span>
                </div>
            </div>

            <div class="footer-col">
                <h3>For Patients</h3>
                <ul class="footer-links">
                    <li><a href="{{ route('doctors.index') }}">🔍 Find a Doctor</a></li>
                    <li><a href="{{ route('medicines.index') }}">💊 Medicine Search</a></li>
                    <li><a href="{{ route('medicine.links') }}">📋 Medicine A-Z List</a></li>
                    <li><a href="{{ route('blog.index') }}">📰 Blog</a></li>
                    <li><a href="{{ route('doctors.index', ['specialty_id' => '']) }}">👨‍⚕️ All Specialties</a></li>
                    <li><a href="{{ route('home') }}#specialties">🏥 Browse by Specialty</a></li>
                    <li><a href="{{ route('home') }}#cities">📍 Browse by City</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Company</h3>
                <ul class="footer-links">
                    <li><a href="{{ route('about') }}">ℹ️ About Us</a></li>
                    <li><a href="{{ route('privacy') }}">🔒 Privacy Policy</a></li>
                    <li><a href="{{ route('disclaimer') }}">⚠️ Disclaimer</a></li>
                    <li><a href="{{ route('terms') }}">📋 Terms of Use</a></li>
                    <li><a href="{{ route('refund') }}">↩️ Return & Refund Policy</a></li>
                    <li><a href="mailto:contact@ehealthfinder.com">✉️ Contact Us</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="{{ route('doctors.index', ['location_id' => 1]) }}">Doctors in Dhaka</a></li>
                    <li><a href="{{ route('doctors.index', ['location_id' => 2]) }}">Doctors in Chittagong</a></li>
                    <li><a href="{{ route('doctors.index', ['location_id' => 4]) }}">Doctors in Sylhet</a></li>
                    <li><a href="{{ route('doctors.index', ['location_id' => 15]) }}">Doctors in Rangpur</a></li>
                    <li><a href="{{ route('doctors.index', ['location_id' => 9]) }}">Doctors in Rajshahi</a></li>
                    <li><a href="/sitemap.xml">🗺️ Sitemap</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>
                &copy; {{ date('Y') }} <strong>eHealthFinder</strong> — Bangladesh's Healthcare Portal. All rights reserved.
                &nbsp;|&nbsp; <a href="{{ route('privacy') }}" style="color:#94a3b8;">Privacy</a>
                &nbsp;|&nbsp; <a href="{{ route('terms') }}" style="color:#94a3b8;">Terms</a>
                &nbsp;|&nbsp; <a href="{{ route('disclaimer') }}" style="color:#94a3b8;">Disclaimer</a>
                &nbsp;|&nbsp; <a href="{{ route('refund') }}" style="color:#94a3b8;">Return Policy</a>
            </p>
        </div>
    </footer>
</body>
</html>
