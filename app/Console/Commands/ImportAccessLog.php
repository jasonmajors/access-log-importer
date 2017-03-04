<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AccessLogParser;
use Storage;

class ImportAccessLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:access-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse and download the GOBankingRates.com access log';

    /**
     * An App\Services\AccessLogParser instance
     * @var App\Services\AccessLogParser
     */
    protected $accessLogParser;

    /**
     * Create a new command instance. 
     * Application service container will automatically resolve AccessLogParser and inject its dependecies
     * @see https://laravel.com/docs/5.4/container if unfamiliar
     * @return void
     */
    public function __construct(AccessLogParser $accessLogParser)
    {
        $this->accessLogParser = $accessLogParser;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $logFile   = 'gobankingrates.com.access.log';
        $accessLog = Storage::disk('s3')->get($logFile);
        $this->comment("File: $logFile found. Parsing and importing...");
        // Convert into an array we can iterate over
        $accessLog = explode(PHP_EOL, $accessLog);
        // The explode() will leave an empty item at the end of the array since it's on \r\n
        array_pop($accessLog); 
        // Display a progress bar when run from Artisan CLI
        $progressBar = $this->output->createProgressBar(count($accessLog));
        $this->accessLogParser->parse($accessLog, $progressBar);
        $this->info("\r\n $logFile has been successfully imported!");
    }
}
