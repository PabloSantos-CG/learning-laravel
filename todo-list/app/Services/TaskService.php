<?php

namespace App\Services;

use App\Models\Task;

class TaskService
{
    public function __construct(
        private Task $task
    ) {}

    
}
