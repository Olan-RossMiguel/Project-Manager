document.addEventListener("DOMContentLoaded", function() {
    // Elementos del DOM
    const toggleSidebar = document.querySelector('.toggle-sidebar');
    const sidebar = document.querySelector('#sidebar');
    const contentSections = document.querySelectorAll('.content-section');
    
    // Estado de la aplicación
    const appState = {
        currentUser: {
            id: document.body.getAttribute('data-user-id'),
            name: document.body.getAttribute('data-user-name'),
            role: 'admin'
        },
        currentView: 'dashboard'
    };

    // Inicialización
    initSidebar();
    initNavigation();
    loadInitialView();

    // Evento para toggle sidebar
    toggleSidebar.addEventListener('click', () => {
        sidebar.classList.toggle('hide');
    });

    function initSidebar() {
        // Manejo de dropdowns del sidebar
        document.querySelectorAll('.side-menu > li > a').forEach(menuItem => {
            if (menuItem.nextElementSibling && menuItem.nextElementSibling.classList.contains('side-dropdown')) {
                menuItem.addEventListener('click', function(e) {
                    e.preventDefault();
                    const dropdown = this.nextElementSibling;
                    dropdown.classList.toggle('show');
                    
                    // Rotar ícono
                    const iconRight = this.querySelector('.icon-right');
                    if (iconRight) {
                        iconRight.classList.toggle('rotate');
                    }
                });
            }
        });
    }

    function initNavigation() {
        // Navegación principal
        document.querySelectorAll('.side-menu a').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href') === '#') {
                    e.preventDefault();
                    
                    // Solo procesamos los links principales
                    if (this.parentElement.parentElement.classList.contains('side-menu')) {
                        const viewId = this.textContent.trim().toLowerCase().replace(/\s+/g, '-');
                        
                        // Mapeo de vistas especiales
                        const viewMap = {
                            'dashboard': 'dashboard',
                            'calendario': 'calendario',
                            'usuarios': 'reportes',
                            'detalles-de-proyectos': 'reportes',
                            'crear-usuario': 'crear-usuario',
                            'crear-proyecto': 'crear-proyecto'
                        };
                        
                        const targetView = viewMap[viewId] || viewId;
                        switchView(targetView);
                    }
                }
            });
        });
    }

    function switchView(viewName, params = {}) {
        // Ocultar todas las secciones
        contentSections.forEach(section => {
            section.style.display = 'none';
        });
        
        // Mostrar la sección solicitada
        const targetSection = document.getElementById(viewName);
        if (targetSection) {
            targetSection.style.display = 'block';
            
            // Cargar contenido dinámico
            loadViewContent(viewName, params);
            
            // Actualizar estado
            appState.currentView = viewName;
            updateActiveMenu();
        }
    }

    async function loadViewContent(viewName, params) {
        const targetSection = document.getElementById(viewName);
        
        // Mostrar loader
        targetSection.innerHTML = '<div class="loader">Cargando...</div>';
        
        try {
            let response;
            
            switch(viewName) {
                case 'dashboard':
                    response = await fetch('partials/dashboard.php');
                    break;
                case 'reportes':
                    response = await fetch('partials/reportes.php');
                    break;
                case 'crear-usuario':
                    response = await fetch('partials/crear-usuario.php');
                    break;
                case 'crear-proyecto':
                    response = await fetch('partials/crear-proyecto.php');
                    break;
                case 'calendario':
                    response = await fetch('partials/calendario.php');
                    break;
                default:
                    response = await fetch('partials/dashboard.php');
            }
            
            if (!response.ok) {
                throw new Error('Error al cargar el contenido');
            }
            
            const content = await response.text();
            targetSection.innerHTML = content;
            
            // Inicializar componentes específicos de la vista
            initViewComponents(viewName);
            
        } catch (error) {
            console.error('Error:', error);
            targetSection.innerHTML = `<div class="error">Error al cargar el contenido: ${error.message}</div>`;
        }
    }

    function initViewComponents(viewName) {
        switch(viewName) {
            case 'crear-usuario':
                initUsuarioForm();
                break;
            case 'crear-proyecto':
                initProyectoForm();
                break;
            case 'calendario':
                initCalendario();
                break;
        }
    }

    function initUsuarioForm() {
        const form = document.getElementById('form-crear-usuario');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'crear-usuario');
                
                try {
                    const response = await fetch('api/usuarios.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showModal('Usuario creado exitosamente');
                        form.reset();
                    } else {
                        showModal(result.message || 'Error al crear usuario', true);
                    }
                } catch (error) {
                    showModal('Error de conexión: ' + error.message, true);
                }
            });
        }
    }

    function initProyectoForm() {
        const form = document.getElementById('form-crear-proyecto');
        if (form) {
            // Cargar líderes disponibles
            loadLideres();
            
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'crear-proyecto');
                
                try {
                    const response = await fetch('api/proyectos.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showModal('Proyecto creado exitosamente');
                        form.reset();
                    } else {
                        showModal(result.message || 'Error al crear proyecto', true);
                    }
                } catch (error) {
                    showModal('Error de conexión: ' + error.message, true);
                }
            });
        }
    }

    async function loadLideres() {
        const select = document.getElementById('lider-id');
        if (select) {
            try {
                const response = await fetch('api/usuarios.php?action=get-lideres');
                const result = await response.json();
                
                if (result.success) {
                    select.innerHTML = result.data.map(user => 
                        `<option value="${user.id}">${user.nombre} ${user.apellido_paterno}</option>`
                    ).join('');
                }
            } catch (error) {
                console.error('Error al cargar líderes:', error);
            }
        }
    }

    function showModal(message, isError = false) {
        const modal = document.getElementById('modal-mensaje');
        const modalText = document.getElementById('modal-texto');
        const closeBtn = document.querySelector('.close-modal');
        
        modalText.textContent = message;
        modalText.style.color = isError ? '#e74c3c' : '#2ecc71';
        modal.style.display = 'block';
        
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    }

    function updateActiveMenu() {
        // Remover clase active de todos los items
        document.querySelectorAll('.side-menu a').forEach(link => {
            link.classList.remove('active');
        });
        
        // Agregar clase active al item correspondiente
        const currentView = appState.currentView;
        let menuItem;
        
        switch(currentView) {
            case 'dashboard':
                menuItem = document.querySelector('.side-menu a[href="#"]');
                break;
            case 'reportes':
                menuItem = document.querySelector('.side-dropdown a[href="#"]');
                break;
            case 'crear-usuario':
                menuItem = document.querySelector('.side-dropdown a[href="../crearUsuario.php"]');
                break;
            case 'crear-proyecto':
                menuItem = document.querySelector('.side-dropdown a[href="#"]:nth-child(2)');
                break;
            case 'calendario':
                menuItem = document.querySelector('.side-menu a[href="#"]:nth-child(2)');
                break;
        }
        
        if (menuItem) {
            menuItem.classList.add('active');
        }
    }

    function loadInitialView() {
        // Verificar si hay parámetros en la URL
        const urlParams = new URLSearchParams(window.location.search);
        const viewParam = urlParams.get('view');
        
        if (viewParam) {
            switchView(viewParam);
        } else {
            switchView('dashboard');
        }
    }
});