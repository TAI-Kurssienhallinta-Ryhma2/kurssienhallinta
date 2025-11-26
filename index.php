<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tervetuloa Kurssienhallintaan</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="home-page">
<?php include 'header.php'; ?>

<main class="home-main">
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>

        <div class="hero-inner shell">
            <p class="hero-kicker">Tervetuloa Kurssienhallintaan</p>
            <h1 class="hero-title">
                Hallinnoi kursseja, opiskelijoita ja opettajia yhdestä paikasta
            </h1>
            <p class="hero-text">
                Lisää uusia kurssikirjautumisia tai hallinnoi olemassa olevia helposti.
            </p>

            <div class="hero-actions">
                <a href="course-registration.php" class="hero-btn hero-btn-primary">
                    Lisää kurssikirjautuminen
                </a>
                <a href="edit-delete-registration.php" class="hero-btn hero-btn-secondary">
                    Poista / muokkaa kurssikirjautuminen
                </a>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
</body>
</html>