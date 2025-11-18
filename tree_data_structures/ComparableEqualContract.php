<?php
// Riku Theodorou

declare(strict_types=1);

require_once "Comparable.php";
require_once "Equality.php";

/**
 * Interface ComparableEqualContract
 *
 * A composite contract that enforces both comparability and equality behavior.
 *
 * This interface combines the capabilities of:
 * - {@see Comparable} — defines a method for ordering or sorting comparisons.
 * - {@see Equality} — defines a method for checking equality between two objects.
 *
 * Classes implementing this interface must provide concrete implementations
 * for both comparison (`compareTo`) and equality (`equals`) methods.
 *
 * @template T The type of object being compared and checked for equality.
 * @extends Comparable<T>
 * @extends Equality<T>
 */
interface ComparableEqualContract extends Comparable, Equality {
    // Marker interface — no additional methods.
    // Enforces that implementing classes must define both Comparable and Equality behavior.
}
?>