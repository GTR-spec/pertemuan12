<?php

use Illuminate\Support\Facades\Route;

Route::get('/mahasiswa', function () {
    return view('mahasiswa.index');
});