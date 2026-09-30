<?php
require_once 'managers/config.php';
require_once 'managers/SystemManagers.php';

SessionManager::start();

$current_page = 'injection';
$current_section = isset($_GET['section']) ? $_GET['section'] : 'home';

// Variables pour les messages
$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

// Vérifier si l'utilisateur est connecté
$isUserLoggedIn = SessionManager::get('user_id') !== null;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= BASE_PATH ?>">
    <title>OptiGF.eu - Injection</title>
    <script src="assets/sections.js"></script>
    <script src="assets/zoomable-images.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="assets/styles.css" rel="stylesheet">
    <link href="assets/zoomable-images.css" rel="stylesheet">
    <link href="assets/mobile.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <?php include '_header.php'; ?>

    <div class="main-container bg-dark text-white pb-5">
        <!-- Menu gauche fixe -->
        <div class="sidebar-left col-md-2">
            <?php include '_sections.php'; ?>
        </div>

        <!-- Contenu principal -->
        <br><br><br>
        <div class="main-content col-md-7">
            <div class="mt-2">
                <!-- Messages -->
                <?php if ($errorMessage || $successMessage): ?>
                    <div class="modal-overlay" onclick="closeModal()">
                        <div class="modal-content <?= $errorMessage ? 'error' : 'success' ?>"
                            onclick="event.stopPropagation()">
                            <h4><?= $errorMessage ? '⚠️ Erreur' : '✅ Succès' ?></h4>
                            <p><?= htmlspecialchars($errorMessage ?: $successMessage) ?></p>
                            <button class="modal-close" onclick="closeModal()">Fermer</button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Section Home Injection -->
                <div id="home-section" class="content-section">
                    <h2>Injection Grand Fantasia !</h2>
                    <p class="lead">Intégrer des ressources personnalisées dans les fichiers du jeu.</p>
                    <div class="alert alert-warning">
                        <h5>⚠️ Avertissements ⚠️</h5>
                        <p class="mb-0">- Modifier les fichiers de GF (hors UI) n'est normalement pas autorisé par
                            X-Legend. Vous vous exposez à des risques en cas de captures écran envoyées au staff par
                            exemple.</p>
                        <p>- Il n'est pas forcément nécessaire d'avoir la dernière version de l'injection pour que cela
                            soit jouable; mais s'il devait y avoir une trop grande différence
                            avec les fichiers de X-Legend et ceux de votre version, vous pourriez rencontrer
                            quelques incohérences (surtout avec les events temporaires ou ajouts de nouveaux items).</p>
                    </div>

                    <!-- <div class="alert alert-info">
                        
                    </div> -->

                    <div class="alert alert-danger">
                        <strong>Ce projet est pour l'instant gratuit. N'acceptez pas d'offre
                            payante par des tiers inconnus ! C'est probablement une arnaque...
                    </div>
                    <h3>> Préambule</h3>

                    <div class="text-justify">
                        <p>
                            L'Injection Grand Fantasia by OptiGF est un ensemble de modifications de fichiers du jeu
                            permettant une meilleure expérience in-game. Que ce soit à travers des textes modifiés ou
                            des icônes revisités, profitez d'un avantage considérable en arènes PvP ou donjons PvE. Pour
                            de plus amples détails, consultez la section des <a href="injection.php#tutos">aperçus</a> incluant des
                            captures écrans pour voir ce que cela donne en jeu !
                        </p>
                        <p>
                            Ce projet s'inspire en partie des idées de "GF Ultimate" pour les connaisseurs. Il a été
                            initié par le staff <strong>OptiGF</strong> et est testé depuis début 2024 dans la guilde
                            <strong>"TrapGang"</strong> et plus récemment chez <strong>"FleurDeLys"</strong>.
                            Vous souhaitez en savoir plus ? Consulter la page
                            <a href="#about">Accueil / À propos</a>.
                        </p>
                        <p>
                            Nous avons expérimenté l'injection en interne pendant de nombreux mois et cela fut très
                            productifs pour les membres. Le jeu étant maintenant beaucoup moins fréquenté qu'auparavent,
                            nous avons décidé de l'ouvrir à tous les joueurs. Alors profitez-en ! 😊
                        </p>
                        <p>
                            Nous rappelons que ce projet est pour l'instant totalement gratuit compte tenu du peu de
                            mises à jour produites par X-Legend pour la version globale. En l'état, cela représente tout
                            de même un coût en temps et en argent (maitenances et hébergement notamment).

                            De nombreuses heures de travail ont permis l'élaboration et la maintenance de ce projet...
                            N'hésitez pas à remercier tout le staff ! Que ce soit à travers des messages de soutien, des
                            dons in-game ou via <a href="https://liberapay.com/OptiGF" target="_blank"
                                rel="noopener noreferrer" rel="noopener">Liberapay</a></p>
                        <p>Nous nous gardons le droit de faire évoluer les conditions d'accès, moyennant finance par
                            exemple.
                        </p>
                    </div>

                    <h3>> Comment bénéficier de l'Injection OptiGF ?</h3>
                    <div class="text-justify">
                        <p class="lead">Inscription</p>
                        <ul>
                            <li><strong>Rejoindre le </strong><a target="_blank" rel="noopener noreferrer"
                                    href="https://discord.gg/58j4JnYFdz"><strong>Serveur Discord :</strong></a></li>
                            <li><strong>Générer une </strong><em><strong>"Clé de Carso"</strong></em> via le bot intégré
                                (cf. explications du salon d'accueil)</li>
                            <li><strong>Récupérer la clé</strong> dans ses MP</li>
                            <li><strong>Créer son compte</strong> sur le site web <a href="/#account">ici</a>
                            </li>
                        </ul>

                        <p class="lead">Connexion et téléchargement</p>
                        <ul>
                            <li><strong>Se connecter</strong> au site via la <a href="#account">même page</a>
                            </li>
                            <li><strong>Télécharger</strong> l'application "Injecteur OptiGF.exe" <a
                                    href="injection.php#download">ici</a></li>
                            <li><strong>Lancer l'app puis s'authentifier</strong> grâce au bouton "Connexion" (API) :
                                mêmes identifiants que sur le site web </li>
                        </ul>

                        <p class="lead">
                            Injection : comment utiliser <a href="#" class="image-trigger"
                                data-image="assets/img/injection/previews/apercu-optigf-exe-3.webp"
                                data-alt="aperçu-optigf.exe">OptiGF.exe <i class="bi bi-info-circle"></i></a>

                        </p>
                        <ul>
                            <li><strong>Sélectionner le dossier</strong> de son GF >> naviguer jusqu'à
                                "GrandFantasia.exe" (ou un autre nom)</li>
                            <li><strong>Vérifier l'installation</strong> avec le bouton bleu "OK"</li>
                            <li><strong>Appliquer</strong> l'injection ou les UI via les boutons correspondants</li>
                            <li><strong>Attendre</strong> les chargements et <strong>Lancer le jeu</strong> directement
                                avec le bouton "Play" vert ou en double cliquant sur l'exécutable GrandFantasia.exe</li>
                        </ul>
                    </div>

                </div>

                <!-- Fonctionnalités -->
                <div id="tutos-section" class="content-section" style="display: none;">
                    <h2>Injection OptiGF : Liste des fonctionnalités</h2>
                    <p class="lead">Retrouvez ici quelques screens de modifications que nous proposons dans l'Injection
                        OptiGF.</p>

                    <div class="accordion" id="faqAccordionInjection">

                        <!-- ICONES -->
                        <div class="accordion-item mt-2">
                            <h4 class="accordion-header" id="faqHeadingInjection1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjection1" aria-expanded="false"
                                    aria-controls="faqCollapseInjection1">
                                    Icônes modifiés -- également inclus dans les procs !
                                </button>
                            </h4>
                            <div id="faqCollapseInjection1" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjection1" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">
                                    <!-- icones body -->
                                    <p>Une sélection de plus de 1000 armes, équipements ou effets considérés comme les
                                        plus forts et les plus joués ont reçu des icônes modifiées par rapport au jeu de
                                        base.</p>

                                    <!-- encadrés -->
                                    <p class="lead">Encadrés :</p>
                                    <div class="p-2 border-start mb-5">
                                        <p>
                                            <span>Appliqués aux armes, équipements et effets :</span><br>
                                            <img style="width: 49%;" class="zoomable-image mx-auto"
                                                src="assets/img/injection/previews/injected/icons-cadre.webp"
                                                alt="screen-icons">
                                        <ul>
                                            <li>encadré bleu pour les items bleus</li>
                                            <li>encadré orange pour certains items oranges</li>
                                            <li>encadré gold pour certains items golds</li>
                                            <li>encadré violet pour tous les items violets du jeu</li>
                                            <li>encadré noir pour les stuffs PVP reforgé et les access TDB Souterraine
                                            </li>
                                            <li>encadré spécial pour les tarots lvl 90 et certains effets comme les
                                                perles
                                                réincarnées</li>
                                        </ul>

                                        </p>
                                    </div>


                                    <!-- background-color -->
                                    <p class="lead">Couleur de fond :</p>
                                    <div class="p-2 border-start mb-5">
                                        <p>
                                            <span>5 couleurs qui représentent les 5 utilités majeures des objets du jeu
                                                en combat.</span><br>Voilà le résumé appliqués aux armes et équipements
                                            :<br>
                                            <img style="width: 49%;" class="zoomable-image mx-auto"
                                                src="assets/img/injection/previews/injected/icons-fond.webp"
                                                alt="screen-icons">
                                        <ul>
                                            <li>fond de couleur violette pour les items qui servent à controler
                                                l'adversaire : dodo, stun, silence, épuisement, poisse, antiswitch etc
                                            </li>
                                            <li>fond de couleur jaune pour les items qui servent à DPS : dégâts
                                                compétences, deg x2, reflect etc</li>
                                            <li>fond de couleur verte pour les items qui servent à résister :
                                                régénération, dispells, réduction de dégâts, anti crit, réduction de
                                                pénétration, PV max etc</li>
                                            <li>fond de couleur grise pour les items qui servent à mieux toucher : break
                                                esquive et bonus de taux de toucher</li>
                                            <li>fond couleur bleu pour les items qui servent à break def (même si vous
                                                ne connaissez pas tous les items break def du jeu, quand vous verrez une
                                                icône bleu que votre adversaire vous a proc dessus dans vos négatifs,
                                                vous saurez que vous êtes break def et qu'il faut vous méfier).</li>
                                        </ul>

                                        </p>
                                        <p>Exemples :
                                        <ul>
                                            <li>
                                                <span>Un joueur qui a reçu beaucoup de pet def, toutes les icônes ont un
                                                    fond de couleur bleue qui représente l'effet pet def</span><br>
                                                <img class="zoomable-image mx-auto"
                                                    src="assets/img/injection/previews/injected/icons-exemple1.webp"
                                                    alt="screen-icons">
                                            </li>
                                            <li>
                                                <span>Un joueur qui a des bonus de réduction de dégâts, les icônes ont
                                                    un fond de couleur verte pour représenter la robustesse</span><br>
                                                <img class="zoomable-image mx-auto"
                                                    src="assets/img/injection/previews/injected/icons-exemple2.webp"
                                                    alt="screen-icons">
                                            </li>
                                        </ul>
                                        </p>
                                    </div>


                                    <!-- stones -->
                                    <p class="lead">Icônes d'effet de perles :</p>
                                    <div class="p-2 border-start mb-5">
                                        <p>
                                            <span>Il y a également des icônes pour différencier les effets de pierre.
                                                L'exemple qui va suivre concerne les pierres qui donnent du taux de
                                                touché mais la même logique s'applique à toutes les autres pierres du
                                                jeu.
                                            </span><br>
                                            Dans le screen ci-dessous :
                                        <ul>
                                            <li>les cibles sont les perles précision.</li>
                                            <li>les oiseaux sont les perles faucon</li>
                                        </ul>
                                        <img style="width: 49%;" class="zoomable-image mx-auto"
                                            src="assets/img/injection/previews/injected/icons-stones-exemple.webp"
                                            alt="screen-icons">
                                        <ul>
                                            <li>la précision normale aura un encadré gris</li>
                                            <li>la précision royale aura un encadré orange</li>
                                            <li>la précision vindicatrice aura un encadré gold</li>
                                            <li>la faucon absolue aura un encadré rouge</li>
                                            <li>la faucon prestigieuse aura un encadré noir</li>
                                            <li>la faucon réincarnée aura un encadré de stuff tarot</li>
                                        </ul>
                                        </p>

                                    </div>

                                    <!-- apercus globaux -->
                                    <p class="lead">Exemple de rendu final :</p>
                                    <div class="p-2 border-start mb-5">
                                        <div class="photos-list d-flex justify-content-center gap-3 mb-3">
                                            <img style="width: 40%;" class="zoomable-image"
                                                src="assets/img/injection/previews/vanilla/stuff.webp"
                                                alt="Capture d'écran avant injection (vanilla)">

                                            <img style="width: 40%;" class="zoomable-image"
                                                src="assets/img/injection/previews/injected/stuff.webp"
                                                alt="Capture d'écran après injection">
                                        </div>
                                        <div class="photos-list d-flex justify-content-center gap-3 mb-3">
                                            <img style="width: 40%;" class="zoomable-image"
                                                src="assets/img/injection/previews/vanilla/icons_fight.webp"
                                                alt="Capture d'écran avant injection (vanilla)">

                                            <img style="width: 40%;" class="zoomable-image"
                                                src="assets/img/injection/previews/injected/icons_fight.webp"
                                                alt="Capture d'écran après injection">
                                        </div>

                                        <div class="photos-list d-flex justify-content-center gap-3 mb-3">
                                            <img style="width: 60%;" class="zoomable-image mx-auto"
                                                src="assets/img/injection/previews/injected/consos.png"
                                                alt="Capture d'écran après injection">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TEXTINDEX -->
                        <div class="accordion-item mt-2">
                            <h4 class="accordion-header" id="faqHeadingInjection2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjection2" aria-expanded="false"
                                    aria-controls="faqCollapse2">
                                    Modifications des traductions (pour l'instant disponible qu'en FR)
                                </button>
                            </h4>
                            <div id="faqCollapseInjection2" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjection2" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">

                                    <p>Une image vaut mille mots ! À gauche sans injection et à droite avec :</p>
                                    <ul>
                                        <li>Réduction de la taille des textes -- meilleur lisibilité !</li>
                                        <li>Des infos utiles mises en avant -- gagnez en productivité !</li>
                                        <li>Quelques bugs trad corrigés</li>
                                    </ul>
                                    <div class="photos-list d-flex justify-content-between">
                                        <img style="width: 49%;" class="zoomable-image"
                                            src="/assets/img/injection/previews/vanilla/detailAttr.webp"
                                            alt="Capture d'écran avant injection (vanilla)">

                                        <img style="width: 49%;" class="zoomable-image"
                                            src="/assets/img/injection/previews/injected/detailAttr.webp"
                                            alt="Capture d'écran après injection">
                                    </div>

                                    <div class="photos-list d-flex justify-content-between">
                                        <img style="width: 49%;" class="zoomable-image"
                                            src="/assets/img/injection/previews/vanilla/dialog_lavigny.webp"
                                            alt="Capture d'écran avant injection (vanilla)">

                                        <img style="width: 49%;" class="zoomable-image"
                                            src="/assets/img/injection/previews/injected/dialog_lavigny.webp"
                                            alt="Capture d'écran après injection">
                                    </div>

                                    <div class="photos-list d-flex justify-content-between">
                                        <img style="width: 49%;" class="zoomable-image"
                                            src="/assets/img/injection/previews/vanilla/dj_reinca.webp"
                                            alt="Capture d'écran avant injection (vanilla)">

                                        <img style="width: 49%;" class="zoomable-image"
                                            src="/assets/img/injection/previews/injected/dj_reinca.webp"
                                            alt="Capture d'écran après injection">
                                    </div>

                                    <div class="photos-list d-flex justify-content-between">
                                        <img style="width: 49%;" class="zoomable-image"
                                            src="/assets/img/injection/previews/vanilla/donjons_infernaux.webp"
                                            alt="Capture d'écran avant injection (vanilla)">

                                        <img style="width: 49%;" class="zoomable-image"
                                            src="/assets/img/injection/previews/injected/donjons_infernaux.webp"
                                            alt="Capture d'écran après injection">
                                    </div>

                                    <div class="photos-list d-flex justify-content-between">
                                        <img style="width: 49%;" class="zoomable-image"
                                            src="/assets/img/injection/previews/vanilla/tdb_names.webp"
                                            alt="Capture d'écran avant injection (vanilla)">

                                        <img style="width: 49%;" class="zoomable-image"
                                            src="/assets/img/injection/previews/injected/tdb_names.webp"
                                            alt="Capture d'écran après injection">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- COFFRES -->
                        <div class="accordion-item mt-2">
                            <h4 class="accordion-header" id="faqHeadingInjection3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjection3" aria-expanded="false"
                                    aria-controls="faqCollapseInjection3">
                                    Nouveaux coffres pour les loots au sol
                                </button>
                            </h4>
                            <div id="faqCollapseInjection3" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjection3" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">
                                    <span>Surtout utile en DJ infernaux / TDB : visualisez directement le drop des items rares
                                        !</span>
                                    <div class="photos-list d-flex justify-content-center mt-3">
                                        <img style="width: 60%;" class="zoomable-image border-white"
                                            src="assets/img/injection/previews/injected/coffres.webp"
                                            alt="Apercu de coffres supplémentaires pour certains loots spéciaux">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- MONSTRES -->
                        <div class="accordion-item mt-2">
                            <h4 class="accordion-header" id="faqHeadingInjectionMonstres">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjectionMonstres" aria-expanded="false"
                                    aria-controls="faqCollapseInjectionMonstres">
                                    Masquage de certains monstres inutiles, modifications de certains boss, ...
                                </button>
                            </h4>
                            <div id="faqCollapseInjectionMonstres" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjectionMonstres" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">
                                    <span>Surtout utile en TDB : passer les étages plus rapidement ou éviter les
                                        lags.</span>
                                </div>
                            </div>
                        </div>

                        <!-- GF61 -->
                        <div class="accordion-item mt-2">
                            <h4 class="accordion-header" id="faqHeadingInjectionGf61">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjectionGf61" aria-expanded="false"
                                    aria-controls="faqCollapseInjectionGf61">
                                    "GF_61Hz" : exécutable GrandFantasia.exe améliorée
                                </button>
                            </h4>
                            <div id="faqCollapseInjectionGf61" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjectionGf61" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">
                                    <span>Lancer le jeu directement depuis l'exécutable, sans modification de la
                                        fréquence/résolution d'affichage écran.</span>
                                </div>
                            </div>
                        </div>

                        <!-- CLIENT.INI -->
                        <div class="accordion-item mt-2">
                            <h4 class="accordion-header" id="faqHeadingInjectionClientIni">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjectionClientIni" aria-expanded="false"
                                    aria-controls="faqCollapseInjectionClientIni">
                                    Client.ini : réglages internes du jeu
                                </button>
                            </h4>
                            <div id="faqCollapseInjectionClientIni" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjectionClientIni" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">
                                    <span>Réglages optimisés préconfigurés. Attention si vous modifier les paramètres en
                                        jeu : vos FPS seront réinitialisés à 60Hz (merci XL...). Il faudra relancer le
                                        jeu pour revenir à une fréquence d'affichage débridée.</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <p>Toute suggestion est la bienvenue ! N'hésitez pas également à nous signer des bugs si vous en
                        voyez...</p>

                </div>



                <!-- UI SWITCHER -->
                <div id="uiswitcher-section" class="content-section">
                    <h2>UI Switcher</h2>
                    <p class="lead">Changer son UI ? Cela n'a jamais été aussi facile...</p>

                    <p class="text-justify">
                        Retrouvez ci-dessous une présentation pour chacun des différents UI switchables sur notre
                        application.
                        <!-- Vous pourrez également télécharger les .ZIP seuls si vous préférez (l'injection ne
                        sera pas inclue). -->
                    </p>

                    <div class="alert alert-info">
                        <h5>Bon à savoir</h5>
                        <p>- Modifier les fichiers UI de son GF est toléré par X-Legend dans la
                            version GLOBALE (contrairement à ORIGIN). Vous ne risquez rien ici.<br>
                            - Chaque UI est stocké sur les serveurs OptiGF pour faciliter les téléchargements mais il
                            s'agit des dernières versions (officielles et à jour) que vous pourrez retrouver sur les
                            sites et serveurs Discord des développeurs eux-mêmes. En cas de mises à jour non effectuées
                            par nos soins,
                            n'hésitez pas à nous le signaler sur Discord.</p>
                    </div>

                    <!-- ui numéro 1 -->
                    <div>
                        <h3>UI TrapGang International</h3>
                        <p class="lead">UI phare spécialement conçu pour le lancement du projet !</p>
                        <div class="mx-3 mb-5">
                            <p class="text-justify">
                                L'UI TrapGang International a été conçu pour qu'il soit compatible avec le plus de
                                résolutions
                                différentes tout en offrant une interface qui améliore la visibilité et le confort de
                                jeu.
                            </p>
                            <p class="text-justify">
                                Cet UI est optimisé pour toutes les résolutions supérieures à 1360p. Il est également
                                utilisable
                                en dessous de 1360 mais certaines parties de l'interface seront mal positionnées sur
                                votre
                                écran.
                            </p>
                            <p class="text-justify">
                                Il est obligatoire d'activer les statuts positifs et négatifs de votre personnage et de
                                la cible
                                dans les paramètres de votre jeu in-game pour utiliser l'UI a son plein potentiel.<br>
                            </p>
                            <p>
                            <div class="photos-list d-flex justify-content-between">
                                <img style="width: 49%;" class="zoomable-image"
                                    src="assets/img/uitrapworld/1920x1080-1.webp" alt="screen1-fhd">

                                <img style="width: 49%;" class="zoomable-image"
                                    src="assets/img/uitrapworld/1920x1080-2.webp" alt="screen2-fhd">
                            </div>
                            <div class="photos-list d-flex justify-content-center mt-3">
                                <img style="width: 60%;" class="zoomable-image"
                                    src="assets/img/uitrapworld/1920x1080-3.webp" alt="screen3-fhd">
                            </div>
                            </p>
                            <p>Ci-dessous le rendu pour les autres résolutions supportées :</p>
                            <div class="accordion" id="faqAccordionUi1">

                                <!-- 1680x1050 -->
                                <div class="accordion-item mt-2">
                                    <h4 class="accordion-header" id="faqHeadingUi2">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapseUi2"
                                            aria-expanded="false" aria-controls="faqCollapseUi2">
                                            1680 x 1050 (WSXGA+)
                                        </button>
                                    </h4>
                                    <div id="faqCollapseUi2" class="accordion-collapse collapse"
                                        aria-labelledby="faqHeadingUi2" data-bs-parent="#faqAccordionUi1">
                                        <div class="accordion-body">
                                            <div class="photos-list d-flex justify-content-between">
                                                <img style="width: 49%;" class="zoomable-image"
                                                    src="assets/img/uitrapworld/1680x1050-1.webp"
                                                    alt="screen1-1680x1050">

                                                <img style="width: 49%;" class="zoomable-image"
                                                    src="assets/img/uitrapworld/1680x1050-2.webp"
                                                    alt="screen2-1680x1050">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 1600x900 -->
                                <div class="accordion-item mt-2">
                                    <h4 class="accordion-header" id="faqHeadingUi3">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapseUi3"
                                            aria-expanded="false" aria-controls="faqCollapseUi3">
                                            1600 x 900 (HD+)
                                        </button>
                                    </h4>
                                    <div id="faqCollapseUi3" class="accordion-collapse collapse"
                                        aria-labelledby="faqHeadingUi3" data-bs-parent="#faqAccordionUi1">
                                        <div class="accordion-body">
                                            <div class="photos-list d-flex justify-content-between">
                                                <img style="width: 60%;" class="zoomable-image mx-auto"
                                                    src="assets/img/uitrapworld/1600x900.webp" alt="screen-1600x900">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 1366x768 -->
                                <div class="accordion-item mt-2">
                                    <h4 class="accordion-header" id="faqHeadingUi4">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapseUi4"
                                            aria-expanded="false" aria-controls="faqCollapseUi4">
                                            1366 x 768 (WXGA)
                                        </button>
                                    </h4>
                                    <div id="faqCollapseUi4" class="accordion-collapse collapse"
                                        aria-labelledby="faqHeadingUi4" data-bs-parent="#faqAccordionUi1">
                                        <div class="accordion-body">
                                            <div class="photos-list d-flex justify-content-between">
                                                <img style="width: 60%;" class="zoomable-image mx-auto"
                                                    src="assets/img/uitrapworld/1366x768.webp" alt="screen-1366x768">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 1280x720 -->
                                <div class="accordion-item mt-2">
                                    <h4 class="accordion-header" id="faqHeading5">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapse5"
                                            aria-expanded="false" aria-controls="faqCollapse5">
                                            1280 x 720 (HD)
                                        </button>
                                    </h4>
                                    <div id="faqCollapse5" class="accordion-collapse collapse"
                                        aria-labelledby="faqHeading5" data-bs-parent="#faqAccordionUi1">
                                        <div class="accordion-body">
                                            <div class="photos-list d-flex justify-content-between">
                                                <img style="width: 60%;" class="zoomable-image mx-auto"
                                                    src="assets/img/uitrapworld/1280x720.webp" alt="screen-1280x720">
                                            </div>
                                        </div>
                                    </div>
                                </div>



                            </div>
                            <!-- <p class="text-muted">
                                <span>Vous ne souhaitez pas passer par notre application ? Téléchargement direct <a
                                        href="#">ici</a></span>
                            </p> -->
                        </div>
                    </div>

                    <!-- ui numéro 2 -->
                    <div>
                        <h3>UI VLIK (black-theme)</h3>
                        <p class="lead">Un ui à l'ancienne mais toujours au rendez-vous !</p>
                        <div class="mx-3 mb-5">
                            <p class="text-justify">
                                Qui mieux que le créateur lui-même pour présenter son UI ? Voici le lien de la vidéo
                                explicative :<br>
                                <a target="_blank" rel="noopener noreferrer" href="https://www.youtube.com/watch?v=0zalNbErAKg
                                    ">https://www.youtube.com/watch?v=0zalNbErAKg</a>
                            </p>

                            <div class="accordion" id="faqAccordionUi2">
                                Il existe également un template payant pour avoir des barres HP/PM/debuff plus grandes
                                :<br>
                                <em>plus de détails dans la vidéo ou sur son serveur Discord accessible <a
                                        target="_blank" rel="noopener noreferrer" href="https://discord.gg/CY8sC5d
                                    ">ici</a></em>

                                <!-- 1920x1080 -->
                                <div class="accordion-item mt-2">
                                    <h4 class="accordion-header" id="faqHeading6">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapse6"
                                            aria-expanded="false" aria-controls="faqCollapse6">
                                            1920 x 1080 (Full HD - 1080p)
                                        </button>
                                    </h4>
                                    <div id="faqCollapse6" class="accordion-collapse collapse"
                                        aria-labelledby="faqHeading6" data-bs-parent="#faqAccordionUi2">
                                        <div class="accordion-body">
                                            <div class="photos-list d-flex justify-content-between">
                                                <img style="width: 60%;" class="zoomable-image mx-auto"
                                                    src="assets/img/uivlik/1920x1080.webp" alt="screen-fhd">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 1366x768 -->
                                <div class="accordion-item mt-2">
                                    <h4 class="accordion-header" id="faqHeading7">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#faqCollapse7"
                                            aria-expanded="false" aria-controls="faqCollapse7">
                                            1366 x 768 (WXGA)
                                        </button>
                                    </h4>
                                    <div id="faqCollapse7" class="accordion-collapse collapse"
                                        aria-labelledby="faqHeading7" data-bs-parent="#faqAccordionUi2">
                                        <div class="accordion-body">
                                            <div class="photos-list d-flex justify-content-between">
                                                <img style="width: 60%;" class="zoomable-image mx-auto"
                                                    src="assets/img/uivlik/1366x768.webp" alt="screen-1366x768">
                                            </div>
                                        </div>
                                    </div>
                                </div>



                            </div>
                            <p class="text-muted">
                                <span>Vous ne souhaitez pas passer par notre application ? Téléchargement direct <a
                                        target="_blank" rel="noopener noreferrer"
                                        href="https://www.mediafire.com/file/oo7asxv48oc8l6o/UI-VLIK-2024-11-17.zip">ici</a></span>
                            </p>
                        </div>
                    </div>

                    <!-- ui numéro 3 -->
                    <div>
                        <h3>UI Vanilla (celui par défaut)</h3>
                        <p class="lead">Fichiers officiels X-Legend</p>
                        <div class="mx-3">
                            <p class="text-justify">
                                L'application OptiGF.exe vous permet en un clin d'oeil de revenir aux sources en
                                rétablissant les fichiers par défaut de l'ui X-Legend. Pratique si vous souhaitez
                                vous-même créer votre propre UI de zéro.<br>

                            </p>
                            <!-- <p class="text-muted mt-3">
                                <span>Vous ne souhaitez pas passer par notre application ? Téléchargement direct <a
                                        href="#">ici</a></span>
                            </p> -->
                        </div>
                    </div>

                </div>

                <!-- Téléchargement -->
                <div id="download-section" class="content-section" style="display: none;">
                    <?php if ($isUserLoggedIn): ?>
                        <h2>Télécharger notre application OptiGF</h2>
                        <p class="lead">Elle vous permet d'accéder à la dernière version de notre injection et au switcher
                            d'ui</p>

                        <div class="alert alert-info">
                            <h5><i class="bi bi-info-circle"></i> Instructions</h5>
                            <p class="mb-0">0. Faites la mise à jour officielle de Grand Fantasia</p>
                            <p class="mb-0">1. Téléchargez le launcher  OptiGF.exe</p>
                            <p class="mb-0">2. Exécutez le fichier et connectez-vous à votre compte OptiGF</p>
                            <p class="mb-0">3. Choisissez le chemin de votre jeu (optionnel : vérifier votre installation)</p>
                            <p class="mb-0">4. Réalisez l'injection + bouton "icons" afin d'éviter les carrés roses</p>
                            <p class="mb-0">5. Attendez la fin du téléchargement + extraction des ressources</p>
                            <p class="mb-0">6. Lancez GrandFantasia.exe directement dans le dossier du jeu ou via le bouton Play de l'application OptiGF</p>
                        </div>
                        <div class="w-100 text-center">
                            <img style="width: 40%;" class="zoomable-image"
                                src="assets/img/injection/previews/apercu-optigf-exe-3.webp" alt="Apperçu de l'application OptiGF.exe">
                        </div>
                        <form method="POST" action="download.exe.php" class="text-center mt-4">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <button type="submit" class="btn btn-custom-success btn-lg">
                                Télécharger
                            </button>
                            <p class="text-muted mt-3">
                                <span><?= CURRENT_APP_VERSION ?> | Taille : ~1,5 Mo</span><br>
                            </p>

                        <p class="text-muted mt-3">
                            <span>Fichiers de l'injection: v<?= CURRENT_INJECTION_COMPILED ?> | Taille : ~250 Mo</span>
                        </p>
                        </form>

                    <?php else: ?>
                        <div class="download-section-locked">
                            <h2>Télécharger notre application OptiGF</h2>
                            <p class="lead">Section réservée aux membres connectés...</p>

                            <div class="alert alert-warning">
                                <h5>⚠️ Accès restreint ⚠️</h5>
                                <p class="mb-0">Vous devez être connecté pour accéder aux téléchargements.</p>
                            </div>

                            <div class="text-center mt-4">
                                <a href="#account" class="btn btn-custom-success btn-lg">
                                    Se connecter
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>






            </div>

            <div class="text-end">
                <a href="https://liberapay.com/OptiGF/donate"
                    title="Le projet est 100% gratuit ! Toute aide est la bienvenue... " target="_blank"
                    rel="noopener noreferrer" rel="noopener">
                    <img src="https://liberapay.com/assets/widgets/donate.svg">
                </a>
            </div>
        </div>

        <!-- Links droite fixe -->
        <div class="sidebar-right col-md-2">
            <?php include '_links.php'; ?>
        </div>
    </div>

    <?php include '_footer.php'; ?>

    <script>
        function closeModal() {
            const modal = document.querySelector('.modal-overlay');
            if (modal) {
                modal.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => modal.remove(), 300);
            }
        }
    </script>


    <!-- Modal pour afficher l'image en grand -->
    <div id="imageModal" class="image-modal">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="modalImage">
        <div id="caption"></div>
    </div>
</body>

</html>