<?php
// Riku Theodorou

require_once "ComparableEqualContract.php";

/**
 * Class Integer
 *
 * A simple wrapper for an integer value that implements ComparableEqualContract.
 * Useful for testing comparison and equality contracts.
 *
 * @implements ComparableEqualContract<int>
 */
class Integer implements ComparableEqualContract {

    /**
     * @var int The wrapped integer value.
     */
    private int $num;

    /**
     * Integer constructor.
     *
     * @param int $num The integer value to wrap.
     */
    public function __construct($num) {
        $this->num = $num;
    }

    /**
     * Checks if this Integer is equal to another object.
     *
     * @param object $other The object to compare with.
     * @return bool True if $other is an Integer with the same value.
     */
    public function equals(object $other): bool {
        if($this === $other) {
            return true;
        }

        if(!($other instanceof Integer)) {
            return false;
        }

        /** @var Integer $integer */
        $integer = $other;  // no cast here!

        return $this->num === $integer->num;
    }

    /**
     * Compares this Integer with another Integer.
     *
     * @param Integer $object The Integer to compare with.
     * @return int Negative if less, zero if equal, positive if greater.
     */
    public function compareTo($object): int {
        return $this->num - $object->num;
    }

    /**
     * Returns the wrapped integer value.
     *
     * @return int
     */
    public function value(): int {
        return $this->num;
    }

    /**
     * Returns string representation of the integer.
     *
     * @return string
     */
    public function __tostring(): string {
        return (string)$this->num;
    }
}
?>