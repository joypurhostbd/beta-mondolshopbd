<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\GeneralSetting;
use App\Models\Category;
use App\Models\Brand;
use App\Models\SocialMedia;
use App\Models\Contact;
use App\Models\CreatePage;
use App\Models\OrderStatus;
use App\Models\EcomPixel;
use App\Models\GoogleTagManager;
use App\Models\Order;
use App\Models\PaymentGateway;
use Modules\Setting\Application\Services\TagManagerService;
use Config;
use Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Module Service Providers
        $this->app->register(\Modules\Catalog\Infrastructure\Providers\CatalogServiceProvider::class);
        $this->app->register(\Modules\Customer\Infrastructure\Providers\CustomerServiceProvider::class);
        $this->app->register(\Modules\Order\Infrastructure\Providers\OrderServiceProvider::class);
        $this->app->register(\Modules\Payment\Infrastructure\Providers\PaymentServiceProvider::class);
        $this->app->register(\Modules\Shipping\Infrastructure\Providers\ShippingServiceProvider::class);
        $this->app->register(\Modules\Setting\Infrastructure\Providers\SettingServiceProvider::class);
        $this->app->register(\Modules\Inventory\Infrastructure\Providers\InventoryServiceProvider::class);
        $this->app->register(\Modules\Promotion\Infrastructure\Providers\PromotionServiceProvider::class);

        // Compatibility alias for ShoppingCart facade
        if (!class_exists('Gloudemans\Shoppingcart\Facades\Cart')) {
            class_alias(\App\Facades\Cart::class, 'Gloudemans\Shoppingcart\Facades\Cart');
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://') || $this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Register safe data URI scheme for HTMLPurifier (allows safe base64 images in rich text/product descriptions)
        if (class_exists(\HTMLPurifier_URISchemeRegistry::class)) {
            \HTMLPurifier_URISchemeRegistry::instance()->register('data', new class extends \HTMLPurifier_URIScheme {
                public $default_port = null;
                public $browsable = true;
                public $hierarchical = false;
                public $may_omit_host = true;

                public function doValidate(&$uri, $config, $context)
                {
                    $raw = $uri->path;
                    return (bool) preg_match('#^image/(png|jpeg|jpg|gif|webp|svg\+xml);base64,[A-Za-z0-9+/=]+$#i', $raw);
                }
            });
        }

        try {
            $shurjopay = PaymentGateway::where(['status' => 1, 'type' => 'shurjopay'])->first();
            if ($shurjopay) {
                
                Config::set(['shurjopay.apiCredentials.username' => $shurjopay->username]);
                Config::set(['shurjopay.apiCredentials.password' => $shurjopay->password]);
                Config::set(['shurjopay.apiCredentials.prefix' => $shurjopay->prefix]);
                Config::set(['shurjopay.apiCredentials.return_url' => $shurjopay->success_url]);
                Config::set(['shurjopay.apiCredentials.cancel_url' => $shurjopay->return_url]);
                Config::set(['shurjopay.apiCredentials.base_url' => $shurjopay->base_url]);
            }
            $generalsetting = GeneralSetting::where('status',1)->limit(1)->first();
            view()->share('generalsetting',$generalsetting); 

            $sidecategories = Category::where('parent_id','=','0')->where('status',1)->select('id','name','slug','status','image')->get();
            view()->share('sidecategories',$sidecategories); 
            
            $menucategories = Category::where('status',1)->select('id','name','slug','status','image')->get();
            view()->share('menucategories',$menucategories); 

            $contact = Contact::where('status',1)->first();
            view()->share('contact',$contact); 

            $socialicons = SocialMedia::where('status',1)->get();
            view()->share('socialicons',$socialicons);

            $pages = CreatePage::where('status',1)->limit(3)->get();
            view()->share('pages',$pages);

            $pagesright = CreatePage::where('status',1)->skip(3)->limit(10)->get();
            view()->share('pagesright',$pagesright);

            $cmnmenu = CreatePage::where('status',1)->get();
            view()->share('cmnmenu',$cmnmenu);

            $brands = Brand::where('status',1)->get();
            view()->share('brands',$brands);
            
            $neworder = Order::where('order_status','1')->count();
            view()->share('neworder',$neworder); 
            
            $pendingorder = Order::where('order_status','1')->latest()->limit(9)->get();
            view()->share('pendingorder',$pendingorder); 
            
            $orderstatus = OrderStatus::get();
            view()->share('orderstatus',$orderstatus);
            
            $pixels = EcomPixel::where('status',1)->get();
            view()->share('pixels',$pixels);
            
            $gtm_code = app(TagManagerService::class)->getActiveCached();
            view()->share('gtm_code',$gtm_code);
        } catch (\Exception $e) {
            // Ignore database connection issues during deployment/artisan commands
        }
    }
}
