@extends('layouts.app')

@section('content')
<div class="row mb-3">
    <div class="col-lg-12 margin-tb">
        <div class="float-end">
            <a class="btn btn-success" href="{{ route('people.create') }}">Add New Person</a>
        </div>
    </div>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th width="280px">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($people as $person)
        <tr>
            <td>{{ $person->id }}</td>
            <td>{{ $person->name }}</td>
            <td>{{ $person->age }}</td>
            <td>
                <form action="{{ route('people.destroy', $person->id) }}" method="POST">
                    <a class="btn btn-info" href="{{ route('people.show', $person->id) }}">Show</a>
                    <a class="btn btn-primary" href="{{ route('people.edit', $person->id) }}">Edit</a>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
