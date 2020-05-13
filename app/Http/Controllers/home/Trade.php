<?php
namespace App\Http\Controllers\Home;

use App\Http\Controllers\Base;
use App\Http\Models\Assets;
use App\Http\Models\Coins;
use App\Http\Models\Orders;
use App\Http\Models\RealNameAuths;
use App\Http\Models\SystemSettings;
use App\Http\Models\TradeNumbers;
use App\Jobs\BuyMatch;
use App\Jobs\SalesMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class Trade extends Base
{

    public function init()
    {
        $openTrade = SystemSettings::getSysSettingValue('trade_open');
        $auth = new RealNameAuths();
        if (!$auth->realNameAuthCheck()){
            view()->share('realNameAuth','请先完成实名认证');
        }elseif ($openTrade == 'off'){
            view()->share('trade','临时暂停交易');
        }else{
            $start = SystemSettings::getSysSettingValue('trade_start');
            $end = SystemSettings::getSysSettingValue('trade_end');
            if (date('H') < $start || date('H') > $end){
                view()->share('trade','交易时间：'.$start.':00 - '.$end.':00');
            }
        }
    }

    /**
     * 交易页
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function buy()
    {
        //币价
        $coinPrice = Coins::orderBy('id','desc')->first();
        $viewParams['coinPrice'] = $coinPrice->price;
        //我的买单
        $buyOrders = Cache::get('tradeBuy');
        $buyOrdersArry = [];
        if (!empty($buyOrders)){
            foreach ($buyOrders as $k => $buyOrder) {
                if ($buyOrder['buy_member_id'] == Auth::id() &&
                    $buyOrder['order_status'] == Orders::ORDER_NO_MATCH){
                    array_push($buyOrdersArry,$buyOrder);
                }
            }
        }
        $viewParams['buyOrders'] = $buyOrdersArry;
        //我的卖单
        $salesOrders = Cache::get('tradeSales');
        $salesOrdersArry = [];
        if (!empty($salesOrders)){
            foreach ($salesOrders as $k => $salesOrder) {
                if ($salesOrder['sales_member_id'] == Auth::id() &&
                    $salesOrder['order_status'] == Orders::ORDER_NO_MATCH){
                    array_push($salesOrdersArry,$salesOrder);
                }
            }
        }
        $viewParams['salesOrders'] = $salesOrdersArry;

        //交易数量
        $numbers = TradeNumbers::all();
        $item = [];
        foreach ($numbers as $k => $n){
            $item[$k] = $n->number;
        }
        $viewParams['tradeNumbers'] = $item;

        return view('home.trade.buy',$viewParams);
    }

    /**
     * 交易安全密码验证
     * @return false|string
     */
    public function tradeCheck()
    {
        $safePassword = $this->request->input('password');
        if (!Hash::check($safePassword,Auth::user()->safe_password)){
            return $this->dataReturn(['status'=>1044,'message'=>'密码错误']);
        }
        $this->request->session()->put('safeP',time());
        return $this->dataReturn(['status'=>0,'message'=>'验证成功']);
    }

    /**
     * 买入
     * @return false|string
     */
    public function tradeBuy()
    {
        $data = $this->request->input();
        $member = Auth::user();
        $buyOrders = Cache::get('tradeBuy');
        $n = 0;
        if (!empty($buyOrders)){
            foreach ($buyOrders as $buyOrder) {
                if ($buyOrder['buy_member_id'] == $member->id){
                    $n++;
                }
            }
            if ($n == 5){
                return $this->dataReturn(['status'=>1040,'message'=>'最多买入5单']);
            }
        }
        //加入队列匹配
        BuyMatch::dispatch($data,$member)->onQueue('match');

        return $this->dataReturn(['status'=>0,'message'=>'买入成功']);
    }

    /**
     * 卖出
     * @return false|string
     */
    public function tradeSales()
    {
        $member = Auth::user();
        $data = $this->request->input();

        $assets = Cache::get('assets'.$member->id);
        $handRate = SystemSettings::getSysSettingValue('trade_handling_charge');
        $deductNumber = $data['salesNumber']*(1+$handRate);
        if ($assets->balance < $deductNumber){
            return $this->dataReturn(['status'=>1041,'message'=>'余额不足']);
        }
        //限制卖出次数
        $salesOrders = Cache::get('tradeSales');
        $availableSalesTimes = $member->level->sales_times;
        $salesNumber = 0;
        if (!empty($salesOrders)){
            foreach ($salesOrders as $salesOrder) {
                if ($salesOrder['sales_member_id'] == $member->id){
                    $salesNumber++;
                }
            }
            if ($availableSalesTimes <= $salesNumber){
                return $this->dataReturn(['status'=>1042,'message'=>'每天只能卖出'.$availableSalesTimes.'单']);
            }
        }
        $tradeCount = Orders::where('sales_member_id',$member->id)->where('updated_at','>=',date('Y-m-d 0:0:0'))->count();
        if ($availableSalesTimes <= ($tradeCount + $salesNumber)){
            return $this->dataReturn(['status'=>1042,'message'=>'每天只能卖出'.$availableSalesTimes.'单']);
        }
        //临时从余额中扣除币
        $assets->balance -= $deductNumber;
        $assets->blocked_assets += $deductNumber;
        Cache::put('assets'.$member->id,$assets,Carbon::tomorrow());
        //加入卖出队列匹配
        SalesMatch::dispatch($data,$member)->onQueue('match');

        return $this->dataReturn(['status'=>0,'message'=>'委托卖出成功']);
    }

    /**
     * 待处理的订单
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function unprocessedOrder()
    {
        $unOrders = Orders::where('trade_status','<>',Orders::TRADE_FINISHED)->cursor()
            ->filter(function ($orders){
                return $orders->buy_member_id == Auth::id() || $orders->sales_member_id == Auth::id();
            });

        return view('home.trade.unprocessedOrder')->with('unOrders',$unOrders);
    }

    /**
     * 取消委托单
     * @param $orderId
     */
    public function cancelOrder($orderId)
    {
        if (substr($orderId,0,2) == 'hb'){
            $buyOrders = Cache::get('tradeBuy');
            foreach ($buyOrders as $k => $buyOrder) {
                if ($buyOrder['order_id'] == $orderId && $buyOrder['order_status'] == Orders::ORDER_NO_MATCH){
                    array_splice($buyOrders,$k,1);
                    Cache::put('tradeBuy',$buyOrders,Carbon::tomorrow());
                    return $this->dataReturn(['status'=>0,'message'=>'取消成功']);
                }
            }
        }else {
            $salesOrders = Cache::get('tradeSales');
            foreach ($salesOrders as $k => $salesOrder) {
                if ($salesOrder['order_id'] == $orderId && $salesOrder['order_status'] == Orders::ORDER_NO_MATCH){
                    array_splice($salesOrders,$k,1);
                    Cache::put('tradeSales',$salesOrders,Carbon::tomorrow());
                    //恢复资产
                    $handRate = SystemSettings::getSysSettingValue('trade_handling_charge');
                    $blockedNumber = $salesOrder['trade_number'] * (1+$handRate);
                    $salesAssets = Cache::get('assets'.$salesOrder['sales_member_id']);
                    $salesAssets->balance += $blockedNumber;
                    $salesAssets->blocked_assets -= $blockedNumber;
                    Cache::put('assets'.$salesOrder['sales_member_id'],$salesAssets,Carbon::tomorrow());
                    return $this->dataReturn(['status'=>0,'message'=>'取消成功']);
                }
            }
        }
        return $this->dataReturn(['status'=>-1,'message'=>'取消失败，订单已匹配']);
    }

    /**
     * 订单详情
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function orderPreview($id)
    {
        $previews = Orders::where('id',$id)->first();
        //买家信息
        $buyMember = $previews->buyMember;
        $previews->buyMemberCredit = $buyMember->credit;
        $previews->buyMemberPhone = $buyMember->phone;
        $previews->buyMemberW = $buyMember->realNameAuth->weixin;
        //卖家信息
        $salesMember = $previews->salesMember;
        $salesMemberRealNameAuth = $salesMember->realNameAuth;
        $previews->salesMemberName = $salesMemberRealNameAuth->name;
        $previews->salesMemberCredit = $salesMember->credit;
        $previews->salesMemberAlipay = $salesMember->phone;
        $previews->salesMemberBankName = $salesMemberRealNameAuth->bank_name;
        $previews->salesMemberBankCard = $salesMemberRealNameAuth->bank_card;
        $previews->salesMemberW = $salesMemberRealNameAuth->weixin;
        $d = 2*3600 - (time() - date_timestamp_get(date_create($previews->updated_at)));
        $previews->h = (int)($d/3600) > 0?(int)($d/3600):0;
        $previews->i = (int)($d/60%60) > 0?(int)($d/60%60):0;
        $previews->s = $d%60 > 0?:0;

        return view('home.trade.orderPreview')->with('previews',$previews);
    }

    /**
     * 完成付款，上传截图
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function finishPay()
    {
        $file = $this->request->file('pay_img');
        if (empty($file)) return back()->withErrors(['tradeError'=>'请选择要上传的截图'])->withInput();
        if ($file->getSize()/(1024*1024) > 1) return back()->withErrors(['uploadError'=>'请上传小于1M的截图'])->withInput();
        $path = $file->store('public/payImg');
        if (empty($path)){
            return back()->withErrors(['tradeError'=>'上传失败，请稍后重新上传'])->withInput();
        }else{
            $res = Orders::where('id',$this->request->input('id'))->update([
                'payment_img' => substr($path,6),
                'trade_status' => Orders::TRADE_NO_CONFIRM
            ]);
            if ($res){
                return redirect('home/unprocessedOrder');
            }
        }
        return back()->withErrors(['tradeError'=>'系统错误'])->withInput();
    }

    /**
     * 交易确认
     */
    public function finishPayConfirm()
    {
        $orderId = $this->request->input('id');
        $orders = new Orders();
        $res = $orders->finishPayConfirm($orderId);
        if (!$res){
            return back()->withErrors(['tradeError'=>'系统错误'])->withInput();
        }
        return redirect('home/unprocessedOrder');
    }

    /**
     * 交易记录
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function record()
    {
        $orders = Orders::where('trade_status',Orders::TRADE_FINISHED)->cursor()
            ->filter(function ($orders){
               return $orders->buy_member_id == Auth::id() || $orders->sales_member_id == Auth::id();
            });

        return view('home.trade.record')->with('orders',$orders);
    }

    /**
     * 交易中心
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tradeCenter()
    {
        //构造币价走势
        $coinPrice = Coins::orderBy('id','desc')->limit(7)->get();
        $coinPriceArry = [];
        foreach ($coinPrice as $k => $p) {
            $coinPriceArry[$k] = $p->price;
        }
        for ($i=count($coinPriceArry);$i<7;$i++){
            $coinPriceArry[$i] = 0;
        }
        sort($coinPriceArry);
        $coinPriceStr = implode(',',$coinPriceArry);
        //排单数量
        $tradeNumber = TradeNumbers::all();
        //买单
        $buyOrders = $this->getBuyOrders(5);

        return view('home.trade.tradeCenter',
            ['coinPrice'=>$coinPriceStr,'tradeNumber'=>$tradeNumber,'buyOrders'=>$buyOrders]);
    }

    public function paidan($number)
    {
        $buyOrders = $this->getBuyOrders($number);
        if (empty($buyOrders)){
            return $this->dataReturn(['status'=>0,'message'=>'无买单']);
        }else{
            return $this->dataReturn(['status'=>1,'orders'=>$buyOrders]);
        }
    }

    protected function getBuyOrders($number)
    {
        $tradeBuy = Cache::get('tradeBuy');
        $buyOrders = [];
        foreach ($tradeBuy as $b) {
            if (count($buyOrders) > 50){
                break;
            }
            if ($b['trade_number'] == $number && $b['order_status'] == Orders::ORDER_NO_MATCH){
                array_push($buyOrders,$b);
            }
        }
        return $buyOrders;
    }
}
