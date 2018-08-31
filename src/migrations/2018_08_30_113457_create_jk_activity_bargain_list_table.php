<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJkActivityBargainListTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('jk_activity_bargain_list', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->integer('bargain_id')->unsigned()->default(0)->index('bargain_id')->comment('activity_bargainirg表主键id');
			$table->integer('assistor_id')->unsigned()->default(0)->index('assistor_id')->comment('帮助者ID');
			$table->integer('create_time')->unsigned()->default(0)->comment('参与时间');
			$table->decimal('bargain_money', 5)->unsigned()->default(0.00)->comment('砍掉价格');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('jk_activity_bargain_list');
	}

}
