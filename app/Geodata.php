<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Geodata extends Model
{
    protected $table = 'geodata';

	/**
	 * Allow mass assignment on everything
	 * @var array
	 */
    protected $guarded = [];

    public function useragent()
    {
    	return $this->belongsTo('App\Useragent');
    }
}
