<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.1.0
 */

namespace Equidna\Caronte\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Equidna\Toolkit\Traits\Database\HasCompositePrimaryKey;

class CaronteUserMetadata extends Model
{
    use HasCompositePrimaryKey;

    protected $table      = 'UsersMetadata';
    protected $primaryKey = ['uri_user', 'key'];

    public $timestamps = false;

    protected $fillable = [
        'uri_user',
        'key',
        'value',
        'scope'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(CaronteUser::class, 'uri_user');
    }
}
