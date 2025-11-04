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

    public function hasNextElement(): bool;

    /**
     * @return E
     */
    public function nextElement(): mixed;

    public function hasPreviousElement(): bool;

    /**
     * @return E
     */
    public function previousElement() : mixed;
    public function reset(IteratorOptions $iteratorOptions): static;
    public function remove(): void;
}
?>