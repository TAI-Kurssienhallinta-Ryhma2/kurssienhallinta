<?php
// Riku Theodorou

declare(strict_types=1);

require_once "Comparable.php";
require_once "Equality.php";

/**
 * @template T
 */
interface ComparableEqualContract extends Comparable, Equality {
    //Marker interface - enforces both comparison and equality
}
?>