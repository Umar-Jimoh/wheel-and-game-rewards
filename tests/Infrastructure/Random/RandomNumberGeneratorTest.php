<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Random;

use App\Application\Dto\RandomNumbersResult;
use App\Infrastructure\Random\RandomNumberGenerator;
use PHPUnit\Framework\TestCase;

final class RandomNumberGeneratorTest extends TestCase
{
    public function testGenerateReturnsRandomNumbersResult(): void
    {
        $randomNumberGenerator = new RandomNumberGenerator();

        $result = $randomNumberGenerator->generate();

        $this->assertInstanceOf(RandomNumbersResult::class, $result);
    }

    public function testGenerateReturnsExactlyFiveNumbers(): void
    {
        $randomNumberGenerator = new RandomNumberGenerator();

        for ($i = 0; $i < 100; $i++) {
            $result = $randomNumberGenerator->generate();
            $this->assertCount(5, $result->getNumbers());
        }
    }

    public function testGenerateReturnsNumbersWithinExpectedRange(): void
    {
        $randomNumberGenerator = new RandomNumberGenerator();

        for ($i = 0; $i < 100; $i++) {
            $numbers = $randomNumberGenerator->generate()->getNumbers();

            foreach ($numbers as $number) {
                $this->assertIsInt($number);
                $this->assertGreaterThanOrEqual(0, $number);
                $this->assertLessThanOrEqual(9, $number);
            }
        }
    }
}
