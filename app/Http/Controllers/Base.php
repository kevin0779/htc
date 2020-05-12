<?php
namespace App\Http\Controllers;

use App\Http\Models\Coins;
use App\Http\Models\MyMiners;
use App\Http\Models\SystemSettings;
use App\Libraries\SMS\SendTemplateSMS;
use App\Http\Models\PhoneTmps;
use Illuminate\Http\Request;

class Base
{
    protected $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * 初始化计算矿机收益
     * @param $miners
     */
    protected function initMiners($miners)
    {
        foreach ($miners as $miner){
            $timeDiff = (int)((time() - date_timestamp_get(date_create($miner->updated_at))) / 3600); //h
            if ($timeDiff > 15*24){ //超过15天未收取则失效
                $miner->run_status = MyMiners::RUN_EXPIRED;
                $miner->save();
                continue;
            }
            $collect = round($miner->nph * $timeDiff, 2);
            $maxCollect = $miner->total_dig - $miner->dug;
            if ($collect >= $maxCollect){
                $collect = $maxCollect;
            }
            $miner->no_collect = $collect;
        }
    }

    protected function initCoin()
    {
        $coins = Coins::orderBy('id','desc')->first();
        $coinPriceBase = SystemSettings::getSysSettingValue('coin_price');
        $coinPriceStep = SystemSettings::getSysSettingValue('coin_price_step');
        if (empty($coins)){
            Coins::create(['price'=>$coinPriceBase]);
        }elseif (date_format($coins->created_at,'Y-m-d') != date('Y-m-d')){
            Coins::create(['price'=>$coins->price+$coinPriceStep]);
        }
    }

    public function getQRcode($url)
    {
        \QRcode::png($url,false,QR_ECLEVEL_L,5,1);
    }

    /**
     * 发送短信验证码
     * @param $phone
     * @return false|mixed|string
     */
    public function sendSMS($phone)
    {
        $phoneTmp = PhoneTmps::where('phone',$phone)->first();
        if (!empty($phoneTmp)){
            $t = time() - date_timestamp_get($phoneTmp->updated_at);
            if($t <= 60){
                return $this->dataReturn(['status'=>1104,'message'=>'发送频繁，请'.(60 - $t).'s后获取']);
            }
        }
        $sendSMS = new SendTemplateSMS();
        $code = rand(1000,9999);
        $sendRes = $sendSMS->sendTemplateSMS($phone, array($code,5), 1);
        $res = json_decode($sendRes,true);
        $res['status'] = 0;
        if ($res['status'] == 0) {
            $tmp = PhoneTmps::updateOrCreate(
                ['phone' => $phone],
                ['code' => $code]
            );
            if (!empty($tmp)) {
                return $this->dataReturn(['status'=>0,'message'=>'发送成功']);
            }else{
                return $this->dataReturn(['status'=>1201,'message'=>'SQL异常']);
            }
        }
        return $res;
    }

    protected function remakeSessionId()
    {
        $this->request->session()->regenerate();
    }

    public function dataReturn($data)
    {
        return json_encode($data);
    }
}
