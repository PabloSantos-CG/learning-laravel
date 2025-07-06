<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;


Route::get('/', [TaskController::class, "index"])->name('index');
Route::post('/tasks', [TaskController::class, "store"])->name('tasks.create');
Route::put('/tasks/{id}/change-checkbox', [TaskController::class, "changeCheckbox"])->name('tasks.changeCheckbox');
Route::delete('/tasks/{id}', [TaskController::class, "destroy"])->name('tasks.delete');
Route::get('/tasks/edit/{id}', [TaskController::class, "editTask"])->name('tasks.editTask');
Route::put('/tasks/{id}', [TaskController::class, "update"])->name('tasks.update');
