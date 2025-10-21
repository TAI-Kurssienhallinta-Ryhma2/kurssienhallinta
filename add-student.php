<?php
include_once 'sql-request.php';

$all_students = get_all_students();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lisää opiskelija</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Lisää opiskelija</h1>

    <!-- just to check connection.php -->
    <label for="students">Valitse opiskelija:</label>
    <select id="students" name="students">
        <?php
        foreach ($all_students as $student) {
        ?>
            <option value="<?php echo $student["opiskelijanumero"]; ?>"><?php echo $student["sukunimi"] . " " . $student["etunimi"]; ?></option>
        <?php
        }
        ?>
    </select>


</body>

</html>