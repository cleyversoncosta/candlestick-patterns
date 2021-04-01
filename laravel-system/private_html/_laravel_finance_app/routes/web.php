<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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

Auth::routes(['verify' => true]);

Route::get('/', 'HomeController@index');

Route::prefix("admin")->middleware(['auth', 'is_admin'])->group(function () {
    Route::get('/', 'Admin\DashboardController@index')->name('admin');
});

Route::prefix("usuario")->middleware(['auth', 'send_welcome_email'])->group(function () {

    Route::get('/', 'User\DashboardController@index')->name('dashboard');

    Route::get('logout', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});
