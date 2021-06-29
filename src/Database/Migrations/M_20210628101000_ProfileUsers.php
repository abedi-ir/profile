<?php
namespace Jalno\Profile\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class M_20210628101000_ProfileUsers extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('profile_users', function (Blueprint $table) {
			$table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                    ->references('id')
                    ->on('userpanel_users')
                    ->onDelete('cascade')
					->onUpdate('cascade');
			
			$table->string("name", 100)->nullable();
			$table->string("lastname", 100)->nullable();
			$table->string("email", 100)->unique()->nullable();
			$table->string("cellphone", 50)->unique()->nullable();
			$table->string("avatar", 255)->nullable();
			$table->string("phone", 12)->nullable();
			$table->string("city", 100)->nullable();
			$table->string("address", 255)->nullable();
			$table->string("web", 255)->nullable();
			$table->json("social_networks")->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('profile_users');
	}
}
