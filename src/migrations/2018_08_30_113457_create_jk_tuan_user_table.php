<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJkTuanUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('jk_tuan_user', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->comment('用户ID');
			$table->integer('t_id')->comment('团购ID');
			$table->boolean('status')->default(1)->comment('参加状态：1正常，0取消');
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
		Schema::drop('jk_tuan_user');
	}

}
