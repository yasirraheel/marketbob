@php
    $metaTitle = $item->name;
    $metaDescription = '';
    $priceInfo = '';
    
    // Add pricing info to meta description and title
    if (!$item->isFree()) {
        $validityPrices = @json_decode($item->validity_prices ?? '{}', true) ?? [];
        $minPrice = null;
        $availablePeriods = [];
        
        foreach ($validityPrices as $period => $price) {
            $priceValue = is_numeric($price) ? (float)$price : 0;
            if ($priceValue > 0) {
                if ($minPrice === null || $priceValue < $minPrice) {
                    $minPrice = $priceValue;
                }
                $availablePeriods[] = (int)$period;
            }
        }
        
        if ($minPrice === null && $item->regular_price > 0) {
            $minPrice = $item->regular_price;
        }
        
        $originalPrice = $item->original_price ?? 0;
        
        if ($minPrice > 0) {
            if ($originalPrice > 0 && $minPrice < $originalPrice) {
                $discountPercent = round((($originalPrice - $minPrice) / $originalPrice) * 100);
                $priceInfo = $discountPercent . '% OFF';
                $metaDescription .= $discountPercent . '% OFF - Was ' . getAmount($originalPrice, 2, '.', '', true) . ', Now ' . getAmount($minPrice, 2, '.', '', true) . '. ';
                $metaTitle = $item->name . ' (' . $discountPercent . '% OFF - ' . getAmount($minPrice, 2, '.', '', true) . ')';
            } else {
                $metaDescription .= 'Price: ' . getAmount($minPrice, 2, '.', '', true) . '. ';
                $metaTitle = $item->name . ' - ' . getAmount($minPrice, 2, '.', '', true);
            }
            
            if (!empty($availablePeriods)) {
                sort($availablePeriods);
                $minPeriod = min($availablePeriods);
                $maxPeriod = max($availablePeriods);
                if ($minPeriod == $maxPeriod) {
                    $metaDescription .= 'Validity: ' . $minPeriod . ' ' . ($minPeriod == 1 ? 'Month' : 'Months') . '. ';
                } else {
                    $metaDescription .= 'Validity: ' . $minPeriod . '-' . $maxPeriod . ' Months. ';
                }
            }
        }
    } else {
        $metaDescription .= 'FREE Download. ';
        $metaTitle = $item->name . ' - FREE';
    }
    
    // Add description
    $metaDescription .= shorterText(strip_tags($item->description), 160 - strlen($metaDescription));
@endphp
@extends('themes.basic.items.layout')
@section('title', $metaTitle)
@section('breadcrumbs', Breadcrumbs::render('items.view', $item))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', 'items.view', $item))
@section('og_image', $item->getPreviewImageLink() ?: $item->getImageLink())
@section('description', $metaDescription)
@section('keywords', $item->tags)
@section('content')
    <div
        class="description {{ @$settings->item->reviews_status || @$settings->item->comments_status || @$settings->item->changelogs_status ? 'border-top pt-3' : '' }}">
        <div class="item-single-paragraph">
            {!! $item->description !!}
        </div>
    </div>
    @push('schema')
        {!! schema($__env, 'item', ['item' => $item]) !!}
    @endpush
@endsection
