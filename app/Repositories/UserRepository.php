<?php
namespace Sponsor\Repositories;

use Sponsor\Contracts\Repositories\UserRepository as UserRepositoryContract;
use Sponsor\Models\User;
use Sponsor\Utils\PaginationUtil;
use DB;

class UserRepository implements UserRepositoryContract
{
    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\UserRepository::findId()
     */
    public function findId($mobile)
    {
        $user = User::where(['mobile' => $mobile], ['id'])->first();
            
        return ($user != null) ? $user->id : null;
    }

    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\UserRepository::findUser()
     */
    public function findUser($mobile)
    {
        return $this->convertToEntity(User::where('mobile', $mobile)->first());
    }

    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\UserRepository::findIdsByMobiles()
     */
    public function findIdsByMobiles(array $mobiles)
    {
        if (empty($mobiles)) {
            return [];
        }
        // initialize mobileUsers, key is email, value is null,
        $mobileUsers = array_combine($mobiles, array_fill(0, count($mobiles), null));
        User::whereIn('mobile', $mobiles)->get()
                                         ->each(function ($user) use (&$mobileUsers) {
                                             $mobileUsers[$user->mobile] = $user->id;
                                         });
        return $mobileUsers;
    }

    /**
     * @see \Sponsor\Contracts\Repositories\UserRepository::add()
     */
    public function add(array $user)
    {
        return User::create($user)->id;
    }

    /**
     * @see \Sponsor\Contracts\Repositories\UserRepository::add()
     */
    public function multipleAdd(array $users)
    {
        return DB::table('users')->insert($users);
    }

    /**
     * @see \Sponsor\Contracts\Repositories\UserRepository::updatePassword()
     */
    public function updatePassword($user, $password, $salt)
    {
        return User::where('id', $user)
                    ->update(['password' => $password,
                              'salt' => $salt]);
    }

    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\UserRepository::findById()
     */
    public function findById($id)
    {
        $userModel = User::find($id);

        return $userModel ? $userModel->toEntity() : null;
    }

    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\UserRepository::updateById()
     */
    public function updateProfile($user, array $profile)
    {
        User::where('id', $user)->update($profile);
    }

    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\UserRepository::findAllUsers()
     */
    public function findAllUsers($mobile, $nickName, $page, $pageSize)
    {
        $query = User::where('id', '>', 0);
        if ($mobile) {
            $query->where('mobile', $mobile);
        }
        if ($nickName) {
            $query->where('nick_name', $nickName);
        }
        $count = $query->count();
        $page = PaginationUtil::sanePage($page, $count, $pageSize);
        $users = $query->forPage($page, $pageSize)->get()->all();
        $users = array_map([ $this, 'convertToEntity' ], $users);

        return [$count, $users];
    }

    /**
     * (non-PHPdoc)
     *
     * @return \Sponsor\Entities\User | null
     */
    private function convertToEntity($user)
    {
        return $user ? $user->toEntity() : null;
    }
}
