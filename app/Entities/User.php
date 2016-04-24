<?php
namespace Sponsor\Entities;

use Crypt;

class User
{
    /**
     * registration is done, but information is incomplete
     *
     * @var int
     */
    const STATUS_INCOMPLETE = 0;
    const STATUS_NORMAL     = 1; // 正常用户
    const STATUS_FORBIDDEN  = 2; // 封号
    
    const TYPE_SELF   = 0;      // 自己注册
    const TYPE_INVITE = 1;      // 别人添加

    const GENDER_UNKNOWN = 0;   // 未知
    const GENDER_MALE   = 1;    // 男
    const GENDER_FEMALE = 2;    // 女

    private static $statusDescMap = [
        self::STATUS_INCOMPLETE => '待完善',
        self::STATUS_NORMAL     => '正常',
        self::STATUS_FORBIDDEN => '已封号',
    ];

    private static $genderDescMap = [
        self::GENDER_UNKNOWN    => '未知',
        self::GENDER_MALE       => '男',
        self::GENDER_FEMALE     => '女',
    ];

    const THUMBNAIL_STYLE_FOR_AVATAR = '@200w_200h_1e_1pr.src';

    const IDENTITY_KEY = 'identity';

    private $id;
    private $mobile;
    private $password;
    private $salt;
    private $nickName;
    private $status;
    private $registerAt;

    /**
     * Get the user code
     *
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user id
     *
     * @return \Sponsor\Entities\User
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * Get user email
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set user email
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * Get user nick name
     */
    public function getNickName()
    {
        return $this->nickName;
    }

    /**
     * Set user nick name
     */
    public function setNickName($nickName)
    {
        $this->nickName = $nickName;
        return $this;
    }

    /**
     * Get user status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set user status
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Judge whether user complete profile
     *
     * @return boolen
     */
    public function isNeedComplete()
    {
        return $this->status == self::STATUS_INCOMPLETE;
    }

    /**
     * Get user status desc
     */
    public function getStatusDesc()
    {
        return array_get(self::$statusDescMap, $this->status);
    }

    /**
     * Get user register time
     *
     * @return \DateTime
     */
    public function getRegisterAt()
    {
        return $this->registerAt;
    }

    /**
     * Set user register time
     *
     * @param \DateTime $registerAt
     *
     * @return \Sponsor\Entities\User
     */
    public function setRegisterAt($registerAt)
    {
        $this->registerAt = $registerAt;
        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getHashedPassword()
    {
        return $this->password;
    }

    /**
     * Set Password
     *
     * @param string $password
     *
     * @return \Sponsor\Entities\User
     */
    public function setHashedPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return \Sponsor\Entities\User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

}
