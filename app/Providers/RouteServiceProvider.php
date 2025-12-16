<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
                require_once base_path('routes/chat.php');
               // require_once base_path('routes/car.php');
               // require_once base_path('routes/profile.php');
               // require_once base_path('routes/admin.php');
              //  require_once base_path('routes/post.php');
              //  require_once base_path('routes/rents.php');
             //  require_once base_path('routes/auth.php');
             //   require_once base_path('routes/rental.php');
             //   require_once base_path('routes/booking.php');
             //   require_once base_path('routes/newbookings.php');
             ///   require_once base_path('routes/users.php');
            ///   require_once base_path('routes/adress.php');
             //   require_once base_path('routes/rezerv.php');
             //   require_once base_path('routes/notifications.php');
             //   require_once base_path('routes/puty.php');
            //    require_once base_path('routes/taxi.php');
             //   require_once base_path('routes/tender.php');
        });
    }
}
