<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJkActivityBargainirgTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('jk_activity_bargainirg', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->integer('activity_bargain_id')->unsigned()->index('activity_bargain_id')->comment('activity_prodcuts主键id');
			$table->integer('product_id')->unsigned()->default(0)->index('product_id')->comment('参与活动的商品');
			$table->smallInteger('attr1_id')->unsigned()->nullable()->index('attr1_id')->comment('attr1属性id');
			$table->smallInteger('attr2_id')->unsigned()->nullable()->index('attr2_id')->comment('attr2属性id');
			$table->integer('user_id')->unsigned()->default(0)->index('user_id')->comment('砍价商品发起的用户ID');
			$table->smallInteger('bargain_count')->unsigned()->default(0)->comment('被砍价次数');
			$table->decimal('deal_money', 7)->unsigned()->default(0.00)->comment('最终交易价格');
			$table->integer('create_time')->unsigned()->default(0)->comment('发起时间');
			$table->boolean('is_addorder')->nullable()->default(0)->index('is_addorder')->comment('是否下单(0:未下单，1已下单)');
			$table->boolean('type')->nullable()->default(0)->comment('0是线上，1是地推');
			$table->boolean('status')->nullable()->comment('0下架');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('jk_activity_bargainirg');
	}

}
