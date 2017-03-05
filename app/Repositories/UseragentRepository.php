<?php

namespace App\Repositories;

use App\Useragent;
use UAParser\Parser as UseragentParser;
use Carbon\Carbon;

class UseragentRepository
{
    /**
     * Makes a Useragent model instance, inserting the record to the database
     * @param  string $useragent The useragent string for an access log
     * @param  Carbon $timestamp
     * @return App\Useragent            
     */
    public function makeUseragent($useragent, Carbon $timestamp)
    {
        $useragentParser  = UseragentParser::create();
        // Parse the information out of the useragent string
        $useragentInfo    = $useragentParser->parse($useragent);
        // Assign the attributes we're after
        $browser          = $useragentInfo->ua->family;
        $operating_system = $useragentInfo->os->family;
        // Device per UAParser
        $uaDevice         = strtolower($useragentInfo->device->family);
        // Set the device to one of the 4 allowed devices
        $device           = $this->normalizeDevice($uaDevice, $useragentInfo);
        // Not required in the specifications, but probably a good idea to track
        $timeOfVisit      = $timestamp->toDateTimeString();

        $attributes = compact('browser', 'device', 'operating_system');
        $useragent  = Useragent::create($attributes);

        return $useragent;
    }

    /**
     * Normalizes the device from what is returned from the UAParser\Parser package
     * We want to store it in the database as either: mobile, tablet, desktop, or robot
     * @link   https://developer.mozilla.org/en-US/docs/Web/HTTP/Browser_detection_using_the_user_agent#Mobile_Tablet_or_Desktop
     * @param  string    $deviceString 
     * @param  stdObject $useragentInfo 
     * @return string    $device
     */
    private function normalizeDevice($uaDevice, \UAParser\Result\Client $useragentInfo)
    {
        $device = $uaDevice;
        // First convert UAParser's device titles over to what we want
        if (strpos($uaDevice, 'spider') !== false) {
            $device = 'robot';
        }

        if (strpos($uaDevice, 'phone') !== false) {
            $device = 'mobile';
        }

        if (strpos($uaDevice, 'nexus') !== false) {
            $device = 'tablet';
        }
        if (strpos($uaDevice, 'samsung') !== false) {
            $device = 'mobile';
        }

        if (strpos($uaDevice, 'lg') !== false) {
            $device = 'mobile';
        }

        if (strpos($uaDevice, 'ipad') !== false) {
            $device = 'tablet';
        }

        if (strpos($uaDevice, 'kindle') !== false) {
            $device = 'tablet';
        }
        // Default "Other"s from parser to desktop
        if ($uaDevice == 'other') {
            $device = 'desktop';
        }
        // Lastly, check the original UA string for "Mobi" as recommended by MDN
        if (strpos($useragentInfo->originalUserAgent, 'Mobi') !== false) {
            $device = 'mobile';
        }

        return $device;
    }
}