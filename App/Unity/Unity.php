<?php

namespace App\Unity;

use App\Client\BotInterface;
use App\Client\Client;

class Unity implements BotInterface
{
    const ONLY_BIG = 1;
    const ONLY_SMALL = 2;

    public function turn(Client $client, int $turnNumber)
    {
        if (count($client->notMyPlanets) > 0) {

            foreach ($client->myPlanets as $planet) {

                if ($planet->value > 2) {

                    if ($client->turnNumber < 12) {
                        $except = 0;
                        while ($planet->value > 2) {
                            $toPlanet = $this->getNearestPlanet($client, $planet, $client->neutralPlanets,
                                self::ONLY_SMALL, $except);
                            if( $toPlanet !== false ) {
                                    $toPlanet = $this->getNearestPlanet($client, $planet, $client->notMyPlanets, self::ONLY_SMALL, $except);
                                    $client->send($planet, $toPlanet, $toPlanet->value + 1);
                                $planet->value -= $toPlanet->value + 1;
                            }

                            $toPlanet = $this->getNearestPlanet($client, $planet, $client->enemyPlanets, self::ONLY_SMALL);
                            if ($toPlanet !== false) {
                                if ($planet->value > ($toPlanet->value + 1)) {
                                    if ($client->distance($planet, $toPlanet) < 0.3) {
                                        $client->send($planet, $toPlanet, $planet->value -1);
                                        $planet->value -= ($planet->value -1);
                                    }
                                }
                                $except = (int)$toPlanet->id;
                                $planet->value -= ($planet->value -1);
                                continue;
                            }
                        }
                        /*if (count($client->notMyPlanets) > 2) {
                            $this->test($client, $planet);
                        }*/
                    }
                    $toPlanet = $this->getNearestPlanet($client, $planet, $client->notMyPlanets);
                    if ($toPlanet !== false) {
                        $client->send($planet, $toPlanet, (int)(round($planet->value * 0.6)));
                    }
                    if (round((microtime(true) - $client->startTime) * 1000) > 90) {
                        break;
                    }
                }


            }

/*
            $arrOfSafePlanets = [];
            foreach ($client->myPlanets as $planetNotUnderTheGun) {
                $arrOfSafePlanets[$planetNotUnderTheGun->value] = $planetNotUnderTheGun;
            }
            $weHave = (int)array_sum(array_keys($arrOfSafePlanets));
            $exc = 0;
            foreach ($client->myPlanets as $planet) {
                $toPlanet = $this->getNearestPlanet($client, $planet, $client->enemyPlanets, $exc);
                if ($toPlanet !== false) {
                    $necessity = ($this->isBigPlanet($toPlanet)) ? (int)(($toPlanet->value) + 16) : (int)$toPlanet->value + 8;

                    while ($necessity < $weHave) {
                        $i = -1;
                        while ($necessity > ($i)) {
                            foreach (array_values($arrOfSafePlanets) as $item) {
                                $client->send($item, $toPlanet, 1);
                                $weHave -= 1;
                                $i++;
                                $exc = $toPlanet->id;
                            }
                        }
                    }
                }
            }
*/
        }
        $client->endTurn();
    }

    private function test($client, $planet)
    {
        $idsUnderTheGun = [];
        $planetsUnderTheGun = [];
        $planetsNotUnderTheGun = [];

        foreach ($client->enemyShips as $enemyShip) {
            $idsUnderTheGun[] = $enemyShip->toId;
        }
        foreach ($client->myPlanets as $myPlanet) {
            if (in_array($myPlanet->id, $idsUnderTheGun)) {

                foreach ($client->enemyShips as $enemyShip) {
                    if ($enemyShip->toId == $myPlanet->id) {
                        $arr[$enemyShip->numberOfTurns] = $enemyShip->value;
                    }
                    if (isset($arr) && !empty($arr)) {
                        $numbOfTurn = max(array_keys($arr));
                        $values = array_sum(array_values($arr));
                        if ($this->isBigPlanet($myPlanet)) {
                            if (($myPlanet->value + ($numbOfTurn * 2)) < $values) {
                                $planetsUnderTheGun[] = $myPlanet;
                            } else {
                                $planetsNotUnderTheGun[] = $myPlanet;
                            }
                        } else {
                            if (($myPlanet->value + ($numbOfTurn)) < $values) {
                                $planetsUnderTheGun[] = $myPlanet;
                            } else {
                                $planetsNotUnderTheGun[] = $myPlanet;
                            }
                        }
                    }
                }
            }
        }

        $arrOfSafePlanets = [];
        foreach ($planetsNotUnderTheGun as $planetNotUnderTheGun) {
            $arrOfSafePlanets[$planetNotUnderTheGun->value] = $planetNotUnderTheGun;
        }
        $weHave = (int)array_sum(array_keys($arrOfSafePlanets));


        $toPlanet = $this->getNearestPlanet($client, $planet, $client->enemyPlanets);
        if ($toPlanet !== false && !empty($arrOfSafePlanets)) {

            $necessity = ($this->isBigPlanet($toPlanet)) ? (int)(($toPlanet->value) + 16) : (int)$toPlanet->value + 8;
            foreach (array_values($arrOfSafePlanets) as $item) {
                $distances[] = $client->distance($item, $toPlanet);
            }
            $maxDistance = isset($distances) ? max($distances) : 1;
            if ($maxDistance < 0.6) {
                if ($necessity < $weHave) {
                    $i = -1;
                    while ($necessity > ($i)) {
                        foreach (array_values($arrOfSafePlanets) as $item) {
                            $client->send($item, $toPlanet, ($item->value - 1));
                            $weHave -= ($item->value - 1);
                            $i++;
                        }
                    }
                }
            }

        }
    }

    protected function isBigBattlefield($client): bool
    {
        return (bool)(count($client->allPlanets) > 16);
    }

    // average execution time is 0.2 milliseconds
    protected function getNearestPlanet(Client $client, $currentPlanet, $planets, $only = 3, $except = 0)
    {
        if (empty($planets)) {
            return false;
        }
        $array = [];

        foreach ($planets as $planet) {
            if ($only === self::ONLY_BIG) {
                if (!$this->isBigPlanet($planet)) {
                    continue;
                }
            }
            if ($only === self::ONLY_SMALL) {
                if ($this->isBigPlanet($planet)) {
                    continue;
                }
            }
            if ($planet->id == $except) {
                continue;
            }
            $distance = $client->distance($currentPlanet, $planet);
            $array["$distance"] = $planet;
        }

        if (empty($array)) {
            return false;
        }

        return $array[min(array_keys($array))];
    }

    protected function isPlanet($object): bool
    {
        return (bool)($object->type === 1);
    }

    protected function isBigPlanet($object): bool
    {
        return (bool)(($this->isPlanet($object)) && ($object->size === 2));
    }

    protected function isNeutralPlanet($object): bool
    {
        return ($this->isPlanet($object) && $object->own === 0);
    }

    protected function isMyPlanet($object): bool
    {
        return ($this->isPlanet($object) && $object->own === 1);
    }

    protected function isEnemyPlanet($object): bool
    {
        return ($this->isPlanet($object) && $object->own === 2);
    }

}