<?php

namespace App\Repositories;

use App\Geodata;
use App\Useragent;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;

class GeodataRepository
{
	/**
	 * Endpoint to get the geodata for a given IP address
	 */
	const ENDPOINT = 'http://freegeoip.net/json/';

	/**
	 * Fetches the geodata for a given IP address
	 * @param string $ipAddress
	 * @return array $geodata
	 */
	protected function getGeodata($ipAddress)
	{
		$client   = new Client();
		$response = $client->request('GET', self::ENDPOINT . $ipAddress);
		if ($response->getStatusCode() == 200) {
			$geodata = json_decode($response->getBody(), true);
		} else {
			Log::error("Unable to fetch geodata for $ipAddress");
		}

		return $geodata;
	}

	/**
	 * Create and assign the geodata for a useragent
	 * @param  Useragent $useragent An instance of a Useragent model
	 * @param  string    $ipAddress 
	 * @return void
	 */
	public function assign(Useragent $useragent, $ipAddress)
	{
		$geodata = $this->getGeodata($ipAddress);
		if ($geodata) {
			$latitude  = $geodata['latitude'];
			$longitude = $geodata['longitude'];
			$country   = $geodata['country_name'];
			$state     = $geodata['region_name'];
			$city      = $geodata['city'];
			$zipcode   = $geodata['zip_code'];

			$attributes = compact('latitude', 'longitude', 'country', 'state', 'city', 'zipcode');
			// Create the geodata on the useragent model
			$useragent->geodata()->create($attributes);
		}
	}
}