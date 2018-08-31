<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJkOrderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('jk_order', function(Blueprint $table)
		{
			$table->increments('id');
			$table->char('order_sn', 20)->nullable()->comment('订单ID');
			$table->integer('user_id')->comment('用户ID');
			$table->integer('sales_id')->nullable()->default(0)->comment('团购ID');
			$table->char('t_orderid', 50)->nullable()->default('')->comment('开团号');
			$table->integer('address_id')->nullable()->default(0)->comment('送货地址');
			$table->integer('yhq_id')->nullable()->default(0)->comment('优惠券id');
			$table->decimal('yh_price')->nullable()->default(0.00)->comment('优惠券金额');
			$table->integer('points')->nullable()->default(0)->comment('积分');
			$table->decimal('points_money', 10)->nullable()->default(0.00)->comment('积分抵现的金额');
			$table->decimal('old_prices')->nullable()->default(0.00)->comment('原价');
			$table->decimal('total_prices', 10)->nullable()->default(0.00)->comment('总价');
			$table->string('create_ip', 50)->nullable()->comment('创建IP');
			$table->boolean('paystatus')->nullable()->default(0)->comment('支付状态,0未，1已');
			$table->string('pay_name', 50)->nullable()->comment('支付方式');
			$table->dateTime('paytime')->nullable()->comment('支付时间');
			$table->boolean('shipstatus')->nullable()->default(0)->comment('发货状态,0未，1已');
			$table->dateTime('ship_at')->nullable()->comment('发货时间');
			$table->string('area', 50)->nullable()->comment('区域');
			$table->integer('ziti')->nullable()->default(0)->comment('自提点');
			$table->string('mark', 500)->nullable()->default('')->comment('用户备注');
			$table->string('shopmark', 500)->nullable()->default('')->comment('商家备注');
			$table->boolean('orderstatus')->default(1)->comment('订单状态，1正常2完成0关闭');
			$table->dateTime('confirm_at')->nullable()->comment('确认收货时间');
			$table->boolean('prom_type')->default(0)->comment('类型：0正常，1抢，2团');
			$table->boolean('display')->default(1)->comment('是否显示：1显示，0不显示');
			$table->boolean('status')->default(1)->comment('状态，1正常0删除');
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
		Schema::drop('jk_order');
	}

}
