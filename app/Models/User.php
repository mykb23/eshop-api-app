<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *      title="User",
 *      type="object",
 * )
 */
class User extends Authenticatable
{

    /**
     * @OA\Property(type="integer",title="id",default="1", property="id")
     * @OA\Property(type="string",title="first_name",default="John", property="first_name",minLength=3)
     * @OA\Property(type="string",title="last_name",default="Doe", property="last_name",minLength=3)
     * @OA\Property(type="email",title="email",default="john-doe@mail.com", property="email")
     * @OA\Property(type="string",title="avatar",default="avatar.png", property="avatar")
     * @OA\Property(type="telephone",title="telephone",default="080 2566 4567", property="telephone")
     * @OA\Property(type="string",title="activation_token",property="activation_token",example="6wjKmRtjddXW79sKXsOrJ9ZnloOY5iuwVm9SlA9Le3xSR8ydD2ugljZwNhPZ")
     */
    use HasApiTokens, HasFactory, Notifiable;

    protected $date = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'activation_token',
        'profile_picture_path',
        'telephone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'deleted_at',
        'created_at',
        'updated_at',
        'roles'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     *
     * string avatar_url
     */
    protected $appends = [
        // 'avatar_url',
        'role'
    ];
    // protected $with = ['role'];

    // public function getAvatarUrlAttribute()
    // {
    //     return Storage::url('images/avatars/' . $this->first_name . '/' . $this->avatar);
    // }

    // return $this->roles[0]->name;
    public function getRoleAttribute()
    {
        return $this->roles;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasAnyRole(string $role)
    {
        return null !== $this->roles()->where('name', $role)->first();
    }

    public function hasAnyRoles(array $role)
    {
        return null !== $this->roles()->whereIn('name', $role)->first();
    }
}
