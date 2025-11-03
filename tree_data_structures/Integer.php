<?php
// Riku Theodorou

require_once "ComparableEqualContract.php";

class Integer implements ComparableEqualContract {

    private int $num;

    public function __construct($num) {
        $this->num = $num;
    }

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
     * Summary of compare
     * @param Integer $object
     * @return int
     */
    public function compareTo($object): int {
        return $this->num - $object->num;
    }

    public function value(): int {
        return $this->num;
    }

    public function __tostring(): string {
        return (string)$this->num;
    }
}
?>