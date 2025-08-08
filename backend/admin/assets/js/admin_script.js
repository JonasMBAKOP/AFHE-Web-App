// Variables globales
let sidebarCollapsed = false;
let isMobileView = window.innerWidth <= 992;

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initializeSidebar();
    initializeChart();
    initializeLogout();
    initializeFooterActions();
    
    // Vérifier la taille de l'écran pour le responsive
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

// Gestion améliorée de la sidebar
function initializeSidebar() {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    // Gestion du bouton hamburger
    hamburgerBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleSidebar();
    });
    
    // Fermer la sidebar si on clique à l'extérieur (sur mobile)
    document.addEventListener('click', function(e) {
        if (isMobileView && !sidebarCollapsed && 
            !sidebar.contains(e.target) && 
            e.target !== hamburgerBtn) {
            toggleSidebar();
        }
    });
    
    // Gestion des sous-menus (uniquement en mode desktop étendu)
    const menuLinks = document.querySelectorAll('.menu-link[data-submenu]');
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (isMobileView || sidebarCollapsed) {
                e.preventDefault();
                return;
            }
            
            e.preventDefault();
            const submenuId = this.getAttribute('data-submenu');
            const submenu = document.getElementById('submenu-' + submenuId);
            const arrow = this.querySelector('.menu-arrow');
            
            if (submenu) {
                toggleSubmenu(submenu, arrow);
            }
        });
    });
}

// Basculer l'état de la sidebar avec gestion améliorée
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (isMobileView) {
        // Comportement spécifique mobile
        if (sidebarCollapsed) {
            // Premier clic - afficher la sidebar complète
            sidebar.classList.remove('collapsed');
            sidebar.classList.add('expanded');
        } else {
            // Deuxième clic - réduire la sidebar
            sidebar.classList.add('collapsed');
            sidebar.classList.remove('expanded');
        }
    } else {
        // Comportement desktop normal
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    }
    
    sidebarCollapsed = !sidebarCollapsed;
    
    // Fermer tous les sous-menus quand on réduit
    if (sidebarCollapsed) {
        closeAllSubmenus();
    }
}

// Gestion des sous-menus
function toggleSubmenu(submenu, arrow) {
    // Fermer les autres sous-menus
    const allSubmenus = document.querySelectorAll('.submenu');
    const allArrows = document.querySelectorAll('.menu-arrow');
    
    allSubmenus.forEach(menu => {
        if (menu !== submenu) menu.classList.remove('open');
    });
    
    allArrows.forEach(arr => {
        if (arr !== arrow) arr.classList.remove('rotated');
    });
    
    // Basculer le sous-menu actuel
    submenu.classList.toggle('open');
    arrow.classList.toggle('rotated');
}

function closeAllSubmenus() {
    document.querySelectorAll('.submenu').forEach(menu => menu.classList.remove('open'));
    document.querySelectorAll('.menu-arrow').forEach(arrow => arrow.classList.remove('rotated'));
}

// Vérifier la taille de l'écran pour le responsive
function checkScreenSize() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    isMobileView = window.innerWidth <= 992;
    
    if (isMobileView) {
        // Mode mobile - sidebar réduite par défaut
        sidebar.classList.add('collapsed');
        sidebarCollapsed = true;
        
        // Fermer tous les sous-menus
        closeAllSubmenus();
    } else {
        // Mode desktop - sidebar étendue par défaut
        sidebar.classList.remove('collapsed', 'expanded');
        sidebarCollapsed = false;
    }
    
    // Ajuster le contenu principal
    mainContent.style.marginLeft = sidebarCollapsed ? '80px' : '280px';
}

// Initialiser le graphique des visites
function initializeChart() {
    const ctx = document.getElementById('visitsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                datasets: [{
                    label: 'Visites',
                    data: [120, 150, 180, 200, 160, 140, 100],
                    borderColor: '#1E90FF',
                    backgroundColor: 'rgba(30, 144, 255, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#E9ECEF'
                        }
                    },
                    x: {
                        grid: {
                            color: '#E9ECEF'
                        }
                    }
                }
            }
        });
    }
}


// Initialiser les actions du footer
function initializeFooterActions() {
    // Les fonctions seront appelées directement depuis les liens onclick
}

// Afficher les informations système
function showSystemInfo() {
    const modal = createModal('Informations Système', `
        <div class="system-info">
            <p><strong>Version PHP:</strong> ${getPhpVersion()}</p>
            <p><strong>Serveur:</strong> ${getServerInfo()}</p>
            <p><strong>Base de données:</strong> MySQL/MariaDB</p>
            <p><strong>Espace disque:</strong> ${getDiskSpace()}</p>
            <p><strong>Mémoire utilisée:</strong> ${getMemoryUsage()}</p>
            <p><strong>Dernière sauvegarde:</strong> ${getLastBackup()}</p>
        </div>
    `);
    
    document.body.appendChild(modal);
}

// Afficher l'aide
function showHelp() {
    const modal = createModal('Centre d\'Aide', `
        <div class="help-content">
            <h4>Navigation</h4>
            <p>Utilisez le menu latéral pour naviguer entre les différentes sections.</p>
            
            <h4>Gestion des Activités</h4>
            <p>Créez, modifiez et supprimez les activités de l'association.</p>
            
            <h4>Gestion des Projets</h4>
            <p>Suivez l'avancement de vos projets et gérez les ressources.</p>
            
            <h4>Témoignages</h4>
            <p>Modérez et publiez les témoignages des bénéficiaires.</p>
            
            <h4>Support</h4>
            <p>Pour plus d'aide, contactez l'équipe technique.</p>
        </div>
    `);
    
    document.body.appendChild(modal);
}

// Afficher la documentation
function showDocumentation() {
    const modal = createModal('Documentation', `
        <div class="documentation-content">
            <h4>Guide d'utilisation</h4>
            <ul>
                <li><a href="#" onclick="showSection('dashboard')">Tableau de Bord</a></li>
                <li><a href="#" onclick="showSection('activities')">Gestion des Activités</a></li>
                <li><a href="#" onclick="showSection('projects')">Gestion des Projets</a></li>
                <li><a href="#" onclick="showSection('testimonials')">Gestion des Témoignages</a></li>
                <li><a href="#" onclick="showSection('donations')">Gestion des Dons</a></li>
            </ul>
            
            <h4>FAQ</h4>
            <p><strong>Q: Comment créer une nouvelle activité ?</strong></p>
            <p>R: Allez dans Activités > Créer une activité et remplissez le formulaire.</p>
            
            <p><strong>Q: Comment modifier un projet ?</strong></p>
            <p>R: Allez dans Projets > Liste des Projets et cliquez sur "Modifier".</p>
        </div>
    `);
    
    document.body.appendChild(modal);
}

// Contacter le support
function contactSupport() {
    const modal = createModal('Contacter le Support', `
        <div class="support-form">
            <form id="supportForm">
                <div class="form-group">
                    <label for="supportSubject">Sujet:</label>
                    <input type="text" id="supportSubject" name="subject" required>
                </div>
                
                <div class="form-group">
                    <label for="supportMessage">Message:</label>
                    <textarea id="supportMessage" name="message" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="supportPriority">Priorité:</label>
                    <select id="supportPriority" name="priority">
                        <option value="low">Basse</option>
                        <option value="medium">Moyenne</option>
                        <option value="high">Haute</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Envoyer</button>
            </form>
        </div>
    `);
    
    document.body.appendChild(modal);
    
    // Gérer l'envoi du formulaire
    document.getElementById('supportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Votre message a été envoyé au support technique.');
        closeModal();
    });
}

// Signaler un bug
function reportBug() {
    const modal = createModal('Signaler un Bug', `
        <div class="bug-report-form">
            <form id="bugReportForm">
                <div class="form-group">
                    <label for="bugTitle">Titre du bug:</label>
                    <input type="text" id="bugTitle" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="bugDescription">Description:</label>
                    <textarea id="bugDescription" name="description" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="bugSteps">Étapes pour reproduire:</label>
                    <textarea id="bugSteps" name="steps" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="bugSeverity">Gravité:</label>
                    <select id="bugSeverity" name="severity">
                        <option value="minor">Mineur</option>
                        <option value="major">Majeur</option>
                        <option value="critical">Critique</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Signaler</button>
            </form>
        </div>
    `);
    
    document.body.appendChild(modal);
    
    // Gérer l'envoi du formulaire
    document.getElementById('bugReportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Votre rapport de bug a été envoyé. Merci pour votre contribution !');
        closeModal();
    });
}

// Créer un modal
function createModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                ${content}
            </div>
        </div>
    `;
    
    // Ajouter les styles du modal
    addModalStyles();
    
    return modal;
}

// Fermer le modal
function closeModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.remove();
    }
}

// Ajouter les styles du modal
function addModalStyles() {
    if (!document.getElementById('modalStyles')) {
        const style = document.createElement('style');
        style.id = 'modalStyles';
        style.textContent = `
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            }
            
            .modal-content {
                background: white;
                border-radius: 12px;
                max-width: 500px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            }
            
            .modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px;
                border-bottom: 1px solid #E9ECEF;
            }
            
            .modal-header h3 {
                margin: 0;
                color: var(--primary-blue);
            }
            
            .modal-close {
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: #6C757D;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .form-group {
                margin-bottom: 15px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: 500;
                color: var(--text-color);
            }
            
            .form-group input,
            .form-group textarea,
            .form-group select {
                width: 100%;
                padding: 10px;
                border: 1px solid #E9ECEF;
                border-radius: 6px;
                font-size: 14px;
            }
            
            .btn {
                padding: 10px 20px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
            }
            
            .btn-primary {
                background: var(--primary-blue);
                color: white;
            }
        `;
        document.head.appendChild(style);
    }
}

// Afficher un loader
function showLoader() {
    const loader = document.createElement('div');
    loader.id = 'loader';
    loader.innerHTML = `
        <div class="loader-overlay">
            <div class="loader-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Déconnexion en cours...</p>
            </div>
        </div>
    `;
    
    // Ajouter les styles du loader
    const style = document.createElement('style');
    style.textContent = `
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        
        .loader-spinner {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .loader-spinner i {
            font-size: 30px;
            color: var(--primary-blue);
            margin-bottom: 15px;
        }
        
        .loader-spinner p {
            margin: 0;
            color: var(--text-color);
        }
    `;
    
    document.head.appendChild(style);
    document.body.appendChild(loader);
}

// Cacher le loader
function hideLoader() {
    const loader = document.getElementById('loader');
    if (loader) {
        loader.remove();
    }
}

// Fonctions utilitaires pour les informations système
function getPhpVersion() {
    return '8.0+';
}

function getServerInfo() {
    return 'Apache/Nginx';
}

function getDiskSpace() {
    return '2.5 GB / 10 GB';
}

function getMemoryUsage() {
    return '128 MB / 512 MB';
}

function getLastBackup() {
    return new Date().toLocaleDateString('fr-FR');
}