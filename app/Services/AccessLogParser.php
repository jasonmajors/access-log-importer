<?php

namespace App\Services;

use Artisan;
use \Kassner\LogParser\LogParser;
use App\Repositories\UseragentRepository;
use App\Repositories\GeodataRepository;
use Carbon\Carbon;
use Symfony\Component\Console\Helper\ProgressBar;
use Illuminate\Support\Facades\Log;

/**
 * Intended to be called from an Artisan Command
 */
class AccessLogParser
{
    protected $logParser;
    protected $geodataRepository;
    protected $useragentRepository;

    /**
     * Inject the class dependecies and create an instance of AccessLogParser
     */
    public function __construct(GeodataRepository $geodataRepostitory, UseragentRepository $useragentRepository, LogParser $logParser)
    {
        $this->geodataRepostitory  = $geodataRepostitory;
        $this->useragentRepository = $useragentRepository;
        $this->logParser           = $logParser;
    }

    /**
     * Parses the access log
     * @param  array       $accessLog   An array of each visit from the access log file
     * @param  ProgressBar $progressBar Instance of Symfony's progress bar component
     * @param  Carbon      $start
     * @param  Carbon      $end  
     * @return void              
     */
    public function parse(array $accessLog, ProgressBar $progressBar, Carbon $start, Carbon $end)
    {
        $this->logParser->setFormat('%h %l %u %t "%r" %>s %O "%{Referer}i" \"%{User-Agent}i"');
        foreach ($accessLog as $visit) {
            // Advance the bar
            $progressBar->advance();
            // stdObject
            $entry = $this->logParser->parse($visit);
            $timestamp = Carbon::createFromTimestamp($entry->stamp);
            // Check if we care about this entry based on filtering params
            $outOfBounds = $this->outOfBounds($timestamp, $start, $end);
            if ($outOfBounds) {
                continue;
            }

            $useragent = $this->useragentRepository->makeUseragent($entry->HeaderUserAgent, $timestamp);
            $geodata   = $this->geodataRepostitory->makeGeodata($useragent, $entry->host);
            // GeodataRepository::makeGeodata() will try to make a geodata entry with missing data, but if there's NO data...
            if (is_null($geodata)) {
                Log::error("Unable to create GeoIP data for $entry->host. No geodata data found");
            }
        }
        $progressBar->finish();
    }

    /**
     * Handles the log for skipping visits based on datetime filtering
     * @param  Carbon $timestamp 
     * @param  Carbon $start     
     * @param  Carbon $end       
     * @return boolean
     */
    private function outOfBounds(Carbon $timestamp, Carbon $start, Carbon $end)
    {
        $outOfBounds = false;

        if ($timestamp < $start || $timestamp > $end) {
            $outOfBounds = true;
        }

        return $outOfBounds;
    }
}