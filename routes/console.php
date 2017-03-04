<?php

use App\Services\AccessLogParser;
use App\Repositories\GeodataRepository;
use App\Repositories\UseragentRepository;
use Illuminate\Foundation\Inspiring;
use \Kassner\LogParser\LogParser;
/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/
// Application service container will automatically resolve AccessLogParser and inject the dependecies
// see https://laravel.com/docs/5.4/container if unfamiliar
Artisan::command('import:access-log', function (AccessLogParser $accessLogParser) {
    $logFile   = 'gobankingrates.com.access.log';
    $accessLog = Storage::disk('s3')->get($logFile);
    // Convert into an array we can iterate over
    $accessLog = explode(PHP_EOL, $accessLog);
    // The explode() will leave an empty item at the end of the array since it's on \r\n
    array_pop($accessLog); 
    // Display a progress bar when run from Artisan CLI
    $progressBar = $this->output->createProgressBar(count($accessLog));
    $this->info("Parsing and importing $logFile");
    $accessLogParser->parse($accessLog, $progressBar);
})->describe('Parse and download the GOBankingRates.com access log');