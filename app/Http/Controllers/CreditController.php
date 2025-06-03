<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Credit;

class CreditController extends Controller
{
    public function getBalance(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $credit = Credit::where('user_id', $user->id)->first();

        return response()->json([
            'balance' => $credit ? $credit->credits : 0.00
        ]);
    }

    public function addBalance(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $credit = Credit::where('user_id', $user->id)->first();

        if (!$credit) {
            return response()->json(['error' => 'Credit record not found'], 404);
        }

        $amountToAdd = $request->input('amount');

        if (!is_numeric($amountToAdd) || $amountToAdd <= 0) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        $credit->credits += $amountToAdd;
        $credit->save();

        return response()->json([
            'message' => 'Balance updated successfully',
            'new_balance' => $credit->credits
        ]);
    }
}
