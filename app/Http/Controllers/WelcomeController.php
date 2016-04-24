<?php
namespace Sponsor\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;

class WelcomeController extends Controller
{
    // the homepage
    public function index(Guard $auth)
    {
        if ($auth->user() != null) { // if user already logged in
            return redirect('/sponsorships');
        }
        return view('welcome.index');
    }
}
