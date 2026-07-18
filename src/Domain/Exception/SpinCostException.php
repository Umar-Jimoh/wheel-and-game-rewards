<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class SpinCostException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Invalid spin cost.');
    }
}