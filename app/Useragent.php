<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Useragent extends Model
{
    /**
     * Allow mass assignment on everything
     * @var array
     */
    protected $guarded = [];

    /**
     * Retrieves the associated geodata information for a useragent (one-to-one)
     * @return App\Geodata
     */
    public function geodata()
    {
        return $this->hasOne('App\Geodata');
    }
}
