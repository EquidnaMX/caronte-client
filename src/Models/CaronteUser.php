<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 *
 */

namespace Equidna\Caronte\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CaronteUser extends Model
{
    protected $table      = 'Users';
    protected $primaryKey = 'uri_user';
    protected $keyType    = 'string';

    public $timestamps   = false;
    public $incrementing = false;

    protected $fillable = [
        'uri_user',
        'name',
        'email'
    ];

    protected $hidden = [];

    public function metadata(): HasMany
    {
        return $this->hasMany(CaronteUserMetadata::class, 'uri_user');
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = ucwords($value);
    }

    public function scopeSearch(Builder $query, null|string $search = null): Builder
    {
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
