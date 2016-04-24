<?php
namespace Sponsor\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Sponsor\Services\UserService;
use Sponsor\Services\TeamService;
use Auth;

class UserController extends Controller
{
    /**
     * show user (user himself/herself or other's) profile
     */
    public function showProfile(Request $request, Guard $auth,
                                UserService $userService,
                                TeamService $teamService
    ) {

        $user = $auth->user()->getAuthIdentifier();
        $profile = $userService->findById($user);
        if (!$profile) { // no profile found
            return $this->json();
        }
        $teams = $teamService->getTeamsByCreator($profile->getId());

        return $this->json([
            'user_id'    => $profile->getId(),
            'mobile'     => $profile->getMobile(),
            'nick_name'  => $profile->getNickName(),
            'is_team_owner' => ! empty($teams),
        ]);
    }

    /**
     * user gets his/her profile updated
     */
    public function updateProfile(Request $request, Guard $auth,
                                  UserService $userService)
    {
        $profile = $this->validateRequestAndLoadProfile($request);
        try {
            $userService->updateProfile($auth->user()->getAuthIdentifier(), $profile);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }

        return $this->json('更新成功');
    }

    /**
     * @param Request $request
     * @return array
     */
    private function validateRequestAndLoadProfile(Request $request)
    {
        $this->validate($request, [
            'nick_name' => 'required|between:1,16',
        ], [
            'nick_name.required'   => '昵称未填写',
            'nick_name.between'    => '昵称格式错误',
        ]);

        // collect profile from request
        $profile = [
            'nick_name' => $request->input('nick_name'),
        ];

        return $profile;
    }

    /**
     * user registration
     */
    public function register(Request $request, UserService $userService)
    {
        $this->validate($request, [
            'captcha' => 'required|captcha',
            'mobile'    => 'required|mobile',
            'password'  => 'required|between:6,32',
            'nick_name' => 'required|between:1,16',
        ], [
            'mobile.required'       => '手机号未填写',
            'mobile.mobile'         => '手机号格式错误',
            'password.required'     => '密码未填写',
            'password.between'      => '密码错误',
            'nick_name.required'    => '昵称未填写',
            'nick_name.between'     => '昵称格式错误',
        ]);

        try {
            // register the user
            $profile = [
                'nick_name'  => $request->input('nick_name'),
            ];
            $userService->register($request->input('mobile'),
                $request->input('password'),
                $profile);

            return $this->json('注册成功');
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    /**
     * user login
     */
    public function login(Request $request, UserService $userService)
    {
        $this->validate($request, [
            'mobile'   => 'required|mobile',
            'password' => 'required|between:6,32',
        ], [
            'mobile.required'   => '手机号未填写',
            'mobile.mobile'     => '手机号格式错误',
            'password.required' => '密码未填写',
            'password.between'  => '密码错误',
        ]);

        try {
            if (!$userService->login(
                $request->input('mobile'),
                $request->input('password'),
                $request->has('remember'))) {
                return $this->jsonException('密码错误');
            }

            return $this->json('登录成功');
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    /**
     * user logout
     */
    public function logout(UserService $userService)
    {
        $userService->logout();

        return $this->json();

    }

    /**
     * user change password
     */
    public function changePassword(Request $request, UserService $userService)
    {
        $this->validate($request, [
            'password'     => 'required|between:6,32',
            'old_password' => 'required|between:6,32',
        ], [
            'password.required'     => '密码未填写',
            'password.between'      => '密码错误',
            'old_password.required' => '旧密码未填写',
            'old_password.between'  => '旧密码错误',
        ]);

        try {
            $userService->changePassword(
                Auth::user()->id,
                $request->input('old_password'),
                $request->input('password'));

            return $this->json('修改密码成功');
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

}
