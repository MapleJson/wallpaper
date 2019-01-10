<?php

Route::get('/', 'index/bing');
Route::get('/360', 'index/spider360')->name('360');
Route::get('/souGou', 'index/souGou')->name('souGou');
Route::get('/download', 'index/download')->name('download');
