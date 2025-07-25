<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.2
 */

namespace Equidna\Caronte\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for Caronte users.
 *
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.1.0
 */
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

    /**
     * Get the metadata relationship for the user.
     *
     * @return HasMany
     */
    public function metadata(): HasMany
    {
        return $this->hasMany(CaronteUserMetadata::class, 'uri_user');
    }

    /**
     * Mutator to set the user's name with proper casing.
     *
     * @param string $value Name value.
     * @return void
     */
    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = ucwords($value);
    }

    /**
     * Scope to search users by name or email.
     *
     * @param Builder $query Eloquent query builder.
     * @param string|null $search Search term.
     * @return Builder
     */
    public function scopeSearch(Builder $query, ?string $search = null): Builder
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
