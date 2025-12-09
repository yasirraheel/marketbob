@extends('themes.basic.workspace.layouts.app')
@section('section', translate('My Items'))
@section('title', $item->name)
@section('back', route('workspace.items.index'))
@section('breadcrumbs', Breadcrumbs::render('workspace.items.edit', $item))
@section('content')
    <div class="dashboard-tabs">
        @include('themes.basic.workspace.items.includes.tabs-nav')
        <div class="dashboard-tabs-content">
            <div class="row g-3">
                <div class="col-lg-7">
                    <form action="{{ route('workspace.items.update', $item->id) }}" method="POST">
                        @csrf
                        <div class="dashboard-card card-v p-0 mb-4">
                            <div class="card-v-header border-bottom py-3 px-4">
                                <h5 class="mb-0">{{ translate('Name And Description') }}</h5>
                            </div>
                            <div class="card-v-body p-4">
                                <div class="row g-3 mb-3">
                                    <div class="col-12">
                                        <label class="form-label">{{ translate('Name') }}</label>
                                        <input type="text" name="name" class="form-control form-control-md"
                                            maxlength="100" value="{{ $item->name }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ translate('Description') }}</label>
                                        <textarea name="description" class="ckeditor">{{ $item->description }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dashboard-card card-v p-0 mb-4">
                            <div class="card-v-header border-bottom py-3 px-4">
                                <h5 class="mb-0">{{ translate('Category And Attributes') }}</h5>
                            </div>
                            <div class="card-v-body p-4">
                                <div class="row g-4 mb-3">
                                    <div class="col-lg-12">
                                        <label class="form-label">{{ translate('Category') }}</label>
                                        <select class="form-select form-select-md" disabled>
                                            @foreach ($categories as $mainCategory)
                                                <option value="{{ $mainCategory->slug }}" @selected($category->id == $mainCategory->id)>
                                                    {{ $mainCategory->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-12">
                                        <label class="form-label">{{ translate('SubCategory (Optional)') }}</label>
                                        <select class="form-select form-select-md" disabled>
                                            <option value="">--</option>
                                            @foreach ($category->subCategories as $subCayegory)
                                                <option value="{{ $subCayegory->slug }}" @selected($item->subCategory && $item->subCategory->id == $subCayegory->id)>
                                                    {{ $subCayegory->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if ($category->categoryOptions->count() > 0)
                                        @foreach ($category->categoryOptions as $categoryOption)
                                            <div class="col-lg-12">
                                                @php
                                                    $categoryOptionName = $categoryOption->name;
                                                @endphp
                                                <label class="form-label">{{ $categoryOptionName }}</label>
                                                <select
                                                    name="options[{{ $categoryOption->id }}]{{ $categoryOption->isMultiple() ? '[]' : '' }}"
                                                    class="selectpicker-md  selectpicker" title="--"
                                                    {{ $categoryOption->isMultiple() ? 'multiple' : '' }}
                                                    {{ $categoryOption->isRequired() ? 'required' : '' }}>
                                                    @if (!$categoryOption->isRequired())
                                                        <option value="">--</option>
                                                    @endif
                                                    @foreach ($categoryOption->options as $option)
                                                        @php
                                                            $selected = false;
                                                            if (isset($item['options'][$categoryOptionName])) {
                                                                $selected = $categoryOption->isMultiple()
                                                                    ? in_array(
                                                                        $option,
                                                                        $item['options'][$categoryOptionName],
                                                                    )
                                                                    : $item['options'][$categoryOptionName] == $option;
                                                            }
                                                        @endphp
                                                        <option value="{{ $option }}" @selected($selected)>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endforeach
                                    @endif
                                    <div class="col-12">
                                        <label class="form-label">{{ translate('Version (Optional)') }}</label>
                                        <input type="text" name="version" class="form-control form-control-md"
                                            placeholder="{{ translate('1.0 or 1.0.0') }}" value="{{ $item->version }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ translate('Demo Link (Optional)') }}</label>
                                        <input type="url" name="demo_link" class="form-control form-control-md"
                                            value="{{ $item->demo_link }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ translate('Tags') }}</label>
                                        <input id="item-tags" type="text" name="tags" value="{{ $item->tags }}"
                                            required>
                                        <div class="form-text">
                                            {{ translate('Type your tag and click enter, maximum :maximum_tags tags.', ['maximum_tags' => @$settings->item->maximum_tags]) }}
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ translate('Features') }}</label>
                                        <div id="features-container">
                                            @php
                                                $features = @json_decode($item->features ?? '[]', true) ?? [];
                                            @endphp
                                            @if(count($features) > 0)
                                                @foreach($features as $feature)
                                                    <div class="input-group mb-2 feature-item">
                                                        <input type="text" name="features[]" class="form-control" value="{{ $feature }}" placeholder="{{ translate('e.g., Quality Checked, Full Documentation') }}">
                                                        <button type="button" class="btn btn-outline-danger remove-feature"><i class="fa fa-times"></i></button>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="input-group mb-2 feature-item">
                                                    <input type="text" name="features[]" class="form-control" placeholder="{{ translate('e.g., Quality Checked, Full Documentation') }}">
                                                    <button type="button" class="btn btn-outline-danger remove-feature"><i class="fa fa-times"></i></button>
                                                </div>
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-feature">
                                            <i class="fa fa-plus me-1"></i>{{ translate('Add Feature') }}
                                        </button>
                                        <div class="form-text">
                                            {{ translate('Add features that highlight what buyers get with this subscription') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @include('themes.basic.workspace.items.includes.files-box')
                        <div class="dashboard-card card-v p-0 mb-4">
                            <div class="card-v-header border-bottom py-3 px-4 d-flex justify-content-between">
                                <h5 class="mb-0">{{ translate('Support') }}</h5>
                            </div>
                            <div class="card-v-body p-4">
                                <p>
                                    {{ translate('Item will be supported?') }}
                                </p>
                                <div>
                                    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                        <input type="radio" class="btn-check support-option" name="support" value="0"
                                            id="support1" @checked(!$item->isSupported())>
                                        <label class="btn btn-outline-primary" for="support1">{{ translate('No') }}</label>
                                        <input type="radio" class="btn-check support-option" name="support" value="1"
                                            id="support2" @checked($item->isSupported())>
                                        <label class="btn btn-outline-primary"
                                            for="support2">{{ translate('Yes') }}</label>
                                    </div>
                                </div>
                                <div class="support-instructions mt-3 {{ !$item->isSupported() ? 'd-none' : '' }}">
                                    <label class="form-label">{{ translate('Instructions') }}</label>
                                    <textarea name="support_instructions" class="form-control" rows="6">{{ $item->support_instructions }}</textarea>
                                    <div class="form-text">
                                        {{ translate('Enter the instructions that the buyer should follow to get support. ') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dashboard-card card-v p-0 mb-4">
                            <div class="card-v-header border-bottom py-3 px-4 d-flex justify-content-between">
                                <h5 class="mb-0">{{ translate('Subscription Pricing') }}</h5>
                                @if (@$settings->links->licenses_terms_link)
                                    <a href="{{ @$settings->links->licenses_terms_link }}">{{ translate('Licenses terms') }}<i
                                            class="fa-solid fa-angle-right fa-rtl ms-1"></i></a>
                                @endif
                            </div>
                            <div class="card-v-body p-4">
                                @if (!$item->hasDiscount())
                                    <div class="row g-4 mb-3">
                                        <div class="col-md-12 col-lg-6">
                                            <label class="form-label">{{ translate('Original Price') }}</label>
                                            @include('themes.basic.workspace.partials.input-price', [
                                                'label' => '',
                                                'id' => 'original_price',
                                                'name' => 'original_price',
                                                'value' => old('original_price', $item->original_price ?? ''),
                                                'min' => @$settings->item->minimum_price,
                                                'max' => @$settings->item->maximum_price,
                                                'required' => false,
                                            ])
                                            <div class="form-text">
                                                {{ translate('This will be shown as strike-through price to show discount') }}
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-4">
                                    <h6 class="mb-3">{{ translate('Validity Period Pricing') }}</h6>
                                    <div class="row g-4 mb-3">
                                        @php
                                            $validityPeriods = [
                                                ['months' => 1, 'label' => '1 Month'],
                                                ['months' => 3, 'label' => '3 Months'],
                                                ['months' => 6, 'label' => '6 Months'],
                                                ['months' => 12, 'label' => '12 Months']
                                            ];
                                            $validityPrices = @json_decode($item->validity_prices ?? '{}', true) ?? [];
                                        @endphp
                                        @foreach($validityPeriods as $period)
                                            <div class="col-md-6 col-lg-6">
                                                <label class="form-label">{{ translate($period['label']) }} - {{ translate('Price') }}</label>
                                                @include('themes.basic.workspace.partials.input-price', [
                                                    'label' => '',
                                                    'id' => 'validity-' . $period['months'] . '-price',
                                                    'name' => 'validity_prices[' . $period['months'] . ']',
                                                    'value' => isset($validityPrices[$period['months']]) && $validityPrices[$period['months']] > 0 ? $validityPrices[$period['months']] : '',
                                                    'min' => @$settings->item->minimum_price,
                                                    'max' => @$settings->item->maximum_price,
                                                    'required' => false,
                                                ])
                                                <div class="form-text">
                                                    {{ translate('Leave empty to not offer this period') }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        <i class="fa-regular fa-circle-question me-2"></i>
                                        <span>{{ translate('The price cannot update while the item is on discount') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if (@$settings->item->free_item_option && !$item->isPremium())
                            <div class="dashboard-card card-v p-0 mb-3">
                                <div class="card-v-header border-bottom py-3 px-4">
                                    <h5 class="mb-0">{{ translate('Free Item') }}</h5>
                                </div>
                                <div class="card-v-body p-4">
                                    <p>
                                        {{ translate('You can allow downloading your item for free, please note that everyone can download the item directly from the item page without purchasing, please make sure your item has no purchase code verification.') }}
                                    </p>
                                    <div>
                                        <div class="btn-group" role="group"
                                            aria-label="Basic radio toggle button group">
                                            <input type="radio" class="btn-check" name="free_item" value="0"
                                                id="op1" @checked(!$item->isFree())>
                                            <label class="btn btn-outline-secondary"
                                                for="op1">{{ translate('No') }}</label>
                                            <input type="radio" class="btn-check" name="free_item" value="1"
                                                id="op2" @checked($item->isFree())>
                                            <label class="btn btn-outline-secondary"
                                                for="op2">{{ translate('Yes') }}</label>
                                        </div>
                                    </div>
                                    <div class="mt-3 {{ !$item->isFree() ? 'd-none' : '' }} purchasing-option">
                                        <p>
                                            {{ translate('You can also allow the purchase option along with the free download in case anyone wants to purchase a license.') }}
                                        </p>
                                        <div class="btn-group" role="group"
                                            aria-label="Basic radio toggle button group">
                                            <input type="radio" class="btn-check" name="purchasing_status"
                                                value="1" id="opg1" @checked($item->isPurchasingEnabled())>
                                            <label class="btn btn-outline-secondary"
                                                for="opg1">{{ translate('Enable Purchasing') }}</label>
                                            <input type="radio" class="btn-check" name="purchasing_status"
                                                value="0" id="opg2" @checked(!$item->isPurchasingEnabled())>
                                            <label class="btn btn-outline-secondary"
                                                for="opg2">{{ translate('Disable Purchasing') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="dashboard-card card-v p-0 mb-3">
                            <div class="card-v-header border-bottom py-3 px-4">
                                <h5 class="mb-0">{{ translate('Message to the Reviewer') }}</h5>
                            </div>
                            <div class="card-v-body p-4">
                                <textarea name="message" class="form-control" rows="6" placeholder="{{ translate('Your message') }}">{{ old('message') }}</textarea>
                            </div>
                        </div>
                        <div class="dashboard-card card-v p-0">
                            <div class="card-v-body p-4">
                                <button class="btn btn-primary btn-md action-confirm">{{ translate('Update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-5">
                    @include('themes.basic.workspace.items.includes.sidebar')
                </div>
            </div>
        </div>
    </div>
    @push('top_scripts')
        <script>
            "use strict";
            const itemConfig = {!! json_encode([
                'buyer_fee' => [
                    'regular' => $category->regular_buyer_fee,
                    'extended' => $category->extended_buyer_fee,
                ],
                'max_tags' => intval(@$settings->item->maximum_tags),
                'load_files_route' => route('workspace.items.files.load', hash_encode($category->id)),
            ]) !!};
        </script>
    @endpush
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.css') }}">
    @endpush
    @include('themes.basic.workspace.partials.ckeditor')
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.min.js') }}"></script>
        <script src="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.js') }}"></script>
        <script src="{{ asset('vendor/libs/jquery/jquery.priceformat.min.js') }}"></script>
        <script src="{{ theme_assets_with_version('assets/js/item.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('form[action="{{ route('workspace.items.update', $item->id) }}"]');
                
                // Add feature button
                document.getElementById('add-feature').addEventListener('click', function() {
                    const container = document.getElementById('features-container');
                    const newFeature = document.createElement('div');
                    newFeature.className = 'input-group mb-2 feature-item';
                    newFeature.innerHTML = '<input type="text" name="features[]" class="form-control" placeholder="{{ translate('e.g., Quality Checked, Full Documentation') }}"><button type="button" class="btn btn-outline-danger remove-feature"><i class="fa fa-times"></i></button>';
                    container.appendChild(newFeature);
                });

                // Remove feature button (using event delegation)
                document.getElementById('features-container').addEventListener('click', function(e) {
                    if (e.target.closest('.remove-feature')) {
                        const featureItem = e.target.closest('.feature-item');
                        if (document.querySelectorAll('.feature-item').length > 1) {
                            featureItem.remove();
                        }
                    }
                });

                // Before form submit, remove empty feature inputs
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const featureInputs = document.querySelectorAll('input[name="features[]"]');
                        featureInputs.forEach(function(input) {
                            if (!input.value || input.value.trim() === '') {
                                input.remove();
                            }
                        });
                    });
                }
            });
        </script>
    @endpush
@endsection
