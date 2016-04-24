<?php
namespace Sponsor\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsorship extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_CLOSED = 2;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sponsorships';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'sponsor_id', 'intro',
                           'application_start_date', 'application_end_date', 'application_condition',
                           'status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sponsor()
    {
        return $this->belongsTo(\Sponsor\Models\Sponsor::class, 'sponsor_id', 'id');
    }
}
