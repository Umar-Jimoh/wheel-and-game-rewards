<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Model\Game;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class GameType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Game
    {
        return $value === null ? null : new Game(json_decode($value, true));
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value === null ? null : json_encode($value->getNumbers());
    }

    public function getName(): string
    {
        return 'game';
    }
}
