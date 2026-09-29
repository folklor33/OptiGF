document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const captionText = document.getElementById('caption');
    const closeBtn = document.getElementsByClassName('close-modal')[0];
    
    // Vérifier que tous les éléments de la modale existent
    if (!modal || !modalImg || !captionText || !closeBtn) {
        console.error('Éléments de la modale manquants dans le DOM');
        return;
    }
    
    // Fonction pour ouvrir la modale
    function openModal(imageSrc, imageAlt) {
        modal.style.display = 'block';
        modalImg.src = imageSrc;
        captionText.innerHTML = imageAlt;
        document.body.classList.add('modal-open');
    }
    
    // Fonction pour fermer la modale
    function closeModal() {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }
    
    // Ajouter l'événement click à toutes les images zoomables
    document.querySelectorAll('.zoomable-image').forEach(img => {
        img.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openModal(this.src, this.alt);
        });
    });
    
    // Ajouter l'événement click aux liens avec image trigger
    document.querySelectorAll('.image-trigger').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const imageSrc = this.getAttribute('data-image');
            const imageAlt = this.getAttribute('data-alt') || '';
            openModal(imageSrc, imageAlt);
        });
    });
    
    // Fermer le modal en cliquant sur le X
    closeBtn.addEventListener('click', closeModal);
    
    // Fermer le modal en cliquant en dehors de l'image
    modal.addEventListener('click', function(event) {
        if (event.target === modal || event.target === closeBtn) {
            closeModal();
        }
    });
    
    // Fermer le modal avec la touche Échap
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
});