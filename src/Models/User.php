<?php

namespace Jalno\Profile\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	/**
	 * The table associated with the model.
	 *
	 * @property string
	 */
	protected $table = 'profile_users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @property array
	 */
	protected $fillable = [];

	/**
	 * The model's default values for attributes.
	 *
	 * @property array
	 */
	protected $attributes = [];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @property array
	 */
	protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
	protected $casts = [
		"social_networks" => "array",
	];

    public function user()
    {
        return $this->hasOne(User::class, "id", "user_id");
    }
}
