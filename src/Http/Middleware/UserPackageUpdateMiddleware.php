<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserPackageUpdateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!is_null(auth()->user()->getCurrentInitiatePlan)) {
            return redirect(route('user.subscription.index'))->with('error', __('Please update the plan rules.'));
        }
        /*elseif (!auth()->user()->BA) {
            return redirect(route('user.subscription.index'))->with('error', __('No plan'));
        }*/

        return $next($request);
    }
}
