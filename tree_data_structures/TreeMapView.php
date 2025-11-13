<?php
// Riku Theodorou

declare(strict_types=1);

require_once "TreeIterator.php";

/**
 * Represents a read-only or modifiable view of a tree map's entries or keys.
 * 
 * @template T The type of elements in this view.
 */
interface TreeMapView extends IteratorAggregate {

    /**
     * Returns an iterator over the elements in this view.
     * 
     * @return TreeIterator<T> An iterator over elements of type T.
     */
    public function getIterator(): TreeIterator;

    /**
     * Returns the number of elements in this view.
     * 
     * @return int The size of the view.
     */
    public function size() : int;

    /**
     * Removes all elements from this view.
     * 
     * @return void
     */
    public function clear() : void;

    /**
     * Removes the specified element from this view, if present.
     * 
     * @param T $object The element to remove.
     * @return void
     */
    public function remove($object);
}
?>