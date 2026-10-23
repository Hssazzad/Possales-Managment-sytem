<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard'; // ✅ typo fix: dshboard → dashboard

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Show flash message after successful login.
     */
    protected function authenticated(Request $request, $user)
    {
        return redirect($this->redirectTo)
            ->with('success', 'Logged In Successfully!');
    }

    /**
     * Show flash message after logout.
     */
    protected function loggedOut(Request $request)
    {
        return redirect('/login')
            ->with('success', 'Logged Out Successfully!');
    }
}
