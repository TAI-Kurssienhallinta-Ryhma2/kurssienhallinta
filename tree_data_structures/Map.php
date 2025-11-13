<?php
// Riku Theodorou

declare(strict_types=1);

require_once "TreeMapView.php";

/**
 * Interface Entry
 *
 * Represents a key-value pair (map entry).
 *
 * @template K K - The type of keys maintained by this entry.
 * @template V V - The type of mapped values.
 */
interface Entry {
    /**
     * Returns the key corresponding to this entry.
     *
     * @return K The key of the entry.
     */
    public function getKey(): mixed;

    /**
     * Returns the value corresponding to this entry.
     *
     * @return V The value of the entry.
     */
   public function getValue(): mixed;

   /**
    * Replaces the value corresponding to this entry with the specified value.
    *
    * @param V $value The new value to set.
    * @return V The old value that was replaced.
    */
   public function setValue($value): mixed;
}

/**
 * Interface Map
 *
 * A collection that maps keys to values. Each key can map to at most one value.
 *
 * @template K K - The type of keys maintained by this map.
 * @template V V - The type of mapped values.
 */
interface Map {
    /**
     * Associates the specified value with the specified key in this map.
     * If the map previously contained a mapping for the key, the old value is replaced.
     *
     * @param K $key The key with which the specified value is to be associated.
     * @param V $value The value to be associated with the specified key.
     * @return V The previous value associated with key, or null if there was no mapping.
     */
    public function put($key, $value): mixed;

    /**
     * Returns the value to which the specified key is mapped.
     *
     * @param K $key The key whose associated value is to be returned.
     * @return V The value associated with the specified key, or null if none.
     */
    public function get($key): mixed;

    /**
     * Removes the mapping for a key from this map if present.
     *
     * @param K $key The key whose mapping is to be removed.
     * @return V The previous value associated with key, or null if there was no mapping.
     */
    public function remove($key): mixed;

    /**
     * Removes the entry for the specified key only if it is currently mapped to the specified value.
     *
     * @param K $key The key whose mapping is to be removed.
     * @param V $value The value expected to be associated with the key.
     * @return bool True if the entry was removed.
     */
    public function removeIfEquals($key, $value): bool;

    /**
     * Returns the first (lowest) entry in the map.
     *
     * @return Entry<K,V>|null The first entry, or null if the map is empty.
     */
    public function first(): mixed;

    /**
     * Returns the last (highest) entry in the map.
     *
     * @return Entry<K,V>|null The last entry, or null if the map is empty.
     */
    public function last(): mixed;

    /**
     * Removes and returns the first (lowest) entry in the map.
     *
     * @return Entry<K,V>|null The removed first entry, or null if the map is empty.
     */
    public function popFirst(): mixed;

    /**
     * Removes and returns the last (highest) entry in the map.
     *
     * @return Entry<K,V>|null The removed last entry, or null if the map is empty.
     */
    public function popLast(): mixed;

    /**
     * Returns true if this map contains a mapping for the specified key.
     *
     * @param K $key The key whose presence in this map is to be tested.
     * @return bool True if this map contains a mapping for the specified key.
     */
    public function containsKey($key): bool;

    /**
     * Returns true if this map maps one or more keys to the specified value.
     *
     * @param V $value The value whose presence in this map is to be tested.
     * @return bool True if this map maps one or more keys to the specified value.
     */
    public function containsValue($value): bool;

    /**
     * Returns the entry immediately preceding the entry for the specified key.
     *
     * @param K $key The key whose preceding entry is to be returned.
     * @return Entry<K,V>|null The previous entry, or null if there is no such entry.
     */
    public function previous($key): mixed;

     /**
     * Returns the entry immediately following the entry for the specified key.
     *
     * @param K $key The key whose next entry is to be returned.
     * @return Entry<K,V>|null The next entry, or null if there is no such entry.
     */
    public function next($key): mixed;

     /**
     * Removes all mappings from this map.
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Returns the number of key-value mappings in this map.
     *
     * @return int The number of entries in this map.
     */
    public function size(): int;

    /**
     * Returns true if this map contains no key-value mappings.
     *
     * @return bool True if this map contains no entries.
     */
    public function isEmpty(): bool;

    /**
     * Returns a view of the entries contained in this map.
     *
     * @return TreeMapView A collection view of the map's entries.
     */
    public function entrySet(): mixed;

    /**
     * Returns a view of the keys contained in this map.
     *
     * @return TreeMapView A collection view of the map's keys.
     */
    public function keySet(): mixed;

    /**
     * Returns a view of the values contained in this map.
     *
     * @return TreeMapView A collection view of the map's values.
     */
    public function values(): mixed;
}
?>