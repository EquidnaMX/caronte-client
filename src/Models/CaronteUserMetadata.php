<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.2
 */

namespace Equidna\Caronte\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Equidna\Toolkit\Traits\Database\HasCompositePrimaryKey;

/**
 * Eloquent model for Caronte user metadata.
 *
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.1.0
 */
class CaronteUserMetadata extends Model
{
    use HasCompositePrimaryKey;

    protected $table      = 'UsersMetadata';
    protected $primaryKey = ['uri_user', 'scope', 'key'];

    public $timestamps = false;

    protected $fillable = [
        'uri_user',
        'key',
        'value',
        'scope'
    ];

    /**
     * Get the user relationship for this metadata.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(CaronteUser::class, 'uri_user');
    }
}
