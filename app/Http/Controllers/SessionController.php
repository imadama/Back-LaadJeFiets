<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpMqtt\Client\MqttClient;
use App\Models\LaadSessie;
use Illuminate\Support\Facades\Auth;
use InfluxDB2\Client;
use App\Jobs\StopSessionJob;

/**
 * SessionController
 * 
 * Deze controller beheert de laadsessies voor elektrische fietsen.
 * Het implementeert de functionaliteit voor het starten en stoppen van laadsessies,
 * inclusief het bijhouden van energieverbruik via InfluxDB en het besturen van
 * sockets via MQTT.
 */
class SessionController extends Controller
{
    /**
     * Start een nieuwe laadsessie
     * 
     * Deze methode:
     * 1. Haalt het socket ID op en normaliseert deze
     * 2. Leest het huidige energieverbruik uit InfluxDB
     * 3. Maakt een nieuwe laadsessie aan in de database
     * 4. Schakelt de laadpaal aan via MQTT
     * 5. Plant automatische stop als duration_minutes is opgegeven
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(Request $request)
    {
        $socketId = $request->input('socket_id');
        $durationMinutes = $request->input('duration_minutes');
        
        // Extract socket ID from the request and remove 'charger_' prefix if present
        $socketId = $request->input('socket_id');
        if (strpos($socketId, 'charger_') === 0) {
            $socketId = substr($socketId, strlen('charger_'));
        }
        // Get current energy reading from InfluxDB
        $client = new Client([
            'url' => env('INFLUXDB_URL'),
            'token' => env('INFLUXDB_TOKEN'),
            'org' => env('INFLUXDB_ORG'),
            'bucket' => env('INFLUXDB_BUCKET'),
        ]);

        $query = $client->createQueryApi();
        $flux = <<<EOT
from(bucket: "mqttdatabase")
  |> range(start: -1m)
  |> filter(fn: (r) => r["_measurement"] == "charger_{$socketId}/SENSOR")
  |> filter(fn: (r) => r["_field"] == "Total")
  |> last()
EOT;

        $result = $query->query($flux);
        $totalEnergy = $result[0]->records[0]->getValue();

        // Create new charging session with energy reading
        $session = LaadSessie::create([
            'user_id' => Auth::id(),
            'socket_id' => $socketId,
            'duration_minutes' => $durationMinutes,
            'start_time' => now(),
            'total_energy_begin' => $totalEnergy
        ]);

        $mqtt = new MqttClient('amafamily.nl', 1883, 'laravel-client');
        $mqtt->connect(null, true, []);

        // Zet socket aan
        $mqtt->publish('cmnd/charger_'.$socketId.'/Power', 'ON', 0);
        $mqtt->disconnect();

        // Als duration_minutes is opgegeven, plan automatische stop
        if ($durationMinutes && $durationMinutes > 0) {
            StopSessionJob::dispatch($session->id)->delay(now()->addMinutes($durationMinutes));
        }

        return response()->json([
            'status' => 'started',
            'session_id' => $session->id,
            'duration_minutes' => $durationMinutes
        ]);
    }

    /**
     * Stop een actieve laadsessie
     * 
     * Deze methode:
     * 1. Haalt het socket ID op en normaliseert deze
     * 2. Leest het huidige energieverbruik uit InfluxDB
     * 3. Vindt de actieve sessie voor deze gebruiker
     * 4. Berekent het totale energieverbruik
     * 5. Update de sessie in de database
     * 6. Schakelt de laadpaal uit via MQTT
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function stop()
    {
        $socketId = request('socket_id');
        
        // Extract socket ID from the request and remove 'charger_' prefix if present
        $socketId = request('socket_id');
        if (strpos($socketId, 'charger_') === 0) {
            $socketId = substr($socketId, strlen('charger_'));
        }
        
        // Get current energy reading from InfluxDB
        $client = new Client([
            'url' => env('INFLUXDB_URL'),
            'token' => env('INFLUXDB_TOKEN'),
            'org' => env('INFLUXDB_ORG'),
            'bucket' => env('INFLUXDB_BUCKET'),
        ]);

        $query = $client->createQueryApi();
        $flux = <<<EOT
from(bucket: "mqttdatabase")
  |> range(start: -1m)
  |> filter(fn: (r) => r["_measurement"] == "charger_{$socketId}/SENSOR")
  |> filter(fn: (r) => r["_field"] == "Total")
  |> last()
EOT;

        $result = $query->query($flux);
        $totalEnergy = number_format($result[0]->records[0]->getValue(), 3, '.', '');

        // Find the latest charging session for this user
        $session = LaadSessie::where('user_id', Auth::id())
            ->where('socket_id', $socketId)
            ->whereNull('stop_time')
            ->latest()
            ->first();

        if ($session) {
            // Calculate final energy (end - begin)
            $finalEnergy = $totalEnergy - $session->total_energy_begin;

            // Update the session with final energy reading
            $session->update([
                'stop_time' => now(),
                'total_energy_end' => $totalEnergy,
                'final_energy' => $finalEnergy
            ]);

            $mqtt = new MqttClient('amafamily.nl', 1883, 'laravel-client');
            $mqtt->connect(null, true, []);

            // Zet socket uit
            $mqtt->publish('cmnd/charger_'.$socketId.'/Power', 'OFF', 0);
            $mqtt->disconnect();
        }

        return response()->json(['status' => 'stopped']);
    }
}