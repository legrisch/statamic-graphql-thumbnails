<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
  Route::get('/legrisch/gql-thumbnails', 'SettingsController@index')->name('legrisch.gql-thumbnails.index');
  Route::post('/legrisch/gql-thumbnails', 'SettingsController@update')->name('legrisch.gql-thumbnails.update');
});
