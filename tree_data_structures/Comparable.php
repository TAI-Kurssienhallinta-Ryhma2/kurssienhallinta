<?php
// Riku Theodorou

declare(strict_types=1);

/**
 * Interface Comparable
 *
 * A generic interface that defines a comparison contract between two objects
 * of the same type. Classes implementing this interface should define
 * how instances are compared, for example, for sorting or equality checks.
 *
 * @template T The type of object being compared.
 */
interface Comparable {
    /**
     * Compares the current object with another object of the same type.
     *
     * Implementations should return:
     * - A negative integer if this object is considered *less than* `$other`
     * - Zero if this object is *equal to* `$other`
     * - A positive integer if this object is *greater than* `$other`
     *
     * Example usage:
     * ```php
     * if ($a->compareTo($b) > 0) {
     *     echo "$a is greater than $b";
     * }
     * ```
     *
     * @param T $other The object to compare with.
     * @return int Comparison result: negative, zero, or positive integer.
     */
    public function compareTo($other): int;
}

?>