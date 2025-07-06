<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

// implementar service

class TaskController extends Controller
{
    public function __construct(
        private Task $task
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->task->query()->get()->toArray();

        return view("home", compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = [
            'title' => $request->input('title'),
            'is_pending' => true
        ];

        $this->task->fill($data);
        $this->task->save();

        return \redirect(\route('index'));
    }

    public function update(Request $request, string $id) {
        $cancel = $request->input('cancel');
        
        if(!empty($cancel)) {
            return \redirect(\route('index'));
        }

        $is_pending = $request->input('is_pending');
        
        $task = $this->task->query()->find($id);

        if(!empty($is_pending)) {
            $task->is_pending = \filter_var($is_pending, \FILTER_VALIDATE_BOOL);
        }

        $task->title = $request->input('title');
        $task->save();
        
        return \redirect(\route('index'));
    }

    
    public function editTask(string $id)
    {
        $task = $this->task->query()->find($id)->toArray();

        return \view('edit_task', \compact('task'));
    }

    public function changeCheckbox(string $id)
    {
        $task = $this->task->query()->findOrFail($id);
        $task->is_pending = !$task->is_pending;
        $task->save();

        return \redirect(\route('index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = $this->task->query()->findOrFail($id);
        $task->delete();
        return \redirect(\route('index'));
    }
}
