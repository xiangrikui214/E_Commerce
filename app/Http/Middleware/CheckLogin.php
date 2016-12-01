<?php

namespace App\Http\Middleware;

use Closure;

class CheckLogin
{
    /**
     * 运行请求过滤器。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $http_referer = $_SERVER['HTTP_REFERER'];//获取上一次访问的地址


        $member = $request->session()->get('member', '');
        if($member == ''){
            return redirect('/login?return_url=' . urlencode($http_referer));
            // return redirect('/login');
        }
        return $next($request);
    }

}