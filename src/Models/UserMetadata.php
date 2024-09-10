<?php

namespace Equidna\Caronte\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Equidna\Toolkit\Traits\Database\HasCompositePrimaryKey;

class UserMetadata extends Model
{
    use HasCompositePrimaryKey;

    protected $table      = 'UsersMetadata';
    protected $primaryKey = ['uri_user', 'key'];

    public $timestamps = false;

    protected $fillable = [
        'uri_user',
        'value',
        'scope',
        'key',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uri_user');
    }
}
