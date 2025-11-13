<?php
// Riku Theodorou

declare(strict_types=1);

/**
 * Interface Equality
 *
 * Provides a contract for equality comparison between objects.
 */
interface Equality {
    /**
     * Determines whether the current object is equal to another object.
     *
     * @param object $other The object to compare with.
     * @return bool True if objects are equal, false otherwise.
     */
    public function equals(object $other): bool;
}

?>