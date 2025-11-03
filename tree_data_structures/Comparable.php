<?php
// Riku Theodorou

declare(strict_types=1);

/**
 * @template T
 */
interface Comparable {
    /**
     * Summary of compareTo
     * @param T $other
     * @return int
     */
    public function compareTo($other): int;
}

?>