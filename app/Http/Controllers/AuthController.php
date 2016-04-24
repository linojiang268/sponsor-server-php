<?php
namespace Sponsor\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Sponsor\ApplicationServices\SponsorServices;

class AuthController extends Controller
{
    /**
     * user registration
     */
    public function register(Request $request,
                             SponsorServices $sponsorApplicationServices)
    {
        $this->validate($request, [
            'email'    => 'required|email',
            'password'  => 'required|between:6,32',
            'name' => 'required|between:2,128',
            'captcha' => 'required|captcha',
        ], [
            'email.required'       => 'email未填写',
            'email.email'         => 'email格式错误',
            'password.required'     => '密码未填写',
            'password.between'      => '密码错误',
            'name.required'    => '赞助方名称未填写',
            'name.between'     => '赞助方名称格式错误',
            'captcha.required'  => '验证码未填写',
            'captcha.captcha'   => '验证码错误',
        ]);
        
        try {
            // register the sponsor
            $profile = [
                'name'  => $request->input('name'),
            ];
            $sponsorApplicationServices->register($request->input('email'),
                                   $request->input('password'),
                                   $profile);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
        return $this->json('注册成功');
    }

    /**
     * user login
     */
    public function login(Request $request,
                          SponsorServices $sponsorApplicationServices
    ) {
        $this->validate($request, [
            'email'   => 'required|email',
            'password' => 'required|between:6,32',
        ], [
            'email.required'       => 'email未填写',
            'email.email'         => 'email格式错误',
            'password.required' => '密码未填写',
            'password.between'  => '密码错误',
        ]);

        try {
            $ret = $sponsorApplicationServices->login($request->input('email'),
                $request->input('password'),
                $request->has('remember'));
            if (!$ret) {
                // hide the underlying errors so that malicious routines
                // won't know what the exact error is
                return $this->jsonException('密码错误');
            }else{
                return $this->json('登录成功');
            }
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    /**
     * user logout
     */
    public function logout(Request $request,
        Guard $auth,
        SponsorServices $sponsorApplicationServices
    ) {
        if ($auth->guest()) {
            return $request->ajax() ? $this->json() : redirect('/');
        }
        $sponsorApplicationServices->logout();

        return $request->ajax() ? $this->json() : redirect('/');
    }


    /**
     * user reset password form
     */
    public function resetPasswordForm()
    {
        return $this->json([
            '_token' => csrf_token(),
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request,
                                   Guard $auth,
                                   SponsorServices $sponsorApplicationServices
    ) {
        $this->validate($request, [
            'original_password' => 'required|between:6,32',
            'new_password'      => 'required|between:6,32',
        ], [
            'original_password.required'    => '当前密码未填写',
            'original_password.between'     => '当前密码格式错误',
            'new_password.required'         => '新密码未填写',
            'new_password.between'          => '新密码格式错误',
        ]);

        try {
            $sponsorApplicationServices->changePassword($auth->user()->getAuthIdentifier(),
                                         $request->input('original_password'),
                                         $request->input('new_password'));
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }

        return $this->json('修改密码成功');
    }

    /**
     * user reset password
     */
    public function resetPassword(Request $request,
                                  SponsorServices $sponsorApplicationServices)
    {
        $this->validate($request, [
            'code'     => 'required|string',
            'email'   => 'required|email',
            'password' => 'required|between:6,32',
        ], [
            'code.required'      => '验证串未填写',
            'code.size'          => '验证串格式错误',
            'email.required'    => 'email未填写',
            'email.mobile'      => 'email格式错误',
            'password.required'  => '密码未填写',
            'password.between'   => '密码错误',
        ]);

        try {
            $sponsorApplicationServices->resetPassword($request->input('email'),
                                        $request->input('password'));
        } catch (\Exception $e) {
            return $this->jsonException($e);
        }

        return $this->json();
    }
}
