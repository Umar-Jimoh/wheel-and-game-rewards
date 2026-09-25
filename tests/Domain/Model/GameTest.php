<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use App\Domain\Model\Game;
use App\Domain\Model\Parity;
use PHPUnit\Framework\TestCase;

final class GameTest extends TestCase
{
    public function testConstructorThrowsExceptionWhenNumbersArrayHasLessThanFiveElements(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Numbers array must contain exactly 5 elements./');

        new Game([1, 2, 3, 4]);
    }

    public function testConstructorThrowsExceptionWhenNumbersArrayHasMoreThanFiveElements(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Numbers array must contain exactly 5 elements./');

        new Game([1, 2, 3, 4, 5, 6]);
    }

    public function testConstructorThrowsExceptionWhenNumbersArrayContainsNegativeInteger(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Numbers array must contain only non-negative integers./');

        new Game([1, 2, -9, 4, 5]);
    }

    public function testEvaluateThrowsExceptionWhenPositionIsOutOfBounds(): void
    {
        $game = new Game([1, 2, 3, 4, 5]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Position is out of bounds./');

        $game->evaluate(5, Parity::EVEN);
    }

    public function testEvaluateThrowsExceptionWhenItemInArrayIsNotInteger(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Numbers array must contain only non-negative integers./');
        new Game([1, 2, '3', 4, 5]);
    }

    public function testEvaluateReturnsTrueWhenNumberMatchesParity(): void
    {
        $game = new Game([1, 2, 3, 4, 5]);

        $this->assertTrue($game->evaluate(1, Parity::EVEN));
    }

    public function testEvaluateReturnsFalseWhenNumberDoesNotMatchParity(): void
    {
        $game = new Game([1, 2, 3, 4, 5]);

        $this->assertFalse($game->evaluate(0, Parity::EVEN));
    }
}