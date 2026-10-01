<?php

namespace App\Http\Controllers\Frontend;

use App\Jobs\SendMetaCapiEventJob;

use App\Services\Payment\ShurjoPayService;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Models\Product;
use App\Models\District;
use App\Models\CreatePage;
use App\Models\Campaign;
use App\Models\Banner;
use App\Models\BannerCategory;
use App\Models\ShippingCharge;
use App\Models\Productcolor;
use App\Models\Productsize;
use App\Models\Customer;
use App\Models\OrderDetails;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Review;
use Session;
use App\Services\CartService;
use Auth;

class FrontendController extends Controller
{
    public function index()
    {
        $frontcategory = Category::where(['status' => 1])
            ->select('id', 'name', 'image', 'slug', 'status')
            ->get();

        $sliderCategory = BannerCategory::where('status', 1)
            ->where(function ($q) {
                $q->where('name', 'LIKE', '%Slider%')
                    ->where('name', 'NOT LIKE', '%Bottom%');
            })
            ->first();

        $sliders = Banner::active()
            ->when($sliderCategory, fn($q) => $q->where('category_id', $sliderCategory->id))
            ->select('id', 'image', 'link')
            ->get();

        if ($sliders->isEmpty()) {
            $sliders = Banner::active()
                ->select('id', 'image', 'link')
                ->limit(5)
                ->get();
        }

        $bottomAdsCategory = BannerCategory::where('status', 1)
            ->where(function ($q) {
                $q->where('name', 'LIKE', '%Slider Bottom%')
                    ->orWhere('name', 'LIKE', '%Bottom Ads%')
                    ->orWhere('id', 5);
            })
            ->first();

        $sliderbottomads = $bottomAdsCategory
            ? Banner::active()
                ->where('category_id', $bottomAdsCategory->id)
                ->select('id', 'image', 'link')
                ->limit(3)
                ->get()
            : collect();

        $footerAdsCategory = BannerCategory::where('status', 1)
            ->where(function ($q) {
                $q->where('name', 'LIKE', '%Footer Top%')
                    ->orWhere('name', 'LIKE', '%Footer Ads%')
                    ->orWhere('id', 6);
            })
            ->first();

        $footertopads = $footerAdsCategory
            ? Banner::active()
                ->where('category_id', $footerAdsCategory->id)
                ->select('id', 'image', 'link')
                ->limit(2)
                ->get()
            : collect();

        $hotdeal_bottom = collect();

        $hotdeal_top = Product::where(['status' => 1, 'topsale' => 1])
            ->orderBy('id', 'DESC')
            ->select('id', 'name', 'slug', 'new_price', 'old_price')
            ->with([
                'image',
                'prosizes' => fn($q) => $q->with('size:id,sizeName'),
                'procolors' => fn($q) => $q->with('color:id,color,colorName'),
            ])
            ->limit(12)
            ->get();

        $homeproducts = Category::where(['front_view' => 1, 'status' => 1])
            ->orderBy('id', 'ASC')
            ->select('id', 'name', 'slug')
            ->with([
                'products' => function ($query) {
                    $query->where('status', 1)
                        ->select('id', 'name', 'slug', 'new_price', 'old_price', 'category_id')
                        ->with([
                            'image',
                            'prosizes' => fn($q) => $q->with('size:id,sizeName'),
                            'procolors' => fn($q) => $q->with('color:id,color,colorName'),
                        ]);
                }
            ])
            ->get()
            ->map(function ($query) {
                $query->setRelation('products', $query->products->take(12));
                return $query;
            });

        return view('frontEnd.layouts.pages.index', compact('sliders', 'frontcategory', 'hotdeal_top', 'hotdeal_bottom', 'homeproducts', 'sliderbottomads', 'footertopads'));
    }

    public function hotdeals()
    {

        $products = Product::where(['status' => 1, 'topsale' => 1])
            ->select('id', 'name', 'slug', 'new_price', 'old_price')
            ->with(['image', 'prosizes.size', 'procolors.color'])
            ->paginate(36);
        return view('frontEnd.layouts.pages.hotdeals', compact('products'));
    }

    public function category($slug, Request $request)
    {
        $category = Category::where(['slug' => $slug, 'status' => 1])->first();
        $products = Product::where(['status' => 1, 'category_id' => $category->id])
            ->select('id', 'name', 'slug', 'new_price', 'old_price', 'category_id')
            ->with(['image', 'prosizes.size', 'procolors.color']);
        $subcategories = Subcategory::where('category_id', $category->id)->get();

        // return $request->sort;
        if ($request->sort == 1) {
            $products = $products->orderBy('created_at', 'desc');
        } elseif ($request->sort == 2) {
            $products = $products->orderBy('created_at', 'asc');
        } elseif ($request->sort == 3) {
            $products = $products->orderBy('new_price', 'desc');
        } elseif ($request->sort == 4) {
            $products = $products->orderBy('new_price', 'asc');
        } elseif ($request->sort == 5) {
            $products = $products->orderBy('name', 'asc');
        } elseif ($request->sort == 6) {
            $products = $products->orderBy('name', 'desc');
        } else {
            $products = $products->latest();
        }

        $min_price = $products->min('new_price');
        $max_price = $products->max('new_price');
        if($request->min_price && $request->max_price){
            $products = $products->where('new_price','>=',$request->min_price);
            $products = $products->where('new_price','<=',$request->max_price);
        }

        $selectedSubcategories = $request->input('subcategory', []);
        $products = $products->when($selectedSubcategories, function ($query) use ($selectedSubcategories) {
            return $query->whereHas('subcategory', function ($subQuery) use ($selectedSubcategories) {
                $subQuery->whereIn('id', $selectedSubcategories);
            });
        });

        $products = $products->paginate(24);
        return view('frontEnd.layouts.pages.category', compact('category', 'products', 'subcategories', 'min_price', 'max_price'));
    }

    public function subcategory($slug, Request $request)
    {
        $subcategory = Subcategory::where(['slug' => $slug, 'status' => 1])->first();
        $products = Product::where(['status' => 1, 'subcategory_id' => $subcategory->id])
            ->select('id', 'name', 'slug', 'new_price', 'old_price', 'category_id', 'subcategory_id')
            ->with(['image', 'prosizes.size', 'procolors.color']);
        $childcategories = Childcategory::where('subcategory_id', $subcategory->id)->get();

        // return $request->sort;
        if ($request->sort == 1) {
            $products = $products->orderBy('created_at', 'desc');
        } elseif ($request->sort == 2) {
            $products = $products->orderBy('created_at', 'asc');
        } elseif ($request->sort == 3) {
            $products = $products->orderBy('new_price', 'desc');
        } elseif ($request->sort == 4) {
            $products = $products->orderBy('new_price', 'asc');
        } elseif ($request->sort == 5) {
            $products = $products->orderBy('name', 'asc');
        } elseif ($request->sort == 6) {
            $products = $products->orderBy('name', 'desc');
        } else {
            $products = $products->latest();
        }
        
        $min_price = $products->min('new_price');
        $max_price = $products->max('new_price');
        if($request->min_price && $request->max_price){
            $products = $products->where('new_price','>=',$request->min_price);
            $products = $products->where('new_price','<=',$request->max_price);
        }

        $selectedChildcategories = $request->input('childcategory', []);
        $products = $products->when($selectedChildcategories, function ($query) use ($selectedChildcategories) {
            return $query->whereHas('childcategory', function ($subQuery) use ($selectedChildcategories) {
                $subQuery->whereIn('id', $selectedChildcategories);
            });
        });

        $products = $products->paginate(24);
        // return $products;
        $impproducts = Product::where(['status' => 1, 'topsale' => 1])
            ->with('image')
            ->limit(6)
            ->select('id', 'name', 'slug')
            ->get();

        return view('frontEnd.layouts.pages.subcategory', compact('subcategory', 'products', 'impproducts', 'childcategories', 'max_price', 'min_price'));
    }

    public function products($slug, Request $request)
    {
        $childcategory = Childcategory::where(['slug' => $slug, 'status' => 1])->first();
        $childcategories = Childcategory::where('subcategory_id', $childcategory->subcategory_id)->get();
        $products = Product::where(['status' => 1, 'childcategory_id' => $childcategory->id])
            ->select('id', 'name', 'slug', 'new_price', 'old_price', 'category_id', 'subcategory_id', 'childcategory_id')
            ->with(['image', 'featuredImage', 'category', 'prosizes.size', 'procolors.color']);


        // return $request->sort;
        if ($request->sort == 1) {
            $products = $products->orderBy('created_at', 'desc');
        } elseif ($request->sort == 2) {
            $products = $products->orderBy('created_at', 'asc');
        } elseif ($request->sort == 3) {
            $products = $products->orderBy('new_price', 'desc');
        } elseif ($request->sort == 4) {
            $products = $products->orderBy('new_price', 'asc');
        } elseif ($request->sort == 5) {
            $products = $products->orderBy('name', 'asc');
        } elseif ($request->sort == 6) {
            $products = $products->orderBy('name', 'desc');
        } else {
            $products = $products->latest();
        }
        
        $min_price = $products->min('new_price');
        $max_price = $products->max('new_price');
        if($request->min_price && $request->max_price){
            $products = $products->where('new_price','>=',$request->min_price);
            $products = $products->where('new_price','<=',$request->max_price);
        }

        $products = $products->paginate(24);
        // return $products;
        $impproducts = Product::where(['status' => 1, 'topsale' => 1])
            ->with('image')
            ->limit(6)
            ->select('id', 'name', 'slug')
            ->get();

        return view('frontEnd.layouts.pages.childcategory', compact('childcategory', 'products', 'impproducts', 'min_price', 'max_price', 'childcategories'));
    }


    public function details($slug)
    {
        $details = Product::where(['slug' => $slug, 'status' => 1])
            ->with('image', 'featuredImage', 'images', 'category', 'subcategory', 'childcategory')
            ->first();

        if (!$details) {
            // Fallback 1: Extract numeric ID suffix from slug (e.g. ...-256 or ...-271)
            if (preg_match('/-(\d+)$/', $slug, $matches)) {
                $details = Product::where(['id' => $matches[1], 'status' => 1])
                    ->with('image', 'featuredImage', 'images', 'category', 'subcategory', 'childcategory')
                    ->first();
            }
        }

        if (!$details) {
            // Fallback 2: Check normalized slug (replacing en-dash, em-dash, multiple hyphens)
            $normalizedSlug = preg_replace('/[–—\s]+/u', '-', $slug);
            $normalizedSlug = preg_replace('/-+/', '-', $normalizedSlug);
            $details = Product::where(['slug' => $normalizedSlug, 'status' => 1])
                ->with('image', 'featuredImage', 'images', 'category', 'subcategory', 'childcategory')
                ->first();
        }

        if (!$details) {
            abort(404);
        }

        $products = Product::where(['category_id' => $details->category_id, 'status' => 1])
            ->with(['image', 'prosizes.size', 'procolors.color'])
            ->select('id', 'name', 'slug', 'new_price', 'old_price')
            ->get();
        $shippingcharge = ShippingCharge::where('status', 1)->get();
        $reviews = Review::where('product_id', $details->id)->get();
        $productcolors = Productcolor::where('product_id', $details->id)
            ->with('color')
            ->get();
        // return $productcolors;
        $productsizes = Productsize::where('product_id', $details->id)
            ->with('size')
            ->get();

        /*
         * Meta ViewContent Server-Side Tracking
         *
         * The same event ID is passed to the browser Pixel so Meta can
         * deduplicate Browser + Server ViewContent events.
         */
        $viewContentEventId = 'viewcontent_' . $details->id . '_' . substr(
            hash('sha256', session()->getId() . '_' . $details->id),
            0,
            16
        );

        try {
            $fbp = request()->cookie('_fbp');
            $fbc = request()->cookie('_fbc');

            if (!$fbc && request()->has('fbclid')) {
                $fbc = 'fb.1.' . time() . '.' . request()->get('fbclid');
            }

            $userData = [
                'fbp' => $fbp,
                'fbc' => $fbc,
                'client_ip_address' => request()->ip(),
                'client_user_agent' => request()->userAgent(),
            ];

            $productData = [
                'id' => $details->id,
                'name' => $details->name,
                'new_price' => $details->new_price,
                'price' => $details->new_price,
            ];

            $customData = [
                'currency' => 'BDT',
                'value' => (float) ($details->new_price ?? 0),
                'content_type' => 'product',
                'content_name' => $details->name,
                'content_ids' => [(string) $details->id],
                'contents' => [
                    [
                        'id' => (string) $details->id,
                        'quantity' => 1,
                        'item_price' => (float) ($details->new_price ?? 0),
                    ],
                ],
            ];

            SendMetaCapiEventJob::dispatch(
                'ViewContent',
                $userData,
                $customData,
                $viewContentEventId,
                request()->fullUrl()
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                'Meta CAPI ViewContent dispatch error: ' . $e->getMessage()
            );
        }

        return view(
            'frontEnd.layouts.pages.details',
            compact(
                'details',
                'products',
                'shippingcharge',
                'productcolors',
                'productsizes',
                'reviews',
                'viewContentEventId'
            )
        );
    }
    public function quickview(Request $request)
    {
        $product = Product::where(['id' => $request->id, 'status' => 1])
            ->with(['images', 'image', 'featuredImage', 'prosizes.size', 'procolors.color', 'category', 'brand'])
            ->withCount('reviews')
            ->first();

        if (!$product) {
            return response('', 404);
        }

        $data = $product;
        $productcolors = $product->procolors;
        $productsizes = $product->prosizes;

        return view('frontEnd.layouts.ajax.quickview', compact('product', 'data', 'productcolors', 'productsizes'));
    }
    public function livesearch(Request $request)
    {
        $keyword = trim((string) $request->keyword);
        $category = $request->category;

        if (empty($keyword) && empty($category)) {
            $products = collect();
            return view('frontEnd.layouts.ajax.search', compact('products'));
        }

        $products = Product::select('id', 'name', 'slug', 'new_price', 'old_price')
            ->where('status', 1)
            ->with('image');

        if (!empty($keyword)) {
            $products = $products->where('name', 'LIKE', '%' . $keyword . '%');
        }

        if (!empty($category)) {
            $products = $products->where('category_id', $category);
        }

        $products = $products->limit(8)->get();

        return view('frontEnd.layouts.ajax.search', compact('products'));
    }
    public function search(Request $request)
    {
        $products = Product::select('id', 'name', 'slug', 'new_price', 'old_price')
            ->where('status', 1)
            ->with(['image', 'prosizes.size', 'procolors.color']);
        if ($request->keyword) {
            $products = $products->where('name', 'LIKE', '%' . $request->keyword . "%");
        }
        if ($request->category) {
            $products = $products->where('category_id', $request->category);
        }
        $products = $products->paginate(36);
        $keyword = $request->keyword;
        return view('frontEnd.layouts.pages.search', compact('products', 'keyword'));
    }

    public function shipping_charge(Request $request)
    {

        $shipping = ShippingCharge::where(['id' => $request->id])->first();
        Session::put('shipping', $shipping->amount);
        return view('frontEnd.layouts.ajax.cart');
    }


    public function contact(Request $request)
    {
        return view('frontEnd.layouts.pages.contact');
    }

    public function page($slug)
    {
        $page = CreatePage::where('slug', $slug)->firstOrFail();
        return view('frontEnd.layouts.pages.page', compact('page'));
    }
    public function districts(Request $request)
    {
        $areas = District::where(['district' => $request->id])->pluck('area_name', 'id');
        return response()->json($areas);
    }
    public function campaign($slug)
    {
        $campaign_data = Campaign::where('slug', $slug)->with('images')->first();
        if (!$campaign_data) {
            abort(404, 'Campaign landing page not found');
        }

        $product = Product::where('id', $campaign_data->product_id)
            ->where('status', 1)
            ->with('image')
            ->first();

        if (!$product) {
            abort(404, 'Campaign product is unavailable or inactive');
        }

        CartService::instance('shopping')->destroy();
        $cart_count = CartService::instance('shopping')->count();
        if ($cart_count == 0) {
            $effectivePrice = ($campaign_data->special_price && $campaign_data->special_price > 0)
                ? (float) $campaign_data->special_price
                : (float) $product->new_price;

            CartService::instance('shopping')->add([
                'id' => $product->id,
                'name' => $product->name,
                'qty' => 1,
                'price' => $effectivePrice,
                'options' => [
                    'slug' => $product->slug,
                    'image' => ($product->featuredImage ?? $product->image)?->image ?? '',
                    'old_price' => $product->old_price,
                    'purchase_price' => $product->purchase_price,
                    'campaign_id' => $campaign_data->id,
                ],
            ]);
        }
        $shippingcharge = ShippingCharge::where('status', 1)->get();
        $select_charge = ShippingCharge::where('status', 1)->first();
        
        $shippingAmount = $campaign_data->free_shipping ? 0 : ($select_charge->amount ?? 0);
        Session::put('shipping', $shippingAmount);
        Session::put('campaign_id', $campaign_data->id);

        return view('frontEnd.layouts.pages.campaign.campaign', compact('campaign_data', 'product', 'shippingcharge'));
    }

    public function payment_success(Request $request)
    {
        $order_id = $request->order_id;
        $shurjopay_service = new ShurjoPayService();
        $json = $shurjopay_service->verify($order_id);
        $data = json_decode($json);

        if ($data[0]->sp_code != 1000) {
            Toastr::error('Your payment failed, try again', 'Oops!');
            if ($data[0]->value1 == 'customer_payment') {
                return redirect()->route('home');
            } else {
                return redirect()->route('home');
            }
        }

        if ($data[0]->value1 == 'customer_payment') {

            $customer = Customer::find(Auth::guard('customer')->user()->id);

            // order data save
            $order = new Order();
            $order->invoice_id = $data[0]->id;
            $order->amount = $data[0]->amount;
            $order->customer_id = Auth::guard('customer')->user()->id;
            $order->order_status = OrderStatusEnum::Pending->value; // TODO: map bank_status to enum
            $order->save();

            // payment data save
            $payment = new Payment();
            $payment->order_id = $order->id;
            $payment->customer_id = Auth::guard('customer')->user()->id;
            $payment->payment_method = 'shurjopay';
            $payment->amount = $order->amount;
            $payment->trx_id = $data[0]->bank_trx_id;
            $payment->sender_number = $data[0]->phone_no;
            $payment->payment_status = PaymentStatusEnum::Paid->value;
            $payment->save();
            // order details data save
            foreach (CartService::instance('shopping')->content() as $cart) {
                $order_details = new OrderDetails();
                $order_details->order_id = $order->id;
                $order_details->product_id = $cart->id;
                $order_details->product_name = $cart->name;
                $order_details->purchase_price = $cart->options->purchase_price;
                $order_details->sale_price = $cart->price;
                $order_details->qty = $cart->qty;
                $order_details->save();
            }

            CartService::instance('shopping')->destroy();
            Toastr::error('Thanks, Your payment send successfully', 'Success!');
            return redirect()->route('home');
        }

        Toastr::error('Something wrong, please try agian', 'Error!');
        return redirect()->route('home');
    }
    public function payment_cancel(Request $request)
    {
        $order_id = $request->order_id;
        $shurjopay_service = new ShurjoPayService();
        $json = $shurjopay_service->verify($order_id);
        $data = json_decode($json);

        Toastr::error('Your payment cancelled', 'Cancelled!');
        if ($data[0]->sp_code != 1000) {
            if ($data[0]->value1 == 'customer_payment') {
                return redirect()->route('home');
            } else {
                return redirect()->route('home');
            }
        }
    }

    public function offers()
    {
        return view('frontEnd.layouts.pages.offers');
    }

}
