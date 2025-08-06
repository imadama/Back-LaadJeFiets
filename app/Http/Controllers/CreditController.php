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

    public function getUserBalance(Request $request, $userId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // In a production environment, you might want to add role-based access control here
        // to ensure only admins or the user themselves can access this information

        $credit = Credit::where('user_id', $userId)->first();

        return response()->json([
            'user_id' => $userId,
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

    public function adminAdjustBalance(Request $request, $userId)
    {
        $adminUser = $request->user();

        if (!$adminUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if the current user is admin (you might want to add proper role checking here)
        // For now, we'll assume all authenticated users can perform this action
        // In a real application, you'd check for admin role here

        $credit = Credit::where('user_id', $userId)->first();

        if (!$credit) {
            // Create a new credit record if it doesn't exist
            $credit = new Credit();
            $credit->user_id = $userId;
            $credit->credits = 0;
            $credit->save();
        }

        $amount = $request->input('amount');
        $operation = $request->input('operation', 'set'); // set, add, subtract

        if (!is_numeric($amount)) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        switch ($operation) {
            case 'add':
                $credit->credits += $amount;
                break;
            case 'subtract':
                $credit->credits -= $amount;
                break;
            case 'set':
            default:
                $credit->credits = $amount;
                break;
        }

        // Ensure credits don't go below 0
        if ($credit->credits < 0) {
            $credit->credits = 0;
        }

        $credit->save();

        return response()->json([
            'message' => 'Credits adjusted successfully',
            'user_id' => $userId,
            'operation' => $operation,
            'amount' => $amount,
            'new_balance' => $credit->credits
        ]);
    }

    public function adminSetBalance(Request $request, $userId)
    {
        $adminUser = $request->user();

        if (!$adminUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Debug: show all request data to help troubleshoot
        $requestData = $request->all();
        $requestMethod = $request->method();
        $contentType = $request->header('Content-Type');
        
        // Temporary debug response
        if (empty($requestData)) {
            return response()->json([
                'debug_info' => [
                    'method' => $requestMethod,
                    'content_type' => $contentType,
                    'all_data' => $requestData,
                    'raw_body' => $request->getContent(),
                    'has_amount' => $request->has('amount'),
                    'amount_value' => $request->input('amount')
                ],
                'error' => 'No data received. Please check your request format.'
            ], 400);
        }
        
        // Accept both 'amount' and 'balance' field names for flexibility
        $rules = [];
        if ($request->has('amount')) {
            $rules['amount'] = 'required|numeric|min:0';
        } elseif ($request->has('balance')) {
            $rules['balance'] = 'required|numeric|min:0';
        } else {
            return response()->json(['error' => 'Either amount or balance field is required'], 400);
        }
        
        $validated = $request->validate($rules);
        
        // Get the amount from either field
        $amount = $validated['amount'] ?? $validated['balance'];

        $credit = Credit::where('user_id', $userId)->first();

        if (!$credit) {
            // Create a new credit record if it doesn't exist
            $credit = new Credit();
            $credit->user_id = $userId;
        }

        $credit->credits = $amount;
        $credit->save();

        return response()->json([
            'message' => 'Credits set successfully',
            'user_id' => $userId,
            'new_balance' => $credit->credits
        ]);
    }

    public function deductCredits(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Niet geautoriseerd'], 401);
        }

        // Valideer de benodigde velden in de request
        $validated = $request->validate([
            'minutes' => 'required|integer|min:1',
            'tariff_per_kwh' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0'
        ]);

        $minuten = $validated['minutes'];
        $tariefPerKwh = $validated['tariff_per_kwh'];
        $bedrag = $validated['amount'];

        // Rond het bedrag af op 2 decimalen
        $bedragAfTeTrekken = round($bedrag, 2);

        if ($bedragAfTeTrekken <= 0) {
            return response()->json(['error' => 'Ongeldig af te trekken bedrag'], 400);
        }

        $credit = Credit::where('user_id', $user->id)->first();

        if (!$credit) {
            return response()->json(['error' => 'Credit record niet gevonden'], 404);
        }

        // Controleer of de gebruiker genoeg credits heeft
        if ($credit->credits < $bedragAfTeTrekken) {
            return response()->json([
                'error' => 'Onvoldoende credits',
                'huidig_saldo' => $credit->credits,
                'benodigd_bedrag' => $bedragAfTeTrekken
            ], 400);
        }

        // Trek de credits af
        $credit->credits -= $bedragAfTeTrekken;
        $credit->save();

        return response()->json([
            'boodschap' => 'Credits succesvol afgetrokken',
            'afgetrokken_bedrag' => $bedragAfTeTrekken,
            'nieuw_saldo' => $credit->credits,
            'details' => [
                'minuten' => $minuten,
                'tarief_per_kwh' => $tariefPerKwh,
                'origineel_bedrag' => $bedrag
            ]
        ]);
    }
}
