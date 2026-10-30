<?php

declare(strict_types=1);

namespace App\Application\Dto\Request;

class SpinWheelInput
{
    public function __construct(
        public int $spinCost
    ){}
}