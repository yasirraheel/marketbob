@extends('admin.layouts.app')
@section('title', translate('New Item'))
@section('content')
    <form action="{{ route('admin.items.store') }}" method="POST">
        @csrf
        @if(!isset($category))
            <div class="dashboard-card card-v p-0 mb-4">
                <div class="card-v-header border-bottom py-3 px-4">
                    <h5 class="mb-0">{{ translate('Select Author and Category') }}</h5>
                </div>
                <div class="card-v-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-lg-6">
                            <label class="form-label">{{ translate('Author') }} <span class="text-danger">*</span></label>
                            <select name="author" id="author-select" class="form-select form-select-md" required>
                                <option value="">{{ translate('-- Select Author --') }}</option>
                                @foreach ($authors as $author)
                                    <option value="{{ $author->id }}" @selected(old('author') == $author->id || request('author') == $author->id)>
                                        {{ $author->username }} ({{ $author->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">{{ translate('Category') }} <span class="text-danger">*</span></label>
                            <select name="category" id="category-select" class="form-select form-select-md" required>
                                <option value="">{{ translate('-- Select Category --') }}</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->slug }}" @selected(old('category') == $cat->slug || request('category') == $cat->slug)>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="button" id="load-category-btn" class="btn btn-primary btn-md">
                        {{ translate('Load Category Form') }}
                    </button>
                </div>
            </div>
        @else
            <input type="hidden" name="author_id" value="{{ $selectedAuthor }}">
            <div class="dashboard-card card-v p-0 mb-4">
                <div class="card-v-header border-bottom py-3 px-4">
                    <h5 class="mb-0">{{ translate('Name And Description') }}</h5>
                </div>
                <div class="card-v-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label">{{ translate('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-md" maxlength="100"
                                value="{{ old('name') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ translate('Description') }} <span class="text-danger">*</span></label>
                            <textarea name="description" class="ckeditor">{{ old('description') }}</textarea>
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
                            <input type="hidden" name="category" value="{{ $category->slug }}">
                            <select class="form-select form-select-md" disabled>
                                @foreach ($categories as $mainCategory)
                                    <option value="{{ $mainCategory->slug }}" @selected($category->id == $mainCategory->id)>
                                        {{ $mainCategory->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-12">
                            <label class="form-label">{{ translate('SubCategory (Optional)') }}</label>
                            <select name="sub_category" class="form-select form-select-md">
                                <option value="">--</option>
                                @foreach ($category->subCategories as $subCategory)
                                    <option value="{{ $subCategory->slug }}" @selected(old('sub_category') == $subCategory->slug)>
                                        {{ $subCategory->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($category->categoryOptions->count() > 0)
                            @foreach ($category->categoryOptions as $categoryOption)
                                <div class="col-lg-12">
                                    <label class="form-label">{{ $categoryOption->name }} @if($categoryOption->isRequired())<span class="text-danger">*</span>@endif</label>
                                    <select
                                        name="options[{{ $categoryOption->id }}]{{ $categoryOption->isMultiple() ? '[]' : '' }}"
                                        class="form-select form-select-md"
                                        {{ $categoryOption->isMultiple() ? 'multiple' : '' }}
                                        {{ $categoryOption->isRequired() ? 'required' : '' }}>
                                        @if (!$categoryOption->isRequired())
                                            <option value="">--</option>
                                        @endif
                                        @foreach ($categoryOption->options as $option)
                                            @php
                                                $selected = false;
                                                if ($categoryOption->isMultiple()) {
                                                    $selected = old("options.{$categoryOption->id}")
                                                        ? in_array($option, old("options.{$categoryOption->id}"))
                                                        : false;
                                                } else {
                                                    $selected = old("options.{$categoryOption->id}") == $option;
                                                }
                                            @endphp
                                            <option value="{{ $option }}" @selected($selected)>
                                                {{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        @endif
                        <div class="col-12">
                            <label class="form-label">{{ translate('Version (Optional)') }}</label>
                            <input type="text" name="version" class="form-control form-control-md" 
                                placeholder="e.g., 1.0.0" value="{{ old('version') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ translate('Demo Link (Optional)') }}</label>
                            <input type="url" name="demo_link" class="form-control form-control-md" 
                                placeholder="https://..." value="{{ old('demo_link') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ translate('Tags') }} <span class="text-danger">*</span></label>
                            <input id="item-tags" type="text" name="tags" value="{{ old('tags') }}" required>
                            <div class="form-text">
                                {{ translate('Type your tag and click enter, maximum :maximum_tags tags.', ['maximum_tags' => @settings('item')->maximum_tags]) }}
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ translate('Features (Optional)') }}</label>
                            <div id="features-container">
                                @if(old('features'))
                                    @foreach(old('features') as $index => $feature)
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
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.items.includes.files-box')
            @if (@settings('item')->support_status)
                <div class="dashboard-card card-v p-0 mb-4">
                    <div class="card-v-header border-bottom py-3 px-4 d-flex justify-content-between">
                        <h5 class="mb-0">{{ translate('Support') }}</h5>
                    </div>
                    <div class="card-v-body p-4">
                        <p>
                            {{ translate('Item will be supported?') }}
                        </p>
                        <div>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check support-option" name="support" value="0"
                                    id="support1" @checked(old('support') ? old('support') == '0' : true)>
                                <label class="btn btn-outline-primary" for="support1">{{ translate('No') }}</label>
                                <input type="radio" class="btn-check support-option" name="support" value="1"
                                    id="support2" @checked(old('support') == '1')>
                                <label class="btn btn-outline-primary" for="support2">{{ translate('Yes') }}</label>
                            </div>
                        </div>
                        <div class="support-instructions mt-3 {{ old('support') && old('support') == '1' ? '' : 'd-none' }}">
                            <label class="form-label">{{ translate('Instructions') }}</label>
                            <textarea name="support_instructions" class="form-control" rows="6">{{ old('support_instructions') }}</textarea>
                        </div>
                    </div>
                </div>
            @endif
            <div class="dashboard-card card-v p-0 mb-4">
                <div class="card-v-header border-bottom py-3 px-4">
                    <h5 class="mb-0">{{ translate('Subscription Pricing') }}</h5>
                </div>
                <div class="card-v-body p-4">
                    <div class="row g-4 mb-3">
                        <div class="col-md-12 col-lg-6">
                            <label class="form-label">{{ translate('Original Price (Optional)') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ @settings('currency')->symbol }}</span>
                                <input type="number" step="0.01" name="original_price" class="form-control" 
                                    value="{{ old('original_price') }}" 
                                    min="{{ @settings('item')->minimum_price }}" 
                                    max="{{ @settings('item')->maximum_price }}">
                            </div>
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
                        @endphp
                        @foreach($validityPeriods as $period)
                            <div class="col-md-6 col-lg-6">
                                <label class="form-label">{{ translate($period['label']) }} - {{ translate('Price') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ @settings('currency')->symbol }}</span>
                                    <input type="number" step="0.01" name="validity_prices[{{ $period['months'] }}]" 
                                        class="form-control" 
                                        value="{{ old('validity_prices.' . $period['months']) }}"
                                        min="{{ @settings('item')->minimum_price }}" 
                                        max="{{ @settings('item')->maximum_price }}">
                                </div>
                                <div class="form-text">
                                    {{ translate('Leave empty to not offer this period') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @if (@settings('item')->free_item_option)
                <div class="dashboard-card card-v p-0 mb-3">
                    <div class="card-v-header border-bottom py-3 px-4">
                        <h5 class="mb-0">{{ translate('Free Item') }}</h5>
                    </div>
                    <div class="card-v-body p-4">
                        <p>{{ translate('Allow free downloads?') }}</p>
                        <div>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check free-item-option" name="free_item" value="0"
                                    id="op1" @checked(old('free_item') ? old('free_item') == '0' : true)>
                                <label class="btn btn-outline-secondary" for="op1">{{ translate('No') }}</label>
                                <input type="radio" class="btn-check free-item-option" name="free_item" value="1"
                                    id="op2" @checked(old('free_item') == '1')>
                                <label class="btn btn-outline-secondary" for="op2">{{ translate('Yes') }}</label>
                            </div>
                        </div>
                        <div class="mt-3 d-none purchasing-option">
                            <p>{{ translate('Allow purchasing along with free download?') }}</p>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="purchasing_status" value="1"
                                    id="opg1" @checked(old('purchasing_status') ? old('purchasing_status') == '1' : true)>
                                <label class="btn btn-outline-secondary" for="opg1">{{ translate('Enable Purchasing') }}</label>
                                <input type="radio" class="btn-check" name="purchasing_status" value="0"
                                    id="opg2" @checked(old('purchasing_status') == '0')>
                                <label class="btn btn-outline-secondary" for="opg2">{{ translate('Disable Purchasing') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="dashboard-card card-v p-0 mb-3">
                <div class="card-v-header border-bottom py-3 px-4">
                    <h5 class="mb-0">{{ translate('Status') }}</h5>
                </div>
                <div class="card-v-body p-4">
                    <label class="form-label">{{ translate('Select Status') }} <span class="text-danger">*</span></label>
                    <select name="status" class="form-select form-select-md" required>
                        <option value="1" @selected(old('status', 1) == 1)>{{ translate('Pending') }}</option>
                        <option value="2" @selected(old('status') == 2)>{{ translate('Soft Rejected') }}</option>
                        <option value="3" @selected(old('status') == 3)>{{ translate('Hard Rejected') }}</option>
                        <option value="4" @selected(old('status') == 4)>{{ translate('Resubmitted') }}</option>
                        <option value="5" @selected(old('status') == 5)>{{ translate('Approved') }}</option>
                        <option value="6" @selected(old('status') == 6)>{{ translate('Updated') }}</option>
                    </select>
                </div>
            </div>
            <div class="dashboard-card card-v p-0 mb-3">
                <div class="card-v-header border-bottom py-3 px-4">
                    <h5 class="mb-0">{{ translate('Message to Author') }}</h5>
                </div>
                <div class="card-v-body p-4">
                    <textarea name="message" class="form-control" rows="6" placeholder="{{ translate('Optional message') }}">{{ old('message') }}</textarea>
                </div>
            </div>
            <div class="dashboard-card card-v p-0">
                <div class="card-v-body p-4">
                    <button class="btn btn-primary btn-md">{{ translate('Create Item') }}</button>
                    <a href="{{ route('admin.items.index') }}" class="btn btn-secondary btn-md">{{ translate('Cancel') }}</a>
                </div>
            </div>
        @endif
    </form>
    @if(isset($category))
        @push('top_scripts')
            <script>
                "use strict";
                const itemConfig = {!! json_encode([
                    'buyer_fee' => [
                        'regular' => $category->regular_buyer_fee,
                        'extended' => $category->extended_buyer_fee,
                    ],
                    'max_tags' => intval(@settings('item')->maximum_tags),
                    'load_files_route' => route('admin.items.files.load', [hash_encode($category->id), $selectedAuthor]),
                ]) !!};
            </script>
        @endpush
        @push('styles_libs')
            <link rel="stylesheet" href="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.css') }}">
        @endpush
        @push('scripts_libs')
            <script src="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.min.js') }}"></script>
            <script src="{{ asset('vendor/libs/jquery/jquery.priceformat.min.js') }}"></script>
            <script>
                $(function() {
                    "use strict";
                    $('#item-tags').tagsinput({
                        trimValue: true,
                        confirmKeys: [13, 44],
                        maxTags: itemConfig.max_tags
                    });

                    $('.support-option').on('change', function() {
                        if ($(this).val() == 1) {
                            $('.support-instructions').removeClass('d-none');
                        } else {
                            $('.support-instructions').addClass('d-none');
                        }
                    });

                    $('.free-item-option').on('change', function() {
                        if ($(this).val() == 1) {
                            $('.purchasing-option').removeClass('d-none');
                        } else {
                            $('.purchasing-option').addClass('d-none');
                        }
                    });

                    // Features
                    $('#add-feature').on('click', function() {
                        const container = $('#features-container');
                        const newFeature = $('<div class="input-group mb-2 feature-item"></div>');
                        newFeature.html('<input type="text" name="features[]" class="form-control" placeholder="{{ translate('e.g., Quality Checked, Full Documentation') }}"><button type="button" class="btn btn-outline-danger remove-feature"><i class="fa fa-times"></i></button>');
                        container.append(newFeature);
                    });

                    $(document).on('click', '.remove-feature', function() {
                        if ($('.feature-item').length > 1) {
                            $(this).closest('.feature-item').remove();
                        }
                    });

                    $('form').on('submit', function() {
                        $('input[name="features[]"]').each(function() {
                            if (!$(this).val() || $(this).val().trim() === '') {
                                $(this).remove();
                            }
                        });
                    });
                });
            </script>
        @endpush
        @include('admin.partials.ckeditor')
    @else
        @push('scripts')
            <script>
                $(function() {
                    $('#load-category-btn').on('click', function() {
                        const author = $('#author-select').val();
                        const category = $('#category-select').val();
                        
                        if (!author) {
                            alert('{{ translate('Please select an author') }}');
                            return;
                        }
                        if (!category) {
                            alert('{{ translate('Please select a category') }}');
                            return;
                        }
                        
                        window.location.href = '{{ route('admin.items.create') }}?author=' + author + '&category=' + category;
                    });
                });
            </script>
        @endpush
    @endif
@endsection
