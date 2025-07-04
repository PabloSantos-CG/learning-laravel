<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaskController::class, "index"])->name('index');
Route::post('/tasks/create', [TaskController::class, "store"])->name('tasks.create');
