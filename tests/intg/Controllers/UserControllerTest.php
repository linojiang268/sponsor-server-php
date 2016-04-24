<?php
namespace intg\Sponsor\Controllers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use intg\Sponsor\TestCase;
use intg\Sponsor\RequestSignCheck;
use \PHPUnit_Framework_Assert as Assert;
use Sponsor\Models\User;


class UserControllerTest extends TestCase
{
    use DatabaseTransactions;
    use RequestSignCheck;

    //=============================================
    //          show profile
    //=============================================
    public function testShowProfileSuccessfully()
    {
        $user = factory(\Sponsor\Models\User::class)->create([
            'mobile'    => '13800138000',
            'nick_name' => 'wangwu',
            ]);
        factory(\Sponsor\Models\Team::class)->create([
            'creator_id'    => $user->id,
        ]);

        $this->actingAs($user, 'extended-eloquent-team')
             ->get('/api/user/profile');
        $this->seeJsonContains([
            'code' => 0,
            'nick_name' => 'wangwu',
            'is_team_owner' => true,
        ]);
    }

    //================================================
    //          update profile
    //================================================
    public function testUpdateProfileSuccessfully()
    {
        $user = factory(\Sponsor\Models\User::class)->create([
            'mobile'    => '13800138000',
            'nick_name' => 'wangwu',
            ]);
        $this->actingAs($user, 'extended-eloquent-team')
            ->call('POST', 'api/user/profile/update', [
            'nick_name'     => 'john',
        ]);
        $this->seeJsonContains(['code' => 0]);
    }


    //=========================================
    //             Registration
    //=========================================
    public function testSuccessfulRegistration()
    {
        $this->startSession();
        $this->mockCaptchaService();
        $this->post('api/user/register', [
            'mobile'    => '13800138000',
            'password' => '*******',
            'nick_name'     => '腾讯',
            'captcha'  => '1234',
        ]);
        $this->seeJsonContains(['code' => 0]);
    }

    public function testRegistrationUserExists()
    {
        factory(User::class)->create([
            'mobile' => '13800138000',
        ]);
        $this->startSession();
        $this->mockCaptchaService();
        $this->post('api/user/register', [
            'mobile'    => '13800138000',
            'password' => '*******',
            'nick_name'     => '腾讯',
            'captcha'  => '123',
        ]);
        $this->seeJsonContains(['code' => 1]);
        $response = json_decode($this->response->getContent());
        self::assertEquals('该用户已注册', $response->message);
    }

    //=========================================
    //             Login
    //=========================================
    public function testSuccessfulLogin()
    {
        $user = factory(User::class)->create([
            'mobile'    => '13800138000',
            'salt'     => 'ptrjb30aOvqWJ4mG',
            'nick_name'     => 'victory',
            'password' => '7907C7ED5F7F4E4872E24CAB8292464F',  // raw password is '*******'
            'remember_token' => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);
        $this->startSession();
        $this->actingAs($user, 'extended-eloquent-team')
            ->post('api/user/login', [
            'mobile'    => '13800138000',
            'password' => '*******',
        ]);
        $this->seeJsonContains(['code' => 0]);
    }

    //=========================================
    //          logout
    //=========================================
    public function testSuccessfullyLogout()
    {
        $user = factory(User::class)->create([
            'id'             => 1,
            'mobile'          => '13800138000',
            'remember_token' => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);
        $this->startSession();
        $this->actingAs($user, 'extended-eloquent-team')
            ->get('api/user/logout');
        $this->seeJsonContains(['code' => 0]);
        $this->seeInDatabase('users', [
            'mobile'          => '13800138000',
            'remember_token'    => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);

    }

    //=========================================
    //          changePassword
    //=========================================
    public function testChangePassword()
    {
        $user = factory(User::class)->create([
            'mobile'          => '13800138000',
            'salt'     => 'ptrjb30aOvqWJ4mG',
            'nick_name'     => 'victory',
            'password' => '7907C7ED5F7F4E4872E24CAB8292464F',  // raw password is '*******'
            'remember_token' => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);
        $this->startSession();
        $this->actingAs($user, 'extended-eloquent-team')
             ->post('api/user/password/change',[
                'old_password' => '*******',
                'password'      => '123123',
            ]);
        $this->seeJsonContains(['code' => 0]);
        $this->actingAs($user, 'extended-eloquent-team')
             ->post('api/user/login', [
            'mobile'          => '13800138000',
            'password' => '123123',
        ]);
        $this->seeJsonContains(['code' => 0]);
    }

    private function mockCaptchaService()
    {
        $captchaService = \Mockery::mock(\Mews\Captcha\Captcha::class);
        $captchaService->shouldReceive('check')->withAnyArgs()->andReturn(true);
        $this->app['captcha'] = $captchaService;
    }

}
