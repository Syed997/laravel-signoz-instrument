<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Http\Resources\PersonResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PersonWriteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:20',
        ]);

        $person = Person::create($validated);

        return new PersonResource($person);
    }

    public function update(Request $request, string $id)
    {
        $person = Person::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
        ]);

        $person->update($validated);

        return new PersonResource($person);
    }

    public function destroy(string $id)
    {
        $person = Person::findOrFail($id);
        $person->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
