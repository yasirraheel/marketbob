@extends('themes.basic.layouts.app')
@section('title', @$settings->seo->title)
@section('content')
    <header class="header header-image"
        style='background-image:url("{{ asset($themeSettings->home_page->header_background) }}")'>
        <div class="container container-custom">
            <div class="header-inner">
                <div class="header-container">
                    <h1 class="header-title" data-aos="fade-down" data-aos-duration="1000">
                        {{ translate('TopDeals Plus - Premium Digital Products & Services Marketplace') }}
                    </h1>
                    <p class="header-text" data-aos="fade-up" data-aos-duration="1000">
                        {{ translate('Discover Premium Software, Templates, Graphics, Courses, Tools, and Digital Services at Unbeatable Prices') }}
                    </p>
                    <div class="header-search" data-aos="fade-up" data-aos-duration="1000">
                        <div class="search">
                            @include('themes.basic.partials.search-form', [
                                'url' => route('items.index'),
                                'placeholder' => translate('Search for software, templates, tools...'),
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <x-ad alias="home_page_top" @class('container container-custom mt-5') />
    @foreach ($homeSections as $key => $homeSection)
        @include('themes.basic.sections.' . str($homeSection->alias)->replace('_', '-'))
    @endforeach
    <x-ad alias="home_page_bottom" @class('container container-custom my-5') />
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/swiper/swiper-bundle.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/libs/aos/aos.min.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/swiper/swiper-bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/libs/aos/aos.min.js') }}"></script>
    @endpush
@endsection
