<?php

namespace App\Client;

class Client
{
    use DataMapper;
    /**
     * @var MapObject[]
     */
    public $allPlanets = [];

    /**
     * @var MapObject[]
     */
    public $myPlanets = [];

    /**
     * @var MapObject[]
     */
    public $enemyPlanets = [];

    /**
     * @var MapObject[]
     */
    public $neutralPlanets = [];

    /**
     * @var MapObject[]
     */
    public $notMyPlanets = [];

    /**
     * @var MapObject[]
     */
    public $allShips = [];

    /**
     * @var MapObject[]
     */
    public $myShips = [];

    /**
     * @var MapObject[]
     */
    public $enemyShips = [];

    /**
     * @var int $turnNumber
     */
    public $turnNumber;

    /**
     * @var double $_shipsSpeed
     */
    private $_shipsSpeed;

    /**
     * @var Engine $_engine
     */
    private $_engine;

    /**
     * @var double $startTime
     */
    public $startTime;

    /**
     * Client constructor.
     *
     * @param Engine $engine
     */
    public function __construct(Engine $engine)
    {
        $this->_engine = $engine;
        $this->turnNumber = 0;
        $this->_shipsSpeed = 0.04; // default value
    }

    public function endTurn()
    {
        $this->_engine->socketWrite('#endTurn');
    }


    public function send(MapObject $from, MapObject $to, int $count)
    {
        $this->_engine->socketWrite('#send:' . $from->id . ',' . $to->id . ',' . $count);
    }

    public function turnsFromTo(MapObject $from, MapObject $to): int
    {
        // round to bigger int
        return round(($this->distance($from, $to) / $this->shipsSpeed()), 0, PHP_ROUND_HALF_EVEN);
    }

    public function distance(MapObject $from, MapObject $to): float
    {
        $x = $to->x - $from->x;
        $y = $to->y - $from->y;

        return sqrt($x * $x + $y * $y);
    }

    public function shipsSpeed(): float
    {
        return $this->_shipsSpeed;
    }

}