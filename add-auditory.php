<?php
include_once 'sql-request.php';

// Käsitellään lomakkeen lähetys
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add-auditory'])) {
    $auditory_name = trim($_POST['auditory-name']);
    $auditory_capacity = trim($_POST['auditory-capacity']);
    
    // Validointi array virheille
    $errors = [];
    
    // Tarkistetaan, että kentät eivät ole tyhjiä
    if (empty($auditory_name)) {
        $errors[] = "Luokkahuoneen nimi on pakollinen.";
    }
    
    if (empty($auditory_capacity)) {
        $errors[] = "Kapasiteetti on pakollinen.";
    }
    
    // Tarkistetaan, että kapasiteetti on numero
    if (!empty($auditory_capacity) && !is_numeric($auditory_capacity)) {
        $errors[] = "Kapasiteetti täytyy olla numero.";
    }
    
    // Tarkistetaan, että kapasiteetti on positiivinen kokonaisluku
    if (!empty($auditory_capacity) && is_numeric($auditory_capacity)) {
        $capacity_int = intval($auditory_capacity);
        if ($capacity_int <= 0) {
            $errors[] = "Kapasiteetti täytyy olla suurempi kuin 0.";
        }
    }
    
    
    // Tarkistetaan, että samannimistä luokkahuonetta ei ole jo olemassa
    if (!empty($auditory_name) && auditory_name_exists($auditory_name)) {
        $errors[] = "Luokkahuone nimellä '{$auditory_name}' on jo olemassa.";
    }
    
    // Jos ei ole virheitä, lisätään luokkahuone
    if (empty($errors)) {
        $result = add_auditory($auditory_name, intval($auditory_capacity));
        
        if ($result) {
            $success_message = "Luokkahuone lisätty onnistuneesti!";
            // Tyhjennetään lomake onnistumisen jälkeen
            unset($_POST);
        } else {
            $error_message = "Virhe lisättäessä luokkahuonetta tietokantaan.";
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
    <title>Lisää luokkahuone</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Lisää uusi luokkahuone</h1>
    
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
                <label for="auditory-name">Luokkahuoneen nimi: <span style="color: red;">*</span></label>
                <input 
                    type="text" 
                    id="auditory-name" 
                    name="auditory-name" 
                    placeholder="Esim. A101"
                    maxlength="100"
                    value="<?php echo isset($_POST['auditory-name']) ? htmlspecialchars($_POST['auditory-name']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="auditory-capacity">Kapasiteetti (henkilöä): <span style="color: red;">*</span></label>
                <input 
                    type="number" 
                    id="auditory-capacity" 
                    name="auditory-capacity" 
                    placeholder="Esim. 30"
                    min="1"
                    step="1"
                    value="<?php echo isset($_POST['auditory-capacity']) ? htmlspecialchars($_POST['auditory-capacity']) : ''; ?>"
                >
            </div>
            
            <div class="button-wrapper">
                <button type="submit" name="add-auditory" class="submit-btn">Lisää luokkahuone</button>
            </div>
        </form>
    </div>
</body>

</html>