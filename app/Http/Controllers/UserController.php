<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function create()
    {
        return view('user.create');
    }

    public function store(Request $request)
    {
        // Validate data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        // Process data
        // Example: Save to database
        // User::create($validated);

        return redirect()->back()->with('success', 'User saved successfully!');
    }
}