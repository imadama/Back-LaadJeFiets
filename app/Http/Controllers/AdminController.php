<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Socket;
use App\Models\LaadSessie;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

    public function generateQrCodes(Request $request)
    {

        // Aantal QR codes om te genereren (standaard 5, max 20)
        $count = min($request->input('count', 5), 20);
        $qrCodes = [];

        for ($i = 0; $i < $count; $i++) {
            // Genereer willekeurige ID
            $randomId = 'QR-' . strtoupper(bin2hex(random_bytes(4))); // Bijvoorbeeld: QR-A1B2C3D4
            
            // Genereer QR code data met de random ID
            $qrData = $randomId;
            
            // Genereer QR code in verschillende formaten
            $qrCodeSvg = QrCode::format('svg')
                ->size(300)
                ->margin(2)
                ->errorCorrection('M')
                ->color(0, 0, 0)
                ->backgroundColor(255, 255, 255)
                ->style('square')
                ->eye('square')
                ->generate($qrData);

            $qrCodePng = QrCode::format('png')
                ->size(300)
                ->margin(2)
                ->errorCorrection('M')
                ->generate($qrData);

            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCodePng);

            $qrCodes[] = [
                'id' => $randomId,
                'qr_data' => $qrData,
                'qr_code_svg' => $qrCodeSvg,
                'qr_code_png_base64' => $qrCodeBase64,
                'created_at' => now()->format('Y-m-d H:i:s')
            ];
        }

        return response()->json([
            'message' => 'QR codes successfully generated',
            'total_codes' => count($qrCodes),
            'qr_codes' => $qrCodes
        ]);
    }

    public function generateSingleQrCode(Request $request, $customId = null)
    {

        // Gebruik custom ID of genereer een willekeurige
        $randomId = $customId ?: 'QR-' . strtoupper(bin2hex(random_bytes(4)));
        
        // Genereer QR code data met de random ID
        $qrData = $randomId;
        
        // Genereer QR code in verschillende formaten
        $qrCodeSvg = QrCode::format('svg')
            ->size(400)
            ->margin(2)
            ->errorCorrection('M')
            ->color(0, 0, 0)
            ->backgroundColor(255, 255, 255)
            ->style('square')
            ->eye('square')
            ->generate($qrData);

        $qrCodePng = QrCode::format('png')
            ->size(400)
            ->margin(2)
            ->errorCorrection('M')
            ->generate($qrData);

        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCodePng);

        return response()->json([
            'id' => $randomId,
            'qr_data' => $qrData,
            'qr_code_svg' => $qrCodeSvg,
            'qr_code_png_base64' => $qrCodeBase64,
            'created_at' => now()->format('Y-m-d H:i:s')
        ]);
    }
}
