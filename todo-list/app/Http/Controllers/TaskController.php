<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private Task $task
    ) {}

    public function index()
    {
        $data = $this->task->query()->get();
        
        return view("home", compact('data'));
    }
}
