<?php
include_once 'sql-request.php';

// Hae kaikki opiskelijat ja kurssit pudotusvalikoita varten
$all_students = get_all_students();
$all_courses = get_all_courses();

$success_message = "";
$error_message = "";

// Lomaketiedot - alustetaan tyhjiksi
$selected_student = "";
$selected_course = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course-registration'])) {
    // Tallennetaan lomaketiedot, jotta pysyy täytettynä virheviestissä
    $selected_student = $_POST['student'] ?? '';
    $selected_course = $_POST['course'] ?? '';

    if ($selected_student != 'empty' && $selected_course != 'empty') {
        // Lisätään tietokantaan
        try {
            global $conn;
            $stmt = $conn->prepare("INSERT INTO kurssikirjautumiset (opiskelija, kurssi) VALUES (:opiskelija, :kurssi)");
            $stmt->bindParam(':opiskelija', $selected_student, PDO::PARAM_INT);
            $stmt->bindParam(':kurssi', $selected_course, PDO::PARAM_INT);
            $stmt->execute();

            // Onnistumisviesti
            $success_message = "Ilmoittautuminen lisätty onnistuneesti!";
            // Tyhjennä lomaketiedot onnistuneen lähetyksen jälkeen
            $selected_student = "";
            $selected_course = "";
        } catch (PDOException $e) {
            // Virheilmoitus pysyy, lomake pysyy täytettynä
            $error_message = "Virhe ilmoittaudutumisessa: " . $e->getMessage();
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
    <title>Ilmoittaudu kurssille</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include 'header.php'; ?>
    <h1>Ilmoittaudu kurssille</h1>
    
    <div class="form-wrapper">
        <!-- Viestien näyttö -->
        <?php
        if (!empty($success_message)) {
            echo "<div class='message success-message'>" . htmlspecialchars($success_message) . "</div>";
        }
        if (!empty($error_message)) {
            echo "<div class='message error-message'>" . htmlspecialchars($error_message) . "</div>";
        }
        ?>        
        <form method="POST" action="">
            <div class="form-group">
                <label for="student">Opiskelija: <span style="color: red;">*</span></label>
                <select id="student" name="student">
                    <option value="empty">----valitse opiskelija----</option>
                    <?php
                    foreach ($all_students as $student) {
                        $value = $student['opiskelijanumero'];
                        $label = $student['sukunimi'] . ' ' . $student['etunimi'] . ', ' . $student['vuosikurssi']. ', ' . $student['syntymapaiva'];
                        $selected = ($selected_student == $value) ? 'selected' : '';
                        echo "<option value='{$value}' {$selected}>";
                        echo htmlspecialchars($label);
                        echo "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="course">Kurssi: <span style="color: red;">*</span></label>
                <select id="course" name="course">
                    <option value="empty">----valitse kurssi----</option>
                    <?php
                    foreach ($all_courses as $course) {
                        $value = $course['tunnus'];
                        $label = $course['nimi'];
                        $selected = ($selected_course == $value) ? 'selected' : '';
                        echo "<option value='{$value}' {$selected}>";
                        echo htmlspecialchars($label);
                        echo "</option>";
                    }
                    ?>
                </select>
            </div>
                        
            <div class="button-wrapper">
                <button type="submit" name="course-registration" class="submit-btn">Ilmoittaudu</button>
            </div>
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>