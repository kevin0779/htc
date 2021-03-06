﻿@extends('layout.admin-master')
@section('tittle')账单列表 @endsection

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
                <form action="{{url('admin/memberBill')}}" method="post">
                    @csrf
                <div class="text-c">
                    日期：
                    <input type="text" name="date_start" onfocus="WdatePicker({maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}',
                    dateFmt:'yyyy-MM-dd HH:mm:ss'})" id="logmin" value="{{old('date_start')?:date('Y-m-d 00:00:00')}}" class="input-text Wdate" style="width:170px;">
                    -
                    <input type="text" name="date_end" onfocus="WdatePicker({minDate:'#F{$dp.$D(\'logmin\')||\'%y-%M-%d\'}',maxDate:'%y-%M-%d',
                    dateFmt:'yyyy-MM-dd HH:mm:ss'})" id="logmax" value="{{old('date_end')?:date('Y-m-d H:i:s')}}" class="input-text Wdate" style="width:170px;">
                    <input type="text" name="account" value="{{old('account')}}" placeholder="会员账号" style="width:200px" class="input-text">
                    <button class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 查找</button>
                </div>
                </form>
                <div class="cl pd-5 bg-1 bk-gray mt-20">
                    <span class="l">
                        @if(session('permission') == 0 || in_array("admin/memberBillDestroy",session('permission')))
                        <a href="javascript:;" onclick="dataDel('{{url("admin/memberBillDestroy")}}')" class="btn btn-danger radius"><i class="Hui-iconfont">&#xe6e2;</i> 批量删除</a>
                        @endif
                    </span>
                    <span class="r">共有数据：<strong>{{count($bills)}}</strong> 条</span>
                </div>
                <table class="table table-border table-bordered table-bg table-sort">
                    <thead>
                        <tr class="text-c">
                            <th width="25"><input type="checkbox"></th>
                            <th>账号</th>
                            <th>标题</th>
                            <th>内容</th>
                            <th width="150">日期</th>
                            <th width="100">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if(!$bills->isEmpty())
                    @foreach($bills as $b)
                        <tr class="text-c">
                            <td><input type="checkbox" value="{{$b->id}}" class="checkBox"></td>
                            <td>{{$b->member->phone}}</td>
                            <td>{{$b->tittle}}</td>
                            <td>{{$b->operation}}</td>
                            <td>{{$b->created_at}}</td>
                            <td class="td-manage">
                                @if(session('permission') == 0 || in_array("admin/memberBillDestroy",session('permission')))
                                <a title="删除" href="javascript:;" onclick="onesDel(this,'{{url("admin/memberBillDestroy")}}','{{$b->id}}')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>
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
            {"orderable":false,"aTargets":[0,3,5]}// 制定列不参与排序
        ]
    });


</script>
@endsection
