<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'streak' => fn () => $request->user()?->role === 'child'
                    ? ($request->user()->student?->streak?->active_streak ?? 0)
                    : null,
                'streak_active_today' => fn () => $request->user()?->role === 'child'
                    ? ($request->user()->student?->streak?->is_active_today ?? false)
                    : false,
            ],
            'flash' => [
                'streak_award' => fn () => $request->session()->get('streak_award'),
            ],
        ];
    }
}
