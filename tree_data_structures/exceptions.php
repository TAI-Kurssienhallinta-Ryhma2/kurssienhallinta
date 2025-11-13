<?php
// Riku Theodorou

/**
 * Exception thrown when a concurrent modification of a collection is detected.
 */
class ConcurrentModificationException extends Exception {}

/**
 * Exception thrown when attempting to access an element that does not exist.
 */
class NoSuchElementException extends Exception {}

/**
 * Exception thrown when a null reference is encountered where an object is required.
 */
class NullPointerException extends Exception {}

/**
 * Exception thrown when an unsupported operation is invoked.
 */
class UnsupportedOperationException extends Exception {}


?>