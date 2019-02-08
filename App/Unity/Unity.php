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

            foreach ($client->myPlanets as $planet) {

                if ($planet->value > 3) {
                    $ships = (int)(round($planet->value * 0.6));
                    /**
                     * уот куда-то сюда можно встроить проверку на то не идет ли на эту планету корабль и за сколько
                     * ходов с учетом роста кораблей на планете корабль сюда борется и хватит ли ему захватить ее
                     * можно еще высчитать оптимальное кол-во кораблей для отправки
                     */
                   /* if (($nearestNeutralPlanet = $this->getNearestPlanet($client, $planet, $client->neutralPlanets)) !== false) {
                        $client->send($planet, $nearestNeutralPlanet, $ships);
                    } elseif ((($nearestEnemyPlanet = $this->getNearestPlanet($client, $planet, $client->enemyPlanets)) !== false)) {
                        $client->send($planet, $nearestEnemyPlanet, $ships);
                    } else {
                        $client->send($planet, $this->getNearestPlanet($client, $planet, $client->myPlanets), $ships);
                    }*/
                    $client->send($planet, $this->getNearestPlanet($client, $planet, $client->notMyPlanets), $ships);
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