<?php

namespace App\Http\Controllers\UserAuth;

use App\Helpers\ValidUserScannerPassword;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Foundation\Auth\ResetsPasswords;


class ResetPasswordController extends Controller
{
    //user redirect path
    protected $redirectTo = '/user/home';

    //trait for handling reset Password
    use ResetsPasswords;

    //Show form to user where they can reset password
    public function showResetForm(Request $request, $token = null)
    {
        return view('user.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', new ValidUserScannerPassword()],
        ];
    }
}
