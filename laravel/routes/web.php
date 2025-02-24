<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

if (env('DEV')) {
    include('tests/file-system.php');
    include('tests/create-new.php');
    include('tests/ms-sql.php');
}

include('controllers/domain.php');

Auth::routes();

Route::get('/home', 'HomeController@index');
