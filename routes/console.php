<?php

use Illuminate\Foundation\Inspiring;
use App\Repositories\ParsingRepository;
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
Artisan::command('download:access-logs', function () {
    $accessLog = Storage::disk('s3')->get('gobankingrates.com.access.log');

})->describe('Display an inspiring quote');