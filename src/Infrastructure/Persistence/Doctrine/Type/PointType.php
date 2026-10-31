<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\Model\Point;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class PointType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): Point
    {
        return new Point((int) $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): int
    {
        return $value->getAmount();
    }

    public function getName(): string
    {
        return 'point';
    }
}
