<?php
// Riku Theodorou

declare(strict_types=1);

/**
 * Interface Comparator
 *
 * Defines a strategy for comparing two objects of the same type.
 *
 * Unlike {@see Comparable}, which is implemented by the objects themselves,
 * a `Comparator` provides an **external** comparison mechanism.
 * This allows flexible sorting or ordering of objects without modifying their classes.
 *
 * Example use case:
 * - Sorting collections using different criteria (e.g., by name, by date, by score)
 *
 * @template T The type of objects being compared.
 */
interface Comparator {
    /**
     * Compares two objects and returns the result.
     *
     * Returns:
     * - Negative number if $object1 is smaller than $object2
     * - Zero if they are equal
     * - Positive number if $object1 is greater than $object2
     *
     * Example 1: Using an anonymous class implementing Comparator
     * ```php
     * /** @var Comparator<int> *\/
     * $compareInts = new class implements Comparator {
     *     public function compare($object1, $object2): int
     *     {
     *         return $object1 - $object2;
     *     }
     * };
     *
     * /** @var TreeMap<int, int> *\
     * $map = new TreeMap($compareInts);
     * ```
     *
     * Example 2: Using an arrow function as a comparator
     * ```php
     * $map = new TreeMap(fn($a, $b) => $a - $b);
     * ```
     *
     * @param T $object1 The first object to compare.
     * @param T $object2 The second object to compare.
     * @return int Negative, zero, or positive integer as comparison result.
     */
    public function compare($object1, $object2): int;
}

?>