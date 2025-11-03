<?php
// Riku Theodorou

declare(strict_types=1);

require_once "TreeIterator.php";

/**
 * @template T
 */
interface TreeMapView extends IteratorAggregate {

    /**
     * @return TreeIterator<T>
     */
    public function getIterator(): TreeIterator;

    public function size() : int;

    public function clear() : void;

    public function remove($object);
}
?>