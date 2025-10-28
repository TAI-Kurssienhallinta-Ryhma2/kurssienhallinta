<?php
include_once 'sql-request.php';

// Hae kaikki opettajat ja luokkahuoneet pudotusvalikoita varten
$all_teachers = get_all_teachers();
$all_auditories = get_all_auditories();

// Käsitellään lomakkeen lähetys
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add-course'])) {
    $course_name = trim($_POST['course-name']);
    $course_description = trim($_POST['course-description']);
    $start_date = trim($_POST['start-date']);
    $end_date = trim($_POST['end-date']);
    $auditory_id = trim($_POST['auditory']);
    $teacher_id = trim($_POST['teacher']);
    
    // Validointi array virheille
    $errors = [];
    
    // Tarkistetaan pakolliset kentät
    if (empty($course_name)) {
        $errors[] = "Kurssin nimi on pakollinen.";
    }
    
    if (empty($start_date)) {
        $errors[] = "Alkupäivä on pakollinen.";
    }
    
    if (empty($end_date)) {
        $errors[] = "Loppupäivä on pakollinen.";
    }
    
    if (empty($auditory_id) || $auditory_id === 'empty') {
        $errors[] = "Luokkahuone on pakollinen.";
    }
    
    if (empty($teacher_id) || $teacher_id === 'empty') {
        $errors[] = "Opettaja on pakollinen.";
    }
    
    // Tarkistetaan, että nimi ei ole liian pitkä
    if (!empty($course_name) && strlen($course_name) > 100) {
        $errors[] = "Kurssin nimi on liian pitkä (max 100 merkkiä).";
    }
    
    // Tarkistetaan päivämäärät
    if (!empty($start_date) && !empty($end_date)) {
        $start = strtotime($start_date);
        $end = strtotime($end_date);
        
        if ($end < $start) {
            $errors[] = "Loppupäivä ei voi olla ennen alkupäivää.";
        }
    }
    
    // Tarkistetaan, että samannimistä kurssia ei ole jo olemassa
    if (!empty($course_name) && course_name_exists($course_name)) {
        $errors[] = "Kurssi nimellä '{$course_name}' on jo olemassa.";
    }
    
    // Jos ei ole virheitä, lisätään kurssi
    if (empty($errors)) {
        $result = add_course($course_name, $course_description, $start_date, $end_date, $auditory_id, $teacher_id);
        
        if ($result) {
            $success_message = "Kurssi lisätty onnistuneesti!";
            // Tyhjennetään lomake onnistumisen jälkeen
            unset($_POST);
        } else {
            $error_message = "Virhe lisättäessä kurssia tietokantaan.";
        }
    } else {
        // Yhdistetään kaikki virheet yhdeksi viestiksi
        $error_message = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="fi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lisää kurssi</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Lisää uusi kurssi</h1>
    
    <div class="form-wrapper">
        <?php
        // Näytetään onnistumis- tai virheviesti
        if (isset($success_message)) {
            echo "<div class='message success-message'>" . htmlspecialchars($success_message) . "</div>";
        }
        if (isset($error_message)) {
            echo "<div class='message error-message'>" . $error_message . "</div>";
        }
        ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="course-name">Kurssin nimi: <span style="color: red;">*</span></label>
                <input 
                    type="text" 
                    id="course-name" 
                    name="course-name" 
                    placeholder="Esim. Ohjelmoinnin perusteet"
                    maxlength="100"
                    value="<?php echo isset($_POST['course-name']) ? htmlspecialchars($_POST['course-name']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="course-description">Kuvaus:</label>
                <textarea 
                    id="course-description" 
                    name="course-description" 
                    placeholder="Kurssin kuvaus (vapaaehtoinen)"
                    rows="4"
                ><?php echo isset($_POST['course-description']) ? htmlspecialchars($_POST['course-description']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="start-date">Alkupäivä: <span style="color: red;">*</span></label>
                <input 
                    type="date" 
                    id="start-date" 
                    name="start-date"
                    value="<?php echo isset($_POST['start-date']) ? htmlspecialchars($_POST['start-date']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="end-date">Loppupäivä: <span style="color: red;">*</span></label>
                <input 
                    type="date" 
                    id="end-date" 
                    name="end-date"
                    value="<?php echo isset($_POST['end-date']) ? htmlspecialchars($_POST['end-date']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="auditory">Luokkahuone: <span style="color: red;">*</span></label>
                <select id="auditory" name="auditory">
                    <option value="empty">----valitse luokkahuone----</option>
                    <?php
                    foreach ($all_auditories as $auditory) {
                        $selected = (isset($_POST['auditory']) && $_POST['auditory'] == $auditory['tunnus']) ? 'selected' : '';
                        echo "<option value='{$auditory['tunnus']}' {$selected}>";
                        echo htmlspecialchars($auditory['nimi']) . " (kapasiteetti: " . $auditory['kapasiteetti'] . ")";
                        echo "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="teacher">Opettaja: <span style="color: red;">*</span></label>
                <select id="teacher" name="teacher">
                    <option value="empty">----valitse opettaja----</option>
                    <?php
                    foreach ($all_teachers as $teacher) {
                        $selected = (isset($_POST['teacher']) && $_POST['teacher'] == $teacher['tunnusnumero']) ? 'selected' : '';
                        echo "<option value='{$teacher['tunnusnumero']}' {$selected}>";
                        echo htmlspecialchars($teacher['sukunimi']) . " " . htmlspecialchars($teacher['etunimi']);
                        echo " (" . htmlspecialchars($teacher['aine']) . ")";
                        echo "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="button-wrapper">
                <button type="submit" name="add-course" class="submit-btn">Lisää kurssi</button>
            </div>
        </form>
    </div>
</body>

</html>