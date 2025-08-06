<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\LaadSessie;
use PhpMqtt\Client\MqttClient;
use InfluxDB2\Client;
use Illuminate\Support\Facades\Log;

class StopSessionJob implements ShouldQueue
{
    use Queueable;

    private $sessionId;

    /**
     * Create a new job instance.
     */
    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find the session
            $session = LaadSessie::find($this->sessionId);
            
            if (!$session || $session->stop_time) {
                // Session doesn't exist or is already stopped
                return;
            }

            $socketId = $session->socket_id;
            
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
            
            if (!empty($result) && !empty($result[0]->records)) {
                $totalEnergy = number_format($result[0]->records[0]->getValue(), 3, '.', '');
                
                // Calculate final energy (end - begin)
                $finalEnergy = $totalEnergy - $session->total_energy_begin;

                // Update the session with final energy reading
                $session->update([
                    'stop_time' => now(),
                    'total_energy_end' => $totalEnergy,
                    'final_energy' => $finalEnergy
                ]);
            } else {
                // If we can't get energy reading, just stop the session
                $session->update([
                    'stop_time' => now(),
                ]);
            }

            // Turn off the socket via MQTT
            $mqtt = new MqttClient('amafamily.nl', 1883, 'laravel-client');
            $mqtt->connect(null, true, []);
            $mqtt->publish('cmnd/charger_'.$socketId.'/Power', 'OFF', 0);
            $mqtt->disconnect();

            Log::info("Sessie automatisch gestopt", ['session_id' => $this->sessionId, 'socket_id' => $socketId]);
            
        } catch (\Exception $e) {
            Log::error("Fout bij automatisch stoppen van sessie", [
                'session_id' => $this->sessionId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
