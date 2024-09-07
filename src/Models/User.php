<?php

namespace Equidna\Caronte\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table      = 'Users';
    protected $primaryKey = 'uri_user';
    protected $keyType    = 'string';

    public $timestamps   = false;
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'uri_parent',
        'uri_user',
        'forceTwoFactor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    public function twoFactorToken(): HasOne
    {
        return $this->hasOne(TwoFactorToken::class, 'uri_user');
    }

    public function metadata(): HasMany
    {
        return $this->hasMany(UserMetadata::class, 'uri_user');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(ApplicationRole::class, 'UsersApplicationRoles', 'uri_user', 'id_applicationRole');
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

    public function scopeSearchRolesAndApplications(Builder $query, null|string $search = null): Builder
    {
        return $query->with(['roles' => function ($query) use ($search) {
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('application', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            }
        }, 'roles.application', 'metadata']);
    }

    public function getRolesWithSearchAndPagination(null|string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        return $this->roles()
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhereHas('application', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        });
                }
            })
            ->paginate($perPage);
    }

    public function scopeWithRolesPerApplication(Builder $query, string $uri_application): Builder
    {
        return $query->with([
            'metadata',
            'roles' => function ($query) use ($uri_application) {
                $query->where('uri_application', $uri_application);
            }
        ]);
    }
}
