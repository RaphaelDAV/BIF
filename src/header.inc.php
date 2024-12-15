<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once 'config.php'; // Ensure this is included to access BASE_URL
?>

<head>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/stylesheet.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');
    </style>
</head>
<nav class="nav-login">
    <img src="<?php echo BASE_URL; ?>/assets/logo.png">
    <h1 id="bif"><a href="<?php echo BASE_URL; ?>/index.php">~B.I.F~</a></h1>
    <div class="Bouton-session">

        <?php
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'ProductOwner') {
            echo '<h2><a href="' . BASE_URL . '/src/product_owner.php">Product Owner</a></h2>';
        }

        if (isset($_SESSION['role']) && $_SESSION['role'] == 'Client') {
            echo '<h2><a href="' . BASE_URL . '/src/client.php">Client</a></h2>';
        }

        if (isset($_SESSION['identifiant'])) {
            echo '<h2><a href="' . BASE_URL . '/src/logout.php">Déconnexion</a></h2>';
        } else {
            echo '<h2><a href="' . BASE_URL . '/index.php">Connexion</a></h2>';
        }

        if (isset($_SESSION['role']) && $_SESSION['role'] == 'Client') {
            echo "<h1><a href='" . BASE_URL . "/src/profil.php'>Profil</a></h1>";
        }
        ?>



        <h1><a href="<?php echo BASE_URL; ?>/src/contact.php">Contact</a></h1>
    </div>
</nav>
