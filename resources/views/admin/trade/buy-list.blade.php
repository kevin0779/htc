﻿@extends('layout.admin-master')
@section('tittle')买入列表 @endsection

@section('header')
    @component('layout.admin-header')@endcomponent
@endsection

@section('aside')
    @component('layout.admin-menu')@endcomponent
@endsection

@section('container')
    <section class="Hui-article-box">
        <nav class="breadcrumb">
            <a class="btn btn-success radius l" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a> </nav>
        <div class="Hui-article">
            <article class="cl pd-20">
                <form action="{{url('admin/tradeBuyList')}}" method="post">
                    @csrf
                <div class="text-c">
                    <span class="select-box inline">
                        <select name="matchStatus" class="select">
                            <option value="-1">所有状态</option>
                            <option value="4">已匹配</option>
                            <option value="5">未匹配</option>
                        </select>
                    </span>
                    <span class="select-box inline">
                        <select name="number" class="select">
                            <option value="-1">所有数量</option>
                            @foreach($numbers as $n)
                            <option value="{{$n->number}}">{{$n->number}}</option>
                            @endforeach
                        </select>
                    </span>
                    <input type="search" name="account" placeholder="会员账号" value="{{old('account')}}" style="width:200px" class="input-text">
                    <button class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 查找</button>
                </div>
                </form>
                <div class="cl pd-5 bg-1 bk-gray mt-20">
                    <span class="l">
                        @if(session('permission') == 0 || in_array("admin/tradeBuyDestroy",session('permission')))
                        <a href="javascript:;" onclick="dataDel('{{url("admin/tradeBuyDestroy")}}')" class="btn btn-danger radius"><i class="Hui-iconfont">&#xe6e2;</i> 批量删除</a>
                        <a href="javascript:;" onclick="queueClear('{{url("admin/tradeBuyClear")}}')" class="btn btn-danger radius"><i class="Hui-iconfont">&#xe6e2;</i> 清空买单</a>
                        @endif
                        @if(session('permission') == 0 || in_array("admin/tradeBuyAdd",session('permission')))
                            <a href="javascript:;" onclick="add('添加买单','{{url("admin/tradeBuyAdd")}}','800','400')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i>添加买单</a>
                        @endif
                    </span>
                    <span class="r">共有数据：<strong>{{count($buys)}}</strong> 条</span>
                </div>
                <table class="table table-border table-bordered table-bg table-sort">
                    <thead>
                        <tr class="text-c">
                            <th width="25"><input type="checkbox"></th>
                            <th>订单号</th>
                            <th>账号</th>
                            <th>买入数量</th>
                            <th>价格</th>
                            <th width="150">挂单时间</th>
                            <th width="100">状态</th>
                            <th width="100">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if(!empty($buys))
                    @foreach($buys as $b)
                        <tr class="text-c">
                            <td><input type="checkbox" value="{{$b['order_id']}}" class="checkBox"></td>
                            <td>{{$b['order_id']}}</td>
                            <td>{{$b['buy_member_phone']}}</td>
                            <td>{{$b['trade_number']}}</td>
                            <td>{{$b['trade_price']}}</td>
                            <td>{{$b['created_at']}}</td>
                            <td><div class="label radius {{$b['order_status']==\App\Http\Models\Orders::ORDER_MATCHED?'label-success':''}}">
                                    {{$b['order_status']==\App\Http\Models\Orders::ORDER_MATCHED?'已匹配':'待匹配'}}</div></td>
                            <td class="td-manage">
                                @if(session('permission') == 0 || in_array("admin/tradeBuyDestroy",session('permission')))
                                <a title="删除" href="javascript:;" onclick="onesDel(this,'{{url("admin/tradeBuyDestroy")}}','{{$b['order_id']}}')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @endif
                    </tbody>
                </table>
            </article>
        </div>
    </section>
@endsection

@section('js')
<script type="text/javascript" src="{{asset('static/admin/lib/datePicker/WdatePicker.js')}}"></script>
<script type="text/javascript" src="{{asset('static/admin/lib/dataTables/jquery.dataTables.min.js')}}"></script>
<script type="text/javascript" src="{{asset('static/admin/lib/layerPage/laypage.js')}}"></script>
<script type="text/javascript">

    $('.table-sort').dataTable({
        "aaSorting": [[ 1, "asc" ]],//默认第几个排序
        "bStateSave": true,//状态保存
        "aoColumnDefs": [
            //{"bVisible": false, "aTargets": [ 3 ]} //控制列的隐藏显示
            {"orderable":false,"aTargets":[0,7]}// 制定列不参与排序
        ]
    });

    function queueClear(url) {
        layer.confirm('确认清空吗？',function () {
            $.post(url);
            refresh();
        });
    }

</script>
@endsection
