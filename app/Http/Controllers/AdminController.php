<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Socket;
use App\Models\LaadSessie;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function getRoleFromUser($account_id)
    {
        $user = User::find($account_id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'role' => $user->role
        ]);
    }

    public function getStats()
    {
        // Totaal aantal kWh berekenen uit alle sessies
        $totalKwh = LaadSessie::sum('final_energy');

        // Totaal aantal gebruikers
        $totalUsers = User::count();

        // Aantal actieve sockets (sockets die momenteel in een actieve sessie zitten)
        $activeSockets = Socket::whereHas('laadSessies', function ($query) {
            $query->whereNull('stop_time');
        })->count();

        return response()->json([
            'totalKwh' => $totalKwh,
            'totalUsers' => $totalUsers,
            'activeSockets' => $activeSockets
        ]);
    }
}
