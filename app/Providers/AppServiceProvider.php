<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Event as SchoolEvent;
use App\Models\House;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\PrefectBoard;
use App\Models\Programme;
use App\Models\User;
use App\Policies\ActivityPolicy;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Activitylog\Models\Activity;

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

        // Media URLs follow the host serving the request (domain root, subfolder, or artisan serve),
        // instead of a fixed APP_URL.
        if (! app()->runningInConsole()) {
            config(['filesystems.disks.public.url' => url('storage')]);
        }

        // Super admins pass every policy check; everyone else goes through App\Policies.
        Gate::before(fn (User $user) => $user->is_active && $user->hasRole('super-admin') ? true : null);
        Gate::policy(Activity::class, ActivityPolicy::class);

        // Navigation and sitemap are cached; edits in the admin must show up straight away.
        foreach ([Page::class, Post::class, MenuItem::class, SchoolEvent::class, Announcement::class, House::class, Programme::class, PrefectBoard::class] as $model) {
            $model::saved(fn () => $this->flushContentCaches());
            $model::deleted(fn () => $this->flushContentCaches());
        }

        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
                activity('auth')->causedBy($event->user)->log('Signed in');
            }
        });
        Event::listen(Failed::class, function (Failed $event) {
            // Log the attempt without the password; the email is kept to spot targeted attacks.
            activity('auth')->withProperties(['email' => $event->credentials['email'] ?? null, 'ip' => request()->ip()])->log('Failed sign-in');
        });

        $this->configureRateLimiting();
    }

    private function flushContentCaches(): void
    {
        Cache::forget('menu.main');
        Cache::forget('menu.footer');
        Cache::forget('sitemap.xml');
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
