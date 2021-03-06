<?php


namespace App\Http\Controllers\Home;

use App\Http\Controllers\Base;
use App\Http\Models\Assets;
use App\Http\Models\Bills;
use App\Http\Models\Images;
use App\Http\Models\Miners;
use App\Http\Models\MyMiners;
use App\Http\Models\RealNameAuths;
use App\Http\Models\SystemSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Index extends Base
{
    public function index()
    {
        $miners = Miners::all();
        $images = Images::where('type','home-index-header')->get();
        return view('home.index',['miners'=>$miners,'images'=>$images]);
    }

    /**
     * 签到
     * @return false|string
     */
    public function qiandao()
    {
        $id = Auth::id();
        $realName = new RealNameAuths();
        if (!$realName->realNameAuthCheck()){
            return $this->dataReturn(['status'=>1033,'message'=>'请先完成实名认证']);
        }
        $q = Cache::get('qiandao'.$id);
        if (!empty($q)){
            return $this->dataReturn(['status'=>1032,'message'=>'今天已签到，请明天再来']);
        }
        $give = SystemSettings::getSysSettingValue('qiandao_give_coin');
        //更新缓存
        $assetsCache = Cache::get('assets'.$id);
        $assetsCache->balance += $give;
        Cache::put('assets'.Auth::id(),$assetsCache,Carbon::tomorrow());
        Cache::put('qiandao'.Auth::id(),time(),Carbon::tomorrow());
        //签到奖励
        $a = Assets::find($assetsCache->id);
        $a->balance += $give;
        $a->save();

        Bills::createBill($id,'余额-签到赠送','+'.$give);
        return $this->dataReturn(['status'=>0,'message'=>'签到成功']);
    }

    /**
     * 租用矿机
     * @return false|string
     */
    public function rent()
    {
        $data = $this->request->input();
        $memberId = Auth::id();
        $max = Miners::where('tittle',$data['miner_tittle'])->first()->rent_max;
        $myMinersCount = MyMiners::where('member_id',Auth::id())->where('miner_tittle',$data['miner_tittle'])
            ->count();
        if ($max < $myMinersCount){
            return $this->dataReturn(['status'=>1031,'message'=>'超过租用数量，最多租用'.$max.'台']);
        }
        $assets = Cache::get('assets'.$memberId);
        if ($data['coin_number'] > $assets->balance){
            return $this->dataReturn(['status'=>1030,'message'=>'余额不足']);
        }
        $res = MyMiners::create([
           'member_id' => $memberId,
            'miner_id' => $data['id'],
            'miner_tittle' => $data['miner_tittle'],
            'total_dig' => $data['total_dig'],
            'hashrate' => $data['hashrate'],
            'nph' => $data['nph'],
            'runtime' => $data['runtime'],
            'remaining_time' => $data['runtime'],
            'run_status' => MyMiners::RUNNING
        ]);
        if ($res){
            $assets->balance -= $data['coin_number'];
            $assets->save();

            Cache::put('assets'.$memberId,$assets,Carbon::tomorrow());
            Bills::createBill($memberId,'余额-租用矿机','-'.$data['coin_number']);
        }
        return $this->dataReturn(['status'=>0,'message'=>'租用成功']);
    }
}
