<?php

include_once 'sql-request.php';
require_once __DIR__ . '/utilities/utilities.php';
require_once __DIR__ . '/tree_data_structures/TreeMap.php';

// Get an array with all the auditories from the database table 'tilat':
$all_auditories = get_all_auditories();
$auditory_map = createTreeMap($all_auditories, "tunnus");

$selected_auditory = null;
if(isset($_GET["auditory-id"])) {
    $auditory_id = $_GET["auditory-id"];
    $selected_auditory = $auditory_map->get((int)$auditory_id);
    $_SESSION["auditory_id"] = $auditory_id;
}

$success_message = null;
$error_message = null;

//UPDATE ROOM
if(isset($_POST["update-room"])) {
    global $conn;

    try {
        $room_id = $_POST["tunnus"];
        $nimi = $_POST["nimi"];
        $kapasiteetti = $_POST["kapasiteetti"];

        $query = "UPDATE tilat
        SET nimi = :nimi, kapasiteetti = :kapasiteetti
        WHERE tunnus = :room_id";

        $statement = $conn->prepare($query);
        $statement->execute([
            ":nimi" => $nimi,
            ":kapasiteetti" => $kapasiteetti,
            ":room_id" => $room_id
        ]);

        $success_message = "Tilan tiedot päivitetty onnistuneesti!";

        header("Location: edit-delete-auditory.php?auditory-id={$room_id}&success=$success_message");
        exit();
        
    } catch (PDOException $e) {
        $error_message = "Virhe tallennettaessa: " . htmlspecialchars($e->getMessage());
    }
}

//DELETE ROOM
if(isset($_POST["delete-room"])) {
    global $conn;

    try {
        $room_id = $_POST["tunnus"];

        $query = "DELETE FROM tilat
        WHERE tunnus = :room_id";

        $statement = $conn->prepare($query);
        $statement->execute([
            ":room_id" => $room_id
        ]);

        $success_message = "Tila poistettu onnistuneesti!";

        header("Location: edit-delete-auditory.php?&success=$success_message");
    } catch(PDOException $e) {
        $error_message = "Poistovirhe: " . htmlspecialchars($e->getMessage());
    }
}

//Success message
if(isset($_GET["success"])) {
    $success_message = $_GET["success"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poista/muokkaa tila</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Poista/muokkaa tila</h1>

    <label for = "auditories">Valitse tilaa: </label>
    <select id = "auditories" name = "auditories">
        <option value = "empty">---Valitse tilaa---</option>
        <?php foreach($all_auditories as $auditory): ?>
            <option value = "<?= htmlspecialchars($auditory["tunnus"]); ?>"
                <?php
                    if(isset($_SESSION["auditory_id"]) && $_SESSION["auditory_id"] == $auditory["tunnus"]) {
                        echo "selected";
                    }
                ?>
            >
            <?= htmlspecialchars($auditory["nimi"]); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div class="form-wrapper">
        <?php if ($success_message): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="message error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if($selected_auditory): ?>
            <form method = "POST" action = "">
                <input type = "hidden" name = "tunnus" value = "<?= $selected_auditory["tunnus"];?>">

                <label for = "nimi">Nimi: <span style = "color:red;">*</span></label>
                <input type = "text" id = "nimi" name = "nimi" value = "<?= $selected_auditory["nimi"]?>">
                <label for = "kapasiteetti">Kapasiteetti: <span style = "color:red;">*</span></label>
                <input type = "text" id = "kapasiteetti" name = "kapasiteetti" value = "<?= $selected_auditory["kapasiteetti"]?>">
            
                <div class="button-wrapper" style="display:flex; gap:.6rem; justify-content:center; flex-wrap:wrap;">
                    <input type="submit" id="update-button" class="submit-btn" name="update-room" value="Tallenna muutokset">
                    <input type="submit" id="delete-button" class="submit-btn" name="delete-room" value="Poista tila">
                </div>
            </form>
        <?php else: ?>
            <div class="message" style="background:#fff; border:1px solid var(--line); color:var(--muted);">
                Valitse tila ylhäältä muokkausta tai poistamista varten.
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById("auditories").addEventListener("change", (event) => {
                const id = event.target.value;
                if(id !== 'empty') {
                    window.location.href = `edit-delete-auditory.php?auditory-id=${id}`;
                }
            });

            const deleteButton = document.getElementById("delete-button");
            if(deleteButton) {
                deleteButton.addEventListener("click", (event) => {
                    const confirmed = confirm('Haluatko varmasti poistaa tämän tilan? Tämä poistaa myös kurssit.');
                    if(!confirmed) {
                        event.preventDefault();
                    }
                });
            }
        });
    </script>
</body>

</html>