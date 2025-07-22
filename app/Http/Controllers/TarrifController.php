<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

class TarrifController extends Controller
{
    public function getTarrif($location_id)
    {
        $location = Location::find($location_id);
        return response()->json($location->tarrif_per_kwh);
    }
}
