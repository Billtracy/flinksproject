@extends('layout')

@section('title')
    <title>{{ $seo_setting->seo_title ?? "" }}</title>
@endsection

@section('title')
    <meta name="description" content="{{ $seo_setting->seo_description ?? "" }}">
@endsection
@section('frontend-content')

    @if ($intro_visibility)
    <!--=========================
        BANNER START
    ==========================-->
    <section class="wsus__banner" style="background: url({{ asset('frontend/images/banner_bg.jpg') }});">
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-xl-7 col-md-12 col-lg-7">
                    <div class="wsus__banner_text">
                        <h6>{{ $intro_section->title }}</h6>
                        <h1>{{ $intro_section->header_one }} <b>{{ $intro_section->header_two }}</b></h1>
                        <p>{{ $intro_section->description }}</p>
                        <form action="{{ route('services') }}">
                            <ul class="wsus__banner_search d-flex flex-wrap">
                                <li>
                                    <p>I am in..</p>
                                    <select name="service_area" class="select_2">
                                        <option value="">{{__('user.Select Location')}}</option>
                                        @foreach ($service_areas as $service_area)
                                        <option value="{{ $service_area->slug }}">{{ $service_area->name }}</option>
                                        @endforeach
                                    </select>
                                </li>
                                <li>
                                    <p>{{__('user.I am looking to')}}..</p>
                                    <select name="category" class="select_2">
                                        <option value="">{{__('user.Find Categories')}}</option>
                                        @foreach ($search_categories as $category)
                                        <option value="{{ $category->slug }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </li>
                                <li><button type="submit" class="common_btn">{{__('user.search')}}</button> </li>
                            </ul>
                        </form>
                    </div>
                </div>
                <div class="col-xl-5 col-md-7 col-lg-5 m-auto">
                    <div class="wsus__banner_img">
                        <div class="img">
                            <img src="{{ asset($intro_section->image) }}" alt="img-fluid w-100">
                            <div class="banner_counter">
                                <span class="counter">{{ $intro_section->total_service_sold }}</span>
                                <p> {{__('user.Service Sold')}}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        BANNER END
    ==========================-->
    @endif

    @if ($category_section->visibility && !$categories->isEmpty())
    <!--=========================
        CATEGORIES START
    ==========================-->
    <section class="wsus__categories mt_90 xs_mt_60">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 m-auto">
                    <div class="wsus__section_heading text-center mb_45">
                        <h2>{{ $category_section->title }}</h2>
                        <p>{{ $category_section->description }}</p>
                    </div>
                </div>
            </div>
            <div class="row category_slider">
                @foreach ($categories as $category)
                    <div class="col-xl-2">
                        <div class="wsus__single_categories">
                            <span>
                                <img src="{{ asset($category->icon) }}" alt="categories" class="img-fluid w-100">
                            </span>
                            <a href="{{ route('services',['category' => $category->slug]) }}">{{ $category->name }}</a>
                            <p>{{ $category->totalService }}+ {{__('user.Services')}}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!--=========================
        CATEGORIES END
    ==========================-->

    @endif

@if ($featured_service_section->visibility && !$featured_services->isEmpty())
    <!--=========================
        FEATURED SERVICES START
    ==========================-->
    <section class="wsus__features_services mt_90 xs_mt_60 mb_60 xs_mb_30">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 m-auto">
                    <div class="wsus__section_heading text-center mb_45">
                        <h2>{{ $featured_service_section->title }}</h2>
                        <p>{{ $featured_service_section->description }}</p>
                    </div>
                </div>
            </div>
            <div class="row featured_service_slider">

                @foreach ($featured_services as $featured_service)
                    <div class="col-xl-4">
                        <div class="wsus__single_services">
                            <div class="wsus__services_img">
                                <img src="{{ asset($featured_service->image) }}" alt="service" class="img-fluid w-100">
                            </div>
                            <div class="wsus__services_text">
                                <ul class="d-flex justify-content-between">
                                    <li><a href="{{ route('services',['category'=> $featured_service->category->slug]) }}">{{ $featured_service->category->name }}</a></li>
                                    <li>{{ $currency_icon->icon }}{{ $featured_service->price }}</li>
                                </ul>
                                <a class="title" href="{{ route('service', $featured_service->slug) }}">{{ $featured_service->name }}</a>
                                <div
                                    class="single_service_footer d-flex flex-wrap justify-content-between align-items-center">
                                    <div class="img_area">
                                        <img src="{{ $featured_service->provider ? asset($featured_service->provider->image) : '' }}" alt="user" class="img-fluid">
                                        <span>{{ $featured_service->provider->name }}</span>
                                    </div>

                                    @php
                                        $reviewQty = $featured_service->totalReview;
                                        $totalReview = $featured_service->averageRating;
                                        if ($reviewQty > 0) {
                                            $average = $totalReview;
                                            $intAverage = intval($average);
                                            $nextValue = $intAverage + 1;
                                            $reviewPoint = $intAverage;
                                            $halfReview=false;
                                            if($intAverage < $average && $average < $nextValue){
                                                $reviewPoint= $intAverage + 0.5;
                                                $halfReview=true;
                                            }
                                        }
                                    @endphp

                                    @if ($reviewQty > 0)
                                        <p>
                                            @for ($i = 1; $i <=5; $i++)
                                                @if ($i <= $reviewPoint)
                                                    <i class="fas fa-star"></i>
                                                @elseif ($i> $reviewPoint )
                                                    @if ($halfReview==true)
                                                    <i class="fas fa-star-half-alt"></i>
                                                        @php
                                                            $halfReview=false
                                                        @endphp
                                                    @else
                                                    <i class="far fa-star"></i>
                                                    @endif
                                                @endif
                                            @endfor
                                            <span>({{ $featured_service->totalReview }})</span>
                                        </p>
                                    @else
                                        <p>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <span>({{ $featured_service->totalReview }})</span>
                                        </p>
                                    @endif
                                </div>
                                <a class="common_btn" href="{{ route('ready-to-booking', $featured_service->slug) }}">{{__('user.Book now')}}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!--=========================
        FEATURED SERVICES END
    ==========================-->
@endif

{{-- @if ($coundown_visibility)

    <!--=========================
        COUNTER START
    ==========================-->
    <section class="wsus__counter" style="background: url({{ asset($counter_bg_image->image) }});">
        <div class="wsus__counter_overlay">
            <div class="container">
                <div class="row">
                    @foreach ($counters as $counter)
                        <div class="col-xl-3 col-sm-6 col-lg-3">
                            <div class="wsus__single_counter">
                                <span>
                                    <img src="{{ asset($counter->icon) }}" alt="counter" class="img-fluid w-100">
                                </span>
                                <h4 class="counter">{{ $counter->number }}</h4>
                                <p>{{ $counter->title }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        COUNTER END
    ==========================-->
@endif --}}

@if ($popular_service_section->visibility && !$popular_services->isEmpty())
    <!--=========================
        POPULAR SERVICES START
    ==========================-->
    <section class="wsus__popular_services pt_90 xs_pt_60 pb_100 xs_pb_70"
        style="background: url({{ asset('frontend/images/popular_service_bg.jpg') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 m-auto">
                    <div class="wsus__section_heading text-center mb_20">
                        <h2>{{ $popular_service_section->title }}</h2>
                        <p>{{ $popular_service_section->description }}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                @foreach ($popular_services as $popular_service)
                    <div class="col-lg-4 col-md-6">
                        <div class="wsus__single_services">
                            <div class="wsus__services_img">
                                <img src="{{ asset($popular_service->image) }}" alt="service" class="img-fluid w-100">
                            </div>
                            <div class="wsus__services_text">
                                <ul class="d-flex justify-content-between">
                                    <li><a href="{{ route('services',['category' => $popular_service->category->slug]) }}">{{ $popular_service->category->name }}</a></li>
                                    <li>{{ $currency_icon->icon }}{{ $popular_service->price }}</li>
                                </ul>
                                <a class="title" href="{{ route('service', $popular_service->slug) }}">{{ $popular_service->name }}</a>
                                <div
                                    class="single_service_footer d-flex flex-wrap justify-content-between align-items-center">
                                    <div class="img_area">
                                        <img src="{{ $popular_service->provider ? asset($popular_service->provider->image) : '' }}" alt="user" class="img-fluid">
                                        <span>{{ $popular_service->provider->name }}</span>
                                    </div>

                                    @php
                                        $reviewQty = $popular_service->totalReview;
                                        $totalReview = $popular_service->averageRating;
                                        if ($reviewQty > 0) {
                                            $average = $totalReview;
                                            $intAverage = intval($average);
                                            $nextValue = $intAverage + 1;
                                            $reviewPoint = $intAverage;
                                            $halfReview=false;
                                            if($intAverage < $average && $average < $nextValue){
                                                $reviewPoint= $intAverage + 0.5;
                                                $halfReview=true;
                                            }
                                        }
                                    @endphp

                                    @if ($reviewQty > 0)
                                        <p>
                                            @for ($i = 1; $i <=5; $i++)
                                                @if ($i <= $reviewPoint)
                                                    <i class="fas fa-star"></i>
                                                @elseif ($i> $reviewPoint )
                                                    @if ($halfReview==true)
                                                    <i class="fas fa-star-half-alt"></i>
                                                        @php
                                                            $halfReview=false
                                                        @endphp
                                                    @else
                                                    <i class="far fa-star"></i>
                                                    @endif
                                                @endif
                                            @endfor
                                            <span>({{ $popular_service->totalReview }})</span>
                                        </p>
                                    @else
                                        <p>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <span>({{ $popular_service->totalReview }})</span>
                                        </p>
                                    @endif

                                </div>
                                <a class="common_btn" href="{{ route('ready-to-booking', $popular_service->slug) }}">{{__('user.Book now')}}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!--=========================
        POPULAR SERVICES END
    ==========================-->
@endif

@if ($join_as_provider_visibility)


    <!--=========================
        SELLAR JOIN START
    ==========================-->
    <section class="wsus__seller_join pt_90 xs_pt_60 pb_100 xs_pb_70" style="background: url({{ asset($join_as_a_provider->image) }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto">
                    <div class="wsus__seller_join_text text-center">
                        <h3>{{ $join_as_a_provider->title }}</h3>
                        <a href="{{ route('join-as-a-provider') }}">{{ $join_as_a_provider->button_text }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        SELLAR JOIN END
    ==========================-->
        @endif


    {{-- @if ($mobile_app_section_visbility)

    <!--=========================
        APP DOWNLOAD START
    ==========================-->
    <section class="wsus__app_download mt_100 xs_mt_70">
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-xl-5 col-md-7">
                    <div class="wsus__app_download_text">
                        <h5>{{ $mobile_app->short_title }}</h5>
                        <h2>{{ $mobile_app->full_title }}</h2>
                        <p>{{ $mobile_app->description }}</p>
                        <ul class="d-flex flex-wrap">
                            <li>
                                <a href="{{ $mobile_app->play_store }}">
                                    <i class="fab fa-google-play"></i>
                                    <span>{{__('user.Available on the')}} <b>{{__('user.Google Play')}}</b></span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ $mobile_app->app_store }}">
                                    <i class="fab fa-apple"></i>
                                    <span>{{__('user.Download on the')}} <b>{{__('user.App Store')}}</b></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-6 col-md-5">
                    <div class="wsus__app_download_img">
                        <img src="{{ asset($mobile_app->image) }}" alt="app download" class="img-fluid w-100">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        APP DOWNLOAD END
    ==========================-->
    @endif --}}

    @if ($testimonial_section->visibility && !$testimonials->isEmpty())
    <!--=========================
        TESTIMONIAL START
    ==========================-->
    <section class="wsus__testimonial mt_100 xs_mt_70 pt_95 xs_pt_65 pb_100 xs_pb_70"
        style="background: url({{ asset('frontend/images/testimonial_bg.jpg') }});">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 m-auto">
                    <div class="wsus__section_heading text-center mb_45">
                        <h2>{{ $testimonial_section->title }}</h2>
                        <p>{{ $testimonial_section->description }}</p>
                    </div>
                </div>
            </div>
            <div class="row testi_slider">
                @foreach ($testimonials as $testimonial)
                    <div class="col-xl-6">
                        <div class="wsus__single_testimonial">
                            <p class="review_text">{{ $testimonial->comment }}</p>
                            <div class="wsus__single_testimonial_img">
                                <img src="{{ asset($testimonial->image) }}" alt="clients" class="img-fluid">
                                <p><span>{{ $testimonial->name }}</span> {{ $testimonial->designation }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!--=========================
        TESTIMONIAL END
    ==========================-->
    @endif

    @if ($blog_section->visibility && !$blogs->isEmpty())

    <!--=========================
        BLOG START
    ==========================-->
    <section class="wsus__blog mt_85 xs_mt_55">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 m-auto">
                    <div class="wsus__section_heading text-center mb_20">
                        <h2>{{ $blog_section->title }}</h2>
                        <p>{{ $blog_section->description }}</p>
                    </div>
                </div>
            </div>
            <div class="row">

                @foreach ($blogs as $blog)
                    <div class="col-lg-4 col-md-6">
                        <div class="wsus__single_blog">
                            <div class="wsus__single_blog_img">
                                <img src="{{ asset($blog->image) }}" alt="blog" class="img-fluid w-100">
                            </div>
                            <div class="wsus__single_blog_text">
                                <ul class="d-flex flex-wrap">
                                    <li><i class="far fa-user"></i> {{__('user.By Admin')}}</li>
                                    <li><i class="far fa-comment-alt-lines"></i> {{ $blog->total_comment }}{{__('user. Comments')}}</li>
                                </ul>
                                <h2><a href="{{ route('blog', $blog->slug) }}">{{ $blog->title }}</a></h2>
                                <a href="{{ route('blog', $blog->slug) }}">{{__('user.Learn More')}} <i class="far fa-long-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!--=========================
        BLOG END
    ==========================-->
    @endif


    @if ($subscription_visbility)

    <!--=========================
        SUBSCRIBE START
    ==========================-->
    <section class="wsus__subscribe mt_100 xs_mt_70" style="background: url({{ asset($subscriber->background_image) }});">
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-xl-6 col-lg-6">
                    <div class="wsus__subscribe_text mt_90 xs_mt_60 mb_100 xs_mb_70">
                        <h3>{{ $subscriber->title }}</h3>
                        <p>{{ $subscriber->description }}</p>
                        <form id="subscriberForm">
                            @csrf
                            <input type="email" placeholder="{{__('user.Your Email')}}" name="email">
                            <button id="subscribe_btn" type="submit" class="common_btn">{{__('user.Subscribe')}}</button>
                        </form>
                    </div>
                </div>
                <div class="col-xl-5 col-md-6 d-none d-lg-block">
                    <div class="wsus__subscribe_img">
                        <img src="{{ asset($subscriber->foreground_image) }}" alt="subecribe" class="img-fluid w-100">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=========================
        SUBSCRIBE END
    ==========================-->
    @endif


    <script>
        (function($) {
            "use strict";
            $(document).ready(function () {
                $("#subscriberForm").on('submit', function(e){
                    e.preventDefault();
                    var isDemo = "{{ env('APP_MODE') }}"
                    if(isDemo == 'DEMO'){
                        toastr.error('This Is Demo Version. You Can Not Change Anything');
                        return;
                    }

                    let loading = "{{__('user.Processing...')}}"

                    $("#subscribe_btn").html(loading);
                    $("#subscribe_btn").attr('disabled',true);

                    $.ajax({
                        type: 'POST',
                        data: $('#subscriberForm').serialize(),
                        url: "{{ route('subscribe-request') }}",
                        success: function (response) {
                            if(response.status == 1){
                                toastr.success(response.message);
                                let subscribe = "{{__('user.Subscribe')}}"
                                $("#subscribe_btn").html(subscribe);
                                $("#subscribe_btn").attr('disabled',false);
                                $("#subscriberForm").trigger("reset");
                            }

                            if(response.status == 0){
                                toastr.error(response.message);
                                let subscribe = "{{__('user.Subscribe')}}"
                                $("#subscribe_btn").html(subscribe);
                                $("#subscribe_btn").attr('disabled',false);
                                $("#subscriberForm").trigger("reset");
                            }
                        },
                        error: function(err) {
                            toastr.error('Something went wrong');
                            let subscribe = "{{__('user.Subscribe')}}"
                                $("#subscribe_btn").html(subscribe);
                                $("#subscribe_btn").attr('disabled',false);
                                $("#subscriberForm").trigger("reset");
                        }
                    });
                })

            });
        })(jQuery);


    </script>

@endsection
