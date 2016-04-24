<?php
namespace Sponsor\Entities;

class Team
{
    const STATUS_NORMAL = 0;// 正常
    const STATUS_FORBIDDEN = 1;// 已封团
    const STATUS_FREEZE = 2;// 已冻结
    
    const JOIN_TYPE_ANY = 0;// 任何人可以加入
    const JOIN_TYPE_VERIFY = 1;// 需要审核方可加入
    
    const UN_CERTIFICATION = 0;// 未认证
    const CERTIFICATION_PENDING = 1;// 认证审核中
    const CERTIFICATION = 2;// 已认证

    const CONTACT_NOT_HIDDEN = 0;//不隐藏联系方式
    const CONTACT_HIDDEN = 1;//隐藏联系方式

    const THUMBNAIL_STYLE_FOR_LOGO = '@200w_200h_1e_1pr.src';
    
    private $id;
    
    private $name;
    private $email;
    private $address;
    private $contactPhone;
    private $contact;
    private $introduction;
    private $status = self::STATUS_NORMAL;

    private $creator;
    private $createdAt;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function getEmail()
    {
        return $this->email;
    }
    
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function getContactPhone()
    {
        return $this->contactPhone;
    }

    public function setContactPhone($contactPhone)
    {
        $this->contactPhone = $contactPhone;
        return $this;
    }

    public function getContact()
    {
        return $this->contact;
    }

    public function setContact($contact)
    {
        $this->contact = $contact;
        return $this;
    }

    public function getIntroduction()
    {
        return $this->introduction;
    }

    public function setIntroduction($introduction)
    {
        $this->introduction = $introduction;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \Sponsor\Entities\User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator(\Sponsor\Entities\User $creator)
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return Team
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
