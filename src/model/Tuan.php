<?php

namespace  Caspar\Packagetest\model;

use Illuminate\Database\Eloquent\Model;

class Tuan extends Model
{
    // 团购
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'jk_tuan';

    //设置查询主键
    protected $primaryKey='tid';

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

    // 商品
    public function good()
    {
        return $this->belongsTo('Caspar\Packagetest\model\Good','good_id','groupsale_id');
    }
    public function TuanUser()
    {
        return $this->hasOne('Caspar\Packagetest\model\TuanUser','t_id', 'tid');
    }
}
