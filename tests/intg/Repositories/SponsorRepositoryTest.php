<?php
namespace intg\Sponsor\Repositories;

use intg\Sponsor\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Sponsor\Models\Sponsor;

class SponsorRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    //=========================================
    //            findUser
    //=========================================
    public function testFindUserFound()
    {
        factory(Sponsor::class)->create([
            'id'    => 1,
            'email' => '123@qq.com',
        ]);
        $sponsor = $this->getRepository()->findUser('123@qq.com');
        self::assertEquals(1, $sponsor->id);
        self::assertEquals('123@qq.com', $sponsor->email);
    }

    public function testFindUserNotFound()
    {
        self::assertNull($this->getRepository()->findUser('123@qq.com'));
    }

    //=========================================
    //            add
    //=========================================
    public function testAddUserNotExists()
    {
        $user = factory(Sponsor::class)->make(['email' => '123@qq.com'])->toArray();
        $user['salt'] = str_random(16);
        $user['password'] = str_random(32);
        self::assertGreaterThanOrEqual(1, $this->getRepository()->add($user));
    }

    public function testAddUserExists()
    {
        $user = factory(Sponsor::class)->create(['email' => '123@qq.com'])->toArray();
        $user['salt'] = str_random(16);
        $user['password'] = str_random(32);
        try {
            $this->getRepository()->add($user);
        } catch (\Exception $e) {
            self::assertContains('1062 Duplicate entry', $e->getMessage());
        }
    }

    //============================================
    //          updatePassword
    //============================================
    public function testUpdatePassword()
    {
        $user = factory(Sponsor::class)->create(['email' => '123@qq.com']);
        self::assertEquals(1, $this->getRepository()->updatePassword($user->id, str_random(32), str_random(16)));
    }

    //============================================
    //          findById
    //============================================
    public function testFindByIdUserExists()
    {
        $user = factory(Sponsor::class)->create(['email' => '123@qq.com']);
        self::assertEquals('123@qq.com', $this->getRepository()->findById($user->id)->email);
    }

    public function testFindByIdUserNotExists()
    {
        self::assertNull($this->getRepository()->findById(1));
    }

    //============================================
    //          updateProfile
    //============================================
    public function testUpdateProfile()
    {
        $user = factory(Sponsor::class)->create(['email' => '123@qq.com',
                                                 'name'  => '腾讯']);
        $this->getRepository()->updateProfile($user->id, [
            'name'  => '阿里',
            'intro' => 'aliPay',
        ]);
        $updatedUser = $this->getRepository()->findById($user->id);
        self::assertEquals('阿里', $updatedUser->name);
    }

    //============================================
    //          findAllUsers
    //============================================
    public function testFindAllUsersFound()
    {
        $userOne = factory(Sponsor::class)->create([
            'id'            => 1,
            'email'        => '123@qq.com',
            'name'    => 'old_avatar_1'
        ]);
        $userTwo = factory(Sponsor::class)->create([
            'id'            => 2,
            'email'        => '234@qq.com',
            'name'    => 'old_avatar_2'
        ]);

        $this->getRepository()->updateProfile($userOne->id, [
            'name' => 'qq',
        ]);
        $this->getRepository()->updateProfile($userTwo->id, [
            'name' => 'ali',
        ]);

        list($total, $users) = $this->getRepository()->findAllUsers(1, 10);

        self::assertEquals(2, $total);
        self::assertCount(2, $users);
        self::assertEquals(1, $users[0]->id);
        self::assertEquals('qq', $users[0]->name);
    }

    //============================================
    //          multipleAdd
    //============================================
    public function testMultipleAdd()
    {
        $users = [];
        $emails = [];
        for($i=0;$i<20;$i++){
            $email = strval(123 + $i).'@qq.com';
            $user = factory(Sponsor::class)->make(['email' => $email])->toArray();
            $user['salt'] = str_random(16);
            $user['password'] = str_random(32);
            $users[] = $user;
            $emails[] = $email;
        }
        self::assertGreaterThanOrEqual(true, $this->getRepository()->multipleAdd($users));
    }

    /**
     * @return \Sponsor\Contracts\Repositories\SponsorRepository
     */
    private function getRepository()
    {
        return $this->app[\Sponsor\Contracts\Repositories\SponsorRepository::class];
    }
}
