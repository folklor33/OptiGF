function showSection(sectionId) {
	// Masquer toutes les sections
	document.querySelectorAll(".content-section").forEach(function (section) {
		section.style.display = "none";
	});

	// Afficher la section sélectionnée
	const targetSection = document.getElementById(sectionId + "-section");
	if (targetSection) {
		targetSection.style.display = "block";
		targetSection.style.animation = "fadeInUp 0.5s ease-out forwards";
	}

	// Mettre à jour les liens actifs
	document.querySelectorAll(".section-link").forEach(function (link) {
		link.classList.remove("active");
	});

	// MODIFIÉ : Trouver le lien actif par l'attribut data-section ou href
	const activeLink =
		document.querySelector(`[data-section="${sectionId}"]`) ||
		document.querySelector(`[href="#${sectionId}"]`) ||
		document.querySelector(`[onclick="showSection('${sectionId}')"]`);
	if (activeLink) {
		activeLink.classList.add("active");
	}

	// Mettre à jour l'URL avec le hash en conservant la page courante
	const currentPath = window.location.pathname;
	const currentSearch = window.location.search;

	if (sectionId !== "home") {
		window.history.replaceState(null, null, currentPath + currentSearch + "#" + sectionId);
	} else {
		// Pour la section home, on enlève le hash
		window.history.replaceState(null, null, currentPath + currentSearch);
	}
}

// Fonction pour détecter la section depuis l'URL
function getSectionFromUrl() {
	const hash = window.location.hash.substring(1);
	return hash || "home";
}

// Fonction pour charger la section selon l'URL
function loadSectionFromUrl() {
	const sectionId = getSectionFromUrl();
	showSection(sectionId);
}

// Écouter les changements d'URL (boutons navigateur)
window.addEventListener("popstate", function (event) {
	loadSectionFromUrl();
});

// Écouter les clics sur les liens de navigation
document.addEventListener("click", function (e) {
	// Gérer les liens avec href="#section" ou href="page.php#section"
	const link = e.target.closest('a[href*="#"]');
	if (link && link.classList.contains("section-link")) {
		e.preventDefault();
		const href = link.getAttribute("href");
		// Extraire la partie après le # (hash)
		const hashIndex = href.indexOf("#");
		if (hashIndex !== -1) {
			const hash = href.substring(hashIndex + 1);
			if (hash && document.getElementById(hash + "-section")) {
				showSection(hash);
			}
		}
	}
});

// Charger la section depuis l'URL au chargement de la page
document.addEventListener("DOMContentLoaded", function () {
	loadSectionFromUrl();

	// NOUVEAU : Gérer les liens hash existants avec onclick (rétrocompatibilité)
	document.querySelectorAll('[onclick*="showSection"]').forEach(function (link) {
		link.addEventListener("click", function (e) {
			e.preventDefault();
			// Extraire le sectionId de l'onclick
			const onclickValue = this.getAttribute("onclick");
			const match = onclickValue.match(/showSection\(['"]([^'"]+)['"]\)/);
			if (match) {
				showSection(match[1]);
			}
		});
	});
});
