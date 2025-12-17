<?php

namespace App\Http\Controllers\Admin\Items;

use App\Events\ItemApproved;
use App\Http\Controllers\Controller;
use App\Jobs\Author\SendAuthorItemHardRejectedNotification;
use App\Jobs\Author\SendAuthorItemSoftRejectedNotification;
use App\Events\ItemSubmitted;
use App\Events\ItemResubmitted;
use App\Models\Badge;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemComment;
use App\Models\ItemHistory;
use App\Models\ItemReview;
use App\Models\ItemView;
use App\Models\Sale;
use App\Models\SubCategory;
use App\Models\UploadedFile;
use App\Models\User;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Date\Date;
use Mews\Purifier\Facades\Purifier;
use Str;

class ItemController extends Controller
{
    private $imageMimeTypes = ['image/png', 'image/jpg', 'image/jpeg'];
    private $videoMimeTypes = ['video/mp4', 'video/webm'];
    private $audioMimeTypes = ['audio/mpeg', 'audio/wav'];

    public function index()
    {
        $categories = Category::all();

        $items = Item::query();

        if (request()->filled('search')) {
            $searchTerm = '%' . request('search') . '%';
            $searchTermStart = request('search') . '%';

            $items->where(function ($query) use ($searchTerm, $searchTermStart) {
                $query->where('name', 'like', $searchTermStart)
                    ->orWhere(function ($query) use ($searchTerm) {
                        $query->where('id', 'like', $searchTerm)
                            ->OrWhere('name', 'like', $searchTerm)
                            ->OrWhere('slug', 'like', $searchTerm)
                            ->OrWhere('description', 'like', $searchTerm)
                            ->OrWhere('options', 'like', $searchTerm)
                            ->OrWhere('demo_link', 'like', $searchTerm)
                            ->OrWhere('tags', 'like', $searchTerm)
                            ->OrWhere('regular_price', 'like', $searchTerm)
                            ->OrWhere('extended_price', 'like', $searchTerm)
                            ->orWhereHas('author', function ($query) use ($searchTerm) {
                                $query->where('firstname', 'like', $searchTerm)
                                    ->OrWhere('lastname', 'like', $searchTerm)
                                    ->OrWhere('username', 'like', $searchTerm)
                                    ->OrWhere('email', 'like', $searchTerm)
                                    ->OrWhere('address', 'like', $searchTerm);
                            })
                            ->orWhereHas('category', function ($query) use ($searchTerm) {
                                $query->where('name', 'like', $searchTerm)
                                    ->OrWhere('slug', 'like', $searchTerm);
                            })
                            ->orWhereHas('subCategory', function ($query) use ($searchTerm) {
                                $query->where('name', 'like', $searchTerm)
                                    ->OrWhere('slug', 'like', $searchTerm);
                            });
                    });
            });
        }

        if (request()->filled('author')) {
            $items->where('author_id', request('author'));
        }

        if (request()->filled('category')) {
            $items->where('category_id', request('category'));
        }

        if (request()->filled('status')) {
            $items->where('status', request('status'));
        }

        $filteredItems = $items->get();
        $counters['pending'] = $filteredItems->where('status', Item::STATUS_PENDING)->count();
        $counters['soft_rejected'] = $filteredItems->where('status', Item::STATUS_SOFT_REJECTED)->count();
        $counters['resubmitted'] = $filteredItems->where('status', Item::STATUS_RESUBMITTED)->count();
        $counters['approved'] = $filteredItems->where('status', Item::STATUS_APPROVED)->count();
        $counters['hard_rejected'] = $filteredItems->where('status', Item::STATUS_HARD_REJECTED)->count();
        $counters['deleted'] = $filteredItems->where('status', Item::STATUS_DELETED)->count();

        if (isset($searchTermStart)) {
            $items->orderByRaw("CASE WHEN name LIKE ? THEN 1 ELSE 2 END", [$searchTermStart])->orderByDesc('id');
        } else {
            $items->orderByDesc('id');
        }

        $items = $items->paginate(50);
        $items->appends(request()->only(['search', 'author', 'category', 'status']));

        return view('admin.items.index', [
            'counters' => $counters,
            'categories' => $categories,
            'items' => $items,
        ]);
    }

    public function makeFeatured(Request $request, $id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $item->is_featured = Item::FEATURED;
        $item->was_featured = Item::FEATURED;
        $item->update();

        $badge = Badge::where('alias', Badge::FEATURED_ITEM_BADGE_ALIAS)->first();
        if ($badge) {
            $item->author->addBadge($badge);
        }

        toastr()->success(translate('The item marked as featured successfully'));
        return back();
    }

    public function removeFeatured(Request $request, $id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $item->is_featured = Item::NOT_FEATURED;
        $item->update();

        toastr()->success(translate('The item marked as not featured successfully'));
        return back();
    }

    public function makePremium(Request $request, $id)
    {
        $item = Item::where('id', $id)->notFree()->firstOrFail();

        $item->is_premium = Item::PREMIUM;
        $item->update();

        $badge = Badge::where('alias', Badge::PREMIUMER_ALIAS)->first();
        if ($badge) {
            $item->author->addBadge($badge);
        }

        toastr()->success(translate('The item added to premium successfully'));
        return back();
    }

    public function removePremium(Request $request, $id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $item->is_premium = Item::NOT_PREMIUM;
        $item->update();

        toastr()->success(translate('The item removed from premium successfully'));
        return back();
    }

    public function show($id)
    {
        $item = Item::where('id', $id)->firstOrFail();
        return view('admin.items.show', [
            'item' => $item,
        ]);
    }

    public function history($id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $itemHistories = ItemHistory::where('item_id', $item->id)
            ->orderbyDesc('id')->paginate(10);

        return view('admin.items.history', [
            'item' => $item,
            'itemHistories' => $itemHistories,
        ]);
    }

    public function historyDelete($id, $history_id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $itemHistory = ItemHistory::where('id', $history_id)
            ->where('item_id', $item->id)->firstOrFail();

        $itemHistory->delete();

        toastr()->success(translate('Deleted Successfully'));
        return back();
    }

    public function discount($id)
    {
        $item = Item::where('id', $id)->firstOrFail();
        return view('admin.items.discount', [
            'item' => $item,
        ]);
    }

    public function discountDelete($id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        if ($item->hasDiscount()) {
            $discount = $item->discount;
            $discount->delete();
        }

        $item->is_on_discount = Item::DISCOUNT_OFF;
        $item->update();

        toastr()->success(translate('Deleted Successfully'));
        return back();
    }

    public function action($id)
    {
        $item = Item::where('id', $id)
            ->whereIn('status', [Item::STATUS_PENDING, Item::STATUS_RESUBMITTED])
            ->firstOrFail();

        return view('admin.items.action', [
            'item' => $item,
        ]);
    }

    public function actionUpdate(Request $request, $id)
    {
        $item = Item::where('id', $id)
            ->whereIn('status', [Item::STATUS_PENDING, Item::STATUS_RESUBMITTED])
            ->firstOrFail();

        switch ($request->action) {
            case 'approve':
                $status = Item::STATUS_APPROVED;
                $title = $item->isPending() ? ItemHistory::TITLE_SUBMISSION_APPROVED : ItemHistory::TITLE_RESUBMISSION_APPROVED;
                $success = translate('The item has been approved');

                break;
            case 'soft_reject':
                if (empty($request->reason)) {
                    toastr()->error(translate('The soft rejection reason is required'));
                    return back();
                }

                $status = Item::STATUS_SOFT_REJECTED;
                $title = ItemHistory::TITLE_SOFT_REJECTION;
                $success = translate('The item has been soft rejected');

                break;
            case 'hard_reject':
                $status = Item::STATUS_HARD_REJECTED;
                $title = ItemHistory::TITLE_HARD_REJECTION;
                $success = translate('The item has been rejected');

                break;
            default:
                return redirect()->route('admin.items.show', $item->id);
                break;
        }

        $itemHistory = new ItemHistory();
        $itemHistory->admin_id = authAdmin()->id;
        $itemHistory->item_id = $item->id;
        $itemHistory->title = $title;
        $itemHistory->body = $request->reason ?? null;
        $itemHistory->save();

        $item->status = $status;
        $item->update();

        if ($item->isApproved()) {
            event(new ItemApproved($item));
        } elseif ($item->isSoftRejected()) {
            dispatch(new SendAuthorItemSoftRejectedNotification($item, $itemHistory));
        } elseif ($item->isHardRejected()) {
            dispatch(new SendAuthorItemHardRejectedNotification($item));
        }

        toastr()->success($success);
        return redirect()->route('admin.items.show', $item->id);
    }

    public function status($id)
    {
        $item = Item::where('id', $id)->firstOrFail();
        return view('admin.items.status', [
            'item' => $item,
        ]);
    }

    public function statusUpdate(Request $request, $id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'status' => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }

        $item->status = $request->status;
        $item->update();

        toastr()->success(translate('Updated Successfully'));
        return back();
    }

    public function statistics($id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        if (request()->filled('period')) {
            $period = request()->input('period');
            $startDate = Date::parse($period)->startOfMonth();
            $endDate = Date::parse($period)->endOfMonth();
        } else {
            $startDate = Date::now()->startOfMonth();
            $endDate = Date::now()->endOfMonth();
        }

        $counters = $this->generateCounters($item, $startDate, $endDate);
        $charts['sales'] = $this->generateSalesChartData($item, $startDate, $endDate);
        $topPurchasingCountries = $this->getTopPurchasingCountries($item, $startDate, $endDate);
        $geoCountries = $this->getGeoCountries($item, $startDate, $endDate);
        $charts['views'] = $this->generateViewsChartData($item, $startDate, $endDate);
        $referrals = $this->generateReferralsData($item, $startDate, $endDate);

        return view('admin.items.statistics', [
            'item' => $item,
            'counters' => $counters,
            'charts' => $charts,
            'topPurchasingCountries' => $topPurchasingCountries,
            'geoCountries' => $geoCountries,
            'referrals' => $referrals,
        ]);
    }

    public function reviews($id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $reviews = ItemReview::where('item_id', $id);

        if (request()->filled('search')) {
            $searchTerm = '%' . request('search') . '%';
            $reviews->where('id', 'like', $searchTerm)
                ->OrWhere('body', 'like', $searchTerm)
                ->orWhereHas('user', function ($query) use ($searchTerm) {
                    $query->where('firstname', 'like', $searchTerm)
                        ->OrWhere('lastname', 'like', $searchTerm)
                        ->OrWhere('username', 'like', $searchTerm)
                        ->OrWhere('email', 'like', $searchTerm)
                        ->OrWhere('address', 'like', $searchTerm);
                });
        }

        $reviews = $reviews->with('user')->orderbyDesc('id')->paginate(10);
        $reviews->appends(request()->only(['search']));

        return view('admin.items.reviews', [
            'item' => $item,
            'reviews' => $reviews,
        ]);
    }

    public function reviewsDelete($id, $review_id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $review = ItemReview::where('id', $review_id)
            ->where('item_id', $id)
            ->firstOrFail();

        $review->delete();
        toastr()->success(translate('Deleted Successfully'));
        return back();
    }

    public function comments($id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $comments = ItemComment::where('item_id', $id);

        if (request()->filled('search')) {
            $searchTerm = '%' . request('search') . '%';
            $comments->where('id', 'like', $searchTerm)
                ->orWhereHas('replies', function ($query) use ($searchTerm) {
                    $query->where('id', 'like', $searchTerm)
                        ->OrWhere('body', 'like', $searchTerm);
                })
                ->orWhereHas('user', function ($query) use ($searchTerm) {
                    $query->where('firstname', 'like', $searchTerm)
                        ->OrWhere('lastname', 'like', $searchTerm)
                        ->OrWhere('username', 'like', $searchTerm)
                        ->OrWhere('email', 'like', $searchTerm)
                        ->OrWhere('address', 'like', $searchTerm);
                });
        }

        $comments = $comments->with('user')->orderbyDesc('id')->paginate(10);
        $comments->appends(request()->only(['search']));

        return view('admin.items.comments', [
            'item' => $item,
            'comments' => $comments,
        ]);
    }

    public function commentsDelete($id, $comment_id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $comment = ItemComment::where('id', $comment_id)
            ->where('item_id', $id)
            ->firstOrFail();

        $comment->delete();
        toastr()->success(translate('Deleted Successfully'));
        return back();
    }

    private function generateCounters($item, $startDate, $endDate)
    {
        $sales = Sale::active()
            ->where('item_id', $item->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        $counters['total_sales'] = $sales->count();
        $counters['total_sales_amount'] = $sales->sum('price');
        $counters['total_earnings'] = $sales->sum('author_earning');

        $counters['total_views'] = ItemView::where('item_id', $item->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->count();

        return $counters;
    }

    private function generateSalesChartData($item, $startDate, $endDate)
    {
        $chart['title'] = translate('Sales');
        $dates = chartDates($startDate, $endDate);

        $sales = Sale::active()
            ->where('item_id', $item->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $salesData = $dates->merge($sales);

        $chart['labels'] = [];
        $chart['data'] = [];
        foreach ($salesData as $date => $count) {
            $label = Date::parse($date)->format('d M');
            $chart['labels'][] = $label;
            $chart['data'][] = $count;
        }

        $chart['max'] = (max($chart['data']) > 9) ? max($chart['data']) + 2 : 10;

        return $chart;
    }

    private function getGeoCountries($item, $startDate, $endDate)
    {
        return Sale::active()
            ->where('item_id', $item->id)
            ->whereNotNull('country')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->select('country', DB::raw('COUNT(*) as total_sales'))
            ->groupBy('country')
            ->orderbyDesc('total_sales')
            ->get();
    }

    private function getTopPurchasingCountries($item, $startDate, $endDate)
    {
        return Sale::active()
            ->where('item_id', $item->id)
            ->whereNotNull('country')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->select('country', DB::raw('SUM(price) as total_spend'))
            ->groupBy('country')
            ->orderbyDesc('total_spend')
            ->limit(6)
            ->get();
    }

    private function generateViewsChartData($item, $startDate, $endDate)
    {
        $chart['title'] = translate('Views');
        $dates = chartDates($startDate, $endDate);

        $sales = ItemView::where('item_id', $item->id)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $salesData = $dates->merge($sales);

        $chart['labels'] = [];
        $chart['data'] = [];
        foreach ($salesData as $date => $count) {
            $label = Date::parse($date)->format('d M');
            $chart['labels'][] = $label;
            $chart['data'][] = $count;
        }

        $chart['max'] = (max($chart['data']) > 9) ? max($chart['data']) + 2 : 10;

        return $chart;
    }

    private function generateReferralsData($item, $startDate, $endDate)
    {
        return ItemView::where('item_id', $item->id)
            ->whereNotNull('referrer')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->select('referrer', DB::raw('COUNT(*) as total_views'))
            ->groupBy('referrer')
            ->orderbyDesc('total_views')
            ->limit(10)
            ->get();
    }

    public function download($id)
    {
        $item = Item::where('id', $id)->notDeleted()
            ->firstOrFail();

        try {
            $response = $item->download();
            if (isset($response->type) && $response->type == "error") {
                throw new Exception($response->message);
            }
            return $response;
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back();
        }
    }

    public function softDelete($id)
    {
        $item = Item::where('id', $id)->notDeleted()
            ->firstOrFail();

        $item->softDelete();
        toastr()->success(translate('Item has marked as deleted successfully'));
        return back();
    }

    public function permanentlyDelete($id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $item->delete();
        toastr()->success(translate('Item has permanently deleted successfully'));
        return redirect()->route('admin.items.index');
    }

    public function restore($id)
    {
        $item = Item::where('id', $id)->deleted()->firstOrFail();

        $item->restore();
        toastr()->success(translate('Item has been restored successfully'));
        return back();
    }

    public function create()
    {
        $categories = Category::all();
        $authors = User::author()->orderBy('username')->get();

        if (request()->filled('category')) {
            $category = Category::where('slug', request('category'))
                ->with(['subCategories', 'categoryOptions'])
                ->firstOrFail();

            $author_id = request()->filled('author') ? request('author') : null;
            $uploadedFiles = $author_id ? UploadedFile::where('author_id', $author_id)
                ->where('category_id', $category->id)->notExpired()->get() : collect();

            return view('admin.items.create', [
                'categories' => $categories,
                'authors' => $authors,
                'category' => $category,
                'uploadedFiles' => $uploadedFiles,
                'selectedAuthor' => $author_id,
            ]);
        }

        return view('admin.items.create', [
            'categories' => $categories,
            'authors' => $authors,
        ]);
    }

    public function loadFiles($category_id, $author_id)
    {
        $uploadedFiles = UploadedFile::where('author_id', $author_id)
            ->where('category_id', hash_decode($category_id))
            ->notExpired()->select(['id', 'name'])
            ->get();

        $result = [];
        foreach ($uploadedFiles as $uploadedFile) {
            $result[hash_encode($uploadedFile->id)] = $uploadedFile->getShortName();
        }

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $itemSettings = settings('item');

        $rules = [
            'author_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'block_patterns', 'unique:items', 'max:100'],
            'description' => ['required'],
            'category' => ['required', 'string', 'exists:categories,slug'],
            'sub_category' => ['nullable', 'string', 'exists:sub_categories,slug'],
            'version' => ['nullable', 'regex:/^\d+\.\d+(\.\d+)*$/', 'max:100'],
            'demo_link' => ['nullable', 'url', 'block_patterns'],
            'tags' => ['required', 'block_patterns'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:255'],
            'original_price' => ['nullable', 'numeric', 'min:' . @$itemSettings->minimum_price, 'max:' . @$itemSettings->maximum_price],
            'validity_prices' => ['required', 'array'],
            'validity_prices.*' => ['nullable', 'numeric', 'min:' . @$itemSettings->minimum_price, 'max:' . @$itemSettings->maximum_price],
            'free_item' => ['nullable', 'boolean'],
            'purchasing_status' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string'],
            'status' => ['required', 'integer', 'min:1', 'max:6'],
        ];

        if (@$itemSettings->external_file_link_option) {
            $rules['main_file_source'] = ['required', 'boolean'];
            if ($request->main_file_source == Item::MAIN_FILE_EXTERNAL) {
                $rules['main_file'] = ['required', 'url'];
            }
        } else {
            $request->main_file_source = false;
        }

        if (@$itemSettings->support_status) {
            $rules['support'] = ['required', 'boolean'];
            if ($request->support == 1) {
                $rules['support_instructions'] = ['required', 'string', 'max:2000'];
            } else {
                $request->support_instructions = null;
            }
        } else {
            $request->is_supported = 0;
            $request->support_instructions = null;
        }

        try {
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    toastr()->error($error);
                }
                return back()->withInput();
            }

            $author = User::where('id', $request->author_id)->author()->firstOrFail();

            $validityPricesArray = $request->validity_prices ?? [];
            $hasPrice = false;
            foreach ($validityPricesArray as $price) {
                if ($price !== null && $price > 0) {
                    $hasPrice = true;
                    break;
                }
            }
            if (!$hasPrice) {
                toastr()->error(translate('Please provide at least one validity period price'));
                return back()->withInput();
            }

            $free = Item::NOT_FREE;
            $purchasing = Item::PURCHASING_STATUS_ENABLED;
            if (@$itemSettings->free_item_option) {
                if ($request->free_item) {
                    $free = Item::FREE;
                    $purchasing = ($request->purchasing_status == Item::PURCHASING_STATUS_ENABLED) ?
                    Item::PURCHASING_STATUS_ENABLED : Item::PURCHASING_STATUS_DISABLED;
                }
            }

            $description = $this->handleItemDescription($request->description);

            $category = Category::where('slug', $request->category)->with('categoryOptions')->firstOrFail();
            $options = $this->handleItemOptions($request, $category, $author->id);

            $subCategory = null;
            if ($request->has('sub_category') && !is_null($request->sub_category)) {
                $subCategory = SubCategory::where('slug', $request->sub_category)->firstOrFail();
            }

            $itemFiles = $this->handleItemFiles($request, $category, $author->id, false);
            $thumbnail = $itemFiles->thumbnail;
            $previewType = $itemFiles->preview_type;
            $previewImage = $itemFiles->preview_image;
            $previewVideo = $itemFiles->preview_video;
            $previewAudio = $itemFiles->preview_audio;
            $mainFile = $itemFiles->main_file;
            $screenshots = $itemFiles->screenshots;

            $validityPrices = json_encode($request->validity_prices);

            $item = new Item();
            $item->author_id = $author->id;
            $item->name = $request->name;
            $item->slug = SlugService::createSlug(Item::class, 'slug', $request->name, ['unique' => false]);
            $item->description = $description;
            $item->category_id = $category->id;
            $item->sub_category_id = $subCategory ? $subCategory->id : null;
            $item->options = $options;
            $item->version = $request->version;
            $item->demo_link = $request->demo_link;
            $item->tags = $request->tags;
            $item->features = json_encode(array_filter($request->features ?? []));
            $item->thumbnail = $thumbnail;
            $item->preview_type = $previewType;
            $item->preview_image = $previewImage;
            $item->preview_video = $previewVideo;
            $item->preview_audio = $previewAudio;
            $item->main_file = $mainFile;
            $item->is_main_file_external = $request->main_file_source;
            $item->screenshots = $screenshots;
            $item->validity_prices = $validityPrices;
            $item->original_price = $request->original_price ?? null;
            $item->regular_price = 0;
            $item->extended_price = 0;
            $item->is_supported = $request->support;
            $item->support_instructions = $request->support_instructions;
            $item->purchasing_status = $purchasing;
            $item->status = $request->status;
            $item->is_free = $free;
            $item->price_updated_at = Carbon::now();
            $item->save();

            $itemHistoryTitle = $request->status == Item::STATUS_APPROVED ? 
                ItemHistory::TITLE_TRUST_SUBMISSION : ItemHistory::TITLE_SUBMISSION;
            
            $this->createItemHistory($item->id, $itemHistoryTitle, $request->message, $author->id);
            $this->handleFileDeletionAfterInsert($request, $author->id);

            event(new ItemSubmitted($item));

            toastr()->success(translate('Item has been created successfully'));
            return redirect()->route('admin.items.show', $item->id);

        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back()->withInput();
        }
    }

    public function edit($id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $categories = Category::all();
        $authors = User::author()->orderBy('username')->get();
        $category = $item->category->load(['subCategories', 'categoryOptions']);

        $uploadedFiles = UploadedFile::where('author_id', $item->author_id)
            ->where('category_id', $category->id)->notExpired()->get();

        return view('admin.items.edit', [
            'item' => $item,
            'categories' => $categories,
            'authors' => $authors,
            'category' => $category,
            'uploadedFiles' => $uploadedFiles,
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = Item::where('id', $id)->firstOrFail();

        $itemSettings = settings('item');

        $rules = [
            'name' => ['required', 'string', 'block_patterns', 'max:100', 'unique:items,name,' . $item->id],
            'description' => ['required'],
            'version' => ['nullable', 'regex:/^\d+\.\d+(\.\d+)*$/', 'max:100'],
            'demo_link' => ['nullable', 'url', 'block_patterns'],
            'tags' => ['required', 'block_patterns'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:255'],
            'original_price' => ['nullable', 'numeric', 'min:' . @$itemSettings->minimum_price, 'max:' . @$itemSettings->maximum_price],
            'free_item' => ['nullable', 'boolean'],
            'purchasing_status' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string'],
            'status' => ['required', 'integer', 'min:1', 'max:6'],
        ];

        if ($itemSettings->external_file_link_option) {
            $rules['main_file_source'] = ['required', 'boolean'];
            if ($request->main_file_source == Item::MAIN_FILE_EXTERNAL) {
                $rules['main_file'] = ['nullable', 'url'];
            }
        } else {
            $request->main_file_source = false;
        }

        if (@$itemSettings->support_status) {
            $rules['support'] = ['required', 'boolean'];
            if ($request->support == 1) {
                $rules['support_instructions'] = ['required', 'string', 'max:2000'];
            } else {
                $request->support_instructions = null;
            }
        } else {
            $request->is_supported = 0;
            $request->support_instructions = null;
        }

        if (!$item->hasDiscount()) {
            $rules['validity_prices'] = ['required', 'array'];
            $rules['validity_prices.*'] = ['nullable', 'numeric', 'min:' . @$itemSettings->minimum_price, 'max:' . @$itemSettings->maximum_price];
        }

        try {
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    toastr()->error($error);
                }
                return back()->withInput();
            }

            if (!$item->hasDiscount()) {
                $validityPricesArray = $request->validity_prices ?? [];
                $hasPrice = false;
                foreach ($validityPricesArray as $price) {
                    if ($price !== null && $price > 0) {
                        $hasPrice = true;
                        break;
                    }
                }
                if (!$hasPrice) {
                    toastr()->error(translate('Please provide at least one validity period price'));
                    return back()->withInput();
                }
            }

            $free = Item::NOT_FREE;
            $purchasing = Item::PURCHASING_STATUS_ENABLED;
            if (@$itemSettings->free_item_option && !$item->isPremium()) {
                if ($request->free_item) {
                    $free = Item::FREE;
                    $purchasing = ($request->purchasing_status == Item::PURCHASING_STATUS_ENABLED) ?
                    Item::PURCHASING_STATUS_ENABLED : Item::PURCHASING_STATUS_DISABLED;
                }
            }

            $description = $this->handleItemDescription($request->description);

            $category = $item->category->load('categoryOptions');
            $options = $this->handleItemOptions($request, $category, $item->author_id);

            $itemFiles = $this->handleItemFiles($request, $category, $item->author_id, false);
            $thumbnail = $itemFiles->thumbnail;
            $previewType = $itemFiles->preview_type;
            $previewImage = $itemFiles->preview_image;
            $previewVideo = $itemFiles->preview_video;
            $previewAudio = $itemFiles->preview_audio;
            $mainFile = $itemFiles->main_file;
            $screenshots = $itemFiles->screenshots;

            if (!$item->hasDiscount()) {
                $validityPrices = json_encode($request->validity_prices);

                if ($validityPrices != $item->validity_prices) {
                    $priceUpdatedAt = Carbon::now();
                } else {
                    $priceUpdatedAt = $item->price_updated_at;
                }
            } else {
                $validityPrices = $item->validity_prices;
                $priceUpdatedAt = $item->price_updated_at;
            }

            $updated = false;
            $itemClone = clone $item;

            $item->name = $request->name;
            $item->slug = Str::slug($request->name);
            $item->description = $description;
            $item->options = $options;
            $item->version = $request->version;
            $item->demo_link = $request->demo_link;
            $item->tags = $request->tags;
            $item->features = json_encode(array_filter($request->features ?? []));

            if ($thumbnail) {
                $item->thumbnail = $thumbnail;
            }

            if ($previewImage) {
                $item->preview_image = $previewImage;
            }

            if ($previewVideo) {
                $item->preview_video = $previewVideo;
            }

            if ($previewAudio) {
                $item->preview_audio = $previewAudio;
            }

            if ($mainFile) {
                $item->main_file = $mainFile;
                $item->is_main_file_external = $request->main_file_source;
                if ($request->status == Item::STATUS_APPROVED) {
                    $item->last_update_at = Carbon::now();
                    $updated = true;
                }
            }

            if ($screenshots) {
                $item->screenshots = $screenshots;
            }

            $item->validity_prices = $validityPrices;
            $item->original_price = $request->original_price ?? null;
            $item->regular_price = 0;
            $item->extended_price = 0;
            $item->is_supported = $request->support;
            $item->support_instructions = $request->support_instructions;
            $item->status = $request->status;
            $item->purchasing_status = $purchasing;
            $item->is_free = $free;
            $item->price_updated_at = $priceUpdatedAt;
            $item->update();

            $itemHistoryTitle = ItemHistory::TITLE_TRUST_UPDATE;
            $this->createItemHistory($item->id, $itemHistoryTitle, $request->message, $item->author_id);
            $this->handleFileDeletionAfterInsert($request, $item->author_id, $itemClone);

            toastr()->success(translate('Item has been updated successfully'));
            return redirect()->route('admin.items.show', $item->id);
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back()->withInput();
        }
    }

    private function handleItemDescription($description)
    {
        $description = Purifier::clean($description);
        if (empty($description)) {
            throw new Exception(translate('Description cannot be empty'));
        }
        return $description;
    }

    private function handleItemOptions($request, $category, $author_id)
    {
        $options = null;
        if ($category->categoryOptions->count() > 0) {
            $options = [];
            foreach ($category->categoryOptions as $categoryOption) {
                $option = isset($request->options[$categoryOption->id]) ? $request->options[$categoryOption->id] : null;
                if ($categoryOption->isMultiple()) {
                    $requestOptions = $option ? $option : [];
                    if ($categoryOption->isRequired() && count($requestOptions) < 1) {
                        throw new Exception(translate(':field Cannot be empty', ['field' => $categoryOption->name]));
                    }
                    foreach ($requestOptions as $requestOption) {
                        if ($requestOption && !in_array($requestOption, $categoryOption->options)) {
                            throw new Exception(translate('Something went wrong, please refresh the page and try again.'));
                        }
                    }
                } else {
                    $requestOption = $option ? $option : null;
                    if ($categoryOption->isRequired() && empty($requestOption)) {
                        throw new Exception(translate(':field Cannot be empty', ['field' => $categoryOption->name]));
                    }
                    if ($requestOption && !in_array($requestOption, $categoryOption->options)) {
                        throw new Exception(translate('Something went wrong, please refresh the page and try again.'));
                    }
                }
                if ($option) {
                    $options[$categoryOption->name] = $option;
                }
            }
        }
        return $options;
    }

    private function handleItemFiles($request, $category, $author_id, $required = true)
    {
        $response['thumbnail'] = null;
        $response['preview_type'] = 'image';
        $response['preview_image'] = null;
        $response['preview_video'] = null;
        $response['preview_audio'] = null;
        $response['main_file'] = null;
        $response['screenshots'] = null;

        if ($request->filled('thumbnail')) {
            $thumbnail = UploadedFile::where('author_id', $author_id)
                ->where('id', hash_decode($request->thumbnail))->notExpired()->first();
            if (!$thumbnail) {
                throw new Exception(translate('One or more of the selected files are expired or not exist'));
            }
            if (!in_array($thumbnail->mime_type, $this->imageMimeTypes)) {
                throw new Exception(translate('Thumbnail must be the type of JPG or PNG'));
            }
            $response['thumbnail'] = $thumbnail->path;
        } else {
            if ($required) {
                throw new Exception(translate(':field Cannot be empty', ['field' => 'Thumbnail']));
            }
        }

        if (!$category->isFileTypeFileWithAudioPreview()) {
            if ($request->filled('preview_image')) {
                $previewImage = UploadedFile::where('author_id', $author_id)
                    ->where('id', hash_decode($request->preview_image))->notExpired()->first();
                if (!$previewImage) {
                    throw new Exception(translate('One or more of the selected files are expired or not exist'));
                }
                if (!in_array($previewImage->mime_type, $this->imageMimeTypes)) {
                    throw new Exception(translate('Preview image must be the type of JPG or PNG'));
                }
                $response['preview_image'] = $previewImage->path;
            } else {
                if ($required) {
                    throw new Exception(translate(':field Cannot be empty', ['field' => 'Preview image']));
                }
            }
        }

        if ($category->isFileTypeFileWithVideoPreview()) {
            if ($request->filled('preview_video')) {
                $previewVideo = UploadedFile::where('author_id', $author_id)
                    ->where('id', hash_decode($request->preview_video))->notExpired()->first();
                if (!$previewVideo) {
                    throw new Exception(translate('One or more of the selected files are expired or not exist'));
                }
                if (!in_array($previewVideo->mime_type, $this->videoMimeTypes)) {
                    throw new Exception(translate('Video preview must be the type of MP4 or WEBM'));
                }
                if ($previewVideo->size > $category->max_preview_file_size) {
                    throw new Exception(translate('Video preview max file size is :size', ['size' => formatBytes($category->max_preview_file_size)]));
                }
                $response['preview_type'] = 'video';
                $response['preview_video'] = $previewVideo->path;
            } else {
                if ($required) {
                    throw new Exception(translate(':field Cannot be empty', ['field' => 'Video preview']));
                }
            }
        }

        if ($category->isFileTypeFileWithAudioPreview()) {
            if ($request->filled('preview_audio')) {
                $previewAudio = UploadedFile::where('author_id', $author_id)
                    ->where('id', hash_decode($request->preview_audio))->notExpired()->first();
                if (!$previewAudio) {
                    throw new Exception(translate('One or more of the selected files are expired or not exist'));
                }
                if (!in_array($previewAudio->mime_type, $this->audioMimeTypes)) {
                    throw new Exception(translate('Audio preview must be the type of MP3 or WAV'));
                }
                if ($previewAudio->size > $category->max_preview_file_size) {
                    throw new Exception(translate('Audio preview max file size is :size', ['size' => formatBytes($category->max_preview_file_size)]));
                }
                $response['preview_type'] = 'audio';
                $response['preview_audio'] = $previewAudio->path;
            } else {
                if ($required) {
                    throw new Exception(translate(':field Cannot be empty', ['field' => 'Audio preview']));
                }
            }
        }

        if ($request->filled('main_file')) {
            if ($request->main_file_source == Item::MAIN_FILE_NOT_EXTERNAL) {
                $mainFile = UploadedFile::where('author_id', $author_id)
                    ->where('id', hash_decode($request->main_file))->notExpired()->first();
                if (!$mainFile) {
                    throw new Exception(translate('One or more of the selected files are expired or not exist'));
                }
                $mainFileTypes = explode(',', $category->main_file_types);
                if (!in_array($mainFile->extension, $mainFileTypes)) {
                    throw new Exception(translate('Main files must be the type of :types', ['types' => $category->main_file_types]));
                }
                $response['main_file'] = $mainFile->path;
            } else {
                $response['main_file'] = $request->main_file;
            }
        } else {
            if ($required) {
                throw new Exception(translate(':field Cannot be empty', ['field' => 'Main file']));
            }
        }

        if ($category->isFileTypeFileWithImagePreview()) {
            if ($request->filled('screenshots')) {
                if ($required && count($request->screenshots) < 0) {
                    throw new Exception(translate(':field Cannot be empty', ['field' => 'Screenshots']));
                }
                $screenshots = [];
                foreach ($request->screenshots as $screenshot) {
                    $screenshot = UploadedFile::where('author_id', $author_id)
                        ->where('id', hash_decode($screenshot))->notExpired()->first();
                    if (!$screenshot) {
                        throw new Exception(translate('One or more of the selected files are expired or not exist'));
                    }
                    if (!in_array($screenshot->mime_type, $this->imageMimeTypes)) {
                        throw new Exception(translate('Screenshots must be the type of JPG or PNG'));
                    }
                    $screenshots[] = $screenshot->path;
                }
                if (count($screenshots) > $category->maximum_screenshots) {
                    throw new Exception(translate('Maximum screenshots is :maximum', ['maximum' => $category->maximum_screenshots]));
                }
                $response['screenshots'] = $screenshots;
            }
        }

        return (object) $response;
    }

    private function handleFileDeletionAfterInsert($request, $author_id, $item = null)
    {
        if ($request->filled('thumbnail')) {
            if ($item) {
                $item->deleteThumbnail();
            }
            $this->deleteUploadedFile($request->thumbnail);
        }

        if ($request->filled('preview_image')) {
            if ($item) {
                $item->deletePreviewImage();
            }
            $this->deleteUploadedFile($request->preview_image);
        }

        if ($request->filled('preview_video')) {
            if ($item) {
                $item->deletePreviewVideo();
            }
            $this->deleteUploadedFile($request->preview_video);
        }

        if ($request->filled('preview_audio')) {
            if ($item) {
                $item->deletePreviewAudio();
            }
            $this->deleteUploadedFile($request->preview_audio);
        }

        if ($request->filled('main_file')) {
            if ($item && $item->isMainFileExternal()) {
                $item->deleteMainFile();
            }

            if ($request->main_file_source == Item::MAIN_FILE_NOT_EXTERNAL) {
                $this->deleteUploadedFile($request->main_file);
            }
        }

        if ($request->filled('screenshots')) {
            if ($item) {
                $item->deleteScreenshots();
            }
            foreach ($request->screenshots as $screenshot) {
                $this->deleteUploadedFile($screenshot);
            }
        }
    }

    private function deleteUploadedFile($fileId)
    {
        $uploadedFile = UploadedFile::where('id', hash_decode($fileId))
            ->notExpired()->first();
        if ($uploadedFile) {
            $uploadedFile->delete();
        }
    }

    private function createItemHistory($itemId, $title, $message = null, $author_id = null)
    {
        $itemHistory = new ItemHistory();
        $itemHistory->admin_id = authAdmin()->id;
        $itemHistory->author_id = $author_id;
        $itemHistory->item_id = $itemId;
        $itemHistory->title = $title;
        $itemHistory->body = $message;
        $itemHistory->save();
    }
}