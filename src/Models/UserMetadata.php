<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class UserMetadata extends Model
{

    protected $table      = 'UsersMetadata';
    protected $primaryKey = 'uri_user';

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
