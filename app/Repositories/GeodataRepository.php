<?php

namespace App\Repositories;

use App\Geodata;
use App\Useragent;
use Illuminate\Support\Facades\Log;
use MaxMind\Db\Reader;

class GeodataRepository
{
    /**
     * Fetches the geodata for a given IP address from the .mmdb file
     * @param string $ipAddress
     * @return array $geodata
     */
    protected function getGeodata($ipAddress)
    {
        $missingData = false;
        $geodataDb   = database_path('geodata/GeoLite2-City.mmdb');

        $result    = (new Reader($geodataDb))->get($ipAddress);
        // Assign geodata attributes if they're available, assign to false if not
        $latitude  = isset($result['location']['latitude']) ? $result['location']['latitude'] : false;
        $longitude = isset($result['location']['longitude']) ? $result['location']['longitude'] : false;
        $country   = isset($result['registered_country']['iso_code']) ? $result['registered_country']['iso_code'] : false;
        $state     = isset($result['subdivisions'][0]['names']['en']) ? $result['subdivisions'][0]['names']['en'] : false;
        $city      = isset($result['city']['names']['en']) ? $result['city']['names']['en'] : false;
        $zipcode   = isset($result['postal']['code']) ? $result['postal']['code'] : false;

        $geodata   = compact('latitude', 'longitude', 'country', 'state', 'city', 'zipcode');

        foreach ($geodata as $attribute => $v) {
            if ($v == false) {
                Log::info("Lookup failed for $ipAddress: No $attribute found");
            }
        }

        return $geodata;
    }

    /**
     * Create and assign the geodata for a useragent
     * @param  Useragent $useragent An instance of a Useragent model
     * @param  string    $ipAddress 
     * @return void
     */
    public function assignTo(Useragent $useragent, $ipAddress)
    {
        $geodata = $this->getGeodata($ipAddress);
        if ($geodata) {
            // Create the geodata on the useragent model
            $useragent->geodata()->create($geodata);
        }
    }
}