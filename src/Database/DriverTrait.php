<?php
namespace Leno\Database;

Trait DriverTrait
{
    protected $max = 5;

    protected $busy = 0;

    public function addWeight(int $weight)
    {
        $this->max += $weight;
        return $this;
    }

    public function getWeight()
    {
        return $this->max - $this->busy;
    }
}
