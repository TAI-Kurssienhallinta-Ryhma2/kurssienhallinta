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
 * Summary of Entry
 * @template K
 * @template V
 * @implements Entry<K,V>
 */
class TreeEntry implements Entry {
    /** @var K */
    protected mixed $key;
    /** @var V */
    protected mixed $value;

    /**
     * Summary of __construct
     * @param K $key
     * @param V $value
     */
    public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Summary of setKey
     * @param K $key
     * @return void
     */
    public function setKey($key): void {
        $this->key = $key;
    }

     /**
     * @return K
     */
    public function getKey(): mixed {
        return $this->key;
    }

    /**
     * @return V
     */
   public function getValue(): mixed {
    return $this->value;
   }

   /**
    * @param V $value
    * @return V
    */
   public function setValue($value): mixed {
    /** @var V $oldvalue */
    $oldValue = $this->value;
    $this->value = $value;
    return $oldValue;
   }

   public static function copy(TreeEntry $entry): TreeEntry {
    return new self($entry->getKey(), $entry->getValue());
   }

   public function __tostring(): string {
    return "{$this->key}={$this->value}";
   }
}

/* -----------------------------#TreeEntry class - END#--------------------------------------------- */











/* ------------------------------------- || TreeNode class ||---------------------------------- */

/**
 * @template K
 * @template V
 */
class TreeNode {
    protected ?TreeNode $parent;
    protected ?TreeNode $left;
    protected ?TreeNode $right;
    /** @var TreeEntry<K,V>|null */
    protected ?TreeEntry $entry;
    protected int $height;

    /**
     * Summary of __construct
     * @param K|null $key
     * @param V|null $value
     * @param ?TreeNode $NULL_GUARD
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

    public static function createSentinel(): TreeNode {
        return new self(null, null, null);
    }

    /**
     * Summary of createNode
     * @param K $key
     * @param V $value
     * @param TreeNode $NULL_GUARD
     * @return TreeNode<K, V>
     */
    public static function createNode($key, $value, TreeNode $NULL_GUARD): TreeNode {
        return new self($key, $value, $NULL_GUARD);
    }

    public function setParent(?TreeNode $parent): void {
        $this->parent = $parent;
    }

    public function getParent(): ?TreeNode {
        return $this->parent;
    }

    public function setLeft(?TreeNode $left): void {
        $this->left = $left;
    }

    public function getLeft(): ?TreeNode {
        return $this->left;
    }

    public function setRight(?TreeNode $right): void {
        $this->right = $right;
    }

    public function getRight(): ?TreeNode {
        return $this->right;
    }

    /** @param TreeEntry<K,V>|null $entry */
    public function setEntry(?TreeEntry $entry): void {
        $this->entry = $entry;
    }

    /** @return TreeEntry<K,V>|null */
    public function getEntry(): ?TreeEntry {
        return $this->entry;
    }

    public function setHeight(int $height): void {
        $this->height = $height;
    }

    public function getHeight(): int {
        return $this->height;
    }
}

/* -----------------------------#TreeNode class - END#--------------------------------------------- */











/* ------------------------------------- || TreeMap class ||---------------------------------- */
/**
 * @template K
 * @template V
 * @implements Map<K,V>
 */
class TreeMap implements Map {

    private TreeNode $NULL_GUARD;
    private TreeNode $root;
    private int $size;
    /** @var Comparator<K> */
    private Comparator $comparator;
    private Closure $equalsKeyFunctionPointer;

    private Closure $equalsValueFunctionPointer;
    private int $modCount;

    /**
     * @param TreeNode $node
     * @return V|null
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
     * @param K $key
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

    private function firstTreeNode(TreeNode $root): TreeNode {
        while($root->getLeft() !== $this->NULL_GUARD) {
            $root = $root->getLeft();
        }
        return $root;
    }

    private function lastTreeNode(TreeNode $root): TreeNode {
        while($root->getRight() !== $this->NULL_GUARD) {
            $root = $root->getRight();
        }
        return $root;
    }

    private function removeNode(TreeNode $root): TreeNode {
        /** @var TreeNode */ $current = $this->NULL_GUARD;
        /** @var TreeNode */ $parent = $this->NULL_GUARD;
        /** @var TreeNode */ $child = $this->NULL_GUARD;

        if($root->getLeft() === $this->NULL_GUARD || $root->getRight() === $this->NULL_GUARD) {
            $current = $root;
        } else {
            $current = $this->firstTreeNode($root->getRight());
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

        if($root !== $current) {
            $root->getEntry()->setKey($current->getEntry()->getKey());
            $root->getEntry()->setValue($current->getEntry()->getValue());
        }

        return $current;
    }

    private function previousTreeNode(TreeNode $root): TreeNode {
        if($root->getLeft() !== $this->NULL_GUARD) {
            return $this->lastTreeNode($root->getLeft());
        } else {
            /** @var TreeNode */ $current = $root->getParent();
            while($current !== $this->NULL_GUARD && $root === $current->getLeft()) {
                $root = $root->getParent();
                $current = $root->getParent();
            }
            return $current;
        }
    }

    private function nextTreeNode(TreeNode $root): TreeNode {
        if($root->getRight() !== $this->NULL_GUARD) {
            return $this->firstTreeNode($root->getRight());
        } else {
            /** @var TreeNode */ $current = $root->getParent();
            while($current !== $this->NULL_GUARD && $root === $current->getRight()) {
                $root = $root->getParent();
                $current = $root->getParent();
            }
            return $current;
        }
    }

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

    private function setHeight(TreeNode $root): void {
        /** @var int */ $hLeft = $root->getLeft()->getHeight();
        /** @var int */ $hRight = $root->getRight()->getHeight();
        $root->setHeight((($hLeft > $hRight) ? $hLeft : $hRight) + 1);
    }

    private function balanceFactor(TreeNode $root): int {
        return ($root->getRight()->getHeight() + 1) - ($root->getLeft()->getHeight() + 1);
    }

    private function leftRotation(TreeNode $root): void {
        /** @var TreeNode */ $parent = $root->getParent();

        /** @var TreeNode */ $pivot = $root->getRight();
        $root->setRight($pivot->getLeft());
        if($pivot->getLeft() !== $this->NULL_GUARD) {
            $pivot->getLeft()->setParent($root);
        }

        $pivot->setParent($parent);
        if($parent === $this->NULL_GUARD) {
            $this->root = $pivot;
        } else if($root === $parent->getLeft()) {
            $parent->setLeft($pivot);
        } else {
            $parent->setRight($pivot);
        }

        $pivot->setLeft($root);
        $root->setParent($pivot);

        $this->setHeight($root);
        $this->setHeight($pivot);
    }

    private function rightRotation(TreeNode $root): void {
        /** @var TreeNode */ $parent = $root->getParent();

        /** @var TreeNode */ $pivot = $root->getLeft();
        $root->setLeft($pivot->getRight());
        if($pivot->getRight() !== $this->NULL_GUARD) {
            $pivot->getRight()->setParent($root);
        }

        $pivot->setParent($parent);
        if($parent === $this->NULL_GUARD) {
            $this->root = $pivot;
        } else if($root === $parent->getLeft()) {
            $parent->setLeft($pivot);
        } else {
            $parent->setRight($pivot);
        }

        $pivot->setRight($root);
        $root->setParent($pivot);

        $this->setHeight($root);
        $this->setHeight($pivot);
    }

    private function doubleLeftRotation(TreeNode $root): void {
        $this->rightRotation($root->getRight());
        $this->leftRotation($root);
    }

    private function doubleRightRotation(TreeNode $root): void {
        $this->leftRotation($root->getLeft());
        $this->rightRotation($root);
    }

    private function checkTreeBalance(TreeNode $root): void {
        /** @var int */ $bf = $this->balanceFactor($root);

        if($bf >= 2) {
            if($this->balanceFactor($root->getRight()) == -1) {
                $this->doubleLeftRotation($root);
            } else {
                $this->leftRotation($root);
            }
        } else if($bf <= -2) {
            if($this->balanceFactor($root->getLeft()) == 1) {
                $this->doubleRightRotation($root);
            } else {
                $this->rightRotation($root);
            }
        }
    }

    private function autoBalance(TreeNode $root): void {
        while($root !== $this->NULL_GUARD) {
            $this->checkTreeBalance($root);
            $this->setHeight($root);
            $root = $root->getParent();
        }
    }

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
     * Summary of put
     * @param K $key
     * @param V $value
     * @return V
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
     * Summary of get
     * @param K $key
     * @return V
     */
    public function get($key): mixed {
        if($key === null) {
            throw new NullPointerException("Key cannot be null");
        }
        return $this->searchNode($key)->getEntry()->getValue();
    }

    /**
     * Summary of remove
     * @param k $key
     * @return V
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
     * Summary of removeIfEquals
     * @param K $key
     * @param V $value
     * @return bool
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
     * Summary of first
     * @return Entry<K,V>|null
     */
    public function first(): mixed {
        if($this->root === $this->NULL_GUARD) {
            return null;
        }
        return TreeEntry::copy($this->firstTreeNode($this->root)->getEntry());
    }

    /**
     * Summary of last
     * @return Entry<K,V>|null
     */
    public function last(): mixed {
        if($this->root === $this->NULL_GUARD) {
            return null;
        }
        return TreeEntry::copy($this->lastTreeNode($this->root)->getEntry());
    }

    /**
     * Summary of popFirst
     * @return Entry<K,V>|null
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
     * Summary of popLast
     * @return Entry<K,V>|null
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
     * @param K $key
     * @return bool
     */
    public function containsKey($key): bool {
        if($key === null) {
            throw new NullPointerException("Key cannot be null");
        }
        return $this->searchNode($key) !== $this->NULL_GUARD;
    }

    /**
     * @param V $value
     * @return bool
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
     * @param K $key
     * @return Entry<K,V>|null
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
     * @param K $key
     * @return Entry<K,V>|null
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
     * @return int
     */
    public function size(): int {
        return $this->size;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool {
        return $this->size === 0;
    }

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
     * @return TreeMapView<K>
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
     * @return TreeMapView<V>
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

    private function preOrderDisplayHelper(TreeNode $root, callable $action): void {
        if($root === $this->NULL_GUARD) {
            return;
        }

        $action($root->getEntry());
        $this->preOrderDisplayHelper($root->getLeft(), $action);
        $this->preOrderDisplayHelper($root->getRight(), $action);
    }

    private function inOrderDisplayHelper(TreeNode $root, callable $action): void {
        if($root === $this->NULL_GUARD) {
            return;
        }

        $this->inOrderDisplayHelper($root->getLeft(), $action);
        $action($root->getEntry());
        $this->inOrderDisplayHelper($root->getRight(), $action);
    }

    private function postOrderDisplayHelper(TreeNode $root, callable $action): void {
        if($root === $this->NULL_GUARD) {
            return;
        }

        $this->postOrderDisplayHelper($root->getLeft(), $action);
        $this->postOrderDisplayHelper($root->getRight(), $action);
        $action($root->getEntry());
    }

    public function preOrderDisplay(callable $action) {
        if($action == null) {
            throw new NullPointerException("Callable function should not be null");
        }
        $this->preOrderDisplayHelper($this->root, $action);
        echo "<br>";
    }

    public function inOrderDisplay(callable $action) {
        if($action == null) {
            throw new NullPointerException("Callable function should not be null");
        }
        $this->inOrderDisplayHelper($this->root, $action);
        echo "<br>";
    }

    public function postOrderDisplay(callable $action) {
        if($action == null) {
            throw new NullPointerException("Callable function should not be null");
        }
        $this->postOrderDisplayHelper($this->root, $action);
        echo "<br>";
    }

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

    // public function heightTree(): int {
    //     return $this->root->getHeight();
    // }

    // public function balanceFactorTree(): int {
    //     return $this->balanceFactor($this->root);
    // }

    // /**
    //  * Summary of getTreeRootElement
    //  * @return TreeEntry<K,V>
    //  */
    // public function getTreeRootElement() : mixed {
    //     return TreeEntry::copy($this->root->getEntry());
    // }
}

/* -----------------------------#TreeMap class - END#--------------------------------------------- */

?>