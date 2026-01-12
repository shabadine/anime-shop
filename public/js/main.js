/**
 * ANIME SHOP - Main JavaScript
 */

// Confirmation de suppression
document.addEventListener('DOMContentLoaded', function() {
    
    // Confirmation pour les suppressions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Êtes-vous sûr ?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-dismiss des alerts après 5 secondes
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Gestion des quantités (+ / -)
    const quantityInputs = document.querySelectorAll('input[type="number"][name="quantity"]');
    quantityInputs.forEach(input => {
        const min = parseInt(input.min) || 1;
        const max = parseInt(input.max) || 999;
        
        // Bouton -
        const decreaseBtn = input.previousElementSibling;
        if (decreaseBtn && decreaseBtn.tagName === 'BUTTON') {
            decreaseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const currentValue = parseInt(input.value) || min;
                if (currentValue > min) {
                    input.value = currentValue - 1;
                }
            });
        }
        
        // Bouton +
        const increaseBtn = input.nextElementSibling;
        if (increaseBtn && increaseBtn.tagName === 'BUTTON') {
            increaseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const currentValue = parseInt(input.value) || min;
                if (currentValue < max) {
                    input.value = currentValue + 1;
                }
            });
        }
    });
    
    // Validation des formulaires en temps réel
    const forms = document.querySelectorAll('form[novalidate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Loading state pour les boutons submit
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            if (form && form.checkValidity()) {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement...';
            }
        });
    });
});

// Fonction pour mettre à jour le compteur du panier
function updateCartCount() {
    fetch('/panier/count')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('.cart-count');
            if (cartBadge) {
                cartBadge.textContent = data.count;
            }
        })
        .catch(error => console.error('Erreur:', error));
}

// Accessibilité : Gestion du clavier
document.addEventListener('keydown', function(e) {
    // Échap pour fermer les modals
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) bsModal.hide();
        });
    }
});

// Smooth scroll pour les ancres
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            target.focus();
        }
    });
});