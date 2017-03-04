<?php

namespace App\Services;

use Artisan;
use \Kassner\LogParser\LogParser;
use App\Repositories\UseragentRepository;
use App\Repositories\GeodataRepository;
use Carbon\Carbon;

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


    public function parse(array $accessLog, $progressBar)
    {
        $this->logParser->setFormat('%h %l %u %t "%r" %>s %O "%{Referer}i" \"%{User-Agent}i"');
        foreach ($accessLog as $visit) {
            // stdObject
            $entry = $this->logParser->parse($visit);
            
            $userAgentString = $entry->HeaderUserAgent;
            $timestamp       = Carbon::createFromTimestamp($entry->stamp); 
            // make App\Useragent
            $userAgent = $this->useragentRepository->make($userAgentString, $timestamp);
            // assign geodata data to the useragent
            $ipAddress = $entry->host;
            $this->geodataRepostitory->assignTo($userAgent, $ipAddress);
            // Advance the bar
            $progressBar->advance();
        }
        $progressBar->finish();
    }
}