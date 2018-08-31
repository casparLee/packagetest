<?php

namespace Caspar\Packagetest\model;

use Illuminate\Database\Eloquent\Model;

class KanJian extends Model
{
    // 商品表
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'jk_activity_bargainirg';

    // 不可以批量赋值的字段，为空则表示都可以
    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $hidden = [];
    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;


    /**
     * 获得与用户关联的电话记录。
     */
    public function KanJianGoods()
    {
        return $this->hasOne('Caspar\Packagetest\model\KanJianGoods','id', 'activity_bargain_id');
    }
}
