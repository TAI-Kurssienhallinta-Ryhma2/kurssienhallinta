<?php

require_once __DIR__ . "/../tree_data_structures/TreeMap.php";

function createTreeMap(array $results, string $uniqueFieldName): TreeMap {
    /** @var TreeMap<int, ArrayObject> */ $map = new TreeMap();
    foreach($results as $row) {
        $map->put((int)$row[$uniqueFieldName], new ArrayObject($row));
    }
    return $map;
}

?>