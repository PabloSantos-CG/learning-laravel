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
            'is_pending' => false
        ];

        $this->task->fill($data);
        $this->task->save();

        return \redirect(\route('index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
