<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLanguage
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('lang')) {
            $lang = in_array($request->get('lang'), ['en', 'zh_CN'])
                ? $request->get('lang') : 'en';
            Session::put('locale', $lang);
        }
        App::setLocale(Session::get('locale', 'en'));
        return $next($request);
    }
}