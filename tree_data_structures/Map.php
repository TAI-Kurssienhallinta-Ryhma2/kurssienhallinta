<?php
// Riku Theodorou

declare(strict_types=1);

require_once "TreeMapView.php";

/**
 * @template K
 * @template V
 */
interface Entry {
    /**
     * @return K
     */
    public function getKey(): mixed;

    /**
     * @return V
     */
   public function getValue(): mixed;

   /**
    * @param V $value
    * @return V
    */
   public function setValue($value): mixed;
}

/**
 * @template K
 * @template V
 */
interface Map {
    /**
     * @param K $key
     * @param V $value
     * @return V
     */
    public function put($key, $value): mixed;

    /**
     * @param K $key
     * @return V
     */
    public function get($key): mixed;
    /**
     * @param K $key
     * @return V
     */
    public function remove($key): mixed;

    /**
     * @param K $key
     * @param V $value
     * @return bool
     */
    public function removeIfEquals($key, $value): bool;

    /**
     * @return Entry<K,V>
     */
    public function first(): mixed;

    /**
     * @return Entry<K,V>
     */
    public function last(): mixed;

    /**
     * @return Entry<K,V>
     */
    public function popFirst(): mixed;

    /**
     * @return Entry<K,V>
     */
    public function popLast(): mixed;

    /**
     * @param K $key
     * @return bool
     */
    public function containsKey($key): bool;

    /**
     * @param V $value
     * @return bool
     */
    public function containsValue($value): bool;

    /**
     * @param K $key
     * @return Entry<K,V>
     */
    public function previous($key): mixed;

    /**
     * @param K $key
     * @return Entry<K,V>
     */
    public function next($key): mixed;

    /**
     * @return void
     */
    public function clear(): void;

    /**
     * @return int
     */
    public function size(): int;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return TreeMapView<Entry<K,V>>
     */
    public function entrySet(): mixed;

    /**
     * @return TreeMapView<K>
     */
    public function keySet(): mixed;

    /**
     * @return TreeMapView<V>
     */
    public function values(): mixed;
}
?>