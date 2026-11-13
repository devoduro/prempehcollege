<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
        Model::shouldBeStrict(! app()->isProduction());

        Password::defaults(fn () => Password::min(12)->letters()->mixedCase()->numbers()->uncompromised());

        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        $ip = fn (Request $r) => $r->ip();

        RateLimiter::for('web', fn (Request $r) => Limit::perMinute(120)->by($ip($r)));
        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(60)->by($ip($r)));
        RateLimiter::for('search', fn (Request $r) => Limit::perMinute(30)->by($ip($r)));
        RateLimiter::for('downloads', fn (Request $r) => Limit::perMinute(30)->by($ip($r)));

        RateLimiter::for('login', fn (Request $r) => [
            Limit::perMinute(5)->by(strtolower((string) $r->input('email')).'|'.$ip($r)),
            Limit::perHour(20)->by($ip($r)),
        ]);

        RateLimiter::for('contact', fn (Request $r) => [
            Limit::perMinute(3)->by($ip($r)),
            Limit::perHour(10)->by($ip($r)),
        ]);
    }
}
