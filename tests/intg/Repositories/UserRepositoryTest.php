<?php
namespace intg\Sponsor\Repositories;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use intg\Sponsor\TestCase;
use Sponsor\Models\User;

class UserRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    //=========================================
    //            findId
    //=========================================
    public function testFindIdFound()
    {
        factory(\Sponsor\Models\User::class)->create([
            'id' => 1,
            'mobile' => '13800138000',
        ]);
        
        self::assertEquals(1, $this->getRepository()->findId('13800138000'));
    }

    public function testFindIdNotFound()
    {
        self::assertNull($this->getRepository()->findId('13800138000'));
    }

    //=========================================
    //            findUser
    //=========================================
    public function testFindUserFound()
    {
        factory(\Sponsor\Models\User::class)->create([
            'id' => 1,
            'mobile' => '13800138000',
        ]);
        
        self::assertEquals(1, $this->getRepository()->findUser('13800138000')->getId());
        self::assertEquals('13800138000', $this->getRepository()
                                               ->findUser('13800138000')->getMobile());
    }

    public function testFindUserNotFound()
    {
        self::assertNull($this->getRepository()->findUser('13800138000'));
    }

    //=========================================
    //            add
    //=========================================
    public function testAddUserNotExists()
    {
        $user = factory(User::class)->make(['mobile' => '13800138000'])->toArray();
        // salt & password are hidden in model, so toArray() cann't fetch these 
        // attributes, we should set them
        $user['salt'] = str_random(16);
        $user['password'] = str_random(32);
        self::assertGreaterThanOrEqual(1, $this->getRepository()->add($user));
    }

    public function testAddUserExists()
    {
        $user = factory(User::class)->create(['mobile' => '13800138000'])->toArray();
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
        $user = factory(User::class)->create(['mobile' => '13800138000']);
        self::assertEquals(1, $this->getRepository()->updatePassword($user->id, str_random(32), str_random(16)));
    }

    //============================================
    //          findById
    //============================================
    public function testFindByIdUserExists()
    {
        $user = factory(User::class)->create(['mobile' => '13800138000']);
        self::assertEquals('13800138000', $this->getRepository()->findById($user->id)->getMobile());
    }

    public function testFindByIdUserNotExists()
    {
        self::assertNull($this->getRepository()->findById(1));
    }

    //============================================
    //          updateProfileById
    //============================================
    public function testUpdateProfileById()
    {
        $user = factory(User::class)->create(['mobile' => '13800138000', 'nick_name' => 'lisi']);

        $this->getRepository()->updateProfile($user->id, [
            'nick_name' => 'wangwu',
        ]);

        $updatedUser = $this->getRepository()->findById($user->id);
        self::assertEquals('wangwu', $updatedUser->getNickName());
    }

    //============================================
    //          findAllUsers
    //============================================
    public function testFindAllUsersFound()
    {
        $userOne = factory(User::class)->create([
            'id'            => 1,
            'mobile'        => '13800138000',
        ]);
        $userTwo = factory(User::class)->create([
            'id'            => 2,
            'mobile'        => '13800138001',
        ]);
        $this->getRepository()->updateProfile($userOne->id, [
            'nick_name' => 'wangwu',
        ]);
        $this->getRepository()->updateProfile($userTwo->id, [
            'nick_name' => 'zhangsan',
        ]);

        list($total, $users) = $this->getRepository()->findAllUsers(null, null, 1, 10);

        self::assertEquals(2, $total);
        self::assertCount(2, $users);
        self::assertEquals(1, $users[0]->getId());
    }

    public function testFindAllUsersFound_UseMobileCondition()
    {
        $this->prepareUserData();

        list($total, $users) = $this->getRepository()->findAllUsers(
                                                    '13800138001', null, 1, 10);
        self::assertEquals(1, $total);
        self::assertCount(1, $users);
        self::assertEquals('13800138001', $users[0]->getMobile());
    }

    //============================================
    //          findAllUsers
    //============================================
    public function testFindIdsByMobilesFound()
    {
        $this->prepareUserData();

        $mobileUsers = $this->getRepository()->findIdsByMobiles([
            '13800138000', '13800138001', '13500135000'
        ]);

        self::assertCount(3, $mobileUsers);
        self::assertEquals(1, array_get($mobileUsers, '13800138000'));
        self::assertEquals(2, array_get($mobileUsers, '13800138001'));
        self::assertEquals(null, array_get($mobileUsers, '13500135000'));
    }
    //============================================
    //          multipleAdd
    //============================================
    public function testMultipleAdd()
    {
        $users = [];
        $mobiles = [];
        for($i=0;$i<20;$i++){
            $mobile = strval(13800138000 + $i);
            $user = factory(User::class)->make(['mobile' => $mobile])->toArray();
            $user['salt'] = str_random(16);
            $user['password'] = str_random(32);
            $users[] = $user;
            $mobiles[] = $mobile;
        }
        self::assertGreaterThanOrEqual(true, $this->getRepository()->multipleAdd($users));
        $mobileUsers = $this->getRepository()->findIdsByMobiles($mobiles);
        self::assertCount(20, $mobileUsers);
        self::assertEquals(false, array_search(null, $mobileUsers));
    }

    /**
     * @return \Sponsor\Contracts\Repositories\UserRepository
     */
    private function getRepository()
    {
        return $this->app[\Sponsor\Contracts\Repositories\UserRepository::class];
    }

    private function prepareUserData()
    {
        factory(User::class)->create([
            'id'            => 1,
            'mobile'        => '13800138000',
        ]);

        $this->getRepository()->updateProfile(1, [
            'nick_name' => 'wangwu',
        ]);

        factory(User::class)->create([
            'id'            => 2,
            'mobile'        => '13800138001',
        ]);

        $this->getRepository()->updateProfile(2, [
            'nick_name' => 'zhangsan',
        ]);

    }
}
