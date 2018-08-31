<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJkTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('jk_type', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('merchant_id')->default(0)->comment('商家id');
			$table->integer('employee_id')->default(0)->comment('员工id');
			$table->boolean('type')->default(0)->comment('活动类型(1=团购;2=分享赚钱;3=砍价)');
			$table->boolean('status')->default(1)->comment('活动状态(0=关闭;1开启)');
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
		Schema::drop('jk_type');
	}

}
