<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Http\Resources\PersonResource;

class PersonReadController extends Controller
{
    public function index()
    {
        $people = Person::all();
        return PersonResource::collection($people);
    }

    public function show(string $id)
    {
        $person = Person::findOrFail($id);
        return new PersonResource($person);
    }
}
