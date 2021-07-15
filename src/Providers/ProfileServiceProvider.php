<?php
namespace Jalno\Profile\Providers;

use Jalno\Userpanel\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Jalno\Profile\Models\User as Profile;

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

	public function boot(): void
	{
		User::addEagerLoadRelation("profile");
		User::resolveRelationUsing("profile", function(User $user) {
			return $user->hasOne(Profile::class, "user_id");
		});

		User::addSearchAttributeToModel("profile");
	}

	protected function registerMigrations(): void
	{
		$this->loadMigrationsFrom(package()->getMigrationPath());
	}
}
