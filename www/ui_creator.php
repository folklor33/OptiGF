<?php
require_once 'managers/config.php';

$current_page = 'ui';
$current_section = isset($_GET['section']) ? $_GET['section'] : 'home';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= BASE_PATH ?>">
    <title>OptiGF.eu - UI</title>
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
                <!-- Section Home UI -->
                <div id="home-section" class="content-section">
                    <h2>Interface Utilisateur</h2>
                    <p class="lead">Nous regroupons ici des ressources utiles pour les créateurs d'UI Grand Fantasia</p>
                    <div class="alert alert-info">
                        <h5>Contenu de cette section :</h5>
                        <ul class="mb-0">
                            <li><strong>Structure</strong> - Organisation et architecture des interfaces</li>
                            <li><strong>Modèle</strong> - Patrons de conception et templates</li>
                        </ul>
                    </div>

                </div>

                <!-- TEMPLATES -->
                <div id="template-section" class="content-section" style="display: none;">
                    <h2>Structure de l'UI</h2>
                    <p>Cette section traite de la structuration des interfaces utilisateur.</p>

                    <div class="alert alert-info">
                        <h5>Contenu de cette section :</h5>
                        <ul class="mb-0">
                            <li><strong>Structure</strong> - Organisation et architecture des interfaces</li>
                            <li><strong>Modèle</strong> - Patrons de conception et templates</li>
                        </ul>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Design Systems</h5>
                                    <p class="card-text">Création de systèmes de design cohérents et réutilisables.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Responsive Design</h5>
                                    <p class="card-text">Techniques pour créer des interfaces adaptatives.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4>Principes de base</h4>
                    <ul>
                        <li>Hiérarchie visuelle</li>
                        <li>Espacement et grilles</li>
                        <li>Consistance des éléments</li>
                        <li>Navigation intuitive</li>
                    </ul>

                    <div class="alert alert-warning">
                        <strong>Important :</strong> Pour observer les modifications rapidement "in game", changer de
                        canal au lieu de vous deconnecter/reconnecter. Vous pouvez même utiliser l'île des sprites afin
                        de ne pas être pénalisé par le délais de changement de canal.
                    </div>

                    <h4>Outils recommandés</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6>
                                        <a href="https://code.visualstudio.com/download" target="_blank"
                                            rel="noopener noreferrer">VS Code</a> /
                                        <a href="https://www.sublimetext.com/3" target="_blank"
                                            rel="noopener noreferrer">Sublime Text</a> /
                                        <a href="https://notepad-plus-plus.org/downloads/" target="_blank"
                                            rel="noopener noreferrer">Notepad++</a>
                                    </h6>
                                    <small class="text-muted">IDE ou éditeur de texte</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6>
                                        <a href="https://www.getpaint.net/download.html" target="_blank"
                                            rel="noopener noreferrer">PaintDotNet</a>
                                    </h6>
                                    <small class="text-muted">Éditeur graphique</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6>Tailwind</h6>
                                    <small class="text-muted">Utility-first CSS</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h2>Modèles UI</h2>
                    <p>Découvrez les différents modèles et patrons de conception pour les interfaces utilisateur.</p>

                    <h4>Patrons de conception courants</h4>
                    <div class="accordion" id="accordionModeles">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne">
                                    MVC (Model-View-Controller)
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show"
                                data-bs-parent="#accordionModeles">
                                <div class="accordion-body">
                                    Pattern architectural qui sépare la logique de présentation de la logique métier.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseTwo">
                                    Component-Based Architecture
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse"
                                data-bs-parent="#accordionModeles">
                                <div class="accordion-body">
                                    Approche modulaire basée sur des composants réutilisables.
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="mt-4">Templates disponibles</h4>
                    <div class="list-group">
                        <div class="list-group-item">
                            <h6 class="mb-1">Dashboard Admin</h6>
                            <p class="mb-1">Template complet pour tableau de bord administrateur.</p>
                            <small class="text-muted">Inclut : navigation, graphiques, tableaux</small>
                        </div>
                        <div class="list-group-item">
                            <h6 class="mb-1">Landing Page</h6>
                            <p class="mb-1">Template pour page d'atterrissage commerciale.</p>
                            <small class="text-muted">Inclus : hero section, features, pricing</small>
                        </div>
                    </div>
                </div>

                <!-- CONTENT CREATOR -->
                <div id="maker-section" class="content-section">
                    <h2>Créations d'UI</h2>
                    <p class="lead">Vous souhaitez modifier / créer vous-même votre UI. Plusieurs choses à savoir...</p>

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