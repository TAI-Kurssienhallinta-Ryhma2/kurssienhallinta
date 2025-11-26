<?php
include_once 'sql-request.php';

$opiskelijat = "opiskelijat";

$success_message = "";
$error_message = "";

// Lomaketiedot - alustetaan tyhjiksi
$etunimi = "";
$sukunimi = "";
$syntymapaiva = "";
$vuosikurssi = "";

// Määrittele SQL ennen `if`-lohkoa
$sql = "INSERT INTO $opiskelijat (etunimi, sukunimi, syntymapaiva, vuosikurssi) VALUES (:etunimi, :sukunimi, :syntymapaiva, :vuosikurssi)";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tallennetaan lomaketiedot, jotta pysyy täytettynä virheviestissä
    $etunimi = $_POST["etunimi"] ?? "";
    $sukunimi = $_POST["sukunimi"] ?? "";
    $syntymapaiva = $_POST["syntymapaiva"] ?? "";
    $vuosikurssi = $_POST["vuosikurssi"] ?? "";

    if (
        isset($_POST["etunimi"], $_POST["sukunimi"], $_POST["syntymapaiva"], $_POST["vuosikurssi"])
        && !empty(trim($_POST["etunimi"]))
        && !empty(trim($_POST["sukunimi"]))
        && !empty(trim($_POST["syntymapaiva"]))
        && !empty(trim($_POST["vuosikurssi"]))
    ) {
        $enimi = $_POST["etunimi"]; 
        $snimi = $_POST["sukunimi"]; 
        $syntymapaiva = $_POST["syntymapaiva"]; 
        $vkurssi = $_POST["vuosikurssi"];

        try { 
            $kysely = $conn->prepare($sql); 
            $kysely->bindParam(':etunimi', $enimi); 
            $kysely->bindParam(':sukunimi', $snimi); 
            $kysely->bindParam(':syntymapaiva', $syntymapaiva); 
            $kysely->bindParam(':vuosikurssi', $vkurssi); 
            $kysely->execute();

            $success_message = "Opiskelija lisätty onnistuneesti!";
            // Tyhjennä lomakedata onnistuneen lähetyksen jälkeen
            $etunimi = "";
            $sukunimi = "";
            $syntymapaiva = "";
            $vuosikurssi = "";
        } catch (PDOException $e) {
            $error_message = "Virhe opiskelijan lisäämisessä: " . $e->getMessage();
        }
    } else {
        $error_message = "Täytä kaikki vaaditut kentät.";
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lisää opiskelija</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Lisää opiskelija</h1>

    <div class="form-wrapper">
        <!-- Viestien näyttö -->
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!empty($success_message)) {
                echo "<div class='message success-message'>" . htmlspecialchars($success_message) . "</div>";
            }
            if (!empty($error_message)) {
                echo "<div class='message error-message'>" . htmlspecialchars($error_message) . "</div>";
            }
        }
        ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="etunimi">Etunimi: <span style="color: red;">*</span></label>
                <input
                    type="text"
                    id="etunimi"
                    name="etunimi"
                    placeholder="Etunimi"
                    maxlength="100"
                    value="<?php echo htmlspecialchars($etunimi); ?>"
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
                    value="<?php echo htmlspecialchars($sukunimi); ?>"
                >
            </div>
            <div class="form-group">
                <label for="syntymapaiva">Syntymäaika: <span style="color: red;">*</span></label>
                <input
                    type="date"
                    id="syntymapaiva"
                    name="syntymapaiva"
                    value="<?php echo htmlspecialchars($syntymapaiva); ?>"
                >
            </div>
            <div class="form-group">
                <label for="vuosikurssi">Vuosikurssi: <span style="color: red;">*</span></label>
                <select id="vuosikurssi" name="vuosikurssi">
                    <option value="">--valitse vuosikurssi--</option>
                    <option value="1" <?php echo ($vuosikurssi == "1") ? "selected" : ""; ?>>1</option>
                    <option value="2" <?php echo ($vuosikurssi == "2") ? "selected" : ""; ?>>2</option>
                    <option value="3" <?php echo ($vuosikurssi == "3") ? "selected" : ""; ?>>3</option>
                </select>
            </div>
            <div class="button-wrapper">
                <button type="submit" class="submit-btn">Lisää opiskelija</button>
            </div>
        </form>
    </div>
</body>
</html>