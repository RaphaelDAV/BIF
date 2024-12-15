<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once 'config.php'; // Ensure this is included to access BASE_URL
?>

<head>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/footer.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');
    </style>

</head>
 <footer>
    <div class="footer-container">
        <div class="footer-main">
            <div class="hr"> <hr> </div>
            <div class="footer-center">
                <div class="logo-container">
                    <p class="vague">~</p>
                    <img src="<?php echo BASE_URL; ?>/assets/logo2.png" alt="">
                    <p>B.I.F</p>
                    <p class="vague">~</p></div>
                </div>
            <div class="hr"> <hr> </div>
        </div>
        <div class="footer-link">
            <p>2 Rue Albert EINSTEIN 77420 <br> Champs-sur-Marne, 77420</p>
            <a href="<?php echo BASE_URL; ?>/index.php">Accueil</a>
            <a href="<?php echo BASE_URL; ?>/src/mention_legal.php">Mentions légal</a>
            <a href="<?php echo BASE_URL; ?>/src/contact.php">Contact</a>
            <a href="<?php echo BASE_URL; ?>/src/cgu.php">Conditions d'utilisation</a>
        </div>
        <div class="footer-legal"><p>© 2024 By B.I.F. Powered and secured by etudiant.u-pem.fr</p></div>
    </div>
</footer>