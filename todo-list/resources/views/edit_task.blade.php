@extends('layouts.base')

@section('content')

<form
    action="{{ route('tasks.update', ['id' => $task['id']]) }}"
    method="post"
    id="form-edit">
    @csrf
    @method('PUT')

    <div class="top-box" id="top-box-form-edit">
        <label class="direction-column" for="title" id="title-label">
            Título:
            <input
                class="input"
                type="text"
                name="title"
                value="{{ $task['title'] }}"
                id="title">
        </label>

        <div class="direction-column" id="container-select-status">
            <label for="category">Status:</label>
            <select class="input-select" name="is_pending" id="category">
                <option disabled selected>
                    {{ $task['is_pending'] ? 'Pendente' : 'Concluído' }}
                </option>
                @if($task['is_pending'])
                <option value="false">Concluído</option>
                @else
                <option value="true">Pendente</option>
                @endif
            </select>
        </div>
    </div>

    <div class="bottom-box" id="bottom-box-form-edit">
        <input
            class="btn-submit"
            type="submit"
            name="save"
            value="Salvar">
        <input
            class="btn-cancel"
            type="submit"
            name="cancel"
            value="Cancelar">
    </div>
</form>

@endsection('content')