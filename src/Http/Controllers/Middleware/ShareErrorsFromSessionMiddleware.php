<?php

namespace MdSazzadulIslam\LaravelDynamicReportGenerator\Http\Middleware;

use Closure;
use Illuminate\Support\ViewErrorBag;

class ShareErrorsFromSessionMiddleware
{
    public function handle($request, Closure $next)
    {
        if ($request->session()->has('errors')) {
            view()->share('errors', $request->session()->get('errors'));
        }

        return $next($request);
    }
}
