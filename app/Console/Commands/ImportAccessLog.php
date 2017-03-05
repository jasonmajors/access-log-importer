<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AccessLogParser;
use Storage;
use Carbon\Carbon;

class ImportAccessLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:access-log
        {--start=}
        {--end=}
        ';

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
     * @todo  Catch exceptions from Carbon to make date formats less strict
     * @return mixed
     */
    public function handle()
    {
        $start = null;
        $end   = null;
        // Check for start and end params
        if ($this->option('start')) {
            $start = Carbon::createFromFormat('m/d/Y h:i A', $this->option('start'));
        }
        if ($this->option('end')) {
            $end = Carbon::createFromFormat('m/d/Y h:i A', $this->option('end'));
        }
        // If a boundary is null, set it to the extreme
        $start = is_null($start) ? Carbon::createFromTimestamp(-1) : $start;
        $end   = is_null($end)   ? Carbon::createFromTimestamp(9999999999) : $end; // year 2286, this hopefully won't be in production
        // Get the file
        $logFile   = env('ACCESS_LOG');
        $accessLog = Storage::disk('s3')->get($logFile);
        $this->comment("Access Log: $logFile found. Parsing and importing...");
        // Convert into an array we can iterate over
        $accessLog = explode(PHP_EOL, $accessLog);
        // The explode() will leave an empty item at the end of the array since it's on \r\n
        array_pop($accessLog); 
        // Display a progress bar when run from Artisan CLI
        $progressBar = $this->output->createProgressBar(count($accessLog));
        // Parse the file
        $this->accessLogParser->parse($accessLog, $progressBar, $start, $end);
        $this->info("\r\n $logFile has been successfully imported!");
    }
}
