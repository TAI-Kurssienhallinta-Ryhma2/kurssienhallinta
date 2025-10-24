<?php
include_once 'sql-request.php';

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

    <form action="add-student.php" method="post">
        <label for=etunimi>Etunimi: </label>
        <input type=text id=etunimi name=etunimi>
        <br><br>
        <label for=sukunimi>Sukunimi: </label>
        <input type=text id=sukunimi name=sukunimi>
        <br><br>
        <label for=syntymapaiva>Syntymäaika: </label>
        <input type=date id=syntymapaiva name=syntymapaiva>
        <br><br>
        <label for=vuosikurssi>Vuosikurssi: </label>
        <select id=vuosikurssi name=vuosikurssi>
            <option value="empty">--valitse vuosikurssi--</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
        </select>
        <br><br>
        <input type="submit" value="Lisää opiskelija">
        <br><br>
    </form>


<!-- From here on
    code does not work,
    also I don't know what
    I have done and why lol -->
    
<?php 

$opiskelijat = "opiskelijat"; 

if (isset($_POST["etunimi"], $_POST["sukunimi"], $_POST["syntymapaiva"], $_POST["vuosikurssi"])) { 
    $enimi = $_POST["etunimi"]; 
    $snimi = $_POST["sukunimi"]; 
    $syntymapaiva = $_POST["syntymapaiva"]; 
    $vkurssi = $_POST["vuosikurssi"];

    $sql = "INSERT INTO $opiskelijat (etunimi, sukunimi, syntymapaiva, vuosikurssi) VALUES (:etunimi, :sukunimi, :syntymapaiva, :vuosikurssi)"; 

    try { 
        $kysely = $conn->prepare($sql); 
        $kysely->bindParam(':etunimi', $enimi); 
        $kysely->bindParam(':sukunimi', $snimi); 
        $kysely->bindParam(':syntymapaiva', $syntymapaiva); 
        $kysely->bindParam(':vuosikurssi', $vkurssi); 
        $kysely->execute(); 

        echo "Opiskelija lisätty onnistuneesti."; 

    } catch (PDOException $e) { 
        die("VIRHE: " . $e->getMessage()); 
    } 
} 
?> 

</body>

</html>