<?php
  $currentPage = basename($_SERVER['PHP_SELF']);
?>
<header class="site-header">
  <div class="site-header-inner">
    <a href="index.php" class="site-logo">
      <span class="site-logo-badge">KH</span>
      <span class="site-logo-text">
        <span class="site-logo-title">Kurssienhallinta</span>
        <span class="site-logo-subtitle">TAI TVT</span>
      </span>
    </a>

    <nav class="main-nav">
      <ul class="nav-list">
        <!-- Opiskelija -->
        <li class="nav-item nav-item-has-dropdown">
          <span class="nav-link">Opiskelija</span>
          <ul class="nav-dropdown">
            <li><a href="add-student.php">Lisää opiskelija</a></li>
            <li><a href="edit-delete-student.php">Poista / muokkaa opiskelija</a></li>
            <li><a href="get-student-info.php">Tarkastele opiskelijan tietoja</a></li>
          </ul>
        </li>

        <!-- Opettaja -->
        <li class="nav-item nav-item-has-dropdown">
          <span class="nav-link">Opettaja</span>
          <ul class="nav-dropdown">
            <li><a href="add-teacher.php">Lisää opettaja</a></li>
            <li><a href="edit-delete-teacher.php">Poista / muokkaa opettaja</a></li>
            <li><a href="get-teacher-info.php">Tarkastele opettajan tietoja</a></li>
          </ul>
        </li>

        <!-- Kurssi -->
        <li class="nav-item nav-item-has-dropdown">
          <span class="nav-link">Kurssi</span>
          <ul class="nav-dropdown">
            <li><a href="add-course.php">Lisää kurssi</a></li>
            <li><a href="edit-delete-course.php">Poista / muokkaa kurssi</a></li>
            <li><a href="get-course-info.php">Tarkastele kurssin tietoja</a></li>
          </ul>
        </li>

        <!-- Tila -->
        <li class="nav-item nav-item-has-dropdown">
          <span class="nav-link">Tila</span>
          <ul class="nav-dropdown">
            <li><a href="add-auditory.php">Lisää tila</a></li>
            <li><a href="edit-delete-auditory.php">Poista / muokkaa tila</a></li>
            <li><a href="get-auditory-info.php">Tarkastele tilan tietoja</a></li>
          </ul>
        </li>

        <!-- Aikataulu -->
        <li class="nav-item">
          <a
            href="get-timetable-info.php"
            class="nav-link <?php echo $currentPage === 'get-timetable-info.php' ? 'nav-link-active' : ''; ?>"
          >
            Aikataulu
          </a>
        </li>

        <!-- Etusivu -->
        <li class="nav-item">
          <a
            href="index.php"
            class="nav-link <?php echo $currentPage === 'index.php' ? 'nav-link-active' : ''; ?>"
          >
            Etusivu
          </a>
        </li>
      </ul>
    </nav>
  </div>
</header>
