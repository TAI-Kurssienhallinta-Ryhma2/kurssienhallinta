<?php
// Riku Theodorou

declare(strict_types=1);

enum IteratorOptions {
    case HEAD;
    case TAIL;
}

/**
 * @template E
 */
interface TreeIterator extends Iterator {

    public function hasNext(): bool;

    /**
     * @return E
     */
    public function nextC(): mixed;

    public function hasPrevious(): bool;

    /**
     * @return E
     */
    public function previous() : mixed;
    public function reset(IteratorOptions $iteratorOptions): static;
    public function remove(): void;
}
?>