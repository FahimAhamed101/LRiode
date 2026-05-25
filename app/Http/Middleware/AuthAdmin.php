<?php

namespace App\Http\Middleware;


/* use Auth;
 */
use Closure;
use Illuminate\Http\Request;
/* use Session; */
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class AuthAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::user()->utype === "ADM") {
                return $next($request);
            } else {
                Session::flush();
                return redirect()->route("login");


            }

        } else {
            return redirect()->route("login");
        }
    }
}