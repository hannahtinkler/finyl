<?php

Route::get('/', 'AuthenticationController@index')->name('login');
Route::get('/persist', 'AuthenticationController@store')->name('login.persist');
Route::get('/refresh', 'AuthenticationController@update')->name('login.refresh');
Route::get('/logout', 'AuthenticationController@destroy')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/me', 'MeController@index')->name('me');
    Route::get('/artists/{userartist}', 'ArtistsController@show')->name('artists.show');
});
