<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('guest')->except('logout');
    // }

    public function login()
    {
        if (auth()->check()){
            // return 'login';
            if (auth()->user()->role == 'kaprodi') {
                return redirect()->route('admin.dashboard.index');
            }else if(auth()->user()->role == 'kjfd'){
                return redirect()->route('kjfd.dashboard.index');
            }
        }else{
            // return 'not login';
            return view('auth.login');
        }
        // return 'neither';
    }

    public function authenticate(Request $request)
    {  
        $inputVal = $request->all();
   
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
   
        if(auth()->attempt(array('email' => $inputVal['email'], 'password' => $inputVal['password']))){
            if (auth()->user()->role == 'kaprodi') {
                return redirect()->route('admin.dashboard.index');
            }else if(auth()->user()->role == 'kjfd'){
                return redirect()->route('kjfd.dashboard.index');
            }
        }else{
            return redirect()->route('login')
                ->with('error','Email & Password salah.');
        }     
    }
    public function logout() {
        auth()->logout();
        return redirect('login');
      }
}
