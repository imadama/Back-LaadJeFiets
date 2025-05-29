<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::all();
        return response()->json($locations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'tariff_per_kwh' => 'required|numeric',
        ]);

        $location = Location::create($validated);
        return response()->json($location, 201);
    }

    public function userLocations($user_id)
    {
        $locations = Location::where('user_id', $user_id)->get();
        return response()->json($locations);
    }
} 