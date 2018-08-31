<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJkTuanTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('jk_tuan', function(Blueprint $table)
		{
			$table->increments('tid');
			$table->integer('good_id')->comment('商品ID');
			$table->string('good_title')->comment('商品标题');
			$table->string('title')->comment('标题');
			$table->integer('tuan_num')->default(0)->comment('开团人数');
			$table->integer('buy_num')->default(0)->comment('参团人数');
			$table->integer('store')->default(0)->comment('库存');
			$table->decimal('price', 10)->default(0.00)->comment('团购价');
			$table->integer('starttime')->nullable()->comment('开始时间');
			$table->integer('endtime')->nullable()->comment('结束时间');
			$table->integer('sort')->default(0)->comment('排序');
			$table->boolean('status')->default(1)->comment('状态：1正常，0结束');
			$table->boolean('delflag')->default(1)->comment('删除状态：1正常，0删除');
			$table->integer('created_at')->nullable();
			$table->integer('updated_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('jk_tuan');
	}

}
