<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Useragent;
use App\Repositories\UseragentRepository;
use UAParser\Parser as UseragentParser;
use Carbon\Carbon;

/**
 * @covers App\Repositories\UseragentRepository
 */
class UseragentTest extends TestCase
{
    // This will rollback our db changes
    use DatabaseTransactions;

    protected $useragentRepository;

    /**
     * Setup the application state
     */
    public function setUp()
    {
        parent::setUp();
        $this->useragentRepository = new UseragentRepository();
    }
    
    /**
     * Assert that UseragentRepository::makeUseragent() creates an App\Useragent instance and inserts it to the database
     * @param array $expected  The expected values of the Useragent instance
     * @dataProvider useragentProvider
     */
    public function testMakeUseragent($useragentString, Carbon $timestamp, $expected)
    {
        $useragent = $this->useragentRepository->makeUseragent($useragentString, $timestamp);
        $this->assertInstanceOf(Useragent::class, $useragent);
        // Assert the useragent values are what we expect
        $this->assertEquals($expected['browser'], $useragent->browser);
        $this->assertEquals($expected['device'], $useragent->device);
        $this->assertEquals($expected['operating_system'], $useragent->operating_system);
        // Assert they were inserted into the database
        $this->assertDatabaseHas('useragents', [
            'id'               => $useragent->id,
            'browser'          => $useragent->browser,
            'operating_system' => $useragent->operating_system,
        ]);
    }

    /**
     * Data Provider for testMakeUseragent
     */
    public function useragentProvider()
    {
        return [
            [
                "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:38.0) Gecko/20100101 Firefox/38.0", 
                Carbon::now(),
                ['browser' => 'Firefox', 'device' => 'desktop', 'operating_system' => 'Mac OS X']
            ],
            [
                "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36",
                Carbon::now(),
                ['browser' => 'Chrome', 'device' => 'desktop', 'operating_system' => 'Linux']
            ],
            [
                "Mozilla/5.0 (iPhone; CPU iPhone OS 10_3 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) CriOS/56.0.2924.75 Mobile/14E5239e Safari/602.1",
                Carbon::now(),
                ['browser' => 'Chrome Mobile iOS', 'device' => 'mobile', 'operating_system' => 'iOS']
            ],
            [
                "Mozilla/5.0 (iPhone; CPU iPhone OS 10_3 like Mac OS X) AppleWebKit/603.1.23 (KHTML, like Gecko) Version/10.0 Mobile/14E5239e Safari/602.1",
                Carbon::now(),
                ['browser' => 'Mobile Safari', 'device' => 'mobile', 'operating_system' => 'iOS']
            ],
            [
                "Mozilla/5.0 (Linux; Android 4.0.4; Galaxy Nexus Build/IMM76B) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.133 Mobile Safari/535.19",
                Carbon::now(),
                ['browser' => 'Chrome Mobile', 'device' => 'tablet', 'operating_system' => 'Android']
            ],
            [
                "Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0)",
                Carbon::now(),
                ['browser' => 'IE Mobile', 'device' => 'mobile', 'operating_system' => 'Windows Phone']
            ],
        ];
    }
}
