<?php
namespace Sponsor\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sponsor\Entities\User as UserEntity;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, SoftDeletes;
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['salt', 'password', 'remember_token'];

    public function teams()
    {
        return $this->belongsTo(\Sponsor\Models\Team::class, 'id', 'creator_id');
    }

    public function toEntity()
    {
        $userEntity = (new UserEntity())
            ->setId($this->id)
            ->setMobile($this->mobile)
            ->setNickName($this->nick_name)
            ->setRegisterAt($this->created_at)
            ->setHashedPassword($this->password)
            ->setSalt($this->salt)
            ->setStatus($this->status);

        return $userEntity;
    }
}
