@extends('layout.master')
@section('tittle')邀请好友@endsection
@section('header')@component('layout.header')@endcomponent @endsection

@section('container')
<div class="link">
    <div class="qrcode">
        <img src="{{url('home/qrcode')}}">
    </div>
</div>
@endsection

@section('js')
<script>
    $(function () {
        showHeaderBack();
    });
</script>
@endsection
