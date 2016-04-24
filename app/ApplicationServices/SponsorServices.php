<?php

namespace Sponsor\ApplicationServices;

use Sponsor\Contracts\Repositories\SponsorRepository;
use Sponsor\Hashing\PasswordHasher;
use Auth;
use DB;
use Crypt;

/**
 * User related service
 *
 */
class SponsorServices
{
    const STATUS_DELETE = -1;
    const STATUS_MO_ACTIVATION = 0;
    const STATUS_ACTIVATION = 1;
    /**
     * repository for user
     *
     * @var \Sponsor\Contracts\Repositories\SponsorRepository
     */
    private $sponsorRepository;

    /**
     * hasher to hash user's password. we hardcode our hasher here (
     * since we don't want change framework's hasher), although our
     * hasher complies with \Illuminate\Contracts\Hashing\Hasher.
     *
     * @var \Sponsor\Hashing\PasswordHasher
     */
    private $hasher;


    public function __construct(SponsorRepository $sponsorRepository,
                                PasswordHasher $hasher = null)
    {
        $this->sponsorRepository = $sponsorRepository;
        $this->hasher = $hasher ?: new PasswordHasher();
    }

    /**
     * User registration
     *
     * @param string $email
     * @param string $password length between 6 and 32
     * @param array  $profile
     *
     * @return int                  id for the registered user
     * @throws \Exception  if user already exists
     */
    public function register($email, $password, array $profile = null)
    {
        $userId = null;
        $sponsor = $this->sponsorRepository->findUser($email);
        if ($sponsor) {
            throw new \Exception('该邮箱已注册');
        }
        $salt = $this->generateSalt();
        $password = $this->hashPassword($password, $salt);
        $registerData = [
            'email'    => $email,
            'salt'     => $salt,
            'password' => $password,
            'status'   => self::STATUS_ACTIVATION,
        ];
        if (!empty($profile)) {
            $registerData = array_merge($registerData, $profile);
        }
        $userId = $this->sponsorRepository->add($registerData);

        return $userId;
    }

    /**
     * complete user profile
     *
     * @param integer $userId
     * @param array   $profile detail of a request for user profile, keys as below:
     *                         - name    string
     *                         - intro   txt
     *
     */
    public function completeProfile($userId, array $profile)
    {
        unset($profile['password']);
        unset($profile['email']);
        $this->sponsorRepository->updateProfile($userId, $profile);
    }

    /**
     * Change user password
     *
     * @param integer $userId           user id
     * @param string  $originalPassword user current password
     * @param string  $newPassword      user new password, will be set for user
     *
     * @return boolean
     * @throws \Exception
     */
    public function changePassword($userId, $originalPassword, $newPassword)
    {
        // Check originalPassword
        $user = $this->sponsorRepository->findById($userId);
        if (!$user) {
            throw new \Exception('非法请求');
        }
        if (!$this->checkPassword($originalPassword, $user)) {
            throw new \Exception('当前密码不正确');
        }
        $salt = $this->generateSalt();
        $password = $this->hashPassword($newPassword, $salt);
        $success = (1 == $this->sponsorRepository->updatePassword($user->id, $password, $salt));

        return $success;
    }

    /**
     * check whether passed in password equeal hashedPassword
     *
     * @param string $password passed in password should be check
     * @param array  $user
     *
     * @return boolean
     */
    private function checkPassword($password, $user)
    {
        $ret = $this->hashPassword($password, $user->salt);
        return $user->password == $ret;
    }


    /**
     * user login
     *
     * @param string $email       user's mobile
     * @param string $password    plain password
     * @param bool   $remember    true to remember the user once successfully logged in.
     *                            false otherwise.
     *
     * @return bool  true if login successfully, false otherwise.
     */
    public function login($email, $password, $remember = false)
    {
        return Auth::attempt([
            'email'    => $email,
            'password' => $password,
        ], $remember);
    }

    /**
     * logout user
     */
    public function logout()
    {
        // Get rememberme token
        $user = Auth::user();
        $rememberToken = $user->getRememberToken();

        Auth::logout();

        // save remember token back to user, make sure remember token
        // not be changed. Cause we want a user not be kicked when
        // the same user logout in other side.
        // eg: a user, whoes mobile is 13800138000,
        // logined on a android device, the same user also logined on
        // pc browser, when user logout from pc, the user still logined
        // on android device.
        Auth::getProvider()->updateRememberToken(
            $user, $rememberToken);
    }

    /**
     * User reset password
     *
     * @param string $mobile   user's mobile
     * @param string $password plain password
     * @param string $salt     salt value
     *
     * @throws \Exception   if user not exist
     * @return bool                     true if password is reset. false otherwise
     */
    public function resetPassword($email, $password, $salt = null)
    {
        if (null == ($user = $this->sponsorRepository->findUser($email))) {
            throw new \Exception();
        }
        $salt = $salt ?: $this->generateSalt(); //  generate salt if needed
        $password = $this->hashPassword($password, $salt);

        return 1 == $this->sponsorRepository->updatePassword($user->id, $password, $salt);
    }

    /**
     * list all users
     *
     * @param int $page
     * @param int $pageSize
     *
     * @return array                first element is total count of users
     *                              second element is user array, which element
     *                              is \Jihe\Entities\User object
     */
    public function listUsers($page, $pageSize)
    {
        $sponsors = $this->sponsorRepository->findAllUsers($page, $pageSize);

        return array_map([$this, 'makeUserData'], $sponsors);

    }

    private function makeUserData($sponsor)
    {
        return [
            'user_id' => $sponsor->id,
            'email'   => $sponsor->email,
            'name'    => $sponsor->name,
            'intro'   => $sponsor->intro,
        ];

    }

    /**
     *
     * hash user's raw password
     *
     * @param string $password plain text form of user's password
     * @param string $salt     salt
     *
     * @return string             hashed password
     */
    private function hashPassword($password, $salt)
    {
        $ret = $this->hasher->make($password, ['salt' => $salt]);
        return $ret;
    }

    /**
     * generate salt for hashing password
     *
     * @return string
     */
    private function generateSalt()
    {
        return str_random(16);
    }

}
