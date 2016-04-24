<?php
namespace Sponsor\Models;

use Illuminate\Database\Eloquent\Model;

class SponsorshipApplication extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sponsorship_applications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sponsorship_id',
        'team_id',
        'team_name',
        'mobile',
        'contact_user',
        'application_reason',
        'memo',
        'status'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sponsorship()
    {
        return $this->belongsTo(\Sponsor\Models\Sponsorship::class, 'sponsorship_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(\Sponsor\Models\User::class, 'team_id', 'id');
    }
}
