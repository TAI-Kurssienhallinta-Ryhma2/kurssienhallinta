<?php
// Riku Theodorou

class Objects {
    private function __construct() {}

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