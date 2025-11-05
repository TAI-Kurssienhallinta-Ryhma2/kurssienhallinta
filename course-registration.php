<?php
include_once 'sql-request.php';

// Get an array with alle courses from the database table 'kurssit':
$all_courses = get_all_courses();
// echo "<pre>";
// print_r($all_courses);
// echo "</pre>";

// If the GET parameter (?course-id=) appears in the address in the browser (after course's selection), then the following code is executed:
if (isset($_POST["etunimi"], $_POST["sukunimi"], $_POST["kirjautumispaiva"], $_POST["kurssi"])) { 
    $enimi = $_POST["etunimi"]; 
    $snimi = $_POST["sukunimi"]; 
    $kirjautumispaiva = $_POST["kirjautumispaiva"]; 
    $kurssi = $_POST["kurssi"];

    $sql = "INSERT INTO kurssikirjautumiset (etunimi, sukunimi, kirjautumispaiva, kurssi) VALUES (:etunimi, :sukunimi, :kirjautumispaiva, :kurssi)"; 

    try { 
        $kysely = $conn->prepare($sql); 
        $kysely->bindParam(':etunimi', $enimi); 
        $kysely->bindParam(':sukunimi', $snimi); 
        $kysely->bindParam(':kirjautumispaiva', $kirjautumispaiva); 
        $kysely->bindParam(':kurssi', $kurssi); 
        $kysely->execute(); 

        echo "Ilmoittautuminen lisätty onnistuneesti."; 

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
    <title>Ilmoittaudu kurssille</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Ilmoittaudu kurssille</h1>

    <div class="form-wrapper">
        <form action="course-registration.php" method="post">
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
                <label for="kirjautumispaiva">Ilmoittautumispäivä: <span style="color: red;">*</span></label>
                <input
                    type="date"
                    id="kirjautumispaiva"
                    name="kirjautumispaiva"
                >
            </div>

            <div class="form-group">
                <label for="kurssi">Kurssi: <span style="color: red;">*</span></label>
                <select id="kurssi" name="kurssi">
                <!-- JATKA TÄSTÄ
                    vrt. "get-course-info.php, rivi 49 -->
                    <option value="empty">--valitse kurssi--</option>
                    <?php
                    // Run through all the entries in the array $all_courses:
                    foreach ($all_courses as $course) {
                    ?>
                        <!-- The value of option element is course's ID: -->
                        <!-- Put attribute 'selected' to the option with selected course - only for the updated page with GET parameter '?course-id=': -->
                        <option value="<?php echo $course["tunnus"]; ?>"

                            <?php if (isset($_SESSION["course_id"]) && $course["tunnus"] == $_SESSION["course_id"]) {
                            ?> selected <?php
                                    } ?>>
                            <?php echo $course["nimi"]; ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            
            <div class="button-wrapper">
                <button type="submit" name="add-student" class="submit-btn">Ilmoittaudu</button>
            </div>
            
        </form>
    </div>

</body>

</html>