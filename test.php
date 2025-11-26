<?php
require_once "sql-request.php";
$data = get_timetable_auditory(1,);

echo "Room: {$data[0]['tilan_nimi']}<br><br>";

foreach($data[1] as &$row) {
    print_r($row);
    echo "<br>";
}
?>