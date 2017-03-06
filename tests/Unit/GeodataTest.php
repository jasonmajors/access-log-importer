<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Geodata;
use App\Useragent;
use App\Repositories\GeodataRepository;
use App\Repositories\UseragentRepository;
use Carbon\Carbon;

/**
 * @covers App\Repositories\GeodataRepository
 * @todo Need a robust way to generate expected results for the geodata for an IP address.
 */
class GeodataTest extends TestCase
{
    // This will rollback our db changes
    use DatabaseTransactions;

    protected $geodataRepostitory;

    /**
     * Setup the application state
     */
    public function setUp()
    {
        parent::setUp();
        $this->geodataRepostitory = new GeodataRepository();
    }

    /**
     * @dataProvider makeGeodataProvider
     */
    public function testMakeGeodata(Useragent $useragent, $ipAddress)
    {
        $geodata = $this->geodataRepostitory->makeGeodata($useragent, $ipAddress);
        // A null return value is fine and expected occasionally
        if (!is_null($geodata)) {
            $this->assertInstanceOf(Geodata::class, $geodata);
        }
    }

    /**
     * Data Provider for testMakeGeodata
     */
    public function makeGeodataProvider()
    {
        parent::setUp();
        // Create an array containing App\Useragent and IP address pairs. You can change the range to change how many are generated
        // Array looks like: [[App\Useragent, ipaddress], [App\Useragent, ipaddress], ...]
        $args = array_map(function() { 
            return [factory(Useragent::class)->make(), \Faker\Factory::create()->ipv4]; 
            }, 
            range(0, 10)
        );
        return $args;
    }
}
