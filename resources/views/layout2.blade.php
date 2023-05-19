@php
    $setting = App\Models\Setting::select('logo','favicon','topbar_phone','topbar_email','opening_time','selected_theme','text_direction')->first();
    $social_icons = App\Models\FooterSocialLink::select('icon','link')->get();
    $custom_pages = App\Models\CustomPage::where('status',1)->get();
    $googleAnalytic = App\Models\GoogleAnalytic::first();
    $facebookPixel = App\Models\FacebookPixel::first();
    $menus = App\Models\MenuVisibility::all();
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi" />
    <link rel="icon" type="image/png" href="{{ asset($setting->favicon) }}">
    @yield('title')
    @yield('meta')

    <link rel="stylesheet" href="{{ asset('frontend/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/jquery.calendar.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/spacing.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/dev.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('toastr/toastr.min.css') }}">
    @if ($setting->text_direction == 'rtl')
    <link rel="stylesheet" href="{{ asset('frontend/css/rtl.css') }}">
    @endif

    @include('theme_color_css')

    <!--jquery library js-->
    <script src="{{ asset('frontend/js/jquery-3.6.0.min.js') }}"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <script src="{{ asset('frontend/js/flatpickr.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('frontend/css/flatpickr.min.css') }}">

    @if ($googleAnalytic->status == 1)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalytic->analytic_id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $googleAnalytic->analytic_id }}');
    </script>
    @endif

    @if ($facebookPixel->status == 1)
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $facebookPixel->app_id }}');
        fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $facebookPixel->app_id }}&ev=PageView&noscript=1"
    /></noscript>
    @endif

</head>

<body class="home_2">


    <!--=========================
        TOPBAR START
    ==========================-->
    <section class="wsus__topbar">
        <div class="container">
            <div class="row">
                <div class="col-xl-7 col-12 col-lg-8">
                    <ul class="wsus__topbar_info d-flex flex-wrap">
                        <li><a href="callto:{{ $setting->topbar_phone }}"><i class="fas fa-phone-alt"></i> {{ $setting->topbar_phone }}</a></li>
                        <li><a href="mailto:{{ $setting->topbar_email }}"><i class="fas fa-envelope"></i> {{ $setting->topbar_email }}</a>
                        </li>
                        <li class="d-none d-sm-block"><span><i class="fas fa-clock"></i> {{ $setting->opening_time }}</span></li>
                    </ul>
                </div>
                <div class="col-xl-5 col-12 col-lg-4">
                    <div class="wsus__topbar_right d-flex flex-wrap justify-content-end">

                        <ul class="wsus__topbar_icon d-flex flex-wrap">
                            @foreach ($social_icons as $social_icon)
                            <li><a target="_blank" href="{{ $social_icon->link }}"><i class="{{ $social_icon->icon }}"></i></a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        TOPBAR END
    ==========================-->


    <!--=========================
        MENU START
    ==========================-->
    <nav class="navbar navbar-expand-lg main_menu">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset($setting->logo) }}" alt="logo" class="img-fluid">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="far fa-bars open_m_menu"></i>
                <i class="far fa-times close_m_menu"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav m-auto">
                    @php
                        $menu = $menus->where('id', 1)->first();
                    @endphp

                    @if ($menu->status == 1)
                        @if ($setting->selected_theme == 0)
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="{{ route('home') }}">{{ $menu->custom_name }}<i
                                        class="far fa-angle-down"></i></a>
                                <ul class="wsus__droap_menu">
                                    <li><a href="{{ route('home',['theme' => 1]) }}">{{__('user.home page 1')}}</a></li>
                                    <li><a class="active" href="{{ route('home',['theme' => 2]) }}">{{__('user.home page 2')}}</a></li>
                                    <li><a href="{{ route('home',['theme' => 3]) }}">{{__('user.home page 3')}}</a></li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="{{ route('home') }}">{{ $menu->custom_name }}</a>
                            </li>
                        @endif
                    @endif

                    @php
                        $menu = $menus->where('id', 2)->first();
                    @endphp

                    @if ($menu->status == 1)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('about-us') }}">{{ $menu->custom_name }}</a>
                    </li>
                    @endif

                    @php
                        $menu = $menus->where('id', 3)->first();
                    @endphp

                    @if ($menu->status == 1)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('services') }}">{{ $menu->custom_name }}</a>
                    </li>
                    @endif

                    @php
                        $menu = $menus->where('id', 4)->first();
                    @endphp

                    @if ($menu->status == 1)

                    <li class="nav-item">
                        <a class="nav-link" href="#">{{ $menu->custom_name }} <i class="far fa-angle-down"></i></a>
                        <ul class="wsus__droap_menu">

                            @php
                                $menu = $menus->where('id', 5)->first();
                            @endphp

                            @if ($menu->status == 1)
                            <li><a href="{{ route('faq') }}">{{ $menu->custom_name }}</a></li>
                            @endif

                            @php
                                $menu = $menus->where('id', 6)->first();
                            @endphp

                            @if ($menu->status == 1)
                            <li><a href="{{ route('terms-and-conditions') }}">{{ $menu->custom_name }}</a></li>
                            @endif

                            @php
                                $menu = $menus->where('id', 7)->first();
                            @endphp

                            @if ($menu->status == 1)
                            <li><a href="{{ route('privacy-policy') }}">{{ $menu->custom_name }}</a></li>
                            @endif

                            @php
                                $menu = $menus->where('id', 8)->first();
                            @endphp
                            @if ($menu->status == 1)
                                @foreach ($custom_pages as $custom_page)
                                <li><a href="{{ route('page', $custom_page->slug) }}">{{ $custom_page->page_name }}</a></li>
                                @endforeach
                            @endif
                        </ul>
                    </li>

                    @endif

                    @php
                        $menu = $menus->where('id', 9)->first();
                    @endphp

                    @if ($menu->status == 1)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('blogs') }}">{{ $menu->custom_name }}</a>
                    </li>
                    @endif

                    @php
                        $menu = $menus->where('id', 10)->first();
                    @endphp

                    @if ($menu->status == 1)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact-us') }}">{{ $menu->custom_name }}</a>
                    </li>
                    @endif

                </ul>
                <ul class="wsus__right_menu d-flex flex-wrap">
                    <li>
                        <form class="menu_search_form" action="{{ route('services') }}">
                            <input type="text" placeholder="{{__('user.Search')}}" name="search">
                            <button class="submit" type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </li>
                    <li><a href="{{ route('contact-us') }}">{{__('user.Hire Now')}} <i class="far fa-angle-right"></i></a></li>
                    <li><a href="{{ route('dashboard') }}"><i class="fas fa-user"></i></a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!--=========================
        MENU END
    ==========================-->


    @yield('frontend-content')


    @php
        $setting = App\Models\Setting::select('logo','footer_logo')->first();
        $social_icons = App\Models\FooterSocialLink::select('icon','link')->get();
        $footer_informations = App\Models\Footer::select('email','phone','address','copyright','payment_image','about_us','first_column','second_column')->first();
        $first_col_links = App\Models\FooterLink::where('column',1)->get();
        $second_col_links = App\Models\FooterLink::where('column',2)->get();

        $tawk_setting = App\Models\TawkChat::first();
        $cookie_consent = App\Models\CookieConsent::first();
    @endphp

    <!--=========================
        FOOTER START
    ==========================-->
    <footer>
        <div class="container pt_100 xs_pt_70">
            <div class="row justify-content-between">
                <div class="col-lg-4 col-md-10">
                    <div class="wsus__footer_content">
                        <a class="footer_logo" href="{{ route('home') }}">
                            <img src="{{ asset($setting->footer_logo) }}" alt="logo" class="img-fluid w-100">
                        </a>
                        <span>{{ $footer_informations->about_us }}
                        </span>
                        <ul class="social_link d-flex flex-wrap">
                            @foreach ($social_icons as $social_icon)
                            <li><a target="_blank" href="{{ $social_icon->link }}"><i class="{{ $social_icon->icon }}"></i></a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="wsus__footer_content">
                        <h2>{{__('user.Important Link')}}</h2>
                        <ul class="footer_link">
                            <li><a href="{{ route('contact-us') }}">{{__('user.Contact Us')}}</a></li>
                            <li><a href="{{ route('blogs') }}">{{__('user.Our Blog')}}</a></li>
                            <li><a href="{{ route('faq') }}">{{__('user.FAQ')}}</a></li>
                            <li><a href="{{ route('terms-and-conditions') }}">{{__('user.Terms And Conditions')}}</a></li>
                            <li><a href="{{ route('privacy-policy') }}">{{__('user.Privacy Policy')}}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="wsus__footer_content">
                        <h2>{{__('user.Quick Link')}}</h2>
                        <ul class="footer_link">
                            <li><a href="{{ route('services') }}">{{__('user.Our Services')}}</a></li>
                            <li><a href="{{ route('about-us') }}">{{__('user.Why Choose Us')}}</a></li>
                            <li><a href="{{ route('dashboard') }}">{{__('user.My Profile')}}</a></li>
                            <li><a href="{{ route('about-us') }}">{{__('user.About Us')}}</a></li>
                            <li><a href="{{ route('become-a-handyman') }}">{{__('user.Join as a Provider')}}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-12">
                    <div class="wsus__footer_content m-0 p-0 border-0">
                        <h2>{{__('user.Contact Info')}}</h2>
                        <ul class="footer_contact">
                            <li>
                                <a href="callto:{{ $footer_informations->phone }}"><i class="fas fa-phone-alt"></i> {{ $footer_informations->phone }}</a>
                            </li>
                            <li>
                                <a href="mailto:{{ $footer_informations->email }}"><i class="fas fa-envelope"></i>
                                    {{ $footer_informations->email }}</a>
                            </li>
                            <li>
                                <p><i class="fas fa-map-marker-alt"></i> {{ $footer_informations->address }}
                                </p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="wsus__footer_bottom mt_100 xs_mt_70">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="wsus__footer_bottom_content d-flex justify-content-between align-items-center">
                            <p>{{ $footer_informations->copyright }}</p>
                            <img src="{{ asset($footer_informations->payment_image) }}" alt="payment" class="img-fluid w-100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!--=========================
        FOOTER END
    ==========================-->


    <!--=========================
        SCROLL BUTTON START
    ===========================-->
    <div class="wsus__scroll_btn">
        <span><i class="fas fa-arrow-alt-up"></i></span>
    </div>
    <!--==========================
        SCROLL BUTTON END
    ===========================-->

    @if ($tawk_setting->status == 1)
    <script type="text/javascript">
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        (function(){
            var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
            s1.async=true;
            s1.src='{{ $tawk_setting->chat_link }}';
            s1.charset='UTF-8';
            s1.setAttribute('crossorigin','*');
            s0.parentNode.insertBefore(s1,s0);
        })();
    </script>

    @endif

    @if ($cookie_consent->status == 1)
    <script src="{{ asset('frontend/js/cookieconsent.min.js') }}"></script>

    <script>
    window.addEventListener("load",function(){window.wpcc.init({"border":"{{ $cookie_consent->border }}","corners":"{{ $cookie_consent->corners }}","colors":{"popup":{"background":"{{ $cookie_consent->background_color }}","text":"{{ $cookie_consent->text_color }} !important","border":"{{ $cookie_consent->border_color }}"},"button":{"background":"{{ $cookie_consent->btn_bg_color }}","text":"{{ $cookie_consent->btn_text_color }}"}},"content":{"href":"{{ route('privacy-policy') }}","message":"{{ $cookie_consent->message }}","link":"{{ $cookie_consent->link_text }}","button":"{{ $cookie_consent->btn_text }}"}})});
    </script>
    @endif

    <!--bootstrap js-->
    <script src="{{ asset('frontend/js/bootstrap.bundle.min.js') }}"></script>
    <!--font-awesome js-->
    <script src="{{ asset('frontend/js/Font-Awesome.js') }}"></script>
    <!-- select js -->
    <script src="{{ asset('frontend/js/select2.min.js') }}"></script>
    <!-- counter up js -->
    <script src="{{ asset('frontend/js/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('frontend/js/jquery.countup.min.js') }}"></script>
    <!-- slick js -->
    <script src="{{ asset('frontend/js/slick.min.js') }}"></script>
    <!-- calender js -->
    <script src="{{ asset('frontend/js/jquery.calendar.js') }}"></script>
    <!-- sticky sidebar -->
    <script src="{{ asset('frontend/js/sticky_sidebar.js') }}"></script>
    <script src="{{ asset('backend/js/bootstrap-datepicker.min.js') }}"></script>
    <!--main/custom js-->
    <script src="{{ asset('frontend/js/main.js') }}"></script>

    <script src="{{ asset('toastr/toastr.min.js') }}"></script>


    <script>
        @if(Session::has('messege'))
        var type="{{Session::get('alert-type','info')}}"
        switch(type){
            case 'info':
                toastr.info("{{ Session::get('messege') }}");
                break;
            case 'success':
                toastr.success("{{ Session::get('messege') }}");
                break;
            case 'warning':
                toastr.warning("{{ Session::get('messege') }}");
                break;
            case 'error':
                toastr.error("{{ Session::get('messege') }}");
                break;
        }
        @endif
    </script>

    @if ($errors->any())
    @foreach ($errors->all() as $error)
        <script>
            toastr.error('{{ $error }}');
        </script>
    @endforeach
    @endif

    <script>
        (function($) {
        "use strict";
        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                startDate: '-Infinity'
            });
        });

        })(jQuery);
    </script>
</body>

</html>
