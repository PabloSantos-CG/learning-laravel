@extends('layouts.base')

@section('content')

<div class="subcontainer">
    <ul class="top-box task-list scroll-container">
        @if(empty($data))
        <li>Escreva sua primeira tarefa</li>
        @else
        @foreach ($data as $task)
        <li>
            <span class="{{ !$task['is_pending'] ? 'risk' : '' }}">
                {{ $task['title'] }}
            </span>

            <div class="last-box-item">
                <form
                    action="{{ route('tasks.changeCheckbox', ['id' => $task['id']]) }}"
                    method="post">

                    @csrf
                    @method('PUT')
                    <input
                        class="circle-checkbox"
                        type="checkbox"
                        name="is_pending"
                        {{ !$task['is_pending'] ? 'checked' : '' }}
                        onchange="this.form.submit()">

                </form>

                <a
                    class="edit-task-buttom flex-center"
                    href="{{ route('tasks.editTask', ['id' => $task['id']]) }}">

                    <img src="{{ asset('icons/Edit.svg') }}">
                </a>

                <form
                    action="{{ route('tasks.delete', ['id' => $task['id']]) }}"
                    method="post">

                    @csrf
                    @method('DELETE')
                    <button class="flex-center container-btn-delete" type="submit">
                        <img src="{{ asset('icons/delete.svg') }}">
                    </button>
                </form>
            </div>
        </li>
        @endforeach
        @endif
    </ul>

    <form class="bottom-box" action="{{ route('tasks.create') }}" method="post">
        @csrf
        <input
            type="text"
            name="title"
            id="newTask"
            placeholder="Digite uma nova tarefa">

        <button class="btn-submit" type="submit">Salvar</button>
    </form>
</div>

@endsection('content')