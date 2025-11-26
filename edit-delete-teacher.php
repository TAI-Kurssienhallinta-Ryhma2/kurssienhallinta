<?php
include_once 'sql-request.php';

/* Fetch all teachers to populate the dropdown selector */
$all_teachers = get_all_teachers();

$success_message = null;
$error_message   = null;

/*
   UPDATE TEACHER LOGIC
   Triggered when the "update-teacher" button is submitted
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update-teacher'])) {

    // Read input values safely
    $id  = trim($_POST['tunnusnumero'] ?? '');
    $fn  = trim($_POST['etunimi'] ?? '');
    $ln  = trim($_POST['sukunimi'] ?? '');
    $sub = trim($_POST['aine'] ?? '');

    // Normalize values the same way as in add-teacher.php
    if ($fn !== '')  $fn  = ucfirst(strtolower($fn));
    if ($ln !== '')  $ln  = ucfirst(strtolower($ln));
    if ($sub !== '') $sub = ucfirst(strtolower($sub));

    // Validation rules
    $errors = [];
    if ($id === '' || !ctype_digit($id))      $errors[] = "Virheellinen opettajan tunnus.";
    if ($fn === '')                            $errors[] = "Etunimi on pakollinen.";
    if ($ln === '')                            $errors[] = "Sukunimi on pakollinen.";
    if ($sub === '')                           $errors[] = "Aine on pakollinen.";
    if ($sub !== '' && strlen($sub) > 70)      $errors[] = "Aineen nimi on liian pitkä (max 70 merkkiä).";

    /* If validation passes - update DB */
    if (empty($errors)) {
        try {
            global $conn;

            // Prepare SQL UPDATE statement
            $stmt = $conn->prepare(
                "UPDATE opettajat
                 SET etunimi = :fn, sukunimi = :ln, aine = :sub
                 WHERE tunnusnumero = :id"
            );
            $stmt->bindParam(':fn',  $fn,  PDO::PARAM_STR);
            $stmt->bindParam(':ln',  $ln,  PDO::PARAM_STR);
            $stmt->bindParam(':sub', $sub, PDO::PARAM_STR);
            $stmt->bindParam(':id',  $id,  PDO::PARAM_INT);
            $stmt->execute();

            // Redirect to show success message and keep selected teacher
            header("Location: edit-delete-teacher.php?teacher-id={$id}&success=updated");
            exit();
        } catch (PDOException $e) {
            $error_message = "Tallennusvirhe: " . htmlspecialchars($e->getMessage());
        }
    } else {
        // Combine all validation errors into one message
        $error_message = implode("<br>", $errors);
    }
}

/*
   DELETE TEACHER LOGIC (CASCADE DELETE)
   Removes teacher - their courses - all course registrations
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete-teacher'])) {

    $id = trim($_POST['tunnusnumero'] ?? '');

    // Validate teacher ID
    if ($id === '' || !ctype_digit($id)) {
        $error_message = "Virheellinen opettajan tunnus.";
    } else {
        try {
            global $conn;
            $conn->beginTransaction();

            /* Delete registrations for all courses taught by this teacher */
            $q1 = $conn->prepare(
                "DELETE FROM kurssikirjautumiset 
                 WHERE kurssi IN (SELECT tunnus FROM kurssit WHERE opettaja = :id)"
            );
            $q1->bindParam(':id', $id, PDO::PARAM_INT);
            $q1->execute();

            /* Delete the teacher’s courses */
            $q2 = $conn->prepare("DELETE FROM kurssit WHERE opettaja = :id");
            $q2->bindParam(':id', $id, PDO::PARAM_INT);
            $q2->execute();

            /* Delete the teacher */
            $q3 = $conn->prepare("DELETE FROM opettajat WHERE tunnusnumero = :id");
            $q3->bindParam(':id', $id, PDO::PARAM_INT);
            $q3->execute();


            $conn->commit();

            // Redirect without selected teacher
            header("Location: edit-delete-teacher.php?success=deleted");
            exit();

        } catch (PDOException $e) {
            // Rollback if something fails
            if ($conn->inTransaction()) $conn->rollBack();
            $error_message = "Poistovirhe: " . htmlspecialchars($e->getMessage());
        }
    }
}

/* READ SUCCESS MESSAGE FROM URL */
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'updated') $success_message = "Opettajan tiedot päivitetty onnistuneesti!";
    if ($_GET['success'] === 'deleted') $success_message = "Opettaja poistettu onnistuneesti!";
}

/*
   DETERMINE SELECTED TEACHER AND LOAD THEIR DATA
   (Runs when ?teacher-id= is present)
*/
$selected_teacher = null;

if (isset($_GET['teacher-id']) && ctype_digit($_GET['teacher-id'])) {
    $tid = $_GET['teacher-id'];

    // Find the teacher inside $all_teachers array
    foreach ($all_teachers as $t) {
        if ((string)$t['tunnusnumero'] === (string)$tid) {
            $selected_teacher = $t;
            $_SESSION["teacher_id"] = $tid; // Save selection
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Poista / muokkaa opettaja</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include 'header.php'; ?>
  <h1>Poista / muokkaa opettaja</h1>

  <label for="teachers">Valitse opettaja:</label>
  <select id="teachers" name="teachers">
    <option value="empty">----valitse opettaja----</option>
    <?php foreach ($all_teachers as $t): ?>
      <option value="<?php echo $t['tunnusnumero']; ?>"
        <?php if (isset($_SESSION['teacher_id']) && $_SESSION['teacher_id'] == $t['tunnusnumero']) echo 'selected'; ?>>
        <?php echo htmlspecialchars($t['sukunimi'].' '.$t['etunimi']); ?>
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

    <?php if ($selected_teacher): ?>
      <form method="POST" action="">
        <input type="hidden" name="tunnusnumero" value="<?php echo (int)$selected_teacher['tunnusnumero']; ?>"/>

        <div class="form-group">
          <label for="etunimi">Etunimi: <span style="color:red;">*</span></label>
          <input type="text" id="etunimi" name="etunimi" maxlength="100"
                 value="<?php echo htmlspecialchars($selected_teacher['etunimi']); ?>"/>
        </div>

        <div class="form-group">
          <label for="sukunimi">Sukunimi: <span style="color:red;">*</span></label>
          <input type="text" id="sukunimi" name="sukunimi" maxlength="100"
                 value="<?php echo htmlspecialchars($selected_teacher['sukunimi']); ?>"/>
        </div>

        <div class="form-group">
          <label for="aine">Aine: <span style="color:red;">*</span></label>
          <input type="text" id="aine" name="aine" maxlength="70"
                 value="<?php echo htmlspecialchars($selected_teacher['aine']); ?>"/>
        </div>

        <div class="button-wrapper" style="display:flex; gap:.6rem; justify-content:center; flex-wrap:wrap;">
          <button type="submit" name="update-teacher" class="submit-btn">Tallenna muutokset</button>
          <button type="submit" name="delete-teacher" class="submit-btn"
                  onclick="return confirm('Haluatko varmasti poistaa tämän opettajan? Tämä poistaa myös hänen kurssinsa ja niiden ilmoittautumiset.');">
            Poista opettaja
          </button>
        </div>
      </form>
    <?php else: ?>
      <div class="message" style="background:#fff; border:1px solid var(--line); color:var(--muted);">
        Valitse opettaja ylhäältä muokkausta tai poistamista varten.
      </div>
    <?php endif; ?>
  </div>

  <script>
    const sel = document.getElementById('teachers');
    sel.addEventListener('change', function () {
      const id = this.value;
      if (id && id !== 'empty') {
        window.location.href = `edit-delete-teacher.php?teacher-id=${id}`;
      }
    });
  </script>
  <?php include 'footer.php'; ?>
</body>
</html>
