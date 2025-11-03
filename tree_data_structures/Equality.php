<?php
// Riku Theodorou

declare(strict_types=1);

interface Equality {
    /**
     * @param object $other
     * @return bool
     */
    public function equals(object $other): bool;
}

?>