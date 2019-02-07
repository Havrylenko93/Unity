<?php

namespace App\Client;

interface BotInterface
{
    public function turn(Client $client, int $turnNumber);
}