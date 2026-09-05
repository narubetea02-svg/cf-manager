<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestoreAuthenticatedUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            $snapshot = $request->session()->get('authenticated_user');

            if (is_array($snapshot) && ! empty($snapshot['facebook_id'])) {
                $user = User::updateOrCreate(
                    ['facebook_id' => $snapshot['facebook_id']],
                    [
                        'name' => $snapshot['name'] ?? 'Facebook User',
                        'email' => $snapshot['email'] ?? ('fb_'.$snapshot['facebook_id'].'@cfshop.local'),
                        'avatar' => $snapshot['avatar'] ?? '',
                    ],
                );

                Auth::login($user, true);
            }
        }

        return $next($request);
    }
}
