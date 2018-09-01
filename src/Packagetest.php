<?php

namespace Caspar\Packagetest;

use Caspar\Packagetest\model\jktype;
use Caspar\Packagetest\model\KanUser;
use Illuminate\Session\SessionManager;
use Illuminate\Config\Repository;
use Caspar\Packagetest\model\common;
use Caspar\Packagetest\model\SalesOrder;
use Caspar\Packagetest\model\kanjian;
use Caspar\Packagetest\model\KanJianGoods;
use \Caspar\Packagetest\model\Tuan;
use \Caspar\Packagetest\model\TuanUser;
use \Caspar\Packagetest\model\Good;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Packagetest
{
    /**
     * @var SessionManager
     */
    protected $session;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * Packagetest constructor.
     * @param SessionManager $session
     * @param Repository $config
     */
    public function __construct()
    {

    }
    /**
     * @param string 返回当前商品
     * @return string
     */
    public function goods($UID){
            return  Good::where('user_id',$UID)->skip(0)->take(5)->get()->toarray();
    }
    /**
     * @param string 返回商品指定列
     * @return string
     */
    public function godoscloum($goodsID){
           return  Good::find($goodsID, ['title']);
    }

    /*
     * 活动列表type 插入一条新增记录 团购/砍价
     * merchant_id 商家ID， employee_id 员工ID，type 活动类型，status状态
     * */
    public static function addhuodong($employee_id,$type,$status,$merchant_id=0){
        $indata=[];
        $indata['merchant_id']=0;
        $indata['employee_id']=$employee_id;
        $indata['type']=$type;
        $indata['status']=$status;
        $typeNEW=jktype::create($indata);
        return $typeNEW;
    }

    /*
     * 活动列表
     * */
    public function  huodonglist($userID){
        $tuanlist= Tuan::get()->toarray();
         $kanjialist=kanjian::where('user_id',$userID)->with('KanJianGoods')->get()->toarray();

        return ['tuan'=>$tuanlist,'kanjia'=>$kanjialist];
    }

    /*
     * 活动删除，上架
     * type 1 团购 3 砍价
     * status 0下架
     * id 业务ID
     * */
    public function actityDEL($type,$id,$status=1){
        if($type==1){
            if($status===0){
               $buy_num= Tuan::find($id, ['buy_num']);
               if ($buy_num['buy_num']>0){
                   return   common::ajaxReturn('0', '已发起活动，不可下架');
               }
            }
            return   Tuan::where('tid', $id)
                ->update(['status' => $status]);
        }
        if($type==3){
            if($status===0){
                $buy_num= kanjian::find($id, ['bargain_count']);
                if ($buy_num['bargain_count']>0){
                    return   common::ajaxReturn('0', '已发起活动，不可下架');
                }
            }
          return  kanjian::where('id', $id)
                ->update(['status' => $status]);
        }
    }

    /**
     * @param string $msg
     * @return string 团购详情
     */
    public function goods_rtn($tid = 0, $gid = 0)
    {

        $tuan = Tuan::find($tid)->toarray();
        $user=TuanUser::where('t_id',$tid)->select()->get()->toarray();


        // 查出来商品信息，关联查询出对应属性及属性名称
        $goodsinfo = Good::find($gid)->toarray();
        $resData = ['tuan' => $tuan, 'user' => $user, 'goodsinfo' => $goodsinfo];

        return $resData;
    }


    public function addTuan(array $data){
        // 事务
        DB::beginTransaction();
        try {
             $Tuan= Tuan::create($data);
             self::addhuodong(123,1,1,$merchant_id=0);
            DB::commit();
            common::ajaxReturn('1', $Tuan);
        } catch (\Throwable $e) {
            DB::rollback();
            common::ajaxReturn('0', $e->getMessage());
        }
    }

    public function postTuan(array $data)
    {

            //获取团购活动提交的表单
            $tid = $data['sid'];
            $gid = $data['gid'];
            // 规格key
            $spec_key = $data['spec_key'];
            $num = $data['num'];
            $userid = $data['uid'];
            $price = $old_price = $data['gp'];

            // 商品信息
            $good = Good::findOrFail($gid);

            // 如果用户已经登录，查以前的购物车
            if (!$userid) {
                common::ajaxReturn('2', "请先登录！");
            }

            if ($num > 1) {
                common::ajaxReturn('0', '团购限量购买，超过限制份数！');
            }

            if (!is_null(TuanUser::where('user_id', $userid)->where('t_id', $tid)->where('status', 1)->first())) {
                common::ajaxReturn('0', '参加过，请不要重复参加！');
            }
            // 没参过团的
            $tuan = Tuan::where('tid', $tid)->orderBy('tid', 'desc')->first();

            if ($tuan['store'] <= 0) {
                common::ajaxReturn('0', '已经满员，等待下次机会吧！');
            }

            $tuanUser=['user_id'=>$userid,'t_id'=>$tid,'status'=>1];

        try {
            //数据写入、更改团购人数
            $resTUSER = TuanUser::create($tuanUser);

            Tuan::where('tid', $tid)
                ->update(['buy_num' => $tuan['buy_num']+1]);
            //当前团购人数已满 开团中
            if($tuan->tuan_num==$tuan->buy_num){
                SalesOrder::where('tid', $tid)
                    ->update(['shipstatus' => 1]);
            }

            DB::commit();
          return ['tuan'=>$tuan,'resTUSER'=>$resTUSER];

        } catch (\Throwable $e) {
            // 出错回滚
            DB::rollBack();
            common::ajaxReturn('0', $e->getMessage());
        }
    }

    public function backTuan($oid)
    {
        /* caspar2018/08/28
         * 1、是否开团，开团商品不允许
         * 2、谁退 user_id
         * 3、退哪 tuan_user状态取消，促销订单表 status取消 pay_status 回款
         * 4、记录财务退款表 退款接口
         * */

        $order = SalesOrder::where('id', $oid)->first();

        $tuanInfo = Tuan::where('tid', $order->sales_id)->first();
        if (time() > $tuanInfo->endtime) {
            common::ajaxReturn('0', '已经开团，不允许退团！');
        }
        /*修改订单状态*/
        $resUser = TuanUser::where('t_id', $order->sales_id)
            ->where('user_id', $order->user_id)
            ->update(['status' => 2]);

        $resOrder = SalesOrder::where('id', $oid)
            ->update(['status' => 2]);
        common::ajaxReturn('1', '退款成功！');
    }

    /*砍价活动新增操作*/
    public function addBargainAction(array $req)
    {

        if (empty($req['product_id']) || empty($req['product_name'])) {
            return common::ajaxReturn('0', '商品信息不能为空！');
        }
        if (empty($req['activity_money']) || empty($req['join_count'])) {
            return common::ajaxReturn('0', '活动价格|人数不能位空！');
        }
        $req['bargain_section'] = $req['min_price'] . '-' . $req['max_price'];
        unset($req['min_price']);
        unset($req['max_price']);
        // 事务开启
        if ($req['id']) {
            $resgoodsID = KanJianGoods::where('id', $req['id'])
                ->update($req);
            return $resgoodsID;
        } else {
            DB::beginTransaction();
            try {
                $resgoodsID = KanJianGoods::create($req);
                //新增插入一条记录在砍价列表
                $kanjiaing = [];
                $kanjiaing['user_id'] = $req['user_id'];
                $kanjiaing['activity_bargain_id'] = $resgoodsID['id'];
                $kanjiaing['product_id'] = $req['product_id'];
                $kanjiaing['bargain_count'] = 0;
                $kanjiaing['create_time'] = time();
                $kanjiaing['type'] = 1;
                $reskj = KanJian::create($kanjiaing);
                self::addhuodong(123,3,1,$merchant_id=0);
            } catch (\Throwable $e) {
                // 出错回滚
                DB::rollBack();
                common::ajaxReturn('0', '参数错误');
            }
            DB::commit();//提交事务
            common::ajaxReturn('1', ['kj' => $reskj, 'goods' => $resgoodsID]);
        }
        return 0;
    }

    /*砍价活动列表操作*/
    public   function listBargain($uid)
    {
        return KanJianGoods::where('user_id', $uid)->select()->get()->toarray();
    }

    /*砍价活动详情*/
    public  function bargaindetail($kid)
    {

        $goods = KanJianGoods::find($kid)->toarray();
        $kjitem = KanJian::where('activity_bargain_id', $kid)->select()->get()->toarray();

        return ['goods'=>$goods,'kjitem'=>$kjitem];
    }

    /*点击链接参与砍价*/
    //帮忙砍价\进度\底部砍价商品列表
    public  function bargainirging($uid, $token)
    {
//        $bargain_param = self::resToken($token);
//
//        $bargain_param = self::retrunBargainCode($encrypt_code);
//        $bargain_id = $bargain_param['bargain_id'];
//        $bargainInfo = kanjian::getBargainirgProgress($bargain_id);
//        if (!is_not_empty_array($bargain_param) || !is_not_empty_array($bargainInfo)) {
//            notFund();
//        }
//
//        $is_addorder = $bargainInfo['is_addorder'] == 1 ? true : false;
//
//        $uid = session('userinfo.uid');
//        $activity_product_id = $bargain_param['activity_product_id'];
//
//        if ($bargain_param['sponsor_uid'] == $uid) {
//            $this->redirect('bargaindetail', ['id' => $activity_product_id]);
//        }
//        $product_id = $bargain_param['product_id'];
//
//        $bargain_list = kanjian::getActivityBargainProducts($bargain_param['type'], 999); //所有参与砍价活动的商品
//
//        foreach ($bargain_list as $v) {
//            if ($v['id'] == $activity_product_id) {
//                $product_info = $v;
//            }
//        }
//
//        if (!is_not_empty_array($product_info)) notFund();
//        $type = $bargain_param['type'];
//        $activity_bargain_url = url('activity/bargainirg');
//
//        $view = new view();
//        $view->assign([
//            'bar_code' => $encrypt_code,   //邀请码
//            'bargainInfo' => $bargainInfo,    //当前砍价进度
//            'product_info' => $product_info,   //商品详情
//            'bargain_list' => $bargain_list,   //底部相关推荐
//            'is_addorder' => $is_addorder,     //是否入库
//            'activity_bargain_url' => $activity_bargain_url
//        ]);
//
//        return $view->fetch();


    }

    //ajax砍价
    public  function goBargain($kid,$k_uid)
    {

        $kanjiaDATA = KanJian::find($kid)->toarray();
        $goodsDATA = KanJianGoods::find($kanjiaDATA['activity_bargain_id'])->toarray();
        if($k_uid==$kanjiaDATA['user_id']){
            common::ajaxReturn('0', '不能给自己砍价');
        }
        if($kanjiaDATA['bargain_count']>$goodsDATA['join_count']){
            common::ajaxReturn('0', '已打砍价人数上线');
        }

        $checkKan= KanUser::where('assistor_id', $k_uid)->select()->get()->toarray();
        if(is_array($checkKan)&&count($checkKan)>0){
            common::ajaxReturn('0', '您已帮伙伴砍掉' . $checkKan[0]['bargain_money'] . '元啦，不要再砍啦!');
        }
        //开始写入业务

        /**
         * 砍价相关数据操作
         *$bargain_id    [activity_bargainirg] 表主键id
         *$sponsor_id    砍价发起者id
         *$assistor_id   帮助砍价者id
         *$min           最小值
         *$max           最大值
         *$join_count    设置要参与砍价的人数
         *return bool
         */
        $bargain_section=explode("-", $goodsDATA['bargain_section']);

        $state = self::givePartBargain($kanjiaDATA['id'], $kanjiaDATA['user_id'], $k_uid, $bargain_section[0], $bargain_section[1], $goodsDATA['join_count']);

/*        $insDD=[];
        $insDD['bargain_id']=$kanjiaDATA['id'];
        $insDD['assistor_id']=$k_uid;
        $insDD['create_time']=time();
        $insDD['bargain_money']=$state;*/
//        $resKanUser = KanUser::create($insDD);
//        $resgoodsID = KanJianGoods::where('id', $req['id'])->update($req);
        if ($state == -1) {
            echo json_encode(array('status' => -3, 'info' => '已经最低价啦，不能再砍啦！'));
            die;
        }
        if ($state === false) {
            echo json_encode(array('status' => -3, 'info' => '哎呀，失败了！稍后帮我砍一次！'));
            die;
        } else {
                echo json_encode(array('status' => 1, 'info' => '砍掉了' . $state . '元', 'deal_money' => $state));
                die;

        }
    }


    /**
     * 砍价相关数据操作
     *$bargain_id    [activity_bargainirg] 表主键id
     *$sponsor_id    砍价发起者id
     *$assistor_id   帮助砍价者id
     *$min           最小值
     *$max           最大值
     *$join_count    设置要参与砍价的人数
     *return bool
     */
    public static
    function givePartBargain($bargain_id = 0, $sponsor_id = 0, $assistor_id = 0, $min = 0, $max = 0,$join_count = 0) {
        $state = false;
        if (is_integer($assistor_id) && $bargain_id > 0 && is_integer($sponsor_id)) {
            $bargainirg_info = KanJian::find($bargain_id)->toarray();
            $bargainirg_goods = KanJianGoods::find($bargainirg_info['activity_bargain_id'])->toarray();
            if ( !$bargainirg_info ) {
                return $state;
            }

            $fp = fopen('./bargain_lock.txt','r');
            $try = 5;
            do {
                $lock = flock($fp,LOCK_EX);
                if(!$lock)
                    usleep(5000);
            } while (!$lock && --$try >= 0) ;
            if ($lock) {
                DB::beginTransaction();
                try {

                    $bargain_money = self::returnRandMoney($bargain_id, $min, $max, $join_count);
                    /*-------------*/
                    $mast_price=($bargainirg_info['deal_money']==0)?$bargainirg_goods['activity_money']:$bargainirg_info['deal_money'];
                    $id  = 0;
                    $up=[];
                    $up[0]=$mast_price-$bargain_money;
                    $up[1]=$bargainirg_info['bargain_count']+1;
                    $up[2]=$bargain_id;
                    $up[3]=$sponsor_id;
//                    $up[4]=$bargain_money;
//                    $up[5]=$join_count;

                    $row = DB::update('update jk_activity_bargainirg set deal_money= ? ,bargain_count=? where id= ? AND `user_id` = ?  ',$up);

                    if ( $row > 0) {
                        $insert_data = [];
                        $insert_data['bargain_id']      = $bargain_id;
                        $insert_data['assistor_id']     = $assistor_id;
                        $insert_data['bargain_money']   = $bargain_money;
                        $insert_data['create_time']     = time();
                        $resDD = KanUser::create($insert_data);
                        $id=$resDD['id'];
                    }
                    /*-------------*/
                    if ($id > 0)
                        $state = true;
                    Db::commit();
                }catch(\Exception $e){
                    $state = false;
                    Db::rollback();
                }
//                　　　　　　　　flock($lock,LOCK_UN);
//                　　　　　　　　fclose($lock);

            }
        }
        if ($state !== false ) {
            return $bargain_money;
        }
        return $state;
    }

    //拿到上次所砍掉的价格
    public static function getBeforeMoney ( $bargain_id = 0, $limit = 1) {

        $max = KanUser::where('bargain_id', $bargain_id)->sum('bargain_money');
        $beforemoney_sum = empty($max)?0:$max;
        return $beforemoney_sum;
    }

    //返回要砍的价格
    public static function returnRandMoney ($bargain_id = 0, $min = 0 ,$max = 0, $join_count = 0 ){
        $randMoney       = self::randomFloat( $min, $max);                  //返回随机价格
        $prev_Progress    = KanJian::find($bargain_id)->toarray();;
        $prev_bargain_count = $prev_Progress['bargain_count'];              //返回已经被砍价的次数
        $remainder = $prev_bargain_count % 3;

        $bout_count = floor($join_count / 3) * 3;  //最后一轮结束的刀数       39
        $last_num = $join_count - $bout_count;
        $avg = ($min + $max) / 2;
        $before_sum  = self::getBeforeMoney($bargain_id, $remainder);

        if ($prev_bargain_count >= $bout_count) {
            if ($last_num == 1){
                return $avg;
            } elseif ($last_num == 2) {
                $end = $join_count - $prev_Progress['bargain_count'] ;
                if ($end == 2) {
                    return $randMoney;
                } elseif($end == 1) {
                    return $avg * 2 - $before_sum;
                }
            }
        }
        // $remainder_num   = $join_count % 3;         //总回合数的余数
        if ($remainder > 0) {
            if ( $remainder == 1) {
                $point      = $max * 0.8;    //最大额度的80%
                $bout_sum   = 3 * $avg;
                if ($before_sum >= $point) {
                    $randMoney = self::randomFloat($min, ($bout_sum - $before_sum) / 2);
                } else {
                    $randMoney = self::randomFloat(($bout_sum - $before_sum) / 2 , $point);
                }
            }
            if ($remainder == 2) {
                $round_sum_money = 3 * $avg;
                $randMoney       = $round_sum_money - $before_sum;
            }
        }
        return $randMoney;

    }
    //拿随机价格
    public static function randomFloat($min = 0, $max = 1) {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min),2);
    }


    public
    static function resToken($token)
    {
        return substr($token, 4, -10);
    }

    public
    static function addToken($id)
    {
        return md5(rand(1000, 9999) . $id . time());
    }

//返回砍价活动相关数据
    public
    static function retrunBargainCode($encrypt_str = '')
    {
        $data = [];
        $code_str = encrypt_hopeband($encrypt_str, 'D', 'Hp_HopeBand_Bargainirg');

        $code_arr = explode('(&)', $code_str);


        if (is_not_empty_array($code_arr) && count($code_arr) == 10) {
            $data['bargain_id'] = $code_arr[0];             //砍价活动表主键id
            $data['activity_product_id'] = $code_arr[1];
            $data['sponsor_uid'] = $code_arr[2];             //砍价活动发起者uid
            $data['sponsor_invite_code'] = $code_arr[3];             //砍价活动发起者邀请码
            $data['product_id'] = $code_arr[4];             //砍价活动发起的商品id
            $data['activity_money'] = $code_arr[5];             //活动最低价格
            $data['bargain_section'] = $code_arr[6];             //老用户砍价区间
            $data['bargain_section2'] = $code_arr[7];             //新用户砍价区间
            $data['join_count'] = $code_arr[8];             //设置砍价次数
            $data['type'] = $code_arr[9];             //设置砍价类型

        }

        return $data;
    }
}
