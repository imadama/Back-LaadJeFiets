<?php

namespace App\Http\Controllers;

use InfluxDB2\Client;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test()
    {
        $client = new Client([
            'url' => env('INFLUXDB_URL'),
            'token' => env('INFLUXDB_TOKEN'),
            'org' => env('INFLUXDB_ORG'),
            'bucket' => env('INFLUXDB_BUCKET'),
        ]);

        $query = $client->createQueryApi();

        $flux = <<<EOT
from(bucket: "mqttdatabase")
  |> range(start: -1h)
  |> filter(fn: (r) => r["_measurement"] == "charger_DDEAFC/SENSOR")
  |> aggregateWindow(every: 1m, fn: mean, createEmpty: false)
  |> yield(name: "mean")
EOT;

        $result = $query->query($flux);

        $grouped = [];

        foreach ($result as $table) {
            foreach ($table->records as $record) {
                $time = (string)$record->getTime();
                $field = $record->getField();
                $value = $record->getValue();

                if (!isset($grouped[$time])) {
                    $grouped[$time] = ['time' => $time];
                }

                $grouped[$time][$field] = $value;
            }
        }

        return response()->json(array_values($grouped));
    }
}