<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Common\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Good;
use App\Models\Tuan;
use App\Models\TuanUser;
use App\Models\SalesOrder;


use DB;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Caspar\Packagetest\Facades\Packagetest;

class TuanController extends Controller
{
    /*
     * 获取当前团购商品
     * 当传了属性时，按属性值计算，没传时按第一个计算
     *@tid 团购业务ID
     *@git 团购商品ID
     */
    public function goodsInfo($tid = '', $gid = '')
    {
        $resData = Packagetest::goods_rtn($tid, $gid);
        $tuan = $resData['tuan'];
        $user = $resData['user'];

        // 查出来商品信息，关联查询出对应属性及属性名称
        $goodsinfo = $resData['goodsinfo'];
        //dd($goodsinfo);

        return view('tuan', compact('goodsinfo', 'tuan', 'user'));
    }

   /*
    * 新增团购活动  
    *object @requiest 活动信息
    */ 
    public function addTuan(Request $request)
    {
        
        $goods_name=Packagetest::godoscloum($request->good_id, ['name']);

        $data['good_id'] = $request->good_id;
        $data['good_title'] = $goods_name['title'];
        $data['title'] = $request->title;
        $data['tuan_num'] = $request->tuan_num;
        $data['buy_num'] = $request->buy_num;
        $data['store'] = $request->store;
        $data['price'] = $request->price;
        $data['starttime'] = strtotime($request->starttime);
        $data['endtime'] = strtotime($request->endtime);
        $data['status'] = empty($request->status)?0:(int)$request->status;

        return Packagetest::addTuan($data);
    }

    /** 
    *接收团购活动post 数据 管够新增API
    *object @requiest 活动信息
    **/
    public function postTuan(Request $request)
    {

        $data['sid'] = $request->sid;
        $data['gid'] = $request->gid;
        $data['spec_key'] = $request->spec_key;
        $data['num'] = $request->num;
        $data['uid'] = $request->uid;
        $data['gp'] = $request->gp;
        $datalist= Packagetest::postTuan($data);
        // 事务
        DB::beginTransaction();
        try {
            // 重新计算价格
            $price = $old_price = $datalist['tuan']->price;
            $prices = $price * $data['num'];

            // 创建订单
            $order = [ 'user_id' => $data['uid'], 'yhq_id' => 0, 'yh_price' => 0,
                'old_prices' => $old_price, 'total_prices' => $prices, 'create_ip' => '127.0.0.1',
                'address_id' => 0, 'ziti' => 0, 'area' => '', 'mark' => '', 'prom_type' => 2,
                'sales_id' => $data['sid'], 'display' => 0];
            $order = SalesOrder::create($order);

            DB::commit();
           $this->ajaxReturn('1', $order);

        } catch (\Throwable $e) {
            // 出错回滚
            DB::rollBack();
            $this->ajaxReturn('0', $e->getMessage());
        }

    }

   /*
    * 1、是否开团，开团商品不允许
    * 2、谁退 user_id
    * 3、退哪 tuan_user状态取消，促销订单表 status取消 pay_status 回款
    * 4、记录财务退款表 退款接口
    * */
    public function backTuan($oid)
    {
    
        $order = SalesOrder::where('id', $oid)->first();

        $tuanInfo = Tuan::where('tid', $order->sales_id)->first();
        if (time() > $tuanInfo->endtime) {
            $this->ajaxReturn('0', '已经开团，不允许退团！');
        }
        /*修改订单状态*/
        $resUser = TuanUser::where('t_id', $order->sales_id)
            ->where('user_id', $order->user_id)
            ->update(['status' => 2]);

        $resOrder = SalesOrder::where('id', $oid)
            ->update(['status' => 2]);
        $this->ajaxReturn('1', '退款成功！');
    }
    
    /*
    *砍价动作
    *@kid 活动ID
    *#k_uid 通过session获取
    */
    public function kjdo($kid)
    {
        //帮忙砍价
        $k_uid = 100;//砍价着uid
        return Packagetest::goBargain($kid, $k_uid);
    }

    /*
    *砍价活动详情
    *@id 活动ID
    */
    public function kjinfo($id)
    {
        //砍价商品详情
          $item=Packagetest::bargaindetail($id);

          $goods=$item['goods'];
          $item=$item['kjitem'][0];

        return view('kanjiainfo', compact( 'goods','item'));
    }

    /*
    *砍价动作列表
    *#k_uid 通过session获取
    */
    public function kjlist($uid)
    {
        //砍价商品列表
          $posts = Packagetest::listBargain($uid);

        return view('kanjialist', compact( 'posts'));
    }

    /*
    *用户动作列表
    *#k_uid 通过session获取
    */
    public function huodonglist(){
        //商家活动列表
        $uid=123;
        $res=Packagetest::huodonglist($uid);
        $tuanlist=$res['tuan'];
        $kanjialist=$res['kanjia'];
        return view('huodonglist', compact( 'tuanlist','kanjialist'));
    }

    /*
    *砍价新增视图
    *#k_uid 通过session获取
    */
    public function kjaddview(){
        //砍价新增视图页面
        $uid=123;
        $goods=Packagetest::goods($uid);

        return view('tuanadd', compact( 'goods'));
    }

    /*
    *新增砍价活动
    *object @requiest 活动信息
    */
    public function kjadd(Request $request)
    {
        $userID=123;
        if ($request->isMethod('post')) {
            $goods_name=Packagetest::godoscloum($request->product_id, ['name']);
            $data = [];
            $data['user_id'] = empty($request->user_id)?$userID:$request->user_id;
            $data['product_id'] = $request->product_id;
            $data['product_name'] = $goods_name['title'];//商品名称
            $data['activity_money'] = $request->activity_money; //活动价
            $data['join_count'] = $request->join_count; //参与人数
            $data['product_desc'] = $request->product_desc;//活动商品描述
            $data['min_price'] = $request->min_price; //最小价格
            $data['max_price'] = $request->max_price; //最大价格
            $data['id'] = isset($request->bargain_id) ? $request->bargain_id : null; //最大价格
            return Packagetest::addBargainAction($data);
        }
       return 0;
    }

  /*
   *编辑砍价 
   *测试demo 调用砍价活动控制器
   */
    public function kjedit(Request $request)
    {
        $data = [];
        $data['bargain_id'] = $request->bargain_id;//业务ID
        $data['user_id'] = $request->user_id;
        $data['product_id'] = $request->product_id;
        $data['product_name'] = $request->product_name;//商品名称
        $data['activity_money'] = $request->activity_money; //活动价
        $data['join_count'] = $request->join_count; //参与人数
        $data['product_desc'] = $request->product_desc;//活动商品描述
        $data['min_price'] = $request->min_price; //最小价格
        $data['max_price'] = $request->max_price; //最大价格
        return Packagetest::addBargainAction($data);
    }

    public function test(Request $request)
    {
        //活动商品测试用例
        $a = Packagetest::goods_rtn(3, 13);
        $goodsinfo = $a['goodsinfo'];
        $tuan = $a['tuan'];
        $user = $a['user'];

        return view('tuan', compact('goodsinfo', 'tuan', 'user'));
    }

    public function tuandel($id)
    {
        //下架团购活动
       return Packagetest::actityDEL(1, $id,0);
    }
    
    public function tuanup($id)
    {
        //上架团购活动
        return  Packagetest::actityDEL(1, $id,1);
    }
    
    public function kanjiadel($id)
    {
        //下架砍价活动
        return  Packagetest::actityDEL(3, $id,0);
    }
    
    public function kanjiaup($id)
    {
        //上架砍价活动
        return   Packagetest::actityDEL(3, $id,1);
    }
    
}

