// Gestion des toasts Bootstrap
document.addEventListener('DOMContentLoaded', function () {
  // Initialisation des toasts
  const toastElList = [].slice.call(document.querySelectorAll('.toast'));
  toastElList.map(function (toastEl) { 
    return new bootstrap.Toast(toastEl).show(); 
  });


  (function () {
    const applyTheme = () => {
      const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
      document.documentElement.setAttribute('class', isDark ? 'dark' : 'light');
    };
    // Première application
    applyTheme();
    // Réagir si l'utilisateur change le thème système
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    if (mq.addEventListener) {
      mq.addEventListener('change', applyTheme);
    } else if (mq.addListener) {
      // Safari anciens
      mq.addListener(applyTheme);
    }
  })();

  // Sélecteur enfant avec Select2
  const childSelect = document.getElementById('child-select');
  if (childSelect) {
    // Initialiser Select2 avec le thème Bootstrap 5
    $(childSelect).select2({
      theme: 'bootstrap-5',
      width: 'style',
      placeholder: 'Choisir un enfant',
      allowClear: false,
      minimumResultsForSearch: -1,
      language: 'fr'
    });

    // Gérer le changement de sélection
    $(childSelect).on('select2:select', function(e) {
      if (e.params.data.id) {
        window.location.href = '/child/' + e.params.data.id;
      }
    });

    // Améliorer l'apparence sur mobile
    if (window.innerWidth <= 576) {
      $(childSelect).select2('destroy');
      $(childSelect).select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('body'), // Éviter les problèmes de z-index sur mobile
        placeholder: 'Choisir un enfant'
      });
    }
  }

  // Filtrage des tâches avec debounce
  const filterInput = document.getElementById('task-filter');
  if (filterInput) {
    const lists = [
      document.getElementById('task-list-bonus'),
      document.getElementById('task-list-penalty'),
      document.getElementById('task-list-consumption')
    ];

    const getItems = () => lists.flatMap(list => Array.from(list.querySelectorAll('li.tasks')));

    function debounce(fn, delay) {
      let t; 
      return (...args) => { 
        clearTimeout(t); 
        t = setTimeout(() => fn.apply(null, args), delay); 
      };
    }

    const applyFilter = () => {
      const filter = (filterInput.value || '').toLowerCase();
      getItems().forEach(item => {
        const name = item.querySelector('.task-name').textContent.toLowerCase();
        item.style.display = name.includes(filter) ? '' : 'none';
      });
    };

    filterInput.addEventListener('input', debounce(applyFilter, 300));
  }

  // Protection anti double-soumission
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const btn = this.querySelector('button[type="submit"][data-once="true"]');
      if (btn) {
        if (btn.dataset.submitted === '1') {
          e.preventDefault();
          return false;
        }
        btn.dataset.submitted = '1';
        btn.disabled = true;
        
        // Optionnel: changer le texte du bouton
        const originalText = btn.textContent;
        btn.textContent = 'En cours...';
        
        // Réactiver le bouton après un délai (fallback)
        setTimeout(() => {
          btn.disabled = false;
          btn.textContent = originalText;
          delete btn.dataset.submitted;
        }, 10000); // 10 secondes
      }
    }, { passive: false });
  });

  // Raccourcis clavier pour historique (gauche/droite)
  const nav = document.getElementById('historic-nav');
  if (nav) {
    document.addEventListener('keydown', (e) => {
      const prevUrl = nav.getAttribute('data-prev-url');
      const nextUrl = nav.getAttribute('data-next-url');
      if (e.key === 'ArrowLeft' && prevUrl) {
        window.location.href = prevUrl;
      } else if (e.key === 'ArrowRight' && nextUrl) {
        window.location.href = nextUrl;
      }
    });
  }

  // Amélioration de l'accessibilité mobile
  if (window.innerWidth <= 576) {
    // Augmenter la taille des zones cliquables sur mobile
    document.querySelectorAll('.btn').forEach(btn => {
      btn.style.minHeight = '44px';
      btn.style.padding = '12px 16px';
    });
    
    // Améliorer la navigation tactile
    document.querySelectorAll('.nav-link').forEach(link => {
      link.style.padding = '12px 16px';
      link.style.minHeight = '44px';
    });
  }

  const pointsBar = document.getElementById("pointsBar");
  const currentPointText = document.getElementById("currentPointText");
  const maxPointText = document.getElementById("maxPointText");        // départ visuel
  setTimeout(() => renderPoints(true, pointsBar, pointsText, currentPointText, maxPointText), 60); // animation de 0 à 100%

});

function renderPoints(animate = true, pointsBar, pointsText, currentPointText, maxPointText) {
  totalPoints = clamp(currentPointText.textContent, 0, maxPointText.textContent);
  const percentage = (totalPoints / maxPointText.textContent) * 100;
  if (animate) {
    pointsBar.style.width = percentage + "%";
  } else {
    const prev = pointsBar.style.transition;
    pointsBar.style.transition = "none";
    pointsBar.style.width = percentage + "%";
    // trigger reflow
    void pointsBar.offsetWidth;
    pointsBar.style.transition = prev || "width 0.6s ease";
  }
}

function clamp(val, min, max) { return Math.max(min, Math.min(max, val)); }


// Fonction utilitaire pour afficher des messages
function showMessage(message, type = 'info') {
  const toastContainer = document.querySelector('.toast-container');
  if (toastContainer) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    toast.setAttribute('aria-atomic', 'true');
    toast.setAttribute('data-bs-delay', '4000');
    
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
  }
}
