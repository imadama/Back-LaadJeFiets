<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Socket;

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
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'string|max:255',
            'postal_code' => 'string|max:10',
            'country' => 'string|max:255',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            'user_id' => 'required|exists:users,id',
            'tariff_per_kwh' => 'numeric'
        ]);

        $location = Location::create($validated);
        return response()->json($location, 201);
    }

    public function userLocations($userId)
    {
        $locations = Location::where('user_id', $userId)->get();
        return response()->json($locations);
    }

    public function show($locations_id)
    {
        $location = Location::findOrFail($locations_id);
        return response()->json($location);
    }

    public function showSockets($locations_id)
    {
        $sockets = Socket::where('location_id', $locations_id)->get();
        return response()->json($sockets);
    }
} 

