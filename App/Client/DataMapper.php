<?php

namespace App\Client;

trait DataMapper
{

    /**
     * @param $arr
     */
    public function parseData($arr)
    {
        $this->parseObjects($arr[0]);
        $this->parseObjects($arr[1]);
        $this->parseMeta($arr[2]);
    }

    /**
     * @param string $string
     */
    public function parseObjects(string $string)
    {
        $arr = explode('#', $string);

        $list = [];
        if (count($arr) > 1) {
            $ps = explode(';', $arr[1]);
            for ($i = 0; $i < count($ps); $i++) {
                $objectString = $ps[$i];
                if ($objectString) {
                    $object = new MapObject($objectString);
                    $object->id = $i;
                    $list[] = $object;
                }
            }
        }
        if ($arr[0] === 'planets') {
            $this->allPlanets = [];
            $this->myPlanets = [];
            $this->notMyPlanets = [];
            $this->enemyPlanets = [];
            $this->neutralPlanets = [];
            foreach ($list as $object) {
                $this->allPlanets[] = $object;
                switch ($object->own) {
                    case 0:
                        $this->neutralPlanets[] = $object;
                        $this->notMyPlanets[] = $object;
                        break;
                    case 1:
                        $this->myPlanets[] = $object;
                        break;
                    case 2:
                        $this->enemyPlanets[] = $object;
                        $this->notMyPlanets[] = $object;
                        break;
                }
            }
        }

        if ($arr[0] === 'ships') {
            $this->allShips = [];
            $this->myShips = [];
            $this->enemyShips = [];
            foreach ($list as $object) {
                $this->allShips[] = $object;
                switch ($object->own) {
                    case 1:
                        $this->myShips[] = $object;
                        break;
                    case 2:
                        $this->enemyShips[] = $object;
                        break;
                }
            }
        }
    }

    /**
     * @param string $string
     */
    public function parseMeta(string $string)
    {
        $arr = explode('#', $string);
        if (count($arr) > 1) {
            $ps = explode(';', $arr[1]);
            $this->_shipsSpeed = (double)($ps[0]);
            $this->turnNumber = (int)($ps[1]);
            $this->startTime = (double)($ps[2]);
        }
    }
}