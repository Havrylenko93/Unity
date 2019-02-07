<?php
namespace App\Unity;

use App\Client\BotInterface;
use App\Client\Client;

class Unity implements BotInterface
{
    public function turn(Client $client, int $turnNumber)
    {
        if (count($client->notMyPlanets) > 0) {
            $toPlanet = reset($client->notMyPlanets);
            /*foreach ($client->myPlanets as $planet) {
                $random = $client->notMyPlanets[array_rand($client->notMyPlanets)];
                if ($planet->value > 1) {
                    if ($planet->value > 10) {
                        $client->send($planet, $random, (int)(round($planet->value / 2)));
                    }
                    $ships = 1;
                    $client->send($planet, $toPlanet, $ships);
                    $planet->value -= $ships;
                }
            }*/
        }
        $client->endTurn();
    }
}