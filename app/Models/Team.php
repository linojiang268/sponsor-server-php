<?php
namespace Sponsor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sponsor\Entities\Team as TeamEntity;
use Sponsor\Entities\User as UserEntity;

class Team extends Model
{
    use SoftDeletes;
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'teams';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
                            'creator_id',
                            'name',
                            'email',
                            'logo_url',
                            'address',
                            'contact_phone',
                            'contact',
                            'contact_hidden',
                            'introduction',
                            'certification',
                            'qr_code_url',
                            'join_type',
                            'status',
                            'activities_updated_at',
                            'members_updated_at',
                            'news_updated_at',
                            'albums_updated_at',
                            'notices_updated_at',
                            'tags',
                          ];
    
    public function creator()
    {
        return $this->belongsTo(\Sponsor\Models\User::class, 'creator_id', 'id');
    }

    public function toEntity()
    {
        $team = (new TeamEntity())
                ->setId($this->id)
                ->setName($this->name)
                ->setEmail($this->email)
                ->setAddress($this->address)
                ->setContactPhone($this->contact_phone)
                ->setContact($this->contact)
                ->setIntroduction($this->introduction)
                ->setStatus($this->status)
                ->setCreatedAt($this->created_at);

        if ($this->relationLoaded('creator')) {
            $team->setCreator($this->creator->toEntity());
        } else {
            $team->setCreator((new UserEntity)->setId($this->creator_id));
        }

        return $team;
    }
}
