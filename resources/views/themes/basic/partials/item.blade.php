<div class="item item-card-modern {{ $item_classes ?? '' }}">
    <div class="item-header">
        @if ($item->isPreviewFileTypeImage())
            <a href="{{ $item->getLink() }}" class="item-image-link">
                <img class="item-img" src="{{ $item->getPreviewImageLink() }}" alt="{{ $item->name }}" />
            </a>
        @elseif($item->isPreviewFileTypeVideo())
            <a href="{{ $item->getLink() }}" class="opacity-100">
                <div class="item-video">
                    <video class="plyr" poster="{{ $item->getPreviewImageLink() }}" muted>
                        <source src="{{ $item->getPreviewLink() }}">
                    </video>
                    <div class="item-video-actions d-flex align-items-center justify-content-between gap-1">
                        <div class="item-video-volume item-video-action">
                            <i class="fa-solid fa-volume-high" class="unmuted"></i>
                            <i class="fa-solid fa-volume-xmark" class="muted"></i>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <div class="item-video-full item-video-action">
                                <i class="fa fa-expand"></i>
                            </div>
                        </div>
                    </div>
                    <div class="item-video-progress">
                        <span></span>
                    </div>
                </div>
            </a>
        @elseif($item->isPreviewFileTypeAudio())
            <div class="item-audio">
                <a href="{{ $item->getLink() }}" class="item-audio-link opacity-100"></a>
                <div class="item-audio-wave">
                    <div class="item-audio-actions">
                        <button class="play-button btn btn-primary btn-sm px-2">
                            <div class="play-button-icon">
                                <i class="fas fa-play"></i>
                            </div>
                        </button>
                        <button class="pause-button btn btn-primary btn-sm px-2 d-none">
                            <div class="play-button-icon">
                                <i class="fas fa-pause"></i>
                            </div>
                        </button>
                    </div>
                    <div class="waveform" data-url="{{ $item->getPreviewLink() }}" data-waveheight="50"></div>
                    <div class="total-duration">00:00</div>
                </div>
            </div>
        @endif
        <div class="item-badges-container">
            @if (licenseType(2) && @$settings->premium->status && $item->isPremium())
                <span class="item-badge-tag item-badge-premium">
                    <i class="fa-solid fa-crown me-1"></i>{{ translate('Premium') }}
                </span>
            @elseif ($item->isFree())
                <span class="item-badge-tag item-badge-free">
                    <i class="fa-regular fa-heart me-1"></i>{{ translate('Free') }}
                </span>
            @else
                @php
                    // Calculate discount for badge
                    $validityPrices = @json_decode($item->validity_prices ?? '{}', true) ?? [];
                    $minPrice = null;
                    foreach ($validityPrices as $price) {
                        $priceValue = is_numeric($price) ? (float)$price : 0;
                        if ($priceValue > 0 && ($minPrice === null || $priceValue < $minPrice)) {
                            $minPrice = $priceValue;
                        }
                    }
                    if ($minPrice === null && $item->regular_price > 0) {
                        $minPrice = $item->regular_price;
                    }
                    
                    $originalPrice = $item->original_price ?? 0;
                    $showDiscount = false;
                    $discountPercent = 0;
                    if ($originalPrice > 0 && $minPrice > 0 && $minPrice < $originalPrice) {
                        $showDiscount = true;
                        $discountPercent = round((($originalPrice - $minPrice) / $originalPrice) * 100);
                    }
                @endphp
                @if ($item->isTrending())
                    <span class="item-badge-tag item-badge-hot">HOT</span>
                @endif
                @if ($showDiscount)
                    <span class="item-badge-tag item-badge-discount">{{ $discountPercent }}%</span>
                @elseif ($item->isOnDiscount())
                    <span class="item-badge-tag item-badge-discount">{{ translate('SALE') }}</span>
                @endif
                <span class="item-badge-tag item-badge-private">{{ translate('Private') }}</span>
            @endif
        </div>
    </div>
    </div>
    <div class="item-body">
        <a class="item-title" href="{{ $item->getLink() }}">{{ $item->name }}</a>
        
        @if ($item->description)
            <p class="item-description">
                {{ Str::limit(strip_tags($item->description), 100) }}
            </p>
        @else
            <p class="item-text">
                {!! translate('By :username in :category', [
                    'username' => "<a href={$item->author->getProfileLink()}>{$item->author->username}</a>",
                    'category' => "<a href={$item->category->getLink()}>{$item->category->name}</a>",
                ]) !!}
            </p>
        @endif
        
        @if ($settings->item->reviews_status && $item->hasReviews())
            <div class="item-ratings">
                <div class="row row-cols-auto align-items-center g-2">
                    @include('themes.basic.partials.rating-stars', [
                        'stars' => $item->avg_reviews,
                    ])
                    <div class="col">
                        <span class="text-muted small">
                            ({{ numberFormat($item->total_reviews) }})
                        </span>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="item-purchase">
            <div class="row row-cols-auto align-items-center justify-content-between g-3">
                <div class="col">
                    @if ($item->isFree())
                        <div class="item-price">
                            <span class="item-price-number">{{ translate('Free') }}</span>
                        </div>
                    @else
                        <div class="item-price">
                            @php
                                $validityPrices = @json_decode($item->validity_prices ?? '{}', true) ?? [];
                                $minPrice = null;
                                
                                // Find minimum price from validity prices
                                foreach ($validityPrices as $price) {
                                    // Convert to float and check if valid
                                    $priceValue = is_numeric($price) ? (float)$price : 0;
                                    if ($priceValue > 0 && ($minPrice === null || $priceValue < $minPrice)) {
                                        $minPrice = $priceValue;
                                    }
                                }
                                
                                // Fallback to regular_price if no validity prices set
                                if ($minPrice === null && $item->regular_price > 0) {
                                    $minPrice = $item->regular_price;
                                }
                                
                                // Calculate discount if original_price is set
                                $originalPrice = $item->original_price ?? 0;
                                $discountPercent = 0;
                                if ($originalPrice > 0 && $minPrice > 0 && $minPrice < $originalPrice) {
                                    $discountPercent = round((($originalPrice - $minPrice) / $originalPrice) * 100);
                                }
                            @endphp
                            @if ($minPrice)
                                @if ($originalPrice > 0 && $minPrice < $originalPrice)
                                    <span class="item-price-through">
                                        {{ getAmount($originalPrice, 2, '.', '', true) }}
                                    </span>
                                @endif
                                <span class="item-price-number">
                                    {{ getAmount($minPrice, 2, '.', '', true) }}
                                </span>
                            @else
                                <span class="item-price-number">
                                    {{ translate('N/A') }}
                                </span>
                            @endif
                        </div>
                    @endif
                    @if ($item->isPurchasingEnabled() && $item->hasSales())
                        <div class="item-sales">
                            <i class="fa fa-cart-shopping me-1"></i>
                            {{ translate($item->total_sales > 1 ? ':count Sales' : ':count Sale', ['count' => numberFormat($item->total_sales)]) }}
                        </div>
                    @elseif(@$settings->item->free_item_total_downloads && $item->free_downloads > 1)
                        <div class="item-sales">
                            <i class="fa fa-download me-1"></i>
                            {{ translate($item->free_downloads > 1 ? ':count Downloads' : ':count Download', ['count' => numberFormat($item->free_downloads)]) }}
                        </div>
                    @endif
                </div>
                <div class="col">
                    <div class="row row-cols-auto g-2">
                        @if ($item->isFree())
                            <div class="col">
                                @if ($item->isMainFileExternal())
                                    <a href="{{ route('items.free.download.external', hash_encode($item->id)) }}"
                                        target="_blank" class="btn btn-outline-primary btn-md btn-padding">
                                        <i class="fa fa-download"></i>
                                    </a>
                                @else
                                    <form action="{{ route('items.free.download', hash_encode($item->id)) }}"
                                        method="POST">
                                        @csrf
                                        <button class="btn btn-outline-primary btn-md btn-padding"><i
                                                class="fa-solid fa-download"></i></button>
                                    </form>
                                @endif
                            </div>
                        @else
                            <div class="col">
                                <form data-action="{{ route('cart.add-item') }}" class="add-to-cart-form"
                                    method="POST">
                                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                                    <input type="hidden" name="license_type" value="1">
                                    @if (@$settings->item->support_status && $item->isSupported() && defaultSupportPeriod())
                                        <input type="hidden" name="support" value="{{ defaultSupportPeriod()->id }}">
                                    @endif
                                    <button class="btn btn-outline-primary btn-md btn-padding"
                                        @disabled(authUser() && authUser()->id == $item->author_id)>
                                        <i class="fa-solid fa-shopping-cart"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                        <div class="col">
                            <a href="{{ $item->getLink() }}" class="btn btn-outline-secondary btn-md btn-padding">
                                <i class="far fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
