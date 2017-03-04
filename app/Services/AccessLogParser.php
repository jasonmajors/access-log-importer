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
     * @param  array  $accessLog   An array of each visit from the access log file
     * @param  [type] $progressBar [description]
     * @return void              
     */
    public function parse(array $accessLog, ProgressBar $progressBar, Carbon $start, Carbon $end)
    {
        $this->logParser->setFormat('%h %l %u %t "%r" %>s %O "%{Referer}i" \"%{User-Agent}i"');
        foreach ($accessLog as $visit) {
            // stdObject
            $entry = $this->logParser->parse($visit);
            $timestamp = Carbon::createFromTimestamp($entry->stamp);
            // Check if we care about this entry based on filtering params
            $outOfBounds = $this->outOfBounds($timestamp, $start, $end);
            Log::info($timestamp->toDateTimeString());
            if ($outOfBounds) {
                continue;
            }

            $userAgentString = $entry->HeaderUserAgent;
            // make App\Useragent
            $userAgent = $this->useragentRepository->make($userAgentString, $timestamp);
            // assign geodata data to the useragent
            $this->geodataRepostitory->assignTo($userAgent, $entry->host);
            // Advance the bar
            $progressBar->advance();
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