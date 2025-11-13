<?php
// Riku Theodorou

declare(strict_types=1);

/**
 * Options to specify the starting position of a tree iterator.
 */
enum IteratorOptions {
    case HEAD;
    case TAIL;
}

/**
 * Interface for a bidirectional iterator over a tree structure.
 * 
 * @template E The element type returned by this iterator.
 */
interface TreeIterator extends Iterator {

     /**
     * Checks if there is a next element available in the iteration.
     * 
     * @return bool True if there is a next element, false otherwise.
     */
    public function hasNextElement(): bool;

     /**
     * Returns the next element in the iteration.
     * 
     * @return E The next element.
     */
    public function nextElement(): mixed;

    /**
     * Checks if there is a previous element available in the iteration.
     * 
     * @return bool True if there is a previous element, false otherwise.
     */
    public function hasPreviousElement(): bool;

    /**
     * Returns the previous element in the iteration.
     * 
     * @return E The previous element.
     */
    public function previousElement() : mixed;

    /**
     * Resets the iterator position based on the specified option.
     * 
     * @param IteratorOptions $iteratorOptions The starting position option.
     * @return static Returns the iterator instance for method chaining.
     */
    public function reset(IteratorOptions $iteratorOptions): static;

    /**
     * Removes the current element from the underlying collection.
     * 
     * @return void
     */
    public function remove(): void;
}
?>