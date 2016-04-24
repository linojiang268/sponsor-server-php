<?php
namespace intg\Sponsor\Controllers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use intg\Sponsor\TestCase;
use Sponsor\Models\Sponsor;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    //=========================================
    //             Registration
    //=========================================
    public function testSuccessfulRegistration()
    {
        $this->startSession();
        $this->mockCaptchaService();
        $this->ajaxPost('web/register', [
            'email'    => '123@qq.com',
            'password' => '*******',
            'name'     => '腾讯',
            'captcha'  => '1234',
            '_token'   => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 0]);
    }

    public function testRegistrationWithInvalidEmail()
    {
        $this->startSession();
        $this->mockCaptchaService();
        $this->ajaxPost('web/register', [
            'email'    => '123qqcom',
            'password' => '*******',
            'name'     => '腾讯',
            'captcha'  => '123',
            '_token'   => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 10000]);
        $this->assertContains('"email\u683c\u5f0f\u9519\u8bef"', $this->response->getContent(),
            'invalid email should be detected');
    }

    public function testRegistrationUserExists()
    {
        factory(\Sponsor\Models\Sponsor::class)->create([
            'email' => '123@qq.com',
        ]);
        $this->startSession();
        $this->mockCaptchaService();
        $this->ajaxPost('web/register', [
            'email'    => '123@qq.com',
            'password' => '*******',
            'name'     => '腾讯',
            'captcha'  => '123',
            '_token'   => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 10000]);
        $response = json_decode($this->response->getContent());
        self::assertEquals('该邮箱已注册', $response->message);
    }

    //=========================================
    //             Login
    //=========================================
    public function testSuccessfulLogin()
    {
        factory(\Sponsor\Models\Sponsor::class)->create([
            'email'    => '123@qq.com',
            'salt'     => 'ptrjb30aOvqWJ4mG',
            'name'     => 'victory',
            'password' => '7907C7ED5F7F4E4872E24CAB8292464F',  // raw password is '*******'
            'remember_token' => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);

        $this->startSession();
        $this->ajaxPost('web/login', [
            'email'    => '123@qq.com',
            'password' => '*******',
            '_token'   => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 0]);
        $cookies = $this->response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertEquals('XSRF-TOKEN', $cookies[0]->getName());
    }

    //=========================================
    //          logout
    //=========================================
    public function testSuccessfullyLogout()
    {
        $user = factory(Sponsor::class)->create([
            'id'             => 1,
            'email'          => '123@qq.com',
            'remember_token' => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);
        $this->startSession();
        $this->actingAs($user)
            ->ajaxGet('web/logout');
        $this->seeJsonContains(['code' => 0]);
        $this->seeInDatabase('sponsors', [
            'email'          => '123@qq.com',
            'remember_token'    => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);

    }

    //=========================================
    //          changePassword
    //=========================================
    public function testChangePassword()
    {
        $user = factory(\Sponsor\Models\Sponsor::class)->create([
            'email'    => '123@qq.com',
            'salt'     => 'ptrjb30aOvqWJ4mG',
            'name'     => 'victory',
            'password' => '7907C7ED5F7F4E4872E24CAB8292464F',  // raw password is '*******'
            'remember_token' => 'FAxm3Uk2awKO1MlRqD7OxKmYUdstEIUNkp4OqjHxzKDBtCgC2ZSw1KEF3jxN',
        ]);
        $this->startSession();
        $this->actingAs($user)
            ->ajaxPost('web/password/change',[
                'original_password' => '*******',
                'new_password'      => '123123',
                '_token'   => csrf_token(),
            ]);
        $this->seeJsonContains(['code' => 0]);
        $this->ajaxPost('web/login', [
            'email'    => '123@qq.com',
            'password' => '123123',
            '_token'   => csrf_token(),
        ]);
        $this->seeJsonContains(['code' => 0]);
        $cookies = $this->response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertEquals('XSRF-TOKEN', $cookies[0]->getName());
    }

    public function testSuccessfullyLogout_SessionAreadyExpired()
    {
        $this->ajaxGet('web/logout')->seeJsonContains(['code' => 0]);
    }

    private function mockCaptchaService()
    {
        $captchaService = \Mockery::mock(\Mews\Captcha\Captcha::class);
        $captchaService->shouldReceive('check')->withAnyArgs()->andReturn(true);
        $this->app['captcha'] = $captchaService;
    }
}
