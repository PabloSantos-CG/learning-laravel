@extends('layouts.base')

@section('content')

<div class="subcontainer">
    <ul class="top-box task-list scroll-container">
        @if(empty($data))
        <li>Escreva sua primeira tarefa</li>
        @else
        @foreach ($data as $task)
        <li>
            <span class="{{ $task['is_pending'] ? 'risk' : '' }}">
                {{ $task['title'] }}
            </span>

            <div class="last-box-item">
                <form action="" method="post">
                    @csrf
                    <button class="flex-center container-btn-check" type="submit">
                        <img src="{{ asset('icons/Check-circle.svg') }}">
                    </button>
                </form>

                <a class="edit-task-buttom flex-center" href="">
                    <img src="{{ asset('icons/Edit.svg') }}">
                </a>

                <form action="" method="post">
                    @csrf
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

        <button type="submit">Salvar</button>
    </form>
</div>

@endsection('content')