<?php
include_once 'sql-request.php';
require_once __DIR__ . '/utilities/utilities.php';
require_once __DIR__ . '/tree_data_structures/TreeMap.php';

// Fetch all students for dropdown list
$all_students = get_all_students();
$student_map = createTreeMap($all_students, "opiskelijanumero");


$success_message = null;
$error_message = null;

/* UPDATE STUDENT LOGIC */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update-student'])) {
    $id  = trim($_POST['opiskelijanumero'] ?? '');
    $fn  = trim($_POST['etunimi'] ?? '');
    $ln  = trim($_POST['sukunimi'] ?? '');
    $dob = trim($_POST['syntymapaiva'] ?? '');
    $yr  = trim($_POST['vuosikurssi'] ?? '');

    $errors = [];

    // Basic validation
    if ($id === '' || !ctype_digit($id))               $errors[] = "Virheellinen opiskelijan tunnus.";
    if ($fn === '')                                     $errors[] = "Etunimi on pakollinen.";
    if ($ln === '')                                     $errors[] = "Sukunimi on pakollinen.";
    if ($dob === '')                                    $errors[] = "Syntymäaika on pakollinen.";
    if ($yr === '' || !in_array($yr, ['1','2','3']))    $errors[] = "Vuosikurssi täytyy olla 1, 2 tai 3.";
// If no validation errors -> update DB
    if (empty($errors)) {
        try {
            global $conn;
            // Update student data
            $stmt = $conn->prepare("UPDATE opiskelijat 
                                    SET etunimi = :fn, sukunimi = :ln, syntymapaiva = :dob, vuosikurssi = :yr
                                    WHERE opiskelijanumero = :id");
            $stmt->bindParam(':fn',  $fn,  PDO::PARAM_STR);
            $stmt->bindParam(':ln',  $ln,  PDO::PARAM_STR);
            $stmt->bindParam(':dob', $dob, PDO::PARAM_STR);
            $stmt->bindParam(':yr',  $yr,  PDO::PARAM_INT);
            $stmt->bindParam(':id',  $id,  PDO::PARAM_INT);
            $stmt->execute();

            // Redirect to show success message + stay on selected student
            header("Location: edit-delete-student.php?student-id={$id}&success=updated");
            exit();
        } catch (PDOException $e) {
            $error_message = "Virhe tallennettaessa: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

/* DELETE STUDENT LOGIC */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete-student'])) {
    $id = trim($_POST['opiskelijanumero'] ?? '');
    if ($id === '' || !ctype_digit($id)) {
        $error_message = "Virheellinen opiskelijan tunnus.";
    } else {
        try {
            global $conn;
            $conn->beginTransaction();// Start transaction (delete student + his registrations)

// Delete student course registrations first (foreign key)
            $delReg = $conn->prepare("DELETE FROM kurssikirjautumiset WHERE opiskelija = :id");
            $delReg->bindParam(':id', $id, PDO::PARAM_INT);
            $delReg->execute();

// Delete student record
            $delSt = $conn->prepare("DELETE FROM opiskelijat WHERE opiskelijanumero = :id");
            $delSt->bindParam(':id', $id, PDO::PARAM_INT);
            $delSt->execute();

            $conn->commit();
// After delete - go back without selected student
            header("Location: edit-delete-student.php?success=deleted");
            exit();
        } catch (PDOException $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            $error_message = "Poistovirhe: " . htmlspecialchars($e->getMessage());
        }
    }
}

/* READ SUCCESS MESSAGES FROM URL */
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'updated') $success_message = "Opiskelijan tiedot päivitetty onnistuneesti!";
    if ($_GET['success'] === 'deleted') $success_message = "Opiskelija poistettu onnistuneesti!";
}

/* IF STUDENT SELECTED — LOAD DATA TO FORM */
$selected_student = null;
if (isset($_GET['student-id']) && ctype_digit($_GET['student-id'])) {
    $sid = $_GET['student-id'];
    $selected_student = $student_map->get((int)$sid);
    $_SESSION["student_id"] = $sid;
}
?>


<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Poista / muokkaa opiskelija</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <a href="./index.php" class="go-back-btn">Palaa pääsivulle</a>
    <h1>Poista / muokkaa opiskelija</h1>

    <label for="students">Valitse opiskelija:</label>
    <select id="students" name="students">
        <option value="empty">----valitse opiskelija----</option>
        <?php foreach ($all_students as $st): ?>
            <option value="<?php echo $st['opiskelijanumero']; ?>"
                <?php if (isset($_SESSION['student_id']) && $_SESSION['student_id'] == $st['opiskelijanumero']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($st['sukunimi'].' '.$st['etunimi']); ?>
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

        <?php if ($selected_student): ?>
            <form method="POST" action="">
                <input type="hidden" name="opiskelijanumero" value="<?php echo (int)$selected_student['opiskelijanumero']; ?>"/>

                <div class="form-group">
                    <label for="etunimi">Etunimi: <span style="color:red;">*</span></label>
                    <input type="text" id="etunimi" name="etunimi" maxlength="100"
                           value="<?php echo htmlspecialchars($selected_student['etunimi']); ?>"/>
                </div>

                <div class="form-group">
                    <label for="sukunimi">Sukunimi: <span style="color:red;">*</span></label>
                    <input type="text" id="sukunimi" name="sukunimi" maxlength="100"
                           value="<?php echo htmlspecialchars($selected_student['sukunimi']); ?>"/>
                </div>

                <div class="form-group">
                    <label for="syntymapaiva">Syntymäaika: <span style="color:red;">*</span></label>
                    <input type="date" id="syntymapaiva" name="syntymapaiva"
                           value="<?php echo htmlspecialchars($selected_student['syntymapaiva']); ?>"/>
                </div>

                <div class="form-group">
                    <label for="vuosikurssi">Vuosikurssi: <span style="color:red;">*</span></label>
                    <select id="vuosikurssi" name="vuosikurssi">
                        <?php
                        $grades = ['1','2','3'];
                        $current = (string)$selected_student['vuosikurssi'];
                        ?>
                        <option value="empty">--valitse vuosikurssi--</option>
                        <?php foreach ($grades as $g): ?>
                            <option value="<?php echo $g; ?>" <?php if ($current === $g) echo 'selected'; ?>>
                                <?php echo $g; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="button-wrapper" style="display:flex; gap:.6rem; justify-content:center; flex-wrap:wrap;">
                    <button type="submit" name="update-student" class="submit-btn">Tallenna muutokset</button>
                    <button type="submit" name="delete-student" class="submit-btn"
                            onclick="return confirm('Haluatko varmasti poistaa tämän opiskelijan? Tämä poistaa myös kurssikirjautumiset.');">
                        Poista opiskelija
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="message" style="background:#fff; border:1px solid var(--line); color:var(--muted);">
                Valitse opiskelija ylhäältä muokkausta tai poistamista varten.
            </div>
        <?php endif; ?>
    </div>

    <script>

      const selectEl = document.getElementById('students');
      selectEl.addEventListener('change', function () {
        const id = this.value;
        if (id && id !== 'empty') {
          window.location.href = `edit-delete-student.php?student-id=${id}`;
        }
      });
    </script>
</body>
</html>
