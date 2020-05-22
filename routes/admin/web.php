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
});
