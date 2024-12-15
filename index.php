<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="css/stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Gloock&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Unicase:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Unicase:wght@300;400;500;600;700&family=Gloock&display=swap');
    </style>
</head>
<body>
<?php
 include ("src/header.inc.php");
?>
<div class="service">
    <p>Gestion de vos comptes en banques client et professionels  - Service disponible  24/24h   &   7/7j</p>
</div>
<div class="connexion">
    <h1>Banque Intercontinentale<br>Française</h1>
    <div class="identifiez">
        <h2>Identifiez-vous</h2>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert <?= isset($_GET['errorClass']) ? htmlspecialchars($_GET['errorClass']) : ''; ?>">
                <p style="color: red;">
                    <?= htmlspecialchars($_GET['error']); ?>
                </p>
                <?php if (isset($_GET['tentatives'])): ?>
                    <p>Nombre de tentatives restantes : <?= htmlspecialchars($_GET['tentatives']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="src/login.php" method="post" class="identification">
                <input id="identifiant" name="identifiant" style="text-indent:17px;" class="icon-id" type="text" placeholder="Identifiant">
                <div class="field field--password">
                    <input type="password" name="password"  placeholder="Mot de passe" />
                     <div class="password-show" onclick="showPassword(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                     </div>
                     <div class="password-hide" onclick="hidePassword(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </div>
                </div>


                <button name="submit" type="submit">Se connecter</button>
            </form>
        <script>
            function showPassword (el) {
                const container = el.parentNode;
                const passwordInput = container.querySelector('input');
                passwordInput.setAttribute('type', 'text');
                document.querySelector('.password-hide').style.display = 'block';
            }
            function hidePassword (el) {
                const container = el.parentNode;
                const passwordInput = container.querySelector('input');
                passwordInput.setAttribute('type', 'password');
                document.querySelector('.password-hide').style.display = 'none';
            }</script>
    </div>
</div>

<section id="intro">
    <div class="text">
        <h1>"La confiance est notre devise"</h1>
        <hr class="slash">
        <br>
        <p>Nous vous garantissons un service à la hauteur de notre renommée en toute sécurité.</p>
    </div>
    <a href="src/cgu.php" >Conditions d’utilisation</a>
</section>

<section id="pourquoi-nous">
    <div class="gauche">
       <h3> <u>Pourquoi nous ? </u></h3>
        <hr class="slash">
        <p>Depuis <b>1975</b>, notre gestion de compte d’entreprises est la <u><b>meilleur solution </b></u>tout-en-un pour une gestion simplifiée des paiements par carte bancaire. </p>
    </div>
    <div class="img"></div>
</section>

<section id="activite">
    <div id="activite-text">
        <p>Suivez vos <b>transactions</b>, extrayez vos <b>données</b> et générez vos <b>rapports</b> en un clic, le tout en toute <b><u>sécurité</u></b> !</p>
    </div>
    <div id="activite-icon-container">
        <div class="activite-icon">
            <img src="assets/Calendrier.png">
            <p>Suivi monétique quotiden</p>
        </div>
        <div class="activite-icon2">
            <img src="assets/Extraction.png">
            <p>Extractions de données</p>
        </div>
        <div class="activite-icon">
            <img src="assets/file.png">
            <p>Édition des états de sortie</p>
        </div>
    </div>
    
</section>

<section id="siege">
    <div class="title"> <h1>Tutoriel d'utilisation vidéo</h1><hr></div>

    <div class="video-container" style="max-width: 1200px; margin: 0 auto;">
        <video controls style="width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <source src="assets/Tuto%20bif.mp4" type="video/mp4">
            Votre navigateur ne prend pas en charge la balise vidéo.
        </video>
    </div>
</section>

<section id="siege">
   <div class="title"> <h1>Notre siège</h1><hr></div>

   <div class="map">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5252.203695273183!2d2.5828800772261893!3d48.83719597132933!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e60e3161ea094f%3A0x1eac03d42a240dc3!2s2%20Rue%20Albert%20Einstein%2C%2077420%20Champs-sur-Marne!5e0!3m2!1sfr!2sfr!4v1726819094667!5m2!1sfr!2sfr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</section>

<?php
    include('src/footer.inc.php');
?>
</body>
</html>
