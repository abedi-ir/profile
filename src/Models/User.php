<?php

namespace Jalno\Profile\Models;

use Illuminate\Database\Eloquent\{Model, Relations\HasOne};

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $name
 * @property string|null $lastname
 * @property string|null $email
 * @property string|null $cellphone
 * @property string|null $avatar
 * @property string|null $phone
 * @property string|null $city
 * @property string|null $address
 * @property string|null $web
 * @property array<string,string>|null $social_networks
 * @property string $created_at
 * @property string|null $updated_at
 */

class User extends Model
{
	/**
	 * The table associated with the model.
	 *
	 * @var string $table
	 */
	protected $table = 'profile_users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var string[] $fillable
	 */
	protected $fillable = [];

	/**
	 * The model's default values for attributes.
	 *
	 * @var array<string,mixed> $attributes
	 */
	protected $attributes = [];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var string[] $hidden
	 */
	protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string> $casts
     */
	protected $casts = [
		"social_networks" => "array",
	];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }
}
