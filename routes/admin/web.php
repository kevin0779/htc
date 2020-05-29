<?php

Route::group(['prefix'=>'admin','namespace'=>'Admin'],function (){
    Route::any('login','Login@login');
});

Route::group(['prefix'=>'admin','namespace'=>'Admin','middleware'=>'admin.guest'],function (){
    Route::get('index','Index@index');
    Route::get('/','Index@index');
    Route::get('logout','Login@logout');
    Route::get('adminList','Admin@list');
    Route::get('adminAccountStop/{id}','Admin@accountStop');
    Route::get('adminAccountOpen/{id}','Admin@accountOpen');
    Route::any('adminAdd','Admin@add');
    Route::any('adminEdit','Admin@edit');
    Route::post('adminDel','Admin@del');
    Route::get('adminRole','Admin@role');
    Route::any('adminRoleAdd','Admin@roleAdd');
    Route::any('adminRoleEdit','Admin@roleEdit');
    Route::post('adminRoleDel','Admin@roleDel');
    Route::get('adminPermission','Admin@permission');
    Route::any('adminPermissionAdd','Admin@permissionAdd');
    Route::any('adminPermissionEdit','Admin@permissionEdit');
    Route::post('adminPermissionDel','Admin@permissionDel');
    Route::any('systemSetting','System@setting');
    Route::any('systemAdvancedSetting','System@advancedSetting');
    Route::get('systemNotice','System@notice');
    Route::any('systemNoticeAdd','System@noticeAdd');
    Route::any('systemNoticeEdit','System@noticeEdit');
    Route::post('systemNoticeDel','System@noticeDel');
    Route::any('systemLog','System@log');
    Route::post('systemLogDestroy','System@logDestroy');
    Route::get('systemLogDetails','System@logDetails');
    Route::any('memberList','Member@list');
    Route::any('memberEdit','Member@edit');
    Route::post('memberDel','Member@del');
    Route::get('memberLevel','Member@level');
    Route::any('memberLevelAdd','Member@levelAdd');
    Route::any('memberLevelEdit','Member@levelEdit');
    Route::post('memberLevelDel','Member@levelDel');
    Route::any('memberRealName','Member@realName');
    Route::post('memberRealNameCheckEdit','Member@realNameCheck');
    Route::any('memberRealNameEdit','Member@realNameEdit');
    Route::post('memberRealNameDel','Member@realNameDel');
    Route::any('memberAssets','Member@assets');
    Route::any('memberAssetsRechargeEdit','Member@assetsRecharge');
    Route::post('memberAssetsBlockEdit','Member@assetsBlock');
    Route::get('memberAssetsSum','Member@assetsSum');
    Route::post('memberAssetsDel','Member@assetsDel');
    Route::any('memberMiner','Member@myMiner');
    Route::get('memberMinerStop/{id}','Member@myMinerStop');
    Route::any('memberMinerAdd','Member@myMinerAdd');
    Route::post('memberMinerDel','Member@myMinerDel');
    Route::any('memberTeam','Member@team');
    Route::get('memberActivity','Member@activity');
    Route::any('memberActivityAdd','Member@activityAdd');
    Route::any('memberActivityEdit','Member@activityEdit');
    Route::post('memberActivityDel','Member@activityDel');
    Route::any('memberIdeal','Member@ideal');
    Route::post('memberIdealDel','Member@idealDel');
    Route::get('minerList','Miner@list');
    Route::any('minerAdd','Miner@add');
    Route::any('minerEdit','Miner@edit');
    Route::any('minerDel','Miner@del');
    Route::get('imageList','Image@list');
    Route::any('imageAdd','Image@add');
    Route::any('imageEdit','Image@edit');
    Route::post('imageDel','Image@del');
    Route::any('tradeBuyList','Trade@buyList');
    Route::any('tradeBuyAdd','Trade@buyAdd');
    Route::post('tradeBuyDestroy','Trade@buyDestroy');
    Route::post('tradeBuyClear','Trade@buyClear');
    Route::any('tradeSalesList','Trade@salesList');
});
