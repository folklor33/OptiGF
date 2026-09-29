<?php
require_once 'managers/config.php';
require_once 'managers/SecureDownloadManager.php';
require_once 'managers/SecurityManager.php';
require_once 'managers/SystemManagers.php';
require_once 'managers/UserManager.php';

SessionManager::start();

// Headers de sécurité et cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

HeadersManager::setHeadersManager();

$current_page = 'accueil';
$current_section = isset($_GET['section']) ? $_GET['section'] : 'home';

// Variables pour les messages
$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

$warningMessage = '';

// Vérifier si l'utilisateur est connecté
$isUserLoggedIn = SessionManager::get('user_id') !== null;

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_form'])) {
    try {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            throw new Exception('La session a expiré. Veuillez actualiser la page.');
        }

        $userManager = new UserManager();
        $result = $userManager->register(
            $_POST['account_key'] ?? '',
            $_POST['username_reg'] ?? '',
            $_POST['password_reg'] ?? ''
        );

        if (isset($result['warning'])) {
            $warningMessage = $result['warning'];
        } else {
            $successMessage = "Compte mis à jour avec succès !";
            if ($result['password_changed']) {
                $successMessage .= " Le mot de passe a bien été changé.";
            }
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_form'])) {
    try {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            throw new Exception('La session a expiré. Veuillez actualiser la page.');
        }

        $userManager = new UserManager();
        $result = $userManager->login(
            $_POST['username'] ?? '',
            $_POST['password'] ?? '',
            $_POST['key'] ?? ''
        );

        if ($result) {
            SessionManager::regenerate();
            $_SESSION['success_message'] = "Connexion réussie !";
            header("Location: /index.php#account");
            exit;
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Traitement de la déconnexion
if (isset($_GET['logout']) && $isUserLoggedIn) {
    SessionManager::destroy();
    header("Location: index.php#account");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= BASE_PATH ?>">
    <title>OptiGF.eu</title>
    <script src="assets/sections.js"></script>
    <script src="assets/zoomable-images.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="assets/zoomable-images.css" rel="stylesheet">
    <link href="assets/styles.css" rel="stylesheet">
    <link href="assets/mobile.css" rel="stylesheet">
    <link href="favicon.ico" rel="favicon">
    <!-- Styles spécifiques aux formulaires d'authentification -->
    <style>

    </style>
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

                <!-- Section d'accueil -->
                <div id="home-section" class="content-section">
                    <h2>Bienvenue sur OptiGF !</h2>
                    <p class="lead mt-4">Améliorer son expérience sur Grand Fantasia (version globale uniquement).</p>

                    <p class="mt-4 text-justify">Vous êtes sur la page d'accueil. <br>Utilisez les onglets en haut pour
                        naviguer entre les différentes pages du site.</p>

                    <ul class="mt-3">
                        <li><a href="/">Accueil</a> - Bienvenue !</li>
                        <li><a href="injection.php">Injection</a> - Tout savoir sur notre injection...</li>
                        <li><a href="settings.php">Paramètres</a> - Affinage des réglages du PC</li>
                        <li class="opacity-25"><a href="#">Créateurs d'UI</a> - Astuces et outils pour les développeurs
                            d'UI</li>
                    </ul>

                    <p>Pour chacun d'entre eux, explorez les sections accessibles sur la gauche.</p>

                    <p class="mt-4">Vous retrouverez également quelques liens utiles sur la droite.</p>
                </div>
                <!-- Remplacer les 3 blocs auth-message par : -->
                <?php if ($errorMessage || $successMessage || $warningMessage): ?>
                    <div class="modal-overlay" onclick="closeModal()">
                        <div class="modal-content <?= $errorMessage ? 'error' : ($successMessage ? 'success' : 'warning') ?>"
                            onclick="event.stopPropagation()">
                            <h4><?= $errorMessage ? '⚠️ Erreur' : ($successMessage ? '✅ Succès' : '⚠️ Attention') ?></h4>
                            <p><?= htmlspecialchars($errorMessage ?: $successMessage ?: $warningMessage) ?></p>
                            <button class="modal-close" onclick="closeModal()">Fermer</button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Section Compte -->
                <div id="account-section" class="content-section d-flex justify-content-between px-3">

                    <?php if (!$isUserLoggedIn): ?>


                        <!-- INSCRIPTION -->
                        <div class="form-container">
                            <h2 class="form-title mb-2 text-center">Inscription</h2>
                            <p class="form-subtitle mb-4">La Clé de Carso est strictement personnelle et est obtenable via
                                notre serveur <a target="_blank" rel="noopener noreferrer"
                                    href="https://discord.gg/58j4JnYFdz">Discord</a>.
                            </p>

                            <form method="POST">
                                <input type="hidden" name="register_form" value="1">
                                <input type="hidden" name="csrf_token"
                                    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" id="account_key" name="account_key"
                                        placeholder="xxxxxx-xxxxxx-xxxxxx-xxxxxx" required
                                        value="<?= !$successMessage ? htmlspecialchars($_POST['account_key'] ?? '') : '' ?>">
                                    <label for="account_key">Clé de Carso</label>
                                </div>

                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" id="username_reg" name="username_reg"
                                        placeholder="Nom d'utilisateur Discord" required
                                        value="<?= !$successMessage ? htmlspecialchars($_POST['username_reg'] ?? '') : '' ?>">
                                    <label for="username_reg">Nom d'utilisateur Discord</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="password_reg" name="password_reg"
                                        placeholder="Mot de passe" required oninput="checkPasswordStrength(this.value)">
                                    <label for="password_reg">Mot de passe</label>
                                </div>
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="strengthBar" style="width: 0%;"></div>
                                </div>
                                <small class="text-muted d-block mb-4" id="strengthText">Entrez un mot de passe</small>

                                <div class="form-floating mb-4">
                                    <input type="password" class="form-control" id="password_confirm"
                                        placeholder="Confirmer le mot de passe" required>
                                    <label for="password_confirm">Confirmer le mot de passe</label>
                                </div>

                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="accept_terms" required>
                                    <label class=" ms-2" for="accept_terms">
                                        J'accepte les <a href="legal.php#cgu">Conditions Générales d'Utilisation</a>
                                        et la <a href="legal.php#privacy">Politique de Confidentialité</a>
                                    </label>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-custom-success btn-lg"
                                        id="register-btn">S'inscrire</button>
                                </div>
                            </form><br>
                            <p> Vous avez oublié votre mot de passe ? Faites simplement une nouvelle
                                inscription (même username Discord), cela modifiera le mot de passe existant (clé
                                nécessaire).</p>
                        </div>

                        <!-- CONNEXION -->
                        <div class="form-container">
                            <h2 class="form-title mb-2 text-center">Connexion</h2>
                            <p class="form-subtitle mb-4">Connectez-vous à votre compte OptiGF pour profiter de toutes les
                                fonctionnalités.</p>

                            <form method="POST">
                                <input type="hidden" name="login_form" value="1">
                                <input type="hidden" name="csrf_token"
                                    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" id="key" name="key"
                                        placeholder="xxxxxx-xxxxxx-xxxxxx-xxxxxx" required>
                                    <label for="key">Clé de Carso</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" id="username" name="username"
                                        placeholder="Nom d'utilisateur Discord" required>
                                    <label for="username">Nom d'utilisateur Discord</label>
                                </div>

                                <div class="form-floating mb-4">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Mot de passe" required>
                                    <label for="password">Mot de passe</label>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-custom-success btn-lg">Se connecter</button>
                                </div>
                            </form>
                        </div>

                    <?php else: ?>
                        <!-- Utilisateur connecté -->
                        <div class="col-12 text-center">
                            <h2 class="form-title mb-4">Connexion réussie</h2>
                            <p class="form-subtitle">Vous êtes connecté !</p>
                            <div class="mt-4">
                                <a href="injection.php#download" class="btn btn-custom-success btn-lg me-3">
                                    Télécharger l'injection OptiGF !
                                </a>
                                <a href="?logout=1" class="btn btn-outline-light btn-lg">Se déconnecter</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- FAQ -->
                <div id="faq-section" class="content-section" style="display: none;">
                    <h2>Foire aux Questions</h2>
                    <p class="lead">Retrouvez une FAQ par catégories inspirée des questions les + fréquemment posées :
                    </p>


                    <div class="accordion mb-5" id="faqAccordionGlobal">

                        <!-- Question globale numéro 1 -->
                        <div class="accordion-item">
                            <h4 class="accordion-header" id="faqHeadingGlobal1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseGlobal1" aria-expanded="false"
                                    aria-controls="faqCollapseGlobal1">
                                    Que faire si j'ai oublié mon mot de passe ?
                                </button>
                            </h4>
                            <div id="faqCollapseGlobal1" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingGlobal1" data-bs-parent="#faqAccordionGlobal">
                                <div class="accordion-body">
                                    Il suffit de refaire une inscription avec la même clé de Carso et le même Discord,
                                    le mot de passe sera mis à jour.
                                </div>
                            </div>
                        </div>

                        <!-- Question globale numéro 2 -->
                        <div class="accordion-item">
                            <h4 class="accordion-header" id="faqHeadingGlobal2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseGlobal2" aria-expanded="false"
                                    aria-controls="faqCollapseGlobal2">
                                    Je dois créer un compte : comment sont utilisées mes données personnelles ?
                                </button>
                            </h4>
                            <div id="faqCollapseGlobal2" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingGlobal2" data-bs-parent="#faqAccordionGlobal">
                                <div class="accordion-body">
                                    Veuillez vous référer à la page des <a href="legal.php">Mentions légales </a>.
                                </div>
                            </div>
                        </div>

                    </div>


                    <h4><a href="injection.php">Injection</a></h4>
                    <div class="accordion mb-5" id="faqAccordionInjection">

                        <!-- Question injection numéro 1 -->
                        <div class="accordion-item">
                            <h4 class="accordion-header" id="faqHeadingInjection1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjection1" aria-expanded="false"
                                    aria-controls="faqCollapseInjection1">
                                    Pourquoi j'ai besoin de créer un compte pour accéder au contenu ?
                                </button>
                            </h4>
                            <div id="faqCollapseInjection1" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjection1" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">
                                    Pour des raisons de sécurité et de gestion des utilisateurs. La Clé de Carso assure
                                    une authentification par intermédiaire (compte Discord) vous évitant ainsi d'avoir à
                                    renseigner une adresse e-mail.
                                </div>
                            </div>
                        </div>

                        <!-- Question injection numéro 2 -->
                        <div class="accordion-item">
                            <h4 class="accordion-header" id="faqHeadingInjection2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjection2" aria-expanded="false"
                                    aria-controls="faqCollapseInjection2">
                                    Je suis un joueur non francophone, mon jeu est-il compatible ?
                                </button>
                            </h4>
                            <div id="faqCollapseInjection2" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjection2" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">
                                    Oui, mais vous ne pourrez pas bénéficier des traductions améliorées qui sont pour
                                    l'instant uniquement présentes dans la version FR.
                                    <br>Les autres modifications (icônes, monstres, modèles de coffre, ...) seront quant
                                    à elles bien fonctionnelles !
                                </div>
                            </div>
                        </div>

                        <!-- Question injection numéro 3 -->
                        <div class="accordion-item">
                            <h4 class="accordion-header" id="faqHeadingInjection3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjection3" aria-expanded="false"
                                    aria-controls="faqCollapseInjection3">
                                    Je ne comprends pas le fonctionnement de l'injection : code couleurs et autres
                                    modifications diverses.
                                </button>
                            </h4>
                            <div id="faqCollapseInjection3" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjection3" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">
                                    Tout est expliqué en détail sur la page "<a href="injection.php#tutos">Injection /
                                        Liste des
                                        fonctionnalités</a>".
                                </div>
                            </div>
                        </div>

                        <!-- Question injection numéro 4 -->
                        <div class="accordion-item">
                            <h4 class="accordion-header" id="faqHeadingInjection4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjection4" aria-expanded="false"
                                    aria-controls="faqCollapseInjection4">
                                    Launcher officiel : Puis-je le lancer ? À quel moment ?
                                </button>
                            </h4>
                            <div id="faqCollapseInjection4" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjection4" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">
                                    <p class="text-justify">Le launcher officiel de X-Legend sert à télécharger le
                                        contenu fraichement
                                        sorti.<br>
                                        Notre injection n'inclus pas ces modifications (nouveaux items, pnj etc,...).
                                        Donc chaque semaine il faut relancer le launcher officiel et refaire l'injection
                                        dès que nous publions le nouveau contenu.</p>

                                    <p class="text-justify">
                                        Cependant, si la mise à jour de l'injection n'a pas encore été réalisée,
                                        vous pourrez constater quelques anomalies.<br>
                                        Ainsi deux possibilités s'offrent à vous :
                                    </p>

                                    <p class="text-justify">
                                        (1) Vous êtes patient et attendez que nous vous informions d'une mise à jour
                                        OptiGF pour lancer le launcher X-Legend.
                                        Vous prenez alors le risque de ne pas profiter du nouveau contenu. Une fois que
                                        nous aurons publié la nouvelle version de l'injection, il faudra retourner dans
                                        l'application et télécharger de nouveaux nos modifs.<br>
                                        <em>En cas de blocage car grosse MAJ X-Legend : optez pour la solution (2)</em>
                                    </p>

                                    <p class="text-justify">
                                        (2) Vous préférez bénéficier des ajouts X-Legend et lancez
                                        le launcher officiel dès la MAJ. La
                                        quasi-totalité des changements que nous proposons seront alors annulés en
                                        attendant que nous publions la nouvelle version de l'injection. Alors à
                                        vous de choisir !
                                    </p>
                                    <br><br>
                                    <p class="text-justify"><span class="text-decoration-underline">
                                            Conseil du staff</span> : installer GF dans deux dossiers différents.
                                        Cela vous permettra d'utiliser la version officielle pendant la période ouleuse.
                                    </p>
                                    <p class="text-justify">Nous faisons le maximum pour publier les mises à jour le
                                        plus rapidement
                                        possible.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Question injection numéro 5 -->
                        <div class="accordion-item">
                            <h4 class="accordion-header" id="faqHeadingInjection5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjection5" aria-expanded="false"
                                    aria-controls="faqCollapseInjection5">
                                    Je viens de faire l'injection et j'obtiens des carrés roses sur les procs ou les
                                    items dans mon sac...
                                </button>
                            </h4>
                            <div id="faqCollapseInjection5" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjection5" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">
                                    Pas de panique ! Dans ce cas vous pouvez utilisez le bouton "Icons" dans notre
                                    application. Cette opération est indépendante de l'Injection et n'est à effectuer
                                    qu'une seule fois.
                                </div>
                            </div>
                        </div>

                        <!-- Question injection numéro 6 -->
                        <div class="accordion-item">
                            <h4 class="accordion-header" id="faqHeadingInjection6">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqCollapseInjection6" aria-expanded="false"
                                    aria-controls="faqCollapseInjection6">
                                    Je ne sais pas comment obtenir l'injection OptiGF
                                </button>
                            </h4>
                            <div id="faqCollapseInjection6" class="accordion-collapse collapse"
                                aria-labelledby="faqHeadingInjection6" data-bs-parent="#faqAccordionInjection">
                                <div class="accordion-body">
                                    Tout le processus est expliqué en détail dans la section
                                    <a href="injection.php">Injection</a> du site.


                                </div>
                            </div>
                        </div>

                        <!-- Ajoutez d'autres questions selon le même modèle -->
                    </div>

                    <p class="mt-5">Votre question reste sans réponse ? Contactez-nous par <a target="_blank"
                            rel="noopener noreferrer" href="https://discord.gg/58j4JnYFdz">Discord</a></p>

                </div>

                <!-- Section À propos -->
                <div id="about-section" class="content-section" style="display: none;">
                    <h2>À propos de nous</h2>

                    <p class="lead">Voici notre équipe ! Pour un contact plus rapproché, venez discuter sur le <a
                            target="_blank" rel="noopener noreferrer" href="https://discord.gg/58j4JnYFdz">Discord</a>
                    </p>

                    <p>
                    <h5>Staff OptiGF:</h5>
                    <ul class="mb-0">
                        <li><strong>Bulle</strong> <span>@folklor</span> <em> > Conception - Icons CO Design
                                - Hébergement - Développement: website/db/c#app</em></li>
                        <li><strong>Kirito</strong> <span>@artzz_gi</span> <em> > Beta tester - Icons Creations</em>
                        </li>
                    </ul>
                    </p>

                    <p>
                    <h5>Staff partenaire (4 membres anonymes) :</h5>
                    <ul class="mb-0">
                        <li>
                            <em>CO Conception - Beta tester - Icons Creations - UI Design - scripting - reverse
                                engineering</em>
                        </li>
                        <!-- <li><strong>Ori</strong> <span>@ori_deck</span> <em> > icons co designer - UI designer</em></li> -->
                        <!-- <li><strong>Askim</strong> <span>@incouchable</span> <em> > UI co designer</em></li>
                                        <li><strong>Tzarode</strong> <span>@tzarode</span> <em> > UI co designer - scripting -
                                            reverse engineering</em></li> -->
                    </ul>
                    </p>

                    <br>
                    <p class="lead">Le projet comporte :
                    <ul>
                        <li>Ce site web fait en PHP (styles avec bootstrap notamment)</li>
                        <li>Un serveur de ressources incluant les fichiers de l'injection, les UI, ...</li>
                        <li>Une application CSharp développée pour relier ces ressources avec votre dossier GF (authentification requise via API)</li>
                        <li>Un serveur Discord incluant un bot fait maison permettant de générer des clés uniques
                            (solution de substitution à une messagerie électronique)</li>
                        <li>Une équipe prête à prendre vos suggestions et/ou signalements de bugs :)</li>
                    </ul>
                    </p>
                    <p class="lead">Vous souhaitez nous faire un don ?</p>
                    <p>
                        Tout le projet est maintenu par le staff OptiGF et est auto-hébergé. Cela nécessite beaucoup de
                        temps et quelques euros par mois d'électricité... N'hésitez pas à nous aider financièrement <3
                            </p>
                            <script src="https://liberapay.com/OptiGF/widgets/receiving.js"></script><br><br>
                            <p>
                                <em class="lead">Merci</em> 😍
                            </p>

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

        <!-- Links droite fixe -->
        <div class="sidebar-right col-md-2">
            <?php include '_links.php'; ?>
        </div>

        <br>
    </div>

    <?php include '_footer.php'; ?>

    <script>
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById("strengthBar");
            const strengthText = document.getElementById("strengthText");

            if (!strengthBar || !strengthText) return;

            let strength = 0;
            let text = "";
            let className = "";

            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[^a-zA-Z0-9]+/)) strength += 1;

            switch (strength) {
                case 0:
                case 1:
                case 2:
                    text = "Mot de passe faible";
                    className = "strength-weak";
                    strengthBar.style.width = "33%";
                    break;
                case 3:
                case 4:
                    text = "Mot de passe moyen";
                    className = "strength-medium";
                    strengthBar.style.width = "66%";
                    break;
                case 5:
                    text = "Mot de passe fort";
                    className = "strength-strong";
                    strengthBar.style.width = "100%";
                    break;
            }

            strengthBar.className = "password-strength-bar " + className;
            strengthText.textContent = text;

            if (password.length === 0) {
                strengthBar.style.width = "0%";
                strengthText.textContent = "Entrez un mot de passe";
                strengthBar.className = "password-strength-bar";
            }

            checkFormValidation();
        }

        function checkFormValidation() {
            const password = document.getElementById("password_reg");
            const confirmPassword = document.getElementById("password_confirm");
            const acceptTerms = document.getElementById("accept_terms");
            const registerBtn = document.getElementById("register-btn");

            // Vérifier que les éléments existent avant de les utiliser
            if (!password || !confirmPassword || !acceptTerms || !registerBtn) return;

            const passwordMatch = password.value === confirmPassword.value && password.value.length > 0;
            const formValid = passwordMatch && acceptTerms.checked;

            registerBtn.disabled = !formValid;

            if (confirmPassword.value.length > 0) {
                confirmPassword.style.borderColor = passwordMatch ? '#198754' : '#dc3545';
            }
        }

        // Ajouter les event listeners seulement si les éléments existent
        document.addEventListener('DOMContentLoaded', function () {
            const passwordConfirm = document.getElementById("password_confirm");
            const acceptTerms = document.getElementById("accept_terms");
            const regForm = document.querySelector('form[method="POST"] input[name="register_form"]');

            if (passwordConfirm) {
                passwordConfirm.addEventListener("input", checkFormValidation);
            }

            if (acceptTerms) {
                acceptTerms.addEventListener("change", checkFormValidation);
            }

            if (regForm) {
                regForm.closest('form').addEventListener('submit', function (e) {
                    const password = document.getElementById("password_reg").value;
                    const confirmPassword = document.getElementById("password_confirm").value;

                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Les mots de passe ne correspondent pas.');
                        return false;
                    }
                });
            }
        });

        function closeModal() {
            const modal = document.querySelector('.modal-overlay');
            if (modal) {
                modal.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => modal.remove(), 300);
            }
        }
    </script>
    <!-- Modal pour afficher l'image en grand - À placer juste avant </body> -->
    <div id="imageModal" class="image-modal">
        <span class="close-modal">&times;</span>
        <img style="max-width: 900px;" class="modal-content" id="modalImage" alt="">
        <div id="caption"></div>
    </div>
</body>

</html>