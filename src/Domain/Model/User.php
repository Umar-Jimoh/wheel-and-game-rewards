<?php 

declare(strict_types=1);

namespace App\Domain\Model;

/**
 * Why this is an ENTITY (not a value object):
 * An entity is defined by IDENTITY, not by its attributes. Two users named
 * "John" with 500 point are still two different people if their IDs differ.
 * A value object (like Point or SpinCost) has no identity — two `Point(500)`
 * are interchangeable and equal purely by value.
 *
 * Because User has identity that persists over time, it is allowed to be
 * MUTABLE (its point balance changes as tasks are completed / spins happen),
 * whereas value objects should generally be immutable.
 *
 * What this class needs to achieve (per docs/prd.md §4.1 and §9):
 * - Hold a User's identity (ID) and Name.
 * - Hold the user's current point balance — ideally as a `Point` value
 *   object, not a raw int, so the "cannot go negative" rule (BR-001) lives
 *   in ONE place (Point) rather than being re-checked everywhere User is
 *   touched.
 * - Expose behavior, not just getters/setters: e.g. addPoints(Point $amount)
 *   and deductPoints(Point $amount) rather than a public setPoints(). This
 *   is what keeps business rules inside the Domain instead of leaking into
 *   Application/UseCase code (PRD §14 — Domain must not depend on any
 *   framework, and its rules must be enforced by the Domain itself, not by
 *   callers remembering to check things).
 * - Stay completely free of framework/infrastructure concerns: no Doctrine
 *   annotations/attributes, no Symfony types, no persistence logic. This
 *   class must be constructible and usable in a plain PHP script with zero
 *   dependencies.
 *
 * Suggested shape (guidance only — write it yourself):
 * - private readonly properties for id and name (identity doesn't change)
 * - private mutable property for point (a Point value object)
 * - constructor takes id, name, and an initial Point balance
 * - addPoints(Point $point): void — delegates to Point' own add() logic
 * - deductPoints(Point $point): void — delegates to Point' own subtract()
 *   logic, which is where BR-001 (insufficient point) should throw
 * - getId(), getName(), getBalance(): simple read accessors are fine — the
 *   goal is to avoid uncontrolled WRITES from outside, not to ban reads
 */
final class User {
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private Point $point
    ){}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBalance(): Point
    {
        return $this->point;
    }

    public function addPoints(Point $reward): void
    {
        $this->point = $this->point->add($reward);
    }

    public function hasEnoughPoints(Point $cost): bool
    {
        return $this->point->isGreaterThanOrEqualTo($cost);
    }

    public function deductPoints(Point $amount): void
    {
        $this->point = $this->point->subtract($amount);
    }

}