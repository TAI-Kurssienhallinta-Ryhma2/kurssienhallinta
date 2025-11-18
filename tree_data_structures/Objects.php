<?php
// Riku Theodorou

/**
 * Utility class for common object-related operations.
 * 
 * This class cannot be instantiated and provides static methods only.
 */
class Objects {
    /**
     * Private constructor to prevent instantiation.
     */
    private function __construct() {}

    /**
     * Compares two values for equality.
     * 
     * - If both values are `null`, returns `true`.
     * - If one is `null` and the other is not, returns `false`.
     * - If the first value is an object implementing an `equals` method,
     *   the comparison is delegated to that method.
     * - Otherwise, uses strict equality (`===`).
     * 
     * @param mixed $value1 First value to compare.
     * @param mixed $value2 Second value to compare.
     * @return bool `true` if values are considered equal, `false` otherwise.
     */
    public static function equals($value1, $value2): bool {
        if($value1 === null && $value2 === null) {
            return true;
        }

        if($value1 === null || $value2 === null) {
            return false;
        }

        if(is_object($value1) && method_exists($value1, "equals")) {
            return $value1->equals($value2);
        } else {
            return $value1 === $value2;
        }
    }
} 
?>