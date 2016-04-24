<?php
namespace Sponsor\Services;

use Illuminate\Contracts\Hashing\Hasher;
use Sponsor\Contracts\Repositories\UserRepository;
use Sponsor\Exceptions\User\UserExistsException;
use Sponsor\Exceptions\User\UserNotExistsException;
use Sponsor\Entities\User as UserEntity;
use Sponsor\Hashing\PasswordHasher;
use Auth;
use DB;
use Sponsor\Utils\StringUtil;
use Crypt;

/**
 * User related service
 *
 */
class UserService
{
    /**
     * repository for user
     *
     * @var \Sponsor\Contracts\Repositories\UserRepository
     */
    private $userRepository;

    /**
     * hasher to hash user's password. we hardcode our hasher here (
     * since we don't want change framework's hasher), although our
     * hasher complies with \Illuminate\Contracts\Hashing\Hasher.
     *
     * @var \Sponsor\Hashing\PasswordHasher
     */
    private $hasher;

    public function __construct(UserRepository $userRepository,
                                Hasher $hasher = null)
    {
        $this->userRepository = $userRepository;
        $this->hasher = $hasher ?: new PasswordHasher();
    }

    /**
     * user registration
     *
     * @param string $mobile
     * @param string $password length between 6 and 32
     * @return int                  id for the registered user
     * @throws UserExistsException  if user already exists
     */
    private function registerWithoutProfile($mobile, $password)
    {
        // user's mobile is supposed to be unique
        if (!$this->isMobileUnique($mobile)) {
            throw new UserExistsException($mobile, '该用户已注册');
        }

        // encrypt user's password, to keep back-compatibility, 
        // user's password will be encrypted with a randomly generated salt
        $salt = $this->generateSalt();
        $password = $this->hashPassword($password, $salt);

        $uid = $this->userRepository->add([
            'mobile'    => $mobile,
            'salt'      => $salt,
            'password'  => $password,
            'status'    => UserEntity::STATUS_INCOMPLETE
        ]);

        return $uid;
    }

    /**
     * multiple register
     *
     * @param array $mobiles
     *
     * @return bool
     */
    public function multipleRegisterWithoutProfile($mobiles)
    {
        $addData = [];
        if(!empty($mobiles)){
            foreach($mobiles as $mobile){
                $salt = $this->generateSalt();
                $password = md5($mobile.time());
                $password = $this->hashPassword($password, $salt);
                $addData[] = [
                    'mobile'    => $mobile,
                    'salt'      => $salt,
                    'password'  => $password,
                    'status'    => UserEntity::STATUS_INCOMPLETE
                ];
            }
        }

        if(!empty($addData)){
           return  $this->userRepository->multipleAdd($addData);
        }else{
            return true;
        }
    }

    /**
     * fetch User id by mobile, if user not exists, then register with mobile
     *
     * @param string $mobile
     *
     * @return array            [0] is user id
     *                          [1] password if user not exists, or null
     */
    public function fetchUserOrRegisterIfUserNotExists($mobile)
    {
        $user = $this->userRepository->findId($mobile);
        if (!$user) {
            $password = StringUtil::quickRandom(6);
            $user = $this->registerWithoutProfile($mobile, $password);

            return [$user, $password];
        }

        return [$user, null];
    }

    /**
     * fetch user ids by mobiles for message push
     *
     * @params array $mobiles   element is user mobile
     *
     * @return array            associate array, key is user mobile,
     *                          value is user id if user exists, or null, example as below:
     *                          [
     *                              '13800138000'   => 1,
     *                              '13800138001'   => 2,
     *                          ]
     */
    public function fetchUsersForMessagePush(array $mobiles)
    {
        return $this->userRepository->findIdsByMobiles($mobiles);
    }

    /**
     * User registration with profile
     *
     * @param string $email
     * @param string $password length between 6 and 32
     * @param array $profile detail of a request for user profile, keys as below:
     *                              - avatar_url string   resource identifier
     *                              - nick_name  string
     *                              - gender     int
     *                              - birthday   string
     *                              - tagIds     array     element is user tag id
     *
     * @return int                  id for the registered user
     * @throws UserExistsException  if user already exists
     */
    public function register($mobile, $password, array $profile = null)
    {
        $userId = null;

        // not need to create user, update profile and status directly if user is exists
        $user = $this->userRepository->findUser($mobile);
        if ($user) {
            if (!$user->isNeedComplete()) {
                throw new UserExistsException($mobile, '该用户已注册');
            }

            $profile = $profile ?: [];
            array_set($profile, 'password', $password);

            $this->completeProfile($user->getId(), $profile);
            return $user->getId();
        }

        // Wrap logic in a transaction
        DB::transaction(function () use ($mobile, $password, $profile, &$userId) {
            $userId = $this->registerWithoutProfile($mobile, $password);

            if (!empty($profile)) {
                // don't get password updated as its splendid fresh
                array_forget($profile, ['password']);
                $this->completeProfile($userId, $profile);
            }
        });

        return $userId;
    }

    /**
     * complete user profile
     *
     * @param integer $user
     * @param array $profile detail of a request for user profile, keys as below:
     *                         - nick_name    string
     *                         - gender       int
     *                         - birthDay     string
     *                         - tags         array     element is user tag id
     *                         - avatar       (optional) array
     *                                        - path    file path to new avatar file
     *                                        - ext     (optional)file extension
     */
    public function completeProfile($user, array $profile)
    {
        $profile['status'] = UserEntity::STATUS_NORMAL;
        $this->updateProfile($user, $profile);
    }


    /**
     * update user profile
     *
     * @param integer $user
     * @param array $profile detail of a request for user profile, keys as below:
     *                         - password     string
     *                         - nick_name    string
     *                         - gender       int
     *                         - birthday     string
     *                         - tags         array     element is user tag id
     *                         - avatar       (optional) array
     *                                        - path    file path to new avatar file
     *                                        - ext     (optional)file extension
     */
    public function updateProfile($user, array $profile)
    {
        // handle password update - regenerate salt and hash the password
        if (!empty($profile['password'])) {
            $profile['salt'] = $this->generateSalt();
            $profile['password'] = $this->hashPassword($profile['password'], $profile['salt']);
        } else { // no password should be updated
            unset($profile['password']);
        }

        // update user
        $this->userRepository->updateProfile($user, $profile);
    }

    /**
     * Change user password
     *
     * @param integer $user user id
     * @param string $originalPassword user current password
     * @param string $newPassword user new password, will be set for user
     *
     * @return boolean
     */
    public function changePassword($user, $originalPassword, $newPassword)
    {
        // Check originalPassword
        $user = $this->userRepository->findById($user);
        if (!$user) {
            throw new UserNotExistsException('');
        }
        if (!$this->checkPassword($originalPassword, $user)) {
            throw new \Exception('当前密码不正确');
        }

        $salt = $this->generateSalt();
        $password = $this->hashPassword($newPassword, $salt);
        $success = (1 == $this->userRepository->updatePassword($user->getId(), $password, $salt));

        return $success;
    }

    /**
     * check whether passed in password equeal hashedPassword
     *
     * @param string $password passed in password should be check
     * @param \Sponsor\Entities\User $user
     *
     * @return boolean
     */
    private function checkPassword($password, UserEntity $user)
    {
        return $user->getHashedPassword() == $this->hashPassword(
            $password, $user->getSalt());
    }

    /**
     * user login
     * @param string $mobile user's mobile
     * @param string $password plain password
     * @param bool $remember true to remember the user once successfully logged in.
     *                            false otherwise.
     *
     * @return bool  true if login successfully, false otherwise.
     */
    public function login($mobile, $password, $remember = false)
    {
        return Auth::attempt([
            'mobile'   => $mobile,
            'password' => $password
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
     * @param string $mobile user's mobile
     * @param string $password plain password
     * @param string $salt salt value
     *
     * @throws UserNotExistsException   if user not exist
     * @return bool                     true if password is reset. false otherwise
     */
    public function resetPassword($mobile, $password, $salt = null)
    {
        if (null == ($id = $this->userRepository->findId($mobile))) {
            throw new UserNotExistsException($mobile);
        }

        $salt = $salt ?: $this->generateSalt(); //  generate salt if needed
        $password = $this->hashPassword($password, $salt);
        return 1 == $this->userRepository->updatePassword($id, $password, $salt);
    }

    /**
     * list all users
     *
     * @param int $page
     * @param int $pageSize
     * @param string $mobile user mobile number
     * @param string $nickName user nick name
     *
     * @return array                first element is total count of users
     *                              second element is user array, which element
     *                              is \Jihe\Entities\User object
     */
    public function listUsers($page, $pageSize, $mobile = null, $nickName = null)
    {
        return $this->userRepository->findAllUsers($mobile, $nickName, $page, $pageSize);
    }

    /**
     *
     * hash user's raw password
     * @param string $password plain text form of user's password
     * @param string $salt salt
     * @return string             hashed password
     */
    private function hashPassword($password, $salt)
    {
        return $this->hasher->make($password, ['salt' => $salt]);
    }

    /**
     * generate salt for hashing password
     * @return string
     */
    private function generateSalt()
    {
        return str_random(16);
    }

    // check whether given mobile number is unique in the system or not
    // @return bool   true if unique. false otherwise.
    private function isMobileUnique($mobile)
    {
        return null == $this->userRepository->findId($mobile);
    }

    public function findUserById($userId)
    {
        return $this->userRepository->findById($userId);
    }

    /**
     * Find user by mobile
     *
     * @param string $mobile        user mobile#
     *
     * @return \Sponsor\Entities\User|null
     */
    public function findUserByMobile($mobile)
    {
        return $this->userRepository->findUser($mobile);
    }

    public function findById($id)
    {
        return $this->userRepository->findById($id);
    }
}
