<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGoodsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('goods', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('cate_id')->comment('商品分类ID');
			$table->integer('brand_id')->unsigned()->comment('品牌ID');
			$table->string('title')->comment('标题');
			$table->string('pronums')->nullable()->comment('商品编号');
			$table->string('keyword')->nullable()->comment('关键字');
			$table->string('describe')->nullable()->comment('描述');
			$table->string('thumb')->nullable()->comment('缩略图');
			$table->text('album', 65535)->nullable()->comment('相册');
			$table->text('content', 65535)->nullable()->comment('内容');
			$table->decimal('market_price', 10)->default(0.00)->comment('市场价');
			$table->decimal('shop_price', 10)->default(0.00)->comment('本店价');
			$table->decimal('cost_price', 10)->default(0.00)->comment('成本价');
			$table->integer('store')->default(100)->comment('库存');
			$table->decimal('weight')->default(1.00)->comment('重量，单位克');
			$table->dateTime('lasttime')->nullable()->comment('上架时间');
			$table->dateTime('lowertime')->nullable()->comment('下架时间');
			$table->boolean('is_pos')->default(0)->comment('是否推荐，0否');
			$table->boolean('is_hot')->default(0)->comment('是否热卖');
			$table->boolean('is_new')->default(0)->comment('是否新品');
			$table->integer('sort')->default(0)->comment('排序');
			$table->integer('hits')->unsigned()->default(100)->comment('点击');
			$table->integer('sales')->default(0)->comment('销量');
			$table->decimal('score', 10)->default(0.00)->comment('评分');
			$table->integer('commentnums')->default(0)->comment('评论数');
			$table->boolean('prom_type')->default(0)->comment('0普通商品，1限时，2团购，3满赠，4活动');
			$table->integer('prom_id')->unsigned()->default(0)->comment('优惠活动ID');
			$table->boolean('status')->default(1)->comment('状态，1正常0下架');
			$table->timestamps();
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
		Schema::drop('goods');
	}

}
