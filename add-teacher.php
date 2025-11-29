<?php
include_once 'sql-request.php';

// Käsitellään lomakkeen lähetys
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add-teacher'])) {
    $teacher_firstname = trim($_POST['first-name']);
    $teacher_lastname = trim($_POST['last-name']);
    $teacher_subject = trim($_POST['subject']);
    
    // Validointi array virheille
    $errors = [];
    
    // Tarkistetaan, että kentät eivät ole tyhjiä
    if (empty($teacher_firstname)) {
        $errors[] = "Etunimi on pakollinen.";
    }
    if (empty($teacher_lastname)) {
        $errors[] = "Sukunimi on pakollinen.";
    }
    if (empty($teacher_subject)) {
        $errors[] = "Aine on pakollinen.";
    }
    if (!empty($teacher_subject) && strlen($teacher_subject) > 70) {
        $errors[] = "Aineen nimi on liian pitkä (max 70 merkkiä).";
    }

    $teacher_firstname = ucfirst(strtolower(trim($_POST['first-name'])));
    $teacher_lastname = ucfirst(strtolower(trim($_POST['last-name'])));
    $teacher_subject = ucfirst(strtolower(trim($_POST['subject'])));
    
    
    
    
    
    // Jos ei ole virheitä, lisätään tila
    if (empty($errors)) {
        $result = add_teacher($teacher_firstname, $teacher_lastname, $teacher_subject);
        
        if ($result) {
            //estää duplikaatit jos sivu päivitetään
            header("Location: add-teacher.php?success=1");
            exit();
        } else {
            $error_message = "Virhe lisättäessä opettajaa tietokantaan.";
        }
    } else {
        // Yhdistetään kaikki virheet yhdeksi viestiksi
        $error_message = implode("<br>", $errors);
    }

}
if (isset($_GET['success']) && $_GET['success'] == 1) {
$success_message = "Opettaja lisätty onnistuneesti!";
}
?>

<!DOCTYPE html>
<html lang="fi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lisää opettaja</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<?php include 'header.php'; ?>
    <h1>Lisää uusi opettaja</h1>
    
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
                <label for="auditory-name">Etunimi: <span style="color: red;">*</span></label>
                <input 
                    type="text" 
                    id="first-name" 
                    name="first-name" 
                    maxlength="100"
                    value="<?php echo isset($_POST['first-name']) ? htmlspecialchars($_POST['first-name']) : ''; ?>"
                >
            </div>
            <div class="form-group">
                <label for="auditory-name">Sukunimi: <span style="color: red;">*</span></label>
                <input 
                    type="text" 
                    id="last-name" 
                    name="last-name" 
                    maxlength="100"
                    value="<?php echo isset($_POST['last-name']) ? htmlspecialchars($_POST['last-name']) : ''; ?>"
                >
            </div>
            <div class="form-group">
                <label for="auditory-name">Aine: <span style="color: red;">*</span></label>
                <input 
                    type="text" 
                    id="subject" 
                    name="subject" 
                    maxlength="70"
                    value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>"
                >
            </div>
            
            
            
            <div class="button-wrapper">
                <button type="submit" name="add-teacher" class="submit-btn">Lisää opettaja</button>
            </div>
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>