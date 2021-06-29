<?php
namespace Jalno\Profile\Providers;

use Jalno\Userpanel\Models\User;
use Illuminate\Support\ServiceProvider;
use Jalno\Profile\Models\User as Profile;
use Illuminate\Validation\ValidationException;
use Jalno\Userpanel\Contracts\IUserValidatorContainer;
use Jalno\Userpanel\Contracts\IUserSaveAddionalFieldsContainer;

class ProfileServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		if ($this->app->runningInConsole()) {
			$this->registerMigrations();
		}
	}

	/**
     * Bootstrap any application services.
     *
     * @return void
     */
	public function boot()
	{
		User::addEagerLoadRelation("profile");
		User::resolveRelationUsing("profile", function(User $user) {
			return $user->hasOne(Profile::class, "user_id");
		});
	}

	protected function registerMigrations()
	{
		$this->loadMigrationsFrom(package()->getMigrationPath());
	}

	protected function addUserValidatorFields()
	{
		$validator = $this->app->make(IUserValidatorContainer::class);

		$validator->add("profile.name", function($value) {
			return is_string($value);
		});

		$validator->add("profile.lastname", function($value) {
			return is_string($value);
		});

		$validator->add("profile.phone", function($value) {
			return is_string($value);
		});

		$validator->add("profile.city", function($value) {
			return is_string($value);
		});

		$validator->add("profile.address", function($value) {
			return is_string($value);
		});

		$validator->add("profile.web", function($value) {
			return is_string($value);
		});

		$validator->add("profile.social_networks", function($value) {
			if (!is_array($value)) {
				throw ValidationException::withMessages(["profile.social_networks" => "The profile.social_networks must be an array."]);
			}

			foreach ($value as $key => $item) {
				if (!is_string($key)) {
					throw ValidationException::withMessages(["profile.social_networks.{$key}" => "The profile.social_networks.{$key} must be contains a social network name."]);
				}
			}

			return true;
		});
	}

	protected function addUserSaveFields()
	{
		$container = $this->app->make(IUserSaveAddionalFieldsContainer::class);

		$container->add("profile.name", function(User $user, string $value): array {
			$logParameters = [];
			if ($user->profile->name != $value) {
				$logParameters["profile"] = [
					"old" => [
						"name" => $user->profile->name,
					],
					"new" => [
						"name" => $value,
					],
				];
				$user->profile->name = $value;
			}

			return $logParameters;
		});
	}
}
