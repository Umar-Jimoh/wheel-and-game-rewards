<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Random;

use PHPUnit\Framework\TestCase;
use App\Domain\Model\Multiplier;
use App\Infrastructure\Random\RandomMultiplierGenerator;

final class RandomMultiplierGeneratorTest extends TestCase
{
    public function testGenerateReturnsInMultiplierRange(): void
    {
        $randomMultiplier = new RandomMultiplierGenerator();

        for ($i = 0; $i < 100; $i++) {
            $result = $randomMultiplier->generate();
            $this->assertTrue(in_array($result, Multiplier::cases(), true));
        }

    }
}