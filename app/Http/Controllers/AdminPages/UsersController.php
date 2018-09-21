<?php

namespace App\Http\Controllers\AdminPages;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Country;
use App\User;

class UsersController extends Controller
{
    //redirect path
    protected $redirectPath = 'admin/users';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
   
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = user::select('users.first_name','users.last_name','users.username','users.phone_number','users.status','users.gender','users.email','users.year_of_birth','users.app_version_code','countries.name')
                ->join('countries', 'countries.id', '=', 'users.country_id')
                ->get();
        return view('admin.pages.users')->with('users',$users); 
    }

    public function showAddForm()
    {
        
        return view('admin.pages.add_venue'); 
    }
}
