<?php
include_once 'sql-request.php';

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

    <div class="form-wrapper">
        <form action="add-student.php" method="post">
            <div class="form-group">
                <label for="etunimi">Etunimi: <span style="color: red;">*</span></label>
                <input
                    type="text"
                    id="etunimi"
                    name="etunimi"
                    placeholder="Etunimi"
                    maxlength="100"
                >
            </div>
            
            <div class="form-group">
                <label for="sukunimi">Sukunimi: <span style="color: red;">*</span></label>
                <input
                    type="text"
                    id="sukunimi"
                    name="sukunimi"
                    placeholder="Sukunimi"
                    maxlength="100"
                >
            </div>

            <div class="form-group">
                <label for="syntymapaiva">Syntymäaika: <span style="color: red;">*</span></label>
                <input
                    type="date"
                    id="syntymapaiva"
                    name="syntymapaiva"
                >
            </div>

            <div class="form-group">
                <label for="vuosikurssi">Vuosikurssi: <span style="color: red;">*</span></label>
                <select id="vuosikurssi" name="vuosikurssi">
                    <option value="empty">--valitse vuosikurssi--</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
            </div>
            
            <div class="button-wrapper">
                <button type="submit" name="add-student" class="submit-btn">Lisää opiskelija</button>
            </div>
            
        </form>
    </div>

</body>

</html>