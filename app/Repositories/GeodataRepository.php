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
                Log::info("No $attribute found for $ipAddress");
            }
        }

        return $geodata;
    }

    /**
     * Create and assign the geodata for a useragent
     * @param  Useragent $useragent An instance of a Useragent model
     * @param  string    $ipAddress 
     * @return App\Geodata
     */
    public function makeGeodata(Useragent $useragent, $ipAddress)
    {
        $geodata = null;
        $geodataArray = $this->getGeodata($ipAddress);
        // This checks if all the values in geodataArray are false
        if (array_values(array_unique($geodataArray)) !== [false]) {
            // Create the geodata on the useragent model
            $geodata = $useragent->geodata()->create($geodataArray);
        }

        return $geodata;
    }
}