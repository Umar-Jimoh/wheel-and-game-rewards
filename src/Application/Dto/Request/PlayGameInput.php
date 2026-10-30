<?php

declare(strict_types=1);

namespace App\Application\Dto\Request;

use App\Domain\Model\Parity;
use Symfony\Component\Validator\Constraints as Assert;

final class PlayGameInput
{
    public function __construct(
        #[Assert\Range(min: 0, max: 4)]
        public readonly int $position,
        public readonly Parity $parity,
    ) {}
}
