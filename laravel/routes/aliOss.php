<?php
Route::get('oss/policy',function(){return app('aliOss')->policy('laravel-test-dir/');});
Route::post('oss/callback', function(){
    return app('aliOss')->callback(function($fileInfo){
        Storage::put('fileInfo.txt', json_encode($fileInfo));
    });
});
Route::get('oss/test',function(){return view('oss-test');});