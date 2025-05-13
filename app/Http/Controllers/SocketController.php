<?php

namespace App\Http\Controllers;

use App\Models\Socket;
use App\Models\ErrorMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SocketController extends Controller
{
    /**
     * Haal alle sockets op voor de geauthenticeerde gebruiker.
     */
    public function index()
    {
        try {
            $sockets = Socket::where('user_id', Auth::id())->get();

            return response()->json([
                'status' => 'success',
                'data' => $sockets
            ]);

        } catch (\Exception $e) {
            ErrorMessage::create([
                'user_id' => Auth::id(),
                'message' => 'Failed to fetch sockets: ' . $e->getMessage(),
                'location' => 'SocketController@index',
                'context' => ['error' => $e->getMessage()]
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Kon sockets niet ophalen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Maak een nieuwe socket aan.
     */
    public function store(Request $request)
    {
        // Valideer de request data
        $validator = Validator::make($request->all(), [
            'socket_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            ErrorMessage::create([
                'user_id' => Auth::id(),
                'message' => 'Validation failed for socket creation',
                'location' => 'SocketController@store',
                'context' => ['errors' => $validator->errors()->toArray()]
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Validatie mislukt',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Controleer of socket_id al bestaat voor een gebruiker
            $existingSocket = Socket::where('socket_id', $request->socket_id)->first();

            if ($existingSocket) {
                ErrorMessage::create([
                    'user_id' => Auth::id(),
                    'message' => 'Socket ID already in use',
                    'location' => 'SocketController@store',
                    'context' => ['socket_id' => $request->socket_id]
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Socket ID wordt al gebruikt door een andere gebruiker'
                ], 409);
            }

            // Maak de socket aan
            $socket = Socket::create([
                'user_id' => Auth::id(),
                'socket_id' => $request->socket_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Socket succesvol aangemaakt',
                'data' => $socket
            ], 201);

        } catch (\Exception $e) {
            ErrorMessage::create([
                'user_id' => Auth::id(),
                'message' => 'Failed to create socket: ' . $e->getMessage(),
                'location' => 'SocketController@store',
                'context' => ['error' => $e->getMessage()]
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Kon socket niet aanmaken',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verwijder een socket.
     */
    public function destroy($id)
    {
        try {
            $socket = Socket::where('user_id', Auth::id())->find($id);

            if (!$socket) {
                ErrorMessage::create([
                    'user_id' => Auth::id(),
                    'message' => 'Socket not found or unauthorized',
                    'location' => 'SocketController@destroy',
                    'context' => ['socket_id' => $id]
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Socket niet gevonden of u heeft geen toestemming om deze te verwijderen'
                ], 404);
            }

            $socket->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Socket succesvol verwijderd'
            ]);

        } catch (\Exception $e) {
            ErrorMessage::create([
                'user_id' => Auth::id(),
                'message' => 'Failed to delete socket: ' . $e->getMessage(),
                'location' => 'SocketController@destroy',
                'context' => ['error' => $e->getMessage()]
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Kon socket niet verwijderen',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}