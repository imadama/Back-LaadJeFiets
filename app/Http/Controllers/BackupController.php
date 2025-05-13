<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpMqtt\Client\MqttClient;
use InfluxDB2\Client;

class BackupController extends Controller
{
    public function start()
    {
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
  |> filter(fn: (r) => r["_measurement"] == "charger_DDEAFC/SENSOR")
  |> filter(fn: (r) => r["_field"] == "Total")
  |> last()
EOT;

        $result = $query->query($flux);
        $totalEnergy = $result[0]->records[0]->getValue();
        dd(['Total Energy at start:', $totalEnergy]);

        $mqtt = new MqttClient('amafamily.nl', 1883, 'laravel-client');
        $mqtt->connect(null, true, []);

        // Zet socket aan
        $mqtt->publish('cmnd/charger_DDEAFC/Power', 'ON', 0);
        $mqtt->disconnect();

        return response()->json(['status' => 'started']);
    }

    public function stop()
    {
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
  |> filter(fn: (r) => r["_measurement"] == "charger_DDEAFC/SENSOR")
  |> filter(fn: (r) => r["_field"] == "Total")
  |> last()
EOT;

        $result = $query->query($flux);
        $totalEnergy = $result[0]->records[0]->getValue();
        dd(['Total Energy at stop:', $totalEnergy]);

        $mqtt = new MqttClient('amafamily.nl', 1883, 'laravel-client');
        $mqtt->connect(null, true, []);

        // Zet socket uit
        $mqtt->publish('cmnd/charger_DDEAFC/Power', 'OFF', 0);
        $mqtt->disconnect();

        return response()->json(['status' => 'stopped']);
    }
}