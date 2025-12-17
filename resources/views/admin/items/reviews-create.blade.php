@extends('admin.layouts.grid')
@section('title', $item->name)
@section('item_view', true)
@section('back', route('admin.items.reviews', $item->id))
@section('content')
    <div class="dashboard-tabs">
        @include('admin.items.includes.tabs-nav')
        <div class="dashboard-tabs-content">
            <div class="card">
                <div class="card-header p-3 border-bottom-small">
                    <h5 class="mb-0">{{ translate('Add Review') }}</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.items.reviews.store', $item->id) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-lg-12">
                                <label class="form-label">{{ translate('Select User') }} <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select selectpicker" title="{{ translate('Choose user...') }}" data-live-search="true" required>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->username }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-12">
                                <label class="form-label">{{ translate('Rating') }} <span class="text-danger">*</span></label>
                                <select name="stars" class="form-select" required>
                                    <option value="">{{ translate('Choose rating...') }}</option>
                                    <option value="5" {{ old('stars') == 5 ? 'selected' : '' }}>5 {{ translate('Stars') }}</option>
                                    <option value="4" {{ old('stars') == 4 ? 'selected' : '' }}>4 {{ translate('Stars') }}</option>
                                    <option value="3" {{ old('stars') == 3 ? 'selected' : '' }}>3 {{ translate('Stars') }}</option>
                                    <option value="2" {{ old('stars') == 2 ? 'selected' : '' }}>2 {{ translate('Stars') }}</option>
                                    <option value="1" {{ old('stars') == 1 ? 'selected' : '' }}>1 {{ translate('Star') }}</option>
                                </select>
                            </div>
                            <div class="col-lg-12">
                                <label class="form-label">{{ translate('Subject') }} <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" maxlength="255" required>
                            </div>
                            <div class="col-lg-12">
                                <label class="form-label">{{ translate('Review') }} <span class="text-danger">*</span></label>
                                <textarea name="body" class="form-control" rows="6" maxlength="5000" required>{{ old('body') }}</textarea>
                            </div>
                        </div>
                        <div class="row g-3 mt-3">
                            <div class="col-lg-6">
                                <a href="{{ route('admin.items.reviews', $item->id) }}" class="btn btn-secondary w-100">
                                    {{ translate('Cancel') }}
                                </a>
                            </div>
                            <div class="col-lg-6">
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ translate('Add Review') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
