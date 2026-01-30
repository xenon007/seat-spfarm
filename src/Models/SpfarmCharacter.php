<?php

namespace Xenon007\SeatSpfarm\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpfarmCharacter
 *
 * Represents per-character settings for the SP Farming plugin. Each row
 * corresponds to a combination of a user and one of their characters. The
 * boolean flags indicate whether the character participates in the farm and
 * whether PI is enabled. A text field allows an optional free‑form plan
 * description. Timestamps track when settings were last modified.
 */
class SpfarmCharacter extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spfarm_characters';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'character_id',
        'is_farm',
        'pi_enabled',
        'plan_text',
    ];

    /**
     * Casts to ensure proper types.
     *
     * @var array
     */
    protected $casts = [
        'is_farm'    => 'boolean',
        'pi_enabled' => 'boolean',
    ];
}