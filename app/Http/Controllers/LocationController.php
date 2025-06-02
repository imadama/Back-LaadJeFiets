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

    public function destroy($locations_id)
    {
        $location = Location::findOrFail($locations_id);
        $location->delete();
        return response()->json(['message' => 'Location deleted successfully']);
    }

    public function destroySocket($locations_id, $socket_id)
    {
        $socket = Socket::where('location_id', $locations_id)
            ->where('id', $socket_id)
            ->firstOrFail();
            
        $socket->update(['location_id' => null]);
        return response()->json(['message' => 'Socket removed from location successfully']);
    }

    /**
     * Wijs een socket toe aan een locatie.
     */
    public function assignSocket($locationId, $socketId)
    {
        try {
            // Controleer of de locatie bestaat
            $location = Location::findOrFail($locationId);
            
            // Controleer of de socket bestaat
            $socket = Socket::findOrFail($socketId);
            
            // Update de socket met de nieuwe locatie
            $socket->update([
                'location_id' => $locationId,
                'address' => $location->address
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Socket succesvol toegewezen aan locatie',
                'data' => $socket
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kon socket niet toewijzen aan locatie',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 

