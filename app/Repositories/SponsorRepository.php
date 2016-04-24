<?php
namespace Sponsor\Repositories;

use Sponsor\Contracts\Repositories\SponsorRepository as SponsorRepositoryContract;
use Sponsor\Models\Sponsor;
use Sponsor\Utils\PaginationUtil;
use DB;

class SponsorRepository implements SponsorRepositoryContract
{
    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\UserRepository::findUser()
     */
    public function findUser($email)
    {
        $sponsor = Sponsor::where('email', $email)->first();
        return $sponsor;
    }

    /**
     * @see \Sponsor\Contracts\Repositories\UserRepository::add()
     */
    public function add(array $user)
    {
        return Sponsor::create($user)->id;
    }

    /**
     * @see \Sponsor\Contracts\Repositories\UserRepository::add()
     */
    public function multipleAdd(array $users)
    {
        return DB::table('sponsors')->insert($users);
    }

    /**
     * @see \Sponsor\Contracts\Repositories\UserRepository::updatePassword()
     */
    public function updatePassword($user, $password, $salt)
    {
        return Sponsor::where('id', $user)
                    ->update(['password' => $password,
                              'salt' => $salt]);
    }

    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\UserRepository::findById()
     */
    public function findById($id)
    {
        $userModel = Sponsor::find($id);

        return $userModel ? $userModel : null;
    }

    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\UserRepository::updateById()
     */
    public function updateProfile($user, array $profile)
    {
        return Sponsor::where('id', $user)->update($profile);
    }

    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\UserRepository::findAllUsers()
     */
    public function findAllUsers($page, $pageSize)
    {
        $count = Sponsor::count();
        $page = PaginationUtil::sanePage($count, $page, $pageSize);
        $users = Sponsor::forPage($page, $pageSize)->get()->all();

        return [$count, $users];
    }

}
