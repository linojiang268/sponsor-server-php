<?php
namespace Sponsor\Contracts\Repositories;

interface SponsorRepository
{
    /**
     * Find user by his/her mobile number
     *
     * @param string $email  register email
     *
     * @return array|null
     */
    public function findUser($email);

    /**
     * add a new sponsor
     *
     * @param array $sponsor
     * @return int   id of the newly added user
     */
    public function add(array $sponsor);

    /**
     * multiple add new sponsor
     *
     * @param array $sponsors
     * @return int   id of the newly added user
     */
    public function multipleAdd(array $sponsors);

    /**
     * update user's password
     * @return int   affected rows
     */
    public function updatePassword($user, $password, $salt);

    /**
     * find a user by id
     *
     * @param int $id   user id
     * @return array
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
     * @param int $page             the current page number
     * @param int $pageSize         the number of data per page
     */
    public function findAllUsers($page, $pageSize);
}
