<?php
// Riku Theodorou

declare(strict_types=1);

require_once "Map.php";
require_once "Comparator.php";
require_once "exceptions.php";
require_once "Objects.php";
require_once "Comparable.php";

/* ------------------------------------- || TreeEntry class ||---------------------------------- */

/**
 * Represents a key-value pair (entry) in a tree map.
 * 
 * @template K K - The type of the key.
 * @template V V - The type of the value.
 * @implements Entry
 */
class TreeEntry implements Entry {
    /** 
     * The key of this entry.
     * 
     * @var K 
     */
    protected mixed $key;

    /** 
     * The value associated with the key.
     * 
     * @var V 
     */
    protected mixed $value;

    /**
     * Constructs a new TreeEntry with the specified key and value.
     *
     * @param K $key The key for this entry.
     * @param V $value The value for this entry.
     */
    public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Sets the key of this entry.
     *
     * @param K|null $key The new key.
     * @return void
     */
    public function setKey($key): void {
        $this->key = $key;
    }

     /**
     * Returns the key of this entry.
     *
     * @return K The key.
     */
    public function getKey(): mixed {
        return $this->key;
    }

    /**
     * Returns the value of this entry.
     *
     * @return V The value.
     */
   public function getValue(): mixed {
    return $this->value;
   }

   /**
     * Sets a new value for this entry and returns the old value.
     *
     * @param V $value The new value.
     * @return V The previous value.
     */
   public function setValue($value): mixed {
    /** @var V $oldvalue */
    $oldValue = $this->value;
    $this->value = $value;
    return $oldValue;
   }

   /**
     * Creates a copy of the given TreeEntry.
     *
     * @param TreeEntry<K,V> $entry The entry to copy.
     * @return TreeEntry<K,V> A new TreeEntry instance with the same key and value.
     */
   public static function copy(TreeEntry $entry): TreeEntry {
    return new self($entry->getKey(), $entry->getValue());
   }

   /**
     * Returns a string representation of the entry in the format "key=value".
     *
     * @return string String representation of this entry.
     */
   public function __tostring(): string {
    return "{$this->key}={$this->value}";
   }
}

/* -----------------------------#TreeEntry class - END#--------------------------------------------- */











/* ------------------------------------- || TreeNode class ||---------------------------------- */

/**
 * Represents a node in a tree structure, holding an entry and references to parent and child nodes.
 * 
 * @template K K - The type of the key.
 * @template V V - The type of the value.
 */
class TreeNode {

    /**
     * Reference to the parent node.
     * 
     * @var TreeNode<K,V>|null
     */
    protected ?TreeNode $parent;

    /**
     * Reference to the left child node.
     * 
     * @var TreeNode<K,V>|null
     */
    protected ?TreeNode $left;

    /**
     * Reference to the right child node.
     * 
     * @var TreeNode<K,V>|null
     */
    protected ?TreeNode $right;

    /**
     * The key-value entry stored in this node.
     * 
     * @var TreeEntry<K,V>|null
     */
    protected ?TreeEntry $entry;

    /**
     * Height of the node in the tree.
     * 
     * @var int
     */
    protected int $height;

    /**
     * Constructs a TreeNode instance.
     * 
     * If $NULL_GUARD is provided, initializes the node with the given key and value,
     * and sets parent, left, and right to the sentinel node ($NULL_GUARD).
     * If $NULL_GUARD is null, creates a sentinel node pointing to itself with no entry and height -1.
     *
     * @param K|null $key The key for the node's entry, or null for sentinel.
     * @param V|null $value The value for the node's entry, or null for sentinel.
     * @param TreeNode<K,V>|null $NULL_GUARD Sentinel node reference, or null to create sentinel.
     */
    protected function __construct($key, $value, ?TreeNode $NULL_GUARD) {
        if($NULL_GUARD !== null) {
            $this->parent = $NULL_GUARD;
            $this->left = $NULL_GUARD;
            $this->right = $NULL_GUARD;
            $this->entry = new TreeEntry($key, $value);
            $this->height = 0;
        } else {
            /* This is for the creation of sentinel node */
            $this->parent = $this;
            $this->left = $this;
            $this->right = $this;
            $this->entry = null;
            $this->height = -1;
        }
    }

    /**
     * Creates a sentinel node used as a null guard.
     * 
     * @return TreeNode<K,V> The sentinel node.
     */
    public static function createSentinel(): TreeNode {
        return new self(null, null, null);
    }

    /**
     * Creates a new tree node with the given key and value.
     * 
     * @param K $key The key for the node's entry.
     * @param V $value The value for the node's entry.
     * @param TreeNode<K,V> $NULL_GUARD The sentinel node used as null guard.
     * @return TreeNode<K,V> The newly created node.
     */
    public static function createNode($key, $value, TreeNode $NULL_GUARD): TreeNode {
        return new self($key, $value, $NULL_GUARD);
    }

    /**
     * Sets the parent node.
     * 
     * @param TreeNode<K,V>|null $parent The new parent node or null.
     * @return void
     */
    public function setParent(?TreeNode $parent): void {
        $this->parent = $parent;
    }

    /**
     * Returns the parent node.
     * 
     * @return TreeNode<K,V>|null The parent node or null if none.
     */
    public function getParent(): ?TreeNode {
        return $this->parent;
    }

    /**
     * Sets the left child node.
     * 
     * @param TreeNode<K,V>|null $left The new left child or null.
     * @return void
     */
    public function setLeft(?TreeNode $left): void {
        $this->left = $left;
    }

    /**
     * Returns the left child node.
     * 
     * @return TreeNode<K,V>|null The left child or null if none.
     */
    public function getLeft(): ?TreeNode {
        return $this->left;
    }

    /**
     * Sets the right child node.
     * 
     * @param TreeNode<K,V>|null $right The new right child or null.
     * @return void
     */
    public function setRight(?TreeNode $right): void {
        $this->right = $right;
    }

    /**
     * Returns the right child node.
     * 
     * @return TreeNode<K,V>|null The right child or null if none.
     */
    public function getRight(): ?TreeNode {
        return $this->right;
    }

    /**
     * Sets the entry (key-value pair) for this node.
     * 
     * @param TreeEntry<K,V>|null $entry The entry to set or null.
     * @return void
     */
    public function setEntry(?TreeEntry $entry): void {
        $this->entry = $entry;
    }

    /**
     * Returns the entry (key-value pair) stored in this node.
     * 
     * @return TreeEntry<K,V>|null The entry or null if none.
     */
    public function getEntry(): ?TreeEntry {
        return $this->entry;
    }

    /**
     * Sets the height of this node.
     * 
     * @param int $height The new height.
     * @return void
     */
    public function setHeight(int $height): void {
        $this->height = $height;
    }

    /**
     * Returns the height of this node.
     * 
     * @return int The height.
     */
    public function getHeight(): int {
        return $this->height;
    }
}

/* -----------------------------#TreeNode class - END#--------------------------------------------- */











/* ------------------------------------- || TreeMap class ||---------------------------------- */

/**
 * TreeMap is a map implementation based on a self-balancing binary search tree.
 * It stores key-value pairs in sorted order according to the keys.
 *
 * This implementation is similar to Java's TreeMap class but uses an AVL tree 
 * for balancing instead of a Red-Black tree.
 *
 * @template K K - The type of keys maintained by this map
 * @template V V - The type of mapped values
 * 
 * @implements Map
 * 
 * This class provides efficient operations for insertion, removal, and lookup
 * with average time complexity of O(log n).
 * 
 * Keys must be comparable either by natural ordering or via a provided comparator.
 * 
 * Common usage:
 * ```php
 * $map = new TreeMap();
 * $map->put($key, $value);
 * $value = $map->get($key);
 * ```
 */
class TreeMap implements Map {

    /**
     * Sentinel node used as a null guard to simplify tree operations.
     * @var TreeNode<K,V>
     */
    private TreeNode $NULL_GUARD;

    /**
     * Root node of the AVL tree representing the map.
     * @var TreeNode<K,V>
     */
    private TreeNode $root;

    /**
     * Number of key-value mappings currently stored in the map.
     * @var int
     */
    private int $size;

    /**
     * Comparator instance used to compare keys for ordering.
     * Must implement Comparator<K>.
     * @var Comparator $comparator Comparator used to order keys.
     */
    private Comparator $comparator;

    /**
     * Closure used for checking equality of keys.
     * Signature: function(K, K): bool
     * @var Closure
     */
    private Closure $equalsKeyFunctionPointer;

    /**
     * Closure used for checking equality of values.
     * Signature: function(V, V): bool
     * @var Closure
     */
    private Closure $equalsValueFunctionPointer;

    /**
     * Modification count to track structural changes to the map
     * (used to detect concurrent modifications during iteration).
     * @var int
     */
    private int $modCount;

    /**
     * Adds a node to the AVL tree and balances the tree as needed.
     *
     * @param TreeNode<K,V> $node The node to add to the tree.
     * @return V|null Returns the previous value associated with the key if replaced, or null if the key is new.
     */
    private function addNode(TreeNode $node) : mixed {
        /** @var TreeNode */ $root = $this->root;
        /** @var TreeNode */ $parent = $this->NULL_GUARD;
        /** @var bool */ $isLeftChild = false;

        while($root !== $this->NULL_GUARD) {
            $parent = $root;

            if(($this->equalsKeyFunctionPointer)($node->getEntry()->getKey(), $root->getEntry()->getKey())){
                /** @var V */ $oldValue = $root->getEntry()->getValue();
                $root->getEntry()->setValue($node->getEntry()->getValue());
                return $oldValue;
            } else if($this->comparator->compare($node->getEntry()->getKey(), $root->getEntry()->getKey()) < 0) {
                $root = $root->getLeft();
                $isLeftChild = true;
            } else {
                $root = $root->getRight();
                $isLeftChild = false;
            }
        }

        if($parent === $this->NULL_GUARD) {
            $this->root = $node;
        } else if($isLeftChild) {
            $parent->setLeft($node);
        } else {
            $parent->setRight($node);
        }

        $node->setParent($parent);

        return null;
    }

    /**
     * Searches for the node with the specified key in the tree.
     *
     * @param K $key The key to search for.
     * @return TreeNode<K,V> The node containing the key, or the sentinel node if not found.
     */
    private function searchNode($key): TreeNode {
        /** @var TreeNode */ $root = $this->root;
        
        while($root !== $this->NULL_GUARD && !($this->equalsKeyFunctionPointer)($key, $root->getEntry()->getKey())) {
            if($this->comparator->compare($key, $root->getEntry()->getKey()) < 0) {
                $root = $root->getLeft();
            } else {
                $root = $root->getRight();
            }
        }
        return $root;
    }

    /**
     * Returns the first node in the tree, starting from the given node, 
     * according to the effective ordering.
     * 
     * If a comparator is provided, it determines the ordering of keys.
     * If no comparator is provided and the key is an object implementing {@see Comparable},
     * the object's natural ordering is used.
     * For primitive keys, normal comparison operators are used.
     *
     * @param TreeNode<K,V> $node The node from which to begin the search.
     * @return TreeNode<K,V> The first node reachable from the given node based on the effective ordering.
     */
    private function firstTreeNode(TreeNode $node): TreeNode {
        while($node->getLeft() !== $this->NULL_GUARD) {
            $node = $node->getLeft();
        }
        return $node;
    }

    /**
     * Returns the last node in the tree, starting from the given node,
     * according to the effective ordering.
     * 
     * If a comparator is provided, it determines the ordering of keys.
     * If no comparator is provided and the key is an object implementing {@see Comparable},
     * the object's natural ordering is used.
     * For primitive keys, normal comparison operators are used.
     *
     * @param TreeNode<K,V> $node The node from which to begin the search.
     * @return TreeNode<K,V> The last node reachable from the given node based on the effective ordering.
     */
    private function lastTreeNode(TreeNode $node): TreeNode {
        while($node->getRight() !== $this->NULL_GUARD) {
            $node = $node->getRight();
        }
        return $node;
    }

    /**
     * Removes the specified node from the tree and rebalances it if necessary.
     * 
     * This operation maintains the AVL balance property after removal.
     * 
     * @param TreeNode<K,V> $node The node to remove from the tree.
     * @return TreeNode<K,V> The node that replaced the removed node, 
     *                       or the sentinel node if the subtree becomes empty.
     */
    private function removeNode(TreeNode $node): TreeNode {
        /** @var TreeNode */ $current = $this->NULL_GUARD;
        /** @var TreeNode */ $parent = $this->NULL_GUARD;
        /** @var TreeNode */ $child = $this->NULL_GUARD;

        if($node->getLeft() === $this->NULL_GUARD || $node->getRight() === $this->NULL_GUARD) {
            $current = $node;
        } else {
            $current = $this->firstTreeNode($node->getRight());
        }

        if($current->getLeft() === $this->NULL_GUARD) {
            $child = $current->getRight();
        } else {
            $child = $current->getLeft();
        }

        $parent = $current->getParent();
        if($parent === $this->NULL_GUARD) {
            $this->root = $child;
        } else if($current === $parent->getLeft()) {
            $parent->setLeft($child);
        } else {
            $parent->setRight($child);
        }

        //if($child !== $this->NULL_GUARD)
        $child->setParent($parent);

        if($node !== $current) {
            $node->getEntry()->setKey($current->getEntry()->getKey());
            $node->getEntry()->setValue($current->getEntry()->getValue());
        }

        return $current;
    }

    /**
     * Returns the node that precedes the given node according to the effective ordering.
     * 
     * If a comparator is provided, it determines the ordering of keys.
     * If no comparator is provided and the key is an object implementing {@see Comparable},
     * the object's natural ordering is used.
     * For primitive keys, normal comparison operators are used.
     *
     * @param TreeNode<K,V> $node The reference node.
     * @return TreeNode<K,V> The previous node in the ordering, 
     *                       or the sentinel node if there is no previous node.
     */
    private function previousTreeNode(TreeNode $node): TreeNode {
        if($node->getLeft() !== $this->NULL_GUARD) {
            return $this->lastTreeNode($node->getLeft());
        } else {
            /** @var TreeNode */ $current = $node->getParent();
            while($current !== $this->NULL_GUARD && $node === $current->getLeft()) {
                $node = $node->getParent();
                $current = $node->getParent();
            }
            return $current;
        }
    }

    /**
     * Returns the node that follows the given node according to the effective ordering.
     * 
     * If a comparator is provided, it determines the ordering of keys.
     * If no comparator is provided and the key is an object implementing {@see Comparable},
     * the object's natural ordering is used.
     * For primitive keys, normal comparison operators are used.
     *
     * @param TreeNode<K,V> $node The reference node.
     * @return TreeNode<K,V> The next node in the ordering, 
     *                       or the sentinel node if there is no next node.
     */
    private function nextTreeNode(TreeNode $node): TreeNode {
        if($node->getRight() !== $this->NULL_GUARD) {
            return $this->firstTreeNode($node->getRight());
        } else {
            /** @var TreeNode */ $current = $node->getParent();
            while($current !== $this->NULL_GUARD && $node === $current->getRight()) {
                $node = $node->getParent();
                $current = $node->getParent();
            }
            return $current;
        }
    }

    /**
     * Removes all nodes from the tree starting at the specified node.
     * 
     * This method is intended to clear the entire tree structure and is 
     * typically called internally by {@see clear()} with the root node as the argument.
     * 
     * All node references are released to allow garbage collection.
     *
     * @param TreeNode<K,V> $root The node from which to start clearing (usually the root).
     * @return void
     */
    private function clearTree(TreeNode $root): void {
        if($root === $this->NULL_GUARD) {
            return;
        }

        $this->clearTree($root->getLeft());
        $this->clearTree($root->getRight());
        $root->setParent(null);
        $root->setLeft(null);
        $root->setRight(null);
        $root->getEntry()->setKey(null);
        $root->getEntry()->setValue(null);
        $root->setEntry(null);
    }

    /**
     * Updates the height of the specified node based on the heights of its children.
     *
     * The height is typically the maximum height of the left and right child nodes plus one.
     * This method helps maintain the AVL tree balance property after modifications.
     *
     * @param TreeNode<K,V> $node The node for which the height is being updated.
     * @return void
     */
    private function setHeight(TreeNode $node): void {
        /** @var int */ $hLeft = $node->getLeft()->getHeight();
        /** @var int */ $hRight = $node->getRight()->getHeight();
        $node->setHeight((($hLeft > $hRight) ? $hLeft : $hRight) + 1);
    }

    /**
     * Calculates the balance factor of the given node.
     *
     * The balance factor is defined as:
     * (height of right subtree + 1) - (height of left subtree + 1).
     *
     * A positive balance factor indicates the right subtree is taller,
     * a negative value indicates the left subtree is taller,
     * and zero means both subtrees have equal height.
     *
     * @param TreeNode $node The node for which to calculate the balance factor.
     * @return int The balance factor of the node.
     */
    private function balanceFactor(TreeNode $node): int {
        return ($node->getRight()->getHeight() + 1) - ($node->getLeft()->getHeight() + 1);
    }

    /**
     * Performs a left rotation on the specified node to rebalance the AVL tree.
     *
     * In this operation:
     * - The node's right child (pivot) becomes the new root of the rotated subtree.
     * - The pivot's left subtree is moved to be the right subtree of the original node.
     * - Parent references are updated to maintain correct tree structure.
     * - Heights of the affected nodes are recalculated after rotation.
     *
     * This rotation is typically applied when the right subtree is heavier,
     * to restore the AVL tree's balance property.
     *
     * @param TreeNode $node The node at which to perform the left rotation.
     * @return void
     */
    private function leftRotation(TreeNode $node): void {
        /** @var TreeNode */ $parent = $node->getParent();

        /** @var TreeNode */ $pivot = $node->getRight();
        $node->setRight($pivot->getLeft());
        if($pivot->getLeft() !== $this->NULL_GUARD) {
            $pivot->getLeft()->setParent($node);
        }

        $pivot->setParent($parent);
        if($parent === $this->NULL_GUARD) {
            $this->root = $pivot;
        } else if($node === $parent->getLeft()) {
            $parent->setLeft($pivot);
        } else {
            $parent->setRight($pivot);
        }

        $pivot->setLeft($node);
        $node->setParent($pivot);

        $this->setHeight($node);
        $this->setHeight($pivot);
    }

    /**
     * Performs a right rotation on the specified node to rebalance the AVL tree.
     *
     * In this operation:
     * - The node's left child (pivot) becomes the new root of the rotated subtree.
     * - The pivot's right subtree is moved to be the left subtree of the original node.
     * - Parent references are updated to maintain correct tree structure.
     * - Heights of the affected nodes are recalculated after rotation.
     *
     * This rotation is typically applied when the left subtree is heavier,
     * to restore the AVL tree's balance property.
     *
     * @param TreeNode $node The node at which to perform the right rotation.
     * @return void
     */
    private function rightRotation(TreeNode $node): void {
        /** @var TreeNode */ $parent = $node->getParent();

        /** @var TreeNode */ $pivot = $node->getLeft();
        $node->setLeft($pivot->getRight());
        if($pivot->getRight() !== $this->NULL_GUARD) {
            $pivot->getRight()->setParent($node);
        }

        $pivot->setParent($parent);
        if($parent === $this->NULL_GUARD) {
            $this->root = $pivot;
        } else if($node === $parent->getLeft()) {
            $parent->setLeft($pivot);
        } else {
            $parent->setRight($pivot);
        }

        $pivot->setRight($node);
        $node->setParent($pivot);

        $this->setHeight($node);
        $this->setHeight($pivot);
    }

    /**
     * Performs a double left rotation (right-left rotation) on the given node to rebalance the AVL tree.
     *
     * This operation consists of two steps:
     * 1. A right rotation on the right child of the given node.
     * 2. A left rotation on the given node itself.
     *
     * It is typically applied when the right subtree's left child causes imbalance,
     * restoring the AVL tree's balance property.
     *
     * @param TreeNode $node The node at which to perform the double left rotation.
     * @return void
     */
    private function doubleLeftRotation(TreeNode $node): void {
        $this->rightRotation($node->getRight());
        $this->leftRotation($node);
    }

    /**
     * Performs a double right rotation (left-right rotation) on the given node to rebalance the AVL tree.
     *
     * This operation consists of two steps:
     * 1. A left rotation on the left child of the given node.
     * 2. A right rotation on the given node itself.
     *
     * It is typically applied when the left subtree's right child causes imbalance,
     * restoring the AVL tree's balance property.
     *
     * @param TreeNode $node The node at which to perform the double right rotation.
     * @return void
     */
    private function doubleRightRotation(TreeNode $node): void {
        $this->leftRotation($node->getLeft());
        $this->rightRotation($node);
    }

    /**
     * Checks the balance factor of the given node and performs the necessary rotations
     * to restore the AVL tree's balance property.
     *
     * If the node is right-heavy (balance factor >= 2), it performs either a single left rotation
     * or a double left rotation (right-left) depending on the balance factor of the right child.
     * 
     * If the node is left-heavy (balance factor <= -2), it performs either a single right rotation
     * or a double right rotation (left-right) depending on the balance factor of the left child.
     *
     * @param TreeNode $node The node at which to check and correct the balance.
     * @return void
     */
    private function checkTreeBalance(TreeNode $node): void {
        /** @var int */ $bf = $this->balanceFactor($node);

        if($bf >= 2) {
            if($this->balanceFactor($node->getRight()) == -1) {
                $this->doubleLeftRotation($node);
            } else {
                $this->leftRotation($node);
            }
        } else if($bf <= -2) {
            if($this->balanceFactor($node->getLeft()) == 1) {
                $this->doubleRightRotation($node);
            } else {
                $this->rightRotation($node);
            }
        }
    }

    /**
     * Automatically balances the tree starting from the given node up to the root.
     *
     * This method traverses upwards from the specified node towards the root,
     * checking and restoring the AVL tree balance at each node by applying rotations if needed,
     * and updating the height of each node along the path.
     *
     * @param TreeNode $node The node from which to start balancing upwards.
     * @return void
     */
    private function autoBalance(TreeNode $node): void {
        while($node !== $this->NULL_GUARD) {
            $this->checkTreeBalance($node);
            $this->setHeight($node);
            $node = $node->getParent();
        }
    }

    /**
     * Constructs a new TreeMap instance.
     *
     * Initializes the AVL tree structure with a sentinel NULL_GUARD node and sets
     * the root to this sentinel. The size of the map is initialized to zero.
     *
     * The comparator used for key ordering can be:
     * - Explicitly provided as a Comparator instance or a callable function.
     * - If null, a default comparator is created that:
     *     - Uses the key's own `compareTo` method if it exists (for objects).
     *     - Uses numeric comparison for numeric keys.
     *     - Uses string comparison for string keys.
     *     - Uses the spaceship operator `<=>` for other types.
     *
     * Two closure functions are initialized to check key equality and value equality:
     * - If the objects have an `equals` method, it is used.
     * - Otherwise, strict equality (`===`) is used.
     *
     * The modification count `modCount` is initialized to zero, used to track structural changes.
     *
     * @param Comparator|callable|null $comparator Optional custom comparator for keys.
     */
    public function __construct(Comparator|callable $comparator = null) {
    $this->NULL_GUARD = TreeNode::createSentinel();
    $this->root = $this->NULL_GUARD;
    $this->size = 0;

    $this->comparator = $comparator === null
    ? new class implements Comparator {
        public function compare($object1, $object2): int {
            if(is_object($object1) && method_exists($object1, "compareTo")) {
                return $object1->compareTo($object2);
            }

            if(is_numeric($object1) && is_numeric($object2)) {
                return $object1 <=> $object2;
            }

            if(is_string($object1) && is_string($object2)) {
                return strcmp($object1, $object2);
            }

            return $object1 <=> $object2;
        }
    }
    : (is_callable($comparator)
        ? new class($comparator) implements Comparator {
            private $functionName;

            public function __construct(callable $functionName) {
                $this->functionName = $functionName;
            }

            public function compare($object1, $object2): int {
                return ($this->functionName)($object1, $object2);
            }
        }
        : $comparator);

    $this->equalsKeyFunctionPointer = function ($object1, $object2) {
        static $hasEquals = null;

        if($hasEquals == null) {
            $hasEquals = is_object($object1) && method_exists($object1, "equals");
        }

        return $hasEquals ? $object1->equals($object2) : $object1 === $object2;
    };

    $this->equalsValueFunctionPointer = function ($object1, $object2) {
        static $hasEquals = null;

        if($hasEquals == null) {
            $hasEquals = is_object($object1) && method_exists($object1, "equals");
        }

        return $hasEquals ? $object1->equals($object2) : $object1 === $object2;
    };


    $this->modCount = 0;
}


    /**
     * Inserts a key-value pair into the TreeMap.
     * 
     * If the key already exists in the map, its value is updated with the new value,
     * and the old value is returned.
     * If the key does not exist, a new entry is created and null is returned.
     * 
     * The keys are ordered according to the comparator or natural ordering.
     * 
     * @param K $key The key to insert or update.
     * @param V $value The value associated with the key.
     * 
     * @return V|null The previous value associated with the key, or null if there was none.
     */
    public function put($key, $value): mixed {
        if($key === null) {
            throw new NullPointerException("Key cannot be null");
        }

        /** @var TreeNode */ $node = TreeNode::createNode($key, $value, $this->NULL_GUARD);
        /** @var V */ $oldValue = $this->addNode($node);

        if($oldValue === null) { // if it's null then that means a new node has been added to the tree
            $this->size += 1;
            $this->autoBalance($node);
            $this->modCount += 1;
        }

        return $oldValue;
    }

   /**
    * Retrieves the value associated with the specified key.
    * 
    * @param K $key The key whose associated value is to be returned.
    * 
    * @return V|null The value associated with the specified key, or null if the key is not found.
    */
    public function get($key): mixed {
        if($key === null) {
            throw new NullPointerException("Key cannot be null");
        }
        return $this->searchNode($key)->getEntry()->getValue();
    }

    /**
     * Removes the mapping for the specified key from this map if present.
     *
     * @param K $key The key whose mapping is to be removed from the map.
     * 
     * @throws NullPointerException if the key is null.
     * 
     * @return V|null The previous value associated with the specified key, or null if there was no mapping for the key.
     */
    public function remove($key): mixed {
        if($key === null) {
            throw new NullPointerException("Key cannot be null");
        }

        /** @var TreeNode */ $root = $this->searchNode($key);
        if($root === $this->NULL_GUARD) {
            return null;
        } else {
            /** @var V */ $oldValue = $root->getEntry()->getValue();
            $this->autoBalance(($root = $this->removeNode($root))->getParent());

            $root->setParent(null);
            $root->setLeft(null);
            $root->setRight(null);
            $root->getEntry()->setKey(null);
            $root->getEntry()->setValue(null);
            $root->setEntry(null);

            $this->size -= 1;
            $this->modCount += 1;

            return $oldValue;
        }
    }

    /**
     * Removes the entry for the specified key only if it is currently mapped to the specified value.
     *
     * @param K $key The key whose mapping is to be removed if it matches the specified value.
     * @param V $value The value expected to be associated with the specified key.
     * 
     * @return bool True if the entry was removed, false otherwise.
     */
    public function removeIfEquals($key, $value): bool {
        /** @var TreeNode */ $root = $this->searchNode($key);
        if($root === $this->NULL_GUARD) {
            return false;
        }

        if(($this->equalsValueFunctionPointer)($value, $root->getEntry()->getValue())) {
            $this->autoBalance(($root = $this->removeNode($root))->getParent());

            $root->setParent(null);
            $root->setLeft(null);
            $root->setRight(null);
            $root->getEntry()->setKey(null);
            $root->getEntry()->setValue(null);
            $root->setEntry(null);

            $this->size -= 1;
            $this->modCount += 1;

            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the first entry in the TreeMap according to the sorting order defined by the comparator.
     * This is the entry with the "lowest" key based on the comparator or natural ordering.
     *
     * @return Entry<K,V>|null The first entry in the map, or null if the map is empty.
     */
    public function first(): mixed {
        if($this->root === $this->NULL_GUARD) {
            return null;
        }
        return TreeEntry::copy($this->firstTreeNode($this->root)->getEntry());
    }

    /**
     * Returns the last entry in the TreeMap according to the sorting order defined by the comparator.
     * This is the entry with the "highest" key based on the comparator or natural ordering.
     *
     * @return Entry<K,V>|null The last entry in the map, or null if the map is empty.
     */
    public function last(): mixed {
        if($this->root === $this->NULL_GUARD) {
            return null;
        }
        return TreeEntry::copy($this->lastTreeNode($this->root)->getEntry());
    }

    /**
     * Removes and returns the first entry in the TreeMap according to the sorting order.
     * The "first" entry is determined by the comparator or natural ordering of the keys.
     * If the map is empty, returns null.
     *
     * This method removes the node corresponding to the first entry, balances the tree,
     * updates the size and modification count, and clears references of the removed node.
     *
     * @return Entry<K,V>|null The removed first entry, or null if the TreeMap is empty.
     */
    public function popFirst(): mixed {
        if($this->root === $this->NULL_GUARD) {
            return null;
        }

        /** @var TreeNode */ $root = $this->firstTreeNode($this->root);
        /** @var Entry<K, V> */ $entry = TreeEntry::copy($root->getEntry());

        $this->autoBalance(($root = $this->removeNode($root))->getParent());
        $this->size -= 1;

        $root->setParent(null);
        $root->setLeft(null);
        $root->setRight(null);
        $root->getEntry()->setKey(null);
        $root->getEntry()->setValue(null);
        $root->setEntry(null);

        $this->modCount += 1;

        return $entry;
    }

    /**
     * Removes and returns the last entry in the TreeMap according to the sorting order.
     * The "last" entry is determined by the comparator or natural ordering of the keys.
     * If the map is empty, returns null.
     *
     * This method removes the node corresponding to the last entry, balances the tree,
     * updates the size and modification count, and clears references of the removed node.
     *
     * @return Entry<K,V>|null The removed last entry, or null if the TreeMap is empty.
     */
    public function popLast(): mixed {
        if($this->root === $this->NULL_GUARD) {
            return null;
        }

        /** @var TreeNode */ $root = $this->lastTreeNode($this->root);
        /** @var Entry<K, V> */ $entry = TreeEntry::copy($root->getEntry());

        $this->autoBalance(($root = $this->removeNode($root))->getParent());
        $this->size -= 1;

        $root->setParent(null);
        $root->setLeft(null);
        $root->setRight(null);
        $root->getEntry()->setKey(null);
        $root->getEntry()->setValue(null);
        $root->setEntry(null);

        $this->modCount += 1;

        return $entry;
    }

    /**
     * Checks if the TreeMap contains a mapping for the specified key.
     *
     * @param K $key The key to check for presence in the map.
     * @return bool True if the key exists in the map, false otherwise.
     * @throws NullPointerException if the key is null.
     */
    public function containsKey($key): bool {
        if($key === null) {
            throw new NullPointerException("Key cannot be null");
        }
        return $this->searchNode($key) !== $this->NULL_GUARD;
    }

    /**
     * Checks if the TreeMap contains one or more mappings to the specified value.
     *
     * This method performs a breadth-first traversal of the tree to search for the value.
     *
     * @param V $value The value to search for in the map.
     * @return bool True if the value exists in the map, false otherwise.
     */
    public function containsValue($value): bool {
        /** @var SplQueue */ $queue = new SplQueue();
        $queue->enqueue($this->root);

        while($queue->count() !== 0) {
            /** @var TreeNode */ $node = $queue->dequeue();

            if(Objects::equals($value, $node->getEntry()->getValue())) {
                return true;
            }

            if($node->getLeft() !== $this->NULL_GUARD) {
                $queue->enqueue($node->getLeft());
            }
            if($node->getRight() !== $this->NULL_GUARD) {
                $queue->enqueue($node->getRight());
            }
        }

        return false;
    }

    /**
     * Returns the entry immediately preceding the entry for the given key in the TreeMap,
     * or null if the key is not found or there is no preceding entry.
     *
     * @param K $key The key whose preceding entry is to be returned.
     * @return Entry<K,V>|null The entry immediately before the given key, or null if none exists.
     */
    public function previous($key): mixed {
        /** @var TreeNode */ $root = $this->searchNode($key);
        if($root === $this->NULL_GUARD) {
            return null;
        }

        /** @var TreeNode */ $previousNode = $this->previousTreeNode($root);
        if($previousNode === $this->NULL_GUARD) {
            return null;
        }
        
        return TreeEntry::copy($previousNode->getEntry());
    }

    /**
     * Returns the entry immediately following the entry for the given key in the TreeMap,
     * or null if the key is not found or there is no following entry.
     *
     * @param K $key The key whose succeeding entry is to be returned.
     * @return Entry<K,V>|null The entry immediately after the given key, or null if none exists.
     */
    public function next($key): mixed {
        /** @var TreeNode */ $root = $this->searchNode($key);
        if($root === $this->NULL_GUARD) {
            return null;
        }

        /** @var TreeNode */ $nextNode = $this->nextTreeNode($root);
        if($nextNode === $this->NULL_GUARD) {
            return null;
        }
        
        return TreeEntry::copy($nextNode->getEntry());
    }

    /**
     * Removes all entries from the tree, resetting it to an empty state.
     *
     * @return void
     */
    public function clear(): void {
        $this->clearTree($this->root);
        $this->root = $this->NULL_GUARD;
        $this->NULL_GUARD->setParent($this->NULL_GUARD);
        $this->NULL_GUARD->setLeft($this->NULL_GUARD);
        $this->NULL_GUARD->setRight($this->NULL_GUARD);
        $this->NULL_GUARD->setEntry(null);
        $this->size = 0;
        $this->modCount += 1;
    }

    /**
     * Returns the number of entries currently stored in the tree.
     *
     * @return int The size of the tree.
     */
    public function size(): int {
        return $this->size;
    }

    /**
     * Checks whether the tree is empty.
     *
     * @return bool True if the tree contains no entries, false otherwise.
     */
    public function isEmpty(): bool {
        return $this->size === 0;
    }

    /**
     * Returns a view of the entries contained in the tree map.
     * 
     * The returned object is an anonymous class implementing the TreeMapView interface.
     * It supports iteration over the tree entries in sorted order.
     * 
     * This view provides:
     * - Standard PHP iteration support, including usage in `foreach` loops.
     * - Full compatibility with PHP's `foreach` construct for easy traversal.
     * - Java-like iteration syntax compatibility for users familiar with Java collections.
     * 
     * @return TreeMapView<K,V> An iterable view over the map entries.
     */
    public function entrySet(): mixed {
        $outer = $this; // capture outer instance for anonymous classes

        return new class($outer) implements TreeMapView {

            private $outer;

            public function __construct($outer) {
                $this->outer = $outer;
            }

            public function getIterator(): TreeIterator {
                $outer = $this->outer;

                return new class($outer) implements TreeIterator {

                    private $outer;
                    private TreeNode $iterator;
                    private TreeNode $trackNode;
                    private int $expectedModCount;
                    private int $index;

                    /**
                     * Closure::bind creates a new Closure (anonymous function) with a specific bound object and class scope.
                     * 
                     * This allows the Closure to access private or protected members of the bound object,
                     * even if called from outside the original class scope.
                     * 
                     * Parameters:
                     * - The first argument is the Closure to be bound.
                     * - The second argument is the object to which `$this` inside the Closure will refer.
                     * - The third argument is the class scope for access control (usually the class name of the bound object).
                     * 
                     * The bound Closure can be stored and called later multiple times, maintaining the bound context.
                     * 
                     * ---
                     * 
                     * Difference from `Closure::call`:
                     * 
                     * - `Closure::call` immediately invokes the Closure with the given object bound to `$this`.
                     * - It cannot be reused later since it calls the Closure right away.
                     * - `Closure::call` does not accept a class scope parameter; it uses the scope of the Closure itself.
                     * 
                     * Use `Closure::bind` when you want to create a new Closure with bound context for later reuse,
                     * and `Closure::call` for a one-time immediate invocation with a specific `$this` context.
                     */
                    public function __construct($outer) {
                        $this->outer = $outer;

                        // Access NULL_GUARD and modCount from outer via closure binding
                        $this->trackNode = (function() { return $this->NULL_GUARD; })->call($this->outer);
                        $this->expectedModCount = (function() { return $this->modCount; })->call($this->outer);

                        // Call firstTreeNode method bound to outer
                        $firstTreeNode = Closure::bind(function($root) {
                            return $this->firstTreeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $root = (function() { return $this->root; })->call($outer);
                        $this->iterator = $firstTreeNode($root);

                        $this->index = 0;
                    }

                    private function checkForModifications(): void {
                        $modCount = (function() { return $this->modCount; })->call($this->outer);

                        if ($modCount !== $this->expectedModCount) {
                            throw new ConcurrentModificationException(
                                "The iterator is no longer valid. Modifications have been made."
                            );
                        }
                    }

                    public function hasPreviousElement(): bool {
                        $NULL_GUARD = (function() { return $this->NULL_GUARD; })->call($this->outer);
                        return $this->iterator !== $NULL_GUARD;
                    }

                    public function previousElement(): TreeEntry {
                        $this->checkForModifications();

                        $NULL_GUARD = (function() { return $this->NULL_GUARD; })->call($this->outer);
                        if ($this->iterator === $NULL_GUARD) {
                            throw new NoSuchElementException("Iterator has no more elements");
                        }

                        $this->trackNode = $this->iterator;

                        /** @var TreeEntry */ 
                        $entry = TreeEntry::copy($this->iterator->getEntry());

                        // Call previousTreeNode bound to outer
                        $previousTreeNode = Closure::bind(function($node) {
                            return $this->previousTreeNode($node);
                        }, $this->outer, get_class($this->outer));

                        $this->iterator = $previousTreeNode($this->iterator);

                        return $entry;
                    }

                    public function hasNextElement(): bool {
                        $nullGuard = (function() { return $this->NULL_GUARD; })->call($this->outer);
                        return $this->iterator !== $nullGuard;
                    }

                    public function nextElement(): TreeEntry {
                        $this->checkForModifications();

                        $nullGuard = (function() { return $this->NULL_GUARD; })->call($this->outer);
                        if ($this->iterator === $nullGuard) {
                            throw new NoSuchElementException("Iterator has no more elements");
                        }

                        $this->trackNode = $this->iterator;

                        /** @var TreeEntry */
                        $entry = TreeEntry::copy($this->iterator->getEntry());

                        // Call nextTreeNode bound to outer
                        $nextTreeNode = Closure::bind(function($node) {
                            return $this->nextTreeNode($node);
                        }, $this->outer, get_class($this->outer));

                        $this->iterator = $nextTreeNode($this->iterator);

                        return $entry;
                    }

                    public function reset(IteratorOptions $iteratorOptions): static {
                        $this->checkForModifications();

                        $firstTreeNode = Closure::bind(function($root) {
                            return $this->firstTreeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $lastTreeNode = Closure::bind(function($root) {
                            return $this->lastTreeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $NULL_GUARD = (function() { return $this->NULL_GUARD; })->call($this->outer);
                        $root = (function() { return $this->root;})->call($this->outer);

                        switch ($iteratorOptions) {
                            case IteratorOptions::HEAD:
                                $this->iterator = $firstTreeNode($root);
                                break;
                            case IteratorOptions::TAIL:
                                $this->iterator = $lastTreeNode($root);
                                break;
                            default:
                                throw new InvalidArgumentException("Invalid iterator option");
                        }

                        $this->trackNode = $NULL_GUARD;

                        return $this;
                    }

                    public function remove(): void {
                        $this->checkForModifications();

                        $NULL_GUARD = (function() { return $this->NULL_GUARD; })->call($this->outer);

                        if ($this->trackNode === $NULL_GUARD) {
                            return;
                        }

                        $iterator = $this->trackNode;

                        // Call removeNode and autoBalance bound to outer
                        $removeNode = Closure::bind(function($node) {
                            return $this->removeNode($node);
                        }, $this->outer, get_class($this->outer));

                        $autoBalance = Closure::bind(function($node) {
                            $this->autoBalance($node);
                        }, $this->outer, get_class($this->outer));

                        $iterator = $removeNode($iterator);
                        $autoBalance($iterator->getParent());

                        // Decrement outer size safely
                        (function() { $this->size -= 1; })->call($this->outer);

                        $iterator->setParent(null);
                        $iterator->setLeft(null);
                        $iterator->setRight(null);
                        $iterator->getEntry()->setKey(null);
                        $iterator->getEntry()->setValue(null);
                        $iterator->setEntry(null);

                        $this->trackNode = $NULL_GUARD;

                        // Increment modCount on outer and expectedModCount on this
                        (function() { $this->modCount += 1; })->call($this->outer);
                        $this->expectedModCount += 1;
                    }

                    // Placeholder implementations for Iterator interface methods 
                    public function current() : mixed {
                        return $this->iterator->getEntry();
                    }

                    public function key() : mixed {
                        return $this->index;
                    }


                    public function next() : void {
                        $this->iterator = (function($iterator){
                            return $this->nextTreeNode($iterator);
                        })->call($this->outer, $this->iterator);
                        $this->index += 1;
                    }
                    
                    public function rewind() : void {
                        $this->reset(IteratorOptions::HEAD);
                        $this->index = 0;
                    }

                    public function valid() : bool {
                        $NULL_GUARD = (function(){return $this->NULL_GUARD;})->call($this->outer);
                        return $this->iterator !== $NULL_GUARD;
                    }
                };
            }

            public function size(): int {
                return (function() { return $this->size; })->call($this->outer);
            }

            public function clear(): void {
                $clear = Closure::bind(function() {
                    $this->clear();
                }, $this->outer, get_class($this->outer));
                $clear();
            }

            public function remove(mixed $object): bool {
                throw new UnsupportedOperationException("This operation is not supported for Map.Entry<K,V> objects");
            }
        };
    }



    /**
     * Returns a view of the keys contained in the tree map.
     * 
     * The returned object is an anonymous class implementing the TreeMapView interface,
     * which provides an iterable view over the keys in sorted order.
     * 
     * This view supports:
     * - Standard PHP iteration, allowing usage in `foreach` loops.
     * - Java-like iteration syntax compatibility for users familiar with Java collections.
     * 
     * Note:
     * The anonymous class internally captures the outer TreeMap instance to access the tree structure.
     * 
     * @return TreeMapView An iterable view over the map keys.
     */
    public function keySet(): mixed {
        $outer = $this; // capture outer instance for anonymous classes

        return new class($outer) implements TreeMapView {

            private $outer;

            public function __construct($outer) {
                $this->outer = $outer;
            }

            public function getIterator(): TreeIterator {
                $outer = $this->outer;

                return new class($outer) implements TreeIterator {

                    private $outer;
                    private TreeNode $iterator;
                    private TreeNode $trackNode;
                    private int $expectedModCount;
                    private int $index;

                    private TreeNode $NULL_GUARD;
                    private TreeNode $root;
                    private Closure $firstTreeNode;
                    private Closure $lastTreeNode;
                    private Closure $previousTreeNode;
                    private Closure $nextTreeNode;
                    private Closure $autoBalance;
                    private Closure $removeNode;
                    private Closure $getModCount;
                    private Closure $increaseModCount;
                    private Closure $decreaseSize;

                    public function current() : mixed {
                        return $this->iterator->getEntry()->getKey();
                    }

                    public function key() : mixed {
                        return $this->index;
                    }


                    public function next() : void {
                        $this->iterator = ($this->nextTreeNode)($this->iterator);
                        $this->index += 1;
                    }
                    
                    public function rewind() : void {
                        $this->reset(IteratorOptions::HEAD);
                        $this->index = 0;
                    }

                    public function valid() : bool {
                        return $this->iterator !== $this->NULL_GUARD;
                    }


                    /**
                     * Closure::bind creates a new Closure (anonymous function) with a specific bound object and class scope.
                     * 
                     * This allows the Closure to access private or protected members of the bound object,
                     * even if called from outside the original class scope.
                     * 
                     * Parameters:
                     * - The first argument is the Closure to be bound.
                     * - The second argument is the object to which `$this` inside the Closure will refer.
                     * - The third argument is the class scope for access control (usually the class name of the bound object).
                     * 
                     * The bound Closure can be stored and called later multiple times, maintaining the bound context.
                     * 
                     * ---
                     * 
                     * Difference from `Closure::call`:
                     * 
                     * - `Closure::call` immediately invokes the Closure with the given object bound to `$this`.
                     * - It cannot be reused later since it calls the Closure right away.
                     * - `Closure::call` does not accept a class scope parameter; it uses the scope of the Closure itself.
                     * 
                     * Use `Closure::bind` when you want to create a new Closure with bound context for later reuse,
                     * and `Closure::call` for a one-time immediate invocation with a specific `$this` context.
                     */
                    public function __construct($outer) {
                        $this->outer = $outer;

                        $this->NULL_GUARD = (function() { return $this->NULL_GUARD; })->call($this->outer);

                        $this->root = (function() { return $this->root; })->call($this->outer);

                        $this->firstTreeNode = Closure::bind(function ($root) {
                            return $this->firstTreeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $this->lastTreeNode = Closure::bind(function ($root) {
                            return $this->lastTreeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $this->previousTreeNode = Closure::bind(function ($root) {
                            return $this->previousTreeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $this->nextTreeNode = Closure::bind(function ($root) {
                            return $this->nextTreeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $this->autoBalance = Closure::bind(function ($root) {
                            return $this->autoBalance($root);
                        }, $this->outer, get_class($this->outer));

                        $this->removeNode = Closure::bind(function ($root) {
                            return $this->removeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $this->getModCount = Closure::bind(function() {
                            return $this->modCount;
                        },$this->outer, get_class($this->outer));

                        $this->increaseModCount = Closure::bind(function() {
                            $this->modCount += 1;
                        },$this->outer, get_class($this->outer));

                        $this->decreaseSize = Closure::bind(function() {
                            $this->size -= 1;
                        }, $this->outer, get_class($this->outer));

                        $this->iterator = ($this->firstTreeNode)($this->root);
                        $this->trackNode = $this->NULL_GUARD;
                        $this->expectedModCount = ($this->getModCount)();

                        $this->index = 0;
                    }

                    private function checkForModifications(): void {
                        if (($this->getModCount)() !== $this->expectedModCount) {
                            throw new ConcurrentModificationException(
                                "The iterator is no longer valid. Modifications have been made."
                            );
                        }
                    }

                    public function hasPreviousElement(): bool {
                        return $this->iterator !== $this->NULL_GUARD;
                    }

                    /**
                     * @return K
                     */
                    public function previousElement(): mixed {
                        $this->checkForModifications();

                        if ($this->iterator === $this->NULL_GUARD) {
                            throw new NoSuchElementException("Iterator has no more elements");
                        }

                        $this->trackNode = $this->iterator;
                        /** @var K */ $key = $this->iterator->getEntry()->getKey();
                        $this->iterator = ($this->previousTreeNode)($this->iterator);
                        return $key;
                    }

                    public function hasNextElement(): bool {
                        return $this->iterator !== $this->NULL_GUARD;
                    }

                    public function nextElement(): mixed {
                        $this->checkForModifications();

                        if ($this->iterator === $this->NULL_GUARD) {
                            throw new NoSuchElementException("Iterator has no more elements");
                        }

                        $this->trackNode = $this->iterator;
                        /** @var K */ $key = $this->iterator->getEntry()->getKey();
                        $this->iterator = ($this->nextTreeNode)($this->iterator);
                        return $key;
                    }

                    public function reset(IteratorOptions $iteratorOptions): static {
                        $this->checkForModifications();

                        switch ($iteratorOptions) {
                            case IteratorOptions::HEAD:
                                $this->iterator = ($this->firstTreeNode)($this->root);
                                break;
                            case IteratorOptions::TAIL:
                                $this->iterator = ($this->lastTreeNode)($this->root);
                                break;
                            default:
                                throw new InvalidArgumentException("Invalid iterator option");
                        }

                        $this->trackNode = $this->NULL_GUARD;

                        return $this;
                    }

                    public function remove(): void {
                        $this->checkForModifications();

                        if ($this->trackNode === $this->NULL_GUARD) {
                            return;
                        }

                        ($this->autoBalance)(($this->trackNode = ($this->removeNode)($this->trackNode))->getParent());
                        ($this->decreaseSize)();

                        $this->trackNode->setParent(null);
                        $this->trackNode->setLeft(null);
                        $this->trackNode->setRight(null);
                        $this->trackNode->getEntry()->setKey(null);
                        $this->trackNode->getEntry()->setValue(null);
                        $this->trackNode->setEntry(null);

                        $this->trackNode = $this->NULL_GUARD;
                        ($this->increaseModCount)();
                        $this->expectedModCount += 1;
                    }
                };
            }

            public function size(): int {
                return (function () { return $this->size; })->call($this->outer);
            }

            public function clear(): void {
                $clear = Closure::bind(function (){
                    $this->clear();
                },$this->outer, get_class($this->outer));
                $clear();
            }

            public function remove($key): bool {
                $searchNode = Closure::bind(function($key) {
                    return $this->searchNode($key);
                }, $this->outer, get_class($this->outer));

                $autoBalance = Closure::bind(function($key) {
                    $this->autoBalance($key);
                }, $this->outer, get_class($this->outer));

                $removeNode = Closure::bind(function($key) {
                    return $this->removeNode($key);
                }, $this->outer, get_class($this->outer));

                $increaseModCount = Closure::bind(function() {
                    $this->modCount++;
                }, $this->outer, get_class($this->outer));

                $decreaseSize = Closure::bind(function() {
                    $this->size -= 1;
                }, $this->outer, get_class($this->outer));

                $NULL_GUARD = (function(){return $this->NULL_GUARD;})->call($this->outer);

               /** @var TreeNode */ $root = $searchNode($key);

                if ($root === $NULL_GUARD) {
                    return false;
                }

                $autoBalance(($root = $removeNode($root))->getParent());
                $decreaseSize();

                $root->setParent(null);
                $root->setLeft(null);
                $root->setRight(null);
                $root->getEntry()->setKey(null);
                $root->getEntry()->setValue(null);
                $root->setEntry(null);

                $increaseModCount();

                return true;
            }
        };
    }

    /**
     * Returns a view of the values contained in the tree map.
     * 
     * The returned object is an anonymous class implementing the TreeMapView interface,
     * providing an iterable view over the values in the map in sorted key order.
     * 
     * This view supports:
     * - Standard PHP iteration, allowing usage in `foreach` loops.
     * - Java-like iteration syntax for users familiar with Java collections.
     * 
     * Note:
     * The anonymous class captures the outer TreeMap instance to access the underlying data.
     * 
     * @return TreeMapView An iterable view over the map values.
     */
    public function values(): mixed {
        $outer = $this; // capture outer instance for anonymous classes

        return new class($outer) implements TreeMapView {

            private $outer;

            public function __construct($outer) {
                $this->outer = $outer;
            }

            public function getIterator(): TreeIterator {
                $outer = $this->outer;

                return new class($outer) implements TreeIterator {

                    private $outer;
                    private TreeNode $iterator;
                    private TreeNode $trackNode;
                    private int $expectedModCount;
                    private int $index;

                    private TreeNode $NULL_GUARD;
                    private TreeNode $root;
                    private Closure $firstTreeNode;
                    private Closure $lastTreeNode;
                    private Closure $previousTreeNode;
                    private Closure $nextTreeNode;
                    private Closure $autoBalance;
                    private Closure $removeNode;
                    private Closure $getModCount;
                    private Closure $increaseModCount;
                    private Closure $decreaseSize;

                    public function current() : mixed {
                        return $this->iterator->getEntry()->getValue();
                    }

                    public function key() : mixed {
                        return $this->index;
                    }


                    public function next() : void {
                        $this->iterator = ($this->nextTreeNode)($this->iterator);
                        $this->index += 1;
                    }
                    
                    public function rewind() : void {
                        $this->reset(IteratorOptions::HEAD);
                        $this->index = 0;
                    }

                    public function valid() : bool {
                        return $this->iterator !== $this->NULL_GUARD;
                    }


                    /**
                     * Closure::bind creates a new Closure (anonymous function) with a specific bound object and class scope.
                     * 
                     * This allows the Closure to access private or protected members of the bound object,
                     * even if called from outside the original class scope.
                     * 
                     * Parameters:
                     * - The first argument is the Closure to be bound.
                     * - The second argument is the object to which `$this` inside the Closure will refer.
                     * - The third argument is the class scope for access control (usually the class name of the bound object).
                     * 
                     * The bound Closure can be stored and called later multiple times, maintaining the bound context.
                     * 
                     * ---
                     * 
                     * Difference from `Closure::call`:
                     * 
                     * - `Closure::call` immediately invokes the Closure with the given object bound to `$this`.
                     * - It cannot be reused later since it calls the Closure right away.
                     * - `Closure::call` does not accept a class scope parameter; it uses the scope of the Closure itself.
                     * 
                     * Use `Closure::bind` when you want to create a new Closure with bound context for later reuse,
                     * and `Closure::call` for a one-time immediate invocation with a specific `$this` context.
                     */
                    public function __construct($outer) {
                        $this->outer = $outer;

                        $this->NULL_GUARD = (function () {return $this->NULL_GUARD;})->call($this->outer);

                        $this->root = (function () {return $this->root;})->call($this->outer);

                        $this->firstTreeNode = Closure::bind(function($root) {
                            return $this->firstTreeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $this->lastTreeNode = Closure::bind(function($root) {
                            return $this->lastTreeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $this->previousTreeNode = Closure::bind(function($root) {
                            return $this->previousTreeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $this->nextTreeNode = Closure::bind(function($root) {
                            return $this->nextTreeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $this->autoBalance = Closure::bind(function($root) {
                            return $this->autoBalance($root);
                        }, $this->outer, get_class($this->outer));

                        $this->removeNode = Closure::bind(function($root) {
                            return $this->removeNode($root);
                        }, $this->outer, get_class($this->outer));

                        $this->getModCount = Closure::bind(function() {
                            return $this->modCount;
                        }, $this->outer, get_class($this->outer));

                        $this->increaseModCount = Closure::bind(function() {
                            return $this->modCount += 1;
                        }, $this->outer, get_class($this->outer));

                        $this->decreaseSize = Closure::bind(function() {
                            return $this->size -= 1;
                        }, $this->outer, get_class($this->outer));

                        $this->iterator = ($this->firstTreeNode)($this->root);
                        $this->trackNode = $this->NULL_GUARD;
                        $this->expectedModCount = ($this->getModCount)();

                        $this->index = 0;
                    }

                    private function checkForModifications(): void {
                        if (($this->getModCount)() !== $this->expectedModCount) {
                            throw new ConcurrentModificationException(
                                "The iterator is no longer valid. Modifications have been made."
                            );
                        }
                    }

                    public function hasPreviousElement(): bool {
                        return $this->iterator !== $this->NULL_GUARD;
                    }

                    /**
                     * @return V
                     */
                    public function previousElement(): mixed {
                        $this->checkForModifications();

                        if ($this->iterator === $this->NULL_GUARD) {
                            throw new NoSuchElementException("Iterator has no more elements");
                        }

                        $this->trackNode = $this->iterator;
                        /** @var V */ $value = $this->iterator->getEntry()->getValue();
                        $this->iterator = ($this->previousTreeNode)($this->iterator);
                        return $value;
                    }

                    public function hasNextElement(): bool {
                        return $this->iterator !== $this->NULL_GUARD;
                    }

                    public function nextElement(): mixed {
                        $this->checkForModifications();

                        if ($this->iterator === $this->NULL_GUARD) {
                            throw new NoSuchElementException("Iterator has no more elements");
                        }

                        $this->trackNode = $this->iterator;
                        /** @var V */ $value = $this->iterator->getEntry()->getValue();
                        $this->iterator = ($this->nextTreeNode)($this->iterator);
                        return $value;
                    }

                    public function reset(IteratorOptions $iteratorOptions): static {
                        $this->checkForModifications();

                        switch ($iteratorOptions) {
                            case IteratorOptions::HEAD:
                                $this->iterator = ($this->firstTreeNode)($this->root);
                                break;
                            case IteratorOptions::TAIL:
                                $this->iterator = ($this->lastTreeNode)($this->root);
                                break;
                            default:
                                throw new InvalidArgumentException("Invalid iterator option");
                        }

                        $this->trackNode = $this->NULL_GUARD;

                        return $this;
                    }

                    public function remove(): void {
                        $this->checkForModifications();

                        if ($this->trackNode === $this->NULL_GUARD) {
                            return;
                        }

                        ($this->autoBalance)(($this->trackNode = ($this->removeNode)($this->trackNode))->getParent());
                        ($this->decreaseSize)();

                        $this->trackNode->setParent(null);
                        $this->trackNode->setLeft(null);
                        $this->trackNode->setRight(null);
                        $this->trackNode->getEntry()->setKey(null);
                        $this->trackNode->getEntry()->setValue(null);
                        $this->trackNode->setEntry(null);

                        $this->trackNode = $this->NULL_GUARD;
                        ($this->increaseModCount)();
                        $this->expectedModCount += 1;
                    }
                };
            }

            public function size(): int {
                return (function() {return $this->size;})->call($this->outer);
            }

            public function clear(): void {
                (function(){$this->clear();})->call($this->outer);
            }

            public function remove($value): bool {

                $NULL_GUARD = (function(){return $this->NULL_GUARD;})->call($this->outer);
                $treeRoot = (function(){return $this->root;})->call($this->outer);
                $equalsValueFunctionPointer = (function(){return $this->equalsValueFunctionPointer;})->call($this->outer);

                $autoBalance = Closure::bind(function($root) {
                    $this->autoBalance($root);
                }, $this->outer, get_class($this->outer));

                $removeNode = Closure::bind(function($root) {
                    return $this->removeNode($root);
                }, $this->outer, get_class($this->outer));

                $decreaseSize = Closure::bind(function() {
                    $this->size -= 1;
                }, $this->outer, get_class($this->outer));

                $increaseModCount = Closure::bind(function() {
                    $this->modCount += 1;
                }, $this->outer, get_class($this->outer));

                /** @var SplQueue */ $queue = new SplQueue();
                /** TreeNode */ $node = $NULL_GUARD;
                $queue->enqueue($treeRoot);

                while($queue->count() !== 0) {
                    /** @var TreeNode */ $node = $queue->dequeue();

                    if($equalsValueFunctionPointer($value, $node->getEntry()->getValue())) {
                        break;
                    }

                    if($node->getLeft() !== $NULL_GUARD) {
                        $queue->enqueue($node->getLeft());
                    }
                    if($node->getRight() !== $NULL_GUARD) {
                        $queue->enqueue($node->getRight());
                    }
                }

                if($node === $NULL_GUARD) {
                    return false;
                }

                $autoBalance(($node = $removeNode($node))->getParent());
                $decreaseSize();

                $node->setParent(null);
                $node->setLeft(null);
                $node->setRight(null);
                $node->getEntry()->setKey(null);
                $node->getEntry()->setValue(null);
                $node->setEntry(null);

                $increaseModCount();

                return true;
            }
        };
    }

    /**
     * Helper function to traverse the tree in pre-order and apply an action on each node's entry.
     *
     * This traversal processes the parent node before its child nodes,
     * which is useful for operations like copying the tree or prefix expression evaluation.
     *
     * @param TreeNode $node The current node to process.
     * @param callable $action A callback function to execute on each node's entry.
     *                         The callable receives the node's entry as its parameter.
     *
     * @return void
     */
    private function preOrderTraverse(TreeNode $node, callable $action): void {
        if($node === $this->NULL_GUARD) {
            return;
        }

        $action($node->getEntry());
        $this->preOrderTraverse($node->getLeft(), $action);
        $this->preOrderTraverse($node->getRight(), $action);
    }

    /**
     * Helper function to traverse the tree in in-order and apply an action on each node's entry.
     *
     * This traversal visits nodes in sorted order according to the comparator
     * or the natural ordering of the keys, making it suitable for displaying
     * entries in their sorted sequence.
     *
     * @param TreeNode $node The current node to process.
     * @param callable $action A callback function to execute on each node's entry.
     *                         The callable receives the node's entry as its parameter.
     *
     * @return void
     */
    private function inOrderTraverse(TreeNode $node, callable $action): void {
        if($node === $this->NULL_GUARD) {
            return;
        }

        $this->inOrderTraverse($node->getLeft(), $action);
        $action($node->getEntry());
        $this->inOrderTraverse($node->getRight(), $action);
    }

    /**
     * Helper function to traverse the tree in post-order and apply an action on each node's entry.
     *
     * This traversal processes child nodes before their parent node,
     * which is useful for operations that require processing
     * children before their parent (e.g., deletion, freeing resources).
     *
     * @param TreeNode $node The current node to process.
     * @param callable $action A callback function to execute on each node's entry.
     *                         The callable receives the node's entry as its parameter.
     *
     * @return void
     */
    private function postOrderTraverse(TreeNode $node, callable $action): void {
        if($node === $this->NULL_GUARD) {
            return;
        }

        $this->postOrderTraverse($node->getLeft(), $action);
        $this->postOrderTraverse($node->getRight(), $action);
        $action($node->getEntry());
    }

    /**
     * Displays the entries in pre-order traversal.
     *
     * @param bool $newLine Whether to display each entry on a new line or separated by spaces.
     * @return void
     */
    public function preOrderDisplay(bool $newLine) {
        if($newLine) {
            $this->preOrderTraverse($this->root, function(Entry $entry) {
                echo "[{$entry}]<br>";
            });
        } else {
            $this->preOrderTraverse($this->root, function(Entry $entry) {
                echo "[{$entry}] ";
            });
        }
        echo "<br>";
    }

    /**
     * Displays the entries in in-order traversal.
     *
     * This method displays the entries in sorted order according to the comparator's
     * or natural ordering.
     *
     * If `$newLine` is true, each entry is printed on its own line.
     * If false, entries are printed on the same line separated by spaces.
     *
     * @param bool $newLine Whether to display each entry on a new line (true) or in one line separated by spaces (false).
     * @return void
     */
    public function inOrderDisplay(bool $newLine) {
        if($newLine) {
            $this->inOrderTraverse($this->root, function(Entry $entry) {
                echo "[{$entry}]<br>";
            });
        } else {
            $this->inOrderTraverse($this->root, function(Entry $entry) {
                echo "[{$entry}] ";
            });
        }
        echo "<br>";
    }

    /**
     * Displays the entries in post-order traversal.
     *
     * @param bool $newLine Whether to display each entry on a new line or separated by spaces.
     * @return void
     */
    public function postOrderDisplay(bool $newLine) {
        if($newLine) {
            $this->postOrderTraverse($this->root, function(Entry $entry) {
                echo "[{$entry}]<br>";
            });
        } else {
            $this->postOrderTraverse($this->root, function(Entry $entry) {
                echo "[{$entry}] ";
            });
        }
        echo "<br>";
    }

    /**
     * Returns a string representation of the tree.
     *
     * If the tree is empty, returns "{}".
     * Otherwise, returns the entries enclosed in braces,
     * separated by commas, e.g. "{entry1, entry2, entry3}".
     *
     * @return string The string representation of the tree.
     */
    public function __tostring(): string {
        if($this->size == 0) {
            return "{}";
        }

        /** @var TreeIterator<Entry<int,int>> */ $it = $this->entrySet()->getIterator();
        $str = "{" . $it->nextElement();
        while($it->hasNextElement()) {
            $str .= ", {$it->nextElement()}";
        }
        $str .= "}";
        return $str;
    }

    /**
     * Gets the height of the tree.
     *
     * Returns the height of the root node, representing the overall height of the tree.
     *
     * @return int The height of the tree.
     */
    public function heightTree(): int {
        return $this->root->getHeight();
    }

    /**
     * Calculates the balance factor of the entire tree.
     *
     * This method computes the balance factor starting from the root node,
     * which is typically used in balanced tree algorithms like AVL trees.
     *
     * @return int The balance factor of the tree's root node.
     */
    public function balanceFactorTree(): int {
        return $this->balanceFactor($this->root);
    }

    /**
     * Returns a copy of the root tree entry.
     *
     * This method retrieves the root element of the tree and returns a copy of its entry.
     *
     * @template K The type of keys maintained by this map.
     * @template V The type of mapped values.
     *
     * @return TreeEntry<K,V> A copy of the root tree entry.
     */
    public function getTreeRootElement() : mixed {
        return TreeEntry::copy($this->root->getEntry());
    }
}

/* -----------------------------#TreeMap class - END#--------------------------------------------- */

?>