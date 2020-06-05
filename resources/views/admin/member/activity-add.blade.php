@extends('layout.admin-master')
@section('tittle')添加购买活动 @endsection

@section('container')
<article class="cl pd-20">
	<form action="{{url('admin/memberActivityAdd')}}" method="post" class="form form-horizontal">
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>直推人数：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="number" class="input-text" placeholder="数量" name="subordinate" min="0" style="width: 200px" required>
			</div>
		</div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>要求算力（G）：</label>
            <div class="formControls col-xs-8 col-sm-9">
                <input type="number" class="input-text" placeholder="算力G" name="hashrate" min="0" step="0.1" style="width: 200px" required>
            </div>
        </div>
        <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>赠送矿机类型：</label>
            <div class="formControls col-xs-8 col-sm-9">
                <span class="select-box inline">
                    <select name="minerType" class="select">
                        @foreach($miners as $miner)
                            <option value="{{$miner->id}}">{{$miner->tittle}}</option>
                        @endforeach
                    </select>
                </span>
            </div>
        </div>
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>赠送矿机数量：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="number" class="input-text" name="number" value="1" min="0" style="width: 200px" required>
			</div>
		</div>
		<div class="row cl">
			<div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
				<input class="btn btn-primary radius" type="submit" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
			</div>
		</div>
	</form>
</article>
@endsection

@section('js')
<script type="text/javascript" src="{{asset('static/admin/lib/jqueryValidation/jquery.validate.js')}}"></script>
<script type="text/javascript" src="{{asset('static/admin/lib/jqueryValidation/validate-methods.js')}}"></script>
<script type="text/javascript" src="{{asset('static/admin/lib/jqueryValidation/messages_zh.js')}}"></script>
<script type="text/javascript">
$(function(){
	$('.skin-minimal input').iCheck({
		checkboxClass: 'icheckbox-blue',
		radioClass: 'iradio-blue',
		increaseArea: '20%'
	});

	$("form").validate({
		rules:{
			buyNumber:{
				required:true,
			},
            minerType:{
                required:true,
            },
			number:{
				required:true,
			},
		},
		onkeyup:false,
		focusCleanup:true,
		success:"valid",
		submitHandler:function(form){
			$(form).ajaxSubmit({
                success: function (data) {
                    if (data.status == 0){
                        layer.msg('添加成功',{icon:6,time:1000});
                        closeLayer();
                    }else {
                        layer.msg(data.message,{icon:5,time:1000});
                    }
                },
                dataType: 'json'
            });
		}
	});
});
</script>
@endsection
