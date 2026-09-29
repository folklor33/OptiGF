<?php
require_once 'managers/config.php';

$current_page = 'settings';
$current_section = isset($_GET['section']) ? $_GET['section'] : 'home';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= BASE_PATH ?>">
    <title>OptiGF.eu - Paramètres</title>
    <script src="assets/sections.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="assets/styles.css" rel="stylesheet">
    <link href="assets/mobile.css" rel="stylesheet">
</head>

<body>
    <?php include '_header.php'; ?>

    <div class="main-container bg-dark text-white pb-5">


        <!-- Menu gauche -->
        <div class="sidebar-left col-md-2">
            <?php include '_sections.php'; ?>
        </div>

        <!-- Contenu principal -->
        <br><br><br>
        <div class="main-content col-md-7">
            <div class="mt-2">
                <!-- Section Home Windows -->
                <div id="home-section" class="content-section">
                    <h2>Tutos : Paramètres logiciel</h2>
                    <p class="lead">Section dédiée aux différents réglages du PC pour éviter les lags sur GF</p>

                    <div class="alert alert-info">
                        <h5>Pour information</h5>
                        <p>- Nous allons voir ici uniquement les réglages qui ont un impact avec Grand Fantasia. Vous
                            pourrez aller
                            plus loin en faisant des recherches complémentaires (notamment si vous avez un pc
                            relativement ancien ou complètement éclaté lol...)</p>
                        <p>- Aucune manipulation risquée ne sera faite dans ces tutos mais veillez à les suivre
                            correctement pour ne pas faire de bétise...<br>
                            <strong>Nous ne sommes pas responsables des mauvaises actions faites de votre part.</strong>
                        </p>
                    </div>


                    <p>Grand Fantasia est un ancien jeu (~2010) qui n'a jamais été adapté aux nouvelles générations
                        d'ordinateurs. Par conséquent, son comportement est adapté aux anciennes machines et est
                        volontairement bridé. Nous verrons ici comment profiter au maximum des performances de votre PC
                        pour faire tourner le jeu, que ce soit en modifiant les réglages in-game, ou de windows
                        directement.</p>
                    <p>Naviguez entre les sections de gauche pour découvrir tous les tutos !</p>
                </div>

                <!-- SECTION SYSTEME -->
                <div id="windows-section" class="content-section" style="display: none;">
                    <h2>Paramètres système : Windows</h2>
                    <p>Configuration et optimisation des systèmes Windows.</p>

                    <h4>Optimisation système</h4>
                    <ul>
                        <li>windows defender</li>
                        <li>affichage 120fps</li>
                        <li>installation sur disque SSD</li>
                        <li>gestion de l'alimentation</li>
                    </ul>

                    <button onclick="ouvrirUri('windowsdefender:')">
                        Ouvrir Sécurité Windows
                    </button>

                    <button onclick="ouvrirUri('ms-settings:advanceddisplay')">
                        Ouvrir Affichage
                    </button>

                    <button onclick="ouvrirUri('ms-settings:powersleep')">
                        Ouvrir Alimentation
                    </button>
                    <br>
                    Win + R <br>
                    <code>dfrgui.exe</code><br>
                    Optimiser les lecteurs afin de savoir si son disque dur est SSD ou HDD

                    <script>
                        function ouvrirUri(uri) {
                            window.location.href = uri;
                        }
                    </script>
                </div>

                <!-- SECTION NVIDIA -->
                <div id="nvidia-section" class="content-section" style="display: none;">
                    <h2>Paramètres graphiques : NVIDIA</h2>
                    <p class="lead">Optimisez vos paramètres graphiques afin d'obtenir de meilleures performances en jeu
                    </p>

                    <ul>
                        <li>débridage FPS (400+)</li>
                        <li>captures écran paramètres</li>
                    </ul>

                </div>
            </div>
            <div class="text-end">
                <p class="mt-5 pt-4 text-white opacity-25">
                    <em>En cours de développement...</em><br>
                    <em>Dernières modifications: Avril 2026</em>
                </p>
                <a href="https://liberapay.com/OptiGF/donate"
                    title="Le projet est 100% gratuit ! Toute aide est la bienvenue... " target="_blank"
                    rel="noopener noreferrer" rel="noopener">
                    <img src="https://liberapay.com/assets/widgets/donate.svg">
                </a>
            </div>


        </div>

        <!-- Links droite -->
        <div class="sidebar-right col-md-2">
            <?php include '_links.php'; ?>
        </div>

    </div>

    <?php include '_footer.php'; ?>
</body>

</html>