<?php

namespace App\Http\Controllers;

use App\Models\Socket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SocketController extends Controller
{
    /**
     * Get all sockets for the authenticated user.
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
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch sockets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new socket.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'socket_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check for duplicate socket_id for the current user
            $existingSocket = Socket::where('user_id', Auth::id())
                ->where('socket_id', $request->socket_id)
                ->first();

            if ($existingSocket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Socket ID already exists for this user'
                ], 409);
            }

            // Create the socket
            $socket = Socket::create([
                'user_id' => Auth::id(),
                'socket_id' => $request->socket_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Socket created successfully',
                'data' => $socket
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create socket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a socket.
     */
    public function destroy($id)
    {
        try {
            $socket = Socket::where('user_id', Auth::id())->find($id);

            if (!$socket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Socket not found or you do not have permission to delete it'
                ], 404);
            }

            $socket->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Socket deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete socket',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}