<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJkActivityBargainTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('jk_activity_bargain', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键');
			$table->integer('product_id')->unsigned()->default(0)->index('product_id')->comment('商品ID');
			$table->string('product_name', 200)->comment('商品名称');
			$table->decimal('activity_money', 7)->unsigned()->default(0.00)->comment('活动价');
			$table->string('bargain_section', 20)->default('')->comment('砍价区间');
			$table->string('bargain_section2', 20)->nullable()->default('')->comment('砍价区间2【用户线上砍价(新用户砍价区间)】');
			$table->smallInteger('join_count')->unsigned()->default(0)->comment('参与人数');
			$table->string('product_desc', 80)->comment('活动商品描述');
			$table->smallInteger('attr1_id')->unsigned()->nullable()->default(0)->index('attr1_id')->comment('attr1属性');
			$table->smallInteger('attr2_id')->unsigned()->nullable()->default(0)->index('attr2_id')->comment('attr2属性');
			$table->boolean('type')->nullable()->default(0)->index('type')->comment('0是线上，1是地推');
			$table->integer('user_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('jk_activity_bargain');
	}

}
