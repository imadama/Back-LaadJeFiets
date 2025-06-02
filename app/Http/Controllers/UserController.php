<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ErrorMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getAllUsers()
    {
        try {
            $users = User::all()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'profile_picture' => $user->profile_picture_url,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $users
            ]);

        } catch (\Exception $e) {
            ErrorMessage::create([
                'user_id' => Auth::id(),
                'message' => 'Failed to fetch users: ' . $e->getMessage(),
                'location' => 'UserController@getAllUsers',
                'context' => ['error' => $e->getMessage()]
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Kon gebruikers niet ophalen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Check if user exists
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gebruiker niet gevonden'
                ], 404);
            }

            // Check if user is trying to delete themselves
            if (Auth::id() == $id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'U kunt uw eigen account niet verwijderen'
                ], 403);
            }

            // Check if user has admin role
            if (Auth::user()->role !== 'Admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'U heeft geen toestemming om gebruikers te verwijderen'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Gebruiker succesvol verwijderd'
            ]);

        } catch (\Exception $e) {
            ErrorMessage::create([
                'user_id' => Auth::id(),
                'message' => 'Failed to delete user: ' . $e->getMessage(),
                'location' => 'UserController@destroy',
                'context' => ['error' => $e->getMessage()]
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Kon gebruiker niet verwijderen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Check if user exists
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gebruiker niet gevonden'
                ], 404);
            }

            // Check if user has admin role or is updating their own profile
            if (Auth::user()->role !== 'Admin' && Auth::id() != $id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'U heeft geen toestemming om deze gebruiker te bewerken'
                ], 403);
            }

            // Validate request data
            $validator = Validator::make($request->all(), [
                'username' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
                'role' => 'sometimes|string|in:User,Admin,Reseller',
                'profile_picture' => 'sometimes|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validatie mislukt',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Only allow role updates by admins
            if ($request->has('role') && Auth::user()->role !== 'Admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'U heeft geen toestemming om rollen te wijzigen'
                ], 403);
            }

            // Update user
            $user->update($request->only([
                'username',
                'email',
                'role',
                'profile_picture'
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Gebruiker succesvol bijgewerkt',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            ErrorMessage::create([
                'user_id' => Auth::id(),
                'message' => 'Failed to update user: ' . $e->getMessage(),
                'location' => 'UserController@update',
                'context' => ['error' => $e->getMessage()]
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Kon gebruiker niet bijwerken',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 