<?php
// Riku Theodorou

declare(strict_types=1);

/**
 * @template T
 */
interface Comparator {
    /**
     * @param T $object1
     * @param T $object2
     * @return int
     */
    public function compare($object1, $object2): int;
}

?>