<?php
namespace Sponsor\Contracts\Repositories;

interface UserRepository
{
    /**
     * find user by his/her mobile number
     *
     * @param string $mobile   mobile number
     * @return int|null   user's id or null if user not exist.
     */
    public function findId($mobile);

    /**
     * Find user by his/her mobile number
     *
     * @param string $mobile    mobile number
     *
     * @return \Sponsor\Entities\User|null
     */
    public function findUser($mobile);

    /**
     * find users by mobile numbers
     *
     * @param array $mobile     element is mobile number
     * @return  array           associate array, key is mobile,
     *                          value is user's id or null if user not exist.
     */
    public function findIdsByMobiles(array $mobiles);

    /**
     * add a new user
     *
     * @param array $user
     * @return int   id of the newly added user
     */
    public function add(array $user);

    /**
     * multiple add new user
     *
     * @param array $users
     * @return int   id of the newly added user
     */
    public function multipleAdd(array $users);

    /**
     * update user's password
     * @return int   affected rows
     */
    public function updatePassword($user, $password, $salt);

    /**
     * find a user by id
     *
     * @param int $id   user id
     * @return \Sponsor\Entities\User
     */
    public function findById($id);

    /**
     * update user profile by user id
     *
     * @param int $user       user id
     * @param array $profile
     */
    public function updateProfile($user, array $profile);

    /**
     * find all users by specified conditions
     *
     * @param string $mobile        user mobile number
     * @param string $nickName      user nick name
     * @param int $page             the current page number
     * @param int $pageSize         the number of data per page
     */
    public function findAllUsers($mobile, $nickName, $page, $pageSize);
}
