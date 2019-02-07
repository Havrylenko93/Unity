<?php

namespace App\Client;

/**
 * Class MapObject
 * @package App\Client\Object
 */
class MapObject
{
    /**
     * @var double $x
     */
    public $x;

    /**
     * @var double $y
     */
    public $y;

    /**
     * @var int $type
     */
    public $type;

    /**
     * @var int $size
     */
    public $size;

    /**
     * @var int $value
     */
    public $value;

    /**
     * @var int $own
     */
    public $own;

    /**
     * @var int $own
     */
    public $numberOfTurns;

    /**
     * @var int $id
     */
    public $id;

    /**
     * @var int $toId
     */
    public $toId;

    /**
     * @var int $fromId
     */
    public $fromId;

    public function __construct(string $string)
    {
        $arr = explode(',', $string);
        $this->type = (int)$arr[0];
        $this->x = (double)$arr[1];
        $this->y = (double)$arr[2];
        $this->value = (int)$arr[3];
        $this->own = (int)$arr[4];
        if ($this->type == 1) {
            $this->size = (int)$arr[5];
            $this->fromId = 0;
            $this->toId = 0;
            $this->numberOfTurns = 0;
        }
        else {
            $this->size = 0;
            $this->id = 0;
            $this->fromId = (int)$arr[5];
            $this->toId = (int)$arr[6];
            $this->numberOfTurns = (int)$arr[7];
        }
    }
}