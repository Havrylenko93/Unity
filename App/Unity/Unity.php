<?php
namespace App\Unity;

use App\Client\BotInterface;
use App\Client\Client;

class Unity implements BotInterface
{
    // среднее кол-во ходов ~80-120.
    // среднее время работы самого девкита около 3-5 миллисекунд
    public function turn(Client $client, int $turnNumber)
    {
        if (count($client->notMyPlanets) > 0) {
            $toPlanet = reset($client->notMyPlanets); // first enemy planet

            foreach ($client->myPlanets as $planet) {
                $a = $this->getNearestPlanet($client, $planet, $client->neutralPlanets);


                $random = $client->notMyPlanets[array_rand($client->notMyPlanets)]; // random enemy planet
                if ($planet->value > 1) {

                    if ($planet->value > 10) {
                        $client->send($planet, $random, (int)(round($planet->value / 2)));
                    }
                    $ships = 1;
                    $client->send($planet, $toPlanet, $ships);
                    $planet->value -= $ships;
                }
            }
        }

        /**
         * if (round((microtime(true) - $client->startTime)*1000) > 80) break N - кончается время, длаем ход как есть
         */

        /*if(($turnNumber / 30) > 1) {
            file_put_contents('/var/www/html/blog/public/777.txt', serialize($client));
        }*/


        $client->endTurn();
    }

    // average execution time is 0.2 milliseconds
    protected function getNearestPlanet(Client $client, $currentPlanet, $planets)
    {
        if(empty($planets)) {
            return false;
        }
        $array =[];

        foreach ($planets as $planet) {
            $distance = $client->distance($currentPlanet, $planet);
            $array["$distance"] = $planet;
        }

        return $array[min(array_keys($array))];
    }

    protected function isPlanet($object) :bool
    {
        return (bool)($object->type === 1);
    }

    protected function isBigPlanet($object) :bool
    {
        return (bool)(($this->isPlanet($object)) &&($object->size === 2));
    }

    protected function isNeutralPlanet($object) :bool
    {
        return ($this->isPlanet($object) &&  $object->own === 0);
    }

    protected function isMyPlanet($object) :bool
    {
        return ($this->isPlanet($object) &&  $object->own === 1);
    }

    protected function isEnemyPlanet($object) :bool
    {
        return ($this->isPlanet($object) &&  $object->own === 2);
    }

}