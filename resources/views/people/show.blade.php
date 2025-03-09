@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="float-start">
            <h2>Show Person</h2>
        </div>
        <div class="float-end">
            <a class="btn btn-primary" href="{{ route('people.index') }}">Back</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
        <div class="form-group">
            <strong>Name:</strong>
            {{ $person->name }}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
        <div class="form-group">
            <strong>Age:</strong>
            {{ $person->age }}
        </div>
    </div>
</div>
@endsection
