<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index()
    {
        $people = Person::all();
        return view('people.index', compact('people'));
    }

    public function create()
    {
        return view('people.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
        ]);

        Person::create($request->all());
        return redirect()->route('people.index')
            ->with('success', 'Person created successfully.');
    }

    public function show(Person $person)
    {
        return view('people.show', compact('person'));
    }

    public function edit(Person $person)
    {
        return view('people.edit', compact('person'));
    }

    public function update(Request $request, Person $person)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
        ]);

        $person->update($request->all());
        return redirect()->route('people.index')
            ->with('success', 'Person updated successfully.');
    }

    public function destroy(Person $person)
    {
        $person->delete();
        return redirect()->route('people.index')
            ->with('success', 'Person deleted successfully.');
    }
}
