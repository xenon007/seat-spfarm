<?php

namespace Xenon007\SeatSpfarm\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpfarmUserSetting
 *
 * Stores per‑user settings for the SP Farming plugin that do not relate to
 * specific characters. Currently this consists solely of the toggle to
 * display non‑farm characters in a secondary table on the dashboard. The
 * boolean cast makes the property easier to work with in Blade views.
 */
class SpfarmUserSetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spfarm_user_settings';

    /**
     * The primary key associated with the table.
     *
     * The table is keyed by user_id (no auto-increment id column).
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'show_idle_table',
    ];

    /**
     * Casts to ensure proper types.
     *
     * @var array
     */
    protected $casts = [
        'show_idle_table' => 'boolean',
    ];
}