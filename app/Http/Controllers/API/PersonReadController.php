<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Http\Resources\PersonResource;
use Illuminate\Support\Facades\DB;

class PersonReadController extends Controller
{
    public function index()
    {
        $people = Person::all();
        return PersonResource::collection($people);
    }

    public function show(string $id)
    {
        if ($id == '5000') {
            // Attempt a query on a non-existent table
            DB::table('non_existent_table')->where('id', $id)->first();
        }
        $person = Person::findOrFail($id);
        return new PersonResource($person);
    }
}
