<?php

include_once 'sql-request.php';
require_once __DIR__ . '/utilities/utilities.php';
require_once __DIR__ . '/tree_data_structures/TreeMap.php';

// Get all courses, teachers and auditories
$all_courses = get_all_courses();
$course_map = createTreeMap($all_courses, "tunnus");

$all_teachers = get_all_teachers();
$all_auditories = get_all_auditories();

$selected_course = null;
if(isset($_GET["course-id"])) {
    $course_id = $_GET["course-id"];
    $selected_course = get_course_by_id($course_id);
    $_SESSION["course_id"] = $course_id;
    
    // Get number of registered students for this course
    $registered_students = get_students_registered_for_course($course_id);
    $student_count = count($registered_students);
}

$success_message = null;
$error_message = null;

//UPDATE COURSE
if(isset($_POST["update-course"])) {
    global $conn;

    try {
        $course_id = $_POST["tunnus"];
        $nimi = $_POST["nimi"];
        $kuvaus = $_POST["kuvaus"];
        $alkupaiva = $_POST["alkupaiva"];
        $loppupaiva = $_POST["loppupaiva"];
        $opettaja = $_POST["opettaja"];
        $tila = $_POST["tila"];

        $query = "UPDATE kurssit
        SET nimi = :nimi, kuvaus = :kuvaus, alkupaiva = :alkupaiva, 
            loppupaiva = :loppupaiva, opettaja = :opettaja, tila = :tila
        WHERE tunnus = :course_id";

        $statement = $conn->prepare($query);
        $statement->execute([
            ":nimi" => $nimi,
            ":kuvaus" => $kuvaus,
            ":alkupaiva" => $alkupaiva,
            ":loppupaiva" => $loppupaiva,
            ":opettaja" => $opettaja,
            ":tila" => $tila,
            ":course_id" => $course_id
        ]);

        $success_message = "Kurssin tiedot päivitetty onnistuneesti!";

        header("Location: edit-delete-course.php?course-id={$course_id}&success=$success_message");
        exit();
        
    } catch (PDOException $e) {
        $error_message = "Virhe tallennettaessa: " . htmlspecialchars($e->getMessage());
    }
}

//DELETE COURSE
if(isset($_POST["delete-course"])) {
    global $conn;

    try {
        $course_id = $_POST["tunnus"];

        // First delete all registrations for this course
        $query1 = "DELETE FROM kurssikirjautumiset WHERE kurssi = :course_id";
        $statement1 = $conn->prepare($query1);
        $statement1->execute([":course_id" => $course_id]);

        // Then delete the course
        $query2 = "DELETE FROM kurssit WHERE tunnus = :course_id";
        $statement2 = $conn->prepare($query2);
        $statement2->execute([":course_id" => $course_id]);

        $success_message = "Kurssi poistettu onnistuneesti!";

        header("Location: edit-delete-course.php?&success=$success_message");
        exit();
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
    <title>Poista/muokkaa kurssi</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Poista/muokkaa kurssi</h1>

    <label for="courses">Valitse kurssi: </label>
    <select id="courses" name="courses">
        <option value="empty">---Valitse kurssi---</option>
        <?php foreach($all_courses as $course): ?>
            <option value="<?= htmlspecialchars($course["tunnus"]); ?>"
                <?php
                    if(isset($_SESSION["course_id"]) && $_SESSION["course_id"] == $course["tunnus"]) {
                        echo "selected";
                    }
                ?>
            >
            <?= htmlspecialchars($course["nimi"]); ?>
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

        <?php if($selected_course): ?>
            <form method="POST" action="">
                <input type="hidden" name="tunnus" value="<?= $selected_course["tunnus"];?>">

                <label for="nimi">Nimi: <span style="color:red;">*</span></label>
                <input type="text" id="nimi" name="nimi" value="<?= htmlspecialchars($selected_course["nimi"])?>" required>
                
                <label for="kuvaus">Kuvaus:</label>
                <textarea id="kuvaus" name="kuvaus" rows="4"><?= htmlspecialchars($selected_course["kuvaus"])?></textarea>
                
                <label for="alkupaiva">Alkupäivä: <span style="color:red;">*</span></label>
                <input type="date" id="alkupaiva" name="alkupaiva" value="<?= htmlspecialchars($selected_course["alkupaiva"])?>" required>
                
                <label for="loppupaiva">Loppupäivä: <span style="color:red;">*</span></label>
                <input type="date" id="loppupaiva" name="loppupaiva" value="<?= htmlspecialchars($selected_course["loppupaiva"])?>" required>
                
                <label for="opettaja">Opettaja: <span style="color:red;">*</span></label>
                <select id="opettaja" name="opettaja" required>
                    <option value="">---Valitse opettaja---</option>
                    <?php foreach($all_teachers as $teacher): ?>
                        <option value="<?= htmlspecialchars($teacher["tunnusnumero"]); ?>"
                            <?php
                                if($selected_course["opettaja"] == $teacher["tunnusnumero"]) {
                                    echo "selected";
                                }
                            ?>
                        >
                        <?= htmlspecialchars($teacher["sukunimi"] . " " . $teacher["etunimi"] . " (" . $teacher["aine"] . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label for="tila">Tila: <span style="color:red;">*</span></label>
                <select id="tila" name="tila" required>
                    <option value="">---Valitse tila---</option>
                    <?php foreach($all_auditories as $auditory): ?>
                        <option value="<?= htmlspecialchars($auditory["tunnus"]); ?>"
                            <?php
                                if($selected_course["tila"] == $auditory["tunnus"]) {
                                    echo "selected";
                                }
                            ?>
                        >
                        <?= htmlspecialchars($auditory["nimi"] . " (" . $auditory["kapasiteetti"] . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label>Rekisteröityneitä oppilaita:</label>
                <input type="text" value="<?= $student_count ?>" readonly style="background-color: #f0f0f0;">
            
                <div class="button-wrapper" style="display:flex; gap:.6rem; justify-content:center; flex-wrap:wrap;">
                    <input type="submit" id="update-button" class="submit-btn" name="update-course" value="Tallenna muutokset">
                    <input type="submit" id="delete-button" class="submit-btn" name="delete-course" value="Poista kurssi">
                </div>
            </form>
        <?php else: ?>
            <div class="message" style="background:#fff; border:1px solid var(--line); color:var(--muted);">
                Valitse kurssi ylhäältä muokkausta tai poistamista varten.
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById("courses").addEventListener("change", (event) => {
                const id = event.target.value;
                if(id !== 'empty') {
                    window.location.href = `edit-delete-course.php?course-id=${id}`;
                }
            });

            const deleteButton = document.getElementById("delete-button");
            if(deleteButton) {
                deleteButton.addEventListener("click", (event) => {
                    const confirmed = confirm('Haluatko varmasti poistaa tämän kurssin? Tämä poistaa myös kaikki kurssikirjautumiset.');
                    if(!confirmed) {
                        event.preventDefault();
                    }
                });
            }
        });
    </script>
</body>

</html>