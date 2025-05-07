// Configuración global de la aplicación
window.App = {
    // Estado de la aplicación
    currentView: null,

    // Inicialización principal
    init: function () {
        this.setupSidebar();
        this.setupNavigation();
        document.addEventListener('DOMContentLoaded', () => {
            app.loadInitialView(); // Asegúrate de que 'app' sea tu instancia de la clase principal en main.js
        });
        this.setupModalHandlers();
    },

    // Configuración del sidebar
    setupSidebar: function () {
        document.querySelector('.toggle-sidebar')?.addEventListener('click', () => {
            document.getElementById('sidebar')?.classList.toggle('close');
        });
    },

    // Configuración de navegación
    // Reemplazar en el setupNavigation
    setupNavigation: function () {
        document.querySelectorAll('[data-view]').forEach(link => {
            link.addEventListener('click', async (e) => {
                e.preventDefault();
                const viewId = link.getAttribute('data-view');

                // Deshabilitar todos los enlaces temporalmente
                document.querySelectorAll('[data-view]').forEach(l => {
                    l.classList.add('disabled-link');
                });

                await this.loadView(viewId);

                // Rehabilitar los enlaces
                document.querySelectorAll('[data-view]').forEach(l => {
                    l.classList.remove('disabled-link');
                });
            });
        });
    },

    // Cargar vista inicial
    loadInitialView: function () {
        const urlParams = new URLSearchParams(window.location.search);
        const viewParam = urlParams.get('view') || 'dashboard';
        this.loadView(viewParam);
    },

    loadView: async function (viewId) {
        try {
            console.log("[Debug] Iniciando carga de vista:", viewId);

            // 1. Ocultar todas las vistas de manera más robusta
            document.querySelectorAll('.content-view').forEach(view => {
                view.style.display = 'none';
                view.classList.remove('active-view');
            });

            // 2. Mostrar loader global
            this.showLoader();

            // 3. Obtener contenedor
            const viewContainer = document.getElementById(viewId);
            if (!viewContainer) {
                console.error("[Error] No se encontró el contenedor con ID:", viewId);
                throw new Error(`Contenedor para ${viewId} no encontrado`);
            }

            // 4. Cargar contenido si es necesario
            const shouldLoadContent = viewContainer.innerHTML.trim() === '' ||
                viewId === 'dashboard' ||
                viewId !== this.currentView;

            if (shouldLoadContent) {
                console.log("[Debug] Intentando cargar contenido para:", viewId);

                // Limpiar el contenedor antes de cargar nuevo contenido
                viewContainer.innerHTML = '';

                // Código de carga de contenido (que estaba comentado)
                const basePath = window.location.pathname.includes('Project-Manager')
                    ? '/Project-Manager'
                    : '';

                const pathsToTry = [
                    `./views/${viewId}.html`,
                    `${basePath}/views/${viewId}.html`,
                    `${window.location.origin}${basePath}/views/${viewId}.html`,
                    `./views/${viewId.replace(/-/g, '_')}.html`,
                    `${basePath}/views/${viewId.replace(/-/g, '_')}.html`
                ];

                let loaded = false;

                for (const path of pathsToTry) {
                    try {
                        console.log("[Debug] Probando ruta:", path);
                        const response = await fetch(path);

                        if (response.ok) {
                            const content = await response.text();
                            console.log("[Debug] Contenido cargado:", content.length, "bytes");
                            viewContainer.innerHTML = content;
                            this.initViewScripts(viewId);
                            loaded = true;
                            break;
                        }
                    } catch (error) {
                        console.warn(`[Warning] Error al cargar ${path}:`, error);
                    }
                }

                if (!loaded) {
                    console.error("[Error] No se pudo cargar la vista desde ninguna ruta");

                    if (viewId === 'dashboard') {
                        viewContainer.innerHTML = `
                            <div class="card">
                                <h2>Dashboard</h2>
                                <p>Vista temporal - no se pudo cargar el dashboard</p>
                            </div>`;
                    } else {
                        throw new Error(`No se pudo cargar la vista ${viewId} desde ninguna ruta`);
                    }
                }
            }

            // 5. Mostrar la vista
            console.log("[Debug] Mostrando vista:", viewId);
            viewContainer.style.display = 'block';
            viewContainer.classList.add('active-view');
            this.currentView = viewId;
            this.updateActiveMenu(viewId);

        } catch (error) {
            console.error('[Error] Al cargar vista:', error);
            const errorContainer = document.getElementById(viewId) || document.querySelector('main');
            errorContainer.innerHTML = `
                <div class="error-view">
                    <h2>Error al cargar la vista</h2>
                    <p>${error.message}</p>
                    <button onclick="location.reload()" class="btn-primary">Recargar</button>
                </div>`;
            errorContainer.style.display = 'block';
            this.showModal('Error', `No se pudo cargar la vista: ${viewId}`, 'error');
        } finally {
            this.hideLoader();
        }
    },

    initViewScripts: function (viewId) {
        try {
            console.log("[Debug] Inicializando scripts para:", viewId);

            switch (viewId) {
                case 'crear-usuario':
                    this.initCrearUsuario();
                    break;

                case 'crear-proyecto':
                    this.initCrearProyecto();
                    break;

                case 'detallar-proyecto':
                    const proyectoId = document.querySelector('.detalle-proyecto.active')?.dataset.proyectoId ||
                        APP_CONFIG?.proyectoId;
                    if (proyectoId) {
                        this.initDetallarProyecto(proyectoId);
                    } else {
                        console.error('No se pudo obtener el ID del proyecto');
                        this.showModal('Error', 'No se pudo cargar el proyecto', 'error');
                    }
                    break;

                case 'agregar-actividades':
                    this.initAgregarActividades();
                    break;

                case 'agregar-integrantes':
                    this.initAgregarIntegrantes();
                    break;

                case 'asignar-actividades':
                    this.initAsignarActividades();
                    break;

                case 'reporte-equipo':
                    this.initReporteEquipo();
                    break;

                case 'reporte-actividades':
                    console.log("[Debug] Caso 'reporte-actividades' ejecutado");
                    this.initReporteActividades();
                    break;

                case 'reporte-lider-proyecto':
                    this.initReporteLiderProyecto();
                    // Asegurar que el gráfico se dibuje cuando el DOM esté visible
                    setTimeout(() => {
                        if (typeof drawChart === 'function') {
                            drawChart();
                            console.log("[Debug] drawChart ejecutado para reporte-lider-proyecto");
                        } else {
                            console.warn("[Warning] drawChart no está definido aún");
                        }
                    }, 100);
                    break;
                case 'reporte-usuarios':
                    this.initReporteUsuarios();
                    break;
                case 'reporte-proyectos-admin':
                    this.initReporteProyectosAdmin();
                    break;
                case 'reporte-proyecto-usuario':
                    this.initReporteProyectoUsuario();
                    break;

                default:
                    console.warn(`No se encontró inicializador para la vista: ${viewId}`);
                    break;
            }
        } catch (error) {
            console.error(`Error al inicializar la vista ${viewId}:`, error);
            this.showModal('Error', `Ocurrió un error al cargar la vista: ${viewId}`, 'error');
        }
    },



    // Inicialización para vista de crear usuario
    initCrearUsuario: function () {
        const form = document.getElementById('form-crear-usuario');
        if (!form) return;

        // Limpiar eventos previos para evitar duplicados
        form.replaceWith(form.cloneNode(true));
        const freshForm = document.getElementById('form-crear-usuario');

        freshForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            try {
                const formData = {
                    nombre: freshForm.nombre.value,
                    apellido_paterno: freshForm.apellido_paterno.value,
                    apellido_materno: freshForm.apellido_materno?.value || '',
                    edad: freshForm.edad.value,
                    correo: freshForm.correo.value,
                    contrasena: freshForm.contrasena.value,
                    rol: freshForm.rol.value
                };

                const response = await fetch('/Project-Manager/api/usuarios.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Error HTTP: ${response.status}`);
                }

                const result = await response.json();
                this.showModal('Éxito', 'Usuario creado correctamente');
                freshForm.reset();
            } catch (error) {
                console.error('Error:', error);
                this.showModal('Error', error.message, 'error');
            }
        });
    },

    // Inicialización para vista de crear proyecto
    initCrearProyecto: function () {
        const form = document.getElementById('form-crear-proyecto');
        if (!form) return;

        this.cargarLideres();

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            try {
                const formData = {
                    nombre: form.nombre.value,
                    area: form.area.value,
                    lider_id: form.lider_id.value
                };

                const response = await fetch('/Project-Manager/api/crear-proyecto.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Error HTTP: ${response.status}`);
                }

                const result = await response.json();
                this.showModal('Éxito', 'Proyecto creado correctamente');
                form.reset();
            } catch (error) {
                console.error('Error:', error);
                this.showModal('Error', error.message, 'error');
            }
        });
    },

    // Inicialización para vista de detalle de proyecto
    initDetallarProyecto: function (proyectoId) {
        if (!proyectoId) {
            console.error('ID de proyecto no proporcionado');
            this.showModal('Error', 'No se ha especificado un proyecto', 'error');
            return;
        }

        const form = document.getElementById('form-detallar-proyecto');
        if (!form) {
            console.error('Formulario no encontrado');
            return;
        }

        this.showLoader(form, true);

        const elementosClave = {
            nombre: document.getElementById('proyecto-nombre'),
            area: document.getElementById('proyecto-area'),
            estado: document.getElementById('proyecto-estado'),
            submitBtn: form.querySelector('button[type="submit"]')
        };

        const camposEditables = ['descripcion', 'objetivos', 'fecha_inicio', 'fecha_fin', 'estado', 'url_repositorio', 'plataforma_repositorio'];

        this.fetchProyecto(proyectoId)
            .then(proyecto => {
                this.mostrarDatosProyecto(proyecto, elementosClave, camposEditables);
                this.configurarEnvio(form, proyectoId, camposEditables);
            })
            .catch(error => {
                console.error('Error al cargar proyecto:', error);
                this.showModal('Error', error.message || 'No se pudieron cargar los datos del proyecto', 'error');
            })
            .finally(() => {
                this.showLoader(form, false);
            });
    },

    // Funciones auxiliares para detalle de proyecto
    fetchProyecto: async function (proyectoId) {
        const response = await fetch(`/Project-Manager/api/obtener-proyecto.php?id=${proyectoId}`);
        if (!response.ok) {
            throw new Error('Error al cargar proyecto');
        }
        return await response.json();
    },

    mostrarDatosProyecto: function (proyecto, elementos, campos) {
        if (elementos.nombre) {
            elementos.nombre.textContent = proyecto.nombre || 'Sin nombre';
            elementos.nombre.className = 'readonly-field field-locked';
        }

        if (elementos.area) {
            elementos.area.textContent = proyecto.area || 'Sin área';
            elementos.area.className = 'readonly-field field-locked';
        }

        if (elementos.estado) {
            elementos.estado.textContent = proyecto.estado || 'Por iniciar';
            elementos.estado.className = '';
        }

        campos.forEach(campo => {
            const element = document.getElementById(campo);
            if (!element) return;

            if (proyecto[campo]) {
                element.value = proyecto[campo];

                if (['nombre', 'area'].includes(campo)) {
                    element.readOnly = true;
                    element.disabled = true;
                    element.classList.add('field-locked');
                } else {
                    element.classList.remove('field-locked');
                    element.classList.add('editable-field');
                }
            }
        });
    },

    configurarEnvio: function (form, proyectoId, camposEditables) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btnSubmit = form.querySelector('button[type="submit"]');
            this.toggleSubmitButton(btnSubmit, true);

            try {
                const formData = this.prepararDatosEnvio(proyectoId, camposEditables);
                await this.enviarActualizacion(formData);
                this.showModal('Éxito', 'Proyecto actualizado correctamente', 'success');
            } catch (err) {
                console.error('Error al actualizar:', err);
                this.showModal('Error', err.message || 'Error al actualizar el proyecto', 'error');
            } finally {
                this.toggleSubmitButton(btnSubmit, false);
            }
        });
    },

    prepararDatosEnvio: function (proyectoId, campos) {
        const formData = {
            id: proyectoId,
            estado: document.getElementById('estado')?.value
        };

        campos.forEach(campo => {
            if (campo !== 'estado') {
                const element = document.getElementById(campo);
                if (element && !element.classList.contains('readonly')) {
                    formData[campo] = element.value;
                }
            }
        });

        return formData;
    },

    enviarActualizacion: async function (formData) {
        const response = await fetch('/Project-Manager/api/actualizar-proyecto.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || 'Error al actualizar');
        }
        return result;
    },

    // Funciones utilitarias
    toggleSubmitButton: function (button, isLoading) {
        if (!button) return;

        const originalText = button.dataset.originalText || button.textContent;
        if (isLoading) {
            button.dataset.originalText = originalText;
            button.disabled = true;
            button.innerHTML = '<span class="loader"></span> Procesando...';
        } else {
            button.disabled = false;
            button.textContent = originalText;
        }
    },

    showLoader: function (container, show) {
        if (!container) return;

        const loaderId = 'dynamic-loader';
        let loader = document.getElementById(loaderId);

        if (show && !loader) {
            loader = document.createElement('div');
            loader.id = loaderId;
            loader.className = 'form-loader';
            loader.innerHTML = '<div class="spinner"></div>';
            container.prepend(loader);
        } else if (!show && loader) {
            loader.remove();
        }
    },

    // Cargar usuarios en el select de líderes
    cargarLideres: async function () {
        try {
            const response = await fetch('/Project-Manager/api/obtener-lideres.php');
            if (!response.ok) throw new Error('No se pudieron cargar los usuarios');

            const usuarios = await response.json();
            const select = document.getElementById('lider_id');
            if (!select) return;

            select.innerHTML = '<option value="">Selecciona un líder</option>';
            usuarios.forEach(usuario => {
                const option = document.createElement('option');
                option.value = usuario.id;
                option.textContent = `${usuario.nombre} ${usuario.apellido_paterno}`;
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Error al cargar usuarios:', error);
            this.showModal('Error', 'No se pudieron cargar los usuarios', 'error');
        }
    },

    // Inicialización para vista de agregar actividades
    initAgregarActividades: function () {
        const submitBtn = document.getElementById('btn-submit-actividad');
        if (!submitBtn) {
            console.error('Botón de submit no encontrado');
            return;
        }

        submitBtn.removeEventListener('click', this.handleSubmitActividad);
        submitBtn.addEventListener('click', this.handleSubmitActividad.bind(this));
        console.log('Listener de actividades registrado correctamente');
    },

    handleSubmitActividad: async function () {
        const form = document.getElementById('form-agregar-actividad');
        const submitBtn = document.getElementById('btn-submit-actividad');

        if (!form || !submitBtn) return;

        try {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creando...';

            if (new Date(form.fecha_fin.value) < new Date(form.fecha_inicio.value)) {
                throw new Error('La fecha final debe ser posterior a la inicial');
            }

            const formData = {
                nombre: form.nombre.value,
                descripcion: form.descripcion.value,
                fecha_inicio: form.fecha_inicio.value,
                fecha_fin: form.fecha_fin.value,
                horas_estimadas: parseInt(form.horas_estimadas.value)
            };

            console.log('Enviando datos:', formData);

            const response = await fetch('/Project-Manager/api/crear-actividad.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            console.log('Respuesta:', result);

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Error en el servidor');
            }

            this.showModal('Éxito', result.message);
            form.reset();

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message.includes('SQLSTATE')
                ? 'Error en la base de datos. Contacta al administrador.'
                : error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Crear Actividad';
        }
    },

    // REPORTES =============================================
    initReporteEquipo: function () {
        this.cargarIntegrantes();

        const searchInput = document.getElementById('search-team');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filtrarIntegrantes(e.target.value.toLowerCase());
            });
        }

        const table = document.getElementById('team-report-table');
        if (table) {
            table.addEventListener('click', (e) => {
                const btn = e.target.closest('button');
                if (!btn) return;

                const row = btn.closest('tr');
                const asignacionId = row?.dataset.asignacionId;

                if (!asignacionId) return;

                if (btn.classList.contains('btn-update')) {
                    this.mostrarFormularioActualizacion(asignacionId);
                } else if (btn.classList.contains('btn-delete')) {
                    this.eliminarIntegrante(asignacionId);
                }
            });
        }
    },

    cargarIntegrantes: async function () {
        try {
            const response = await fetch(`${APP_CONFIG.apiBase}/reporte-equipo.php`);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al cargar integrantes');
            }

            this.renderizarIntegrantes(data.data);
        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message, 'error');
        }
    },

    renderizarIntegrantes: function (integrantes) {
        const tableBody = document.getElementById('team-report-body');
        if (!tableBody) return;

        tableBody.innerHTML = '';

        if (integrantes.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="3"><span class="no-data">No hay integrantes en este proyecto</span></td>
                </tr>
            `;
            return;
        }

        integrantes.forEach(integrante => {
            const row = document.createElement('tr');
            row.dataset.asignacionId = integrante.asignacion_id || integrante.id;

            row.innerHTML = `
                <td>${integrante.nombre_completo}</td>
                <td>${integrante.rol_proyecto}</td>
                <td class="actions">
                    <button class="btn-update" title="Actualizar rol">
                        <i class='bx bx-edit'></i>
                    </button>
                    <button class="btn-delete" title="Eliminar del proyecto">
                        <i class='bx bx-trash'></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(row);
        });
    },

    filtrarIntegrantes: function (term) {
        const rows = document.querySelectorAll('#team-report-body tr');

        rows.forEach(row => {
            if (row.querySelector('.no-data')) return;

            const nombre = row.cells[0].textContent.toLowerCase();
            const rol = row.cells[1].textContent.toLowerCase();

            row.style.display = (nombre.includes(term) || rol.includes(term))
                ? ''
                : 'none';
        });
    },

    initAgregarIntegrantes: function () {
        const isEditMode = window.currentViewData?.mode === 'edit';
        const form = document.getElementById('form-agregar-integrante');

        if (!form) return;

        const formTitle = document.getElementById('form-integrantes-title');
        if (formTitle) {
            formTitle.textContent = isEditMode ? 'Editar Integrante' : 'Agregar Integrante';
        }

        this.cargarUsuariosDisponibles(isEditMode);
        this.cargarRolesDisponibles();

        if (isEditMode && window.currentViewData?.data) {
            this.precargarFormularioIntegrante(window.currentViewData.data);
        }

        const btnSubmit = document.getElementById('btn-agregar-integrante');
        if (btnSubmit) {
            btnSubmit.textContent = isEditMode ? 'Actualizar Integrante' : 'Agregar Integrante';

            btnSubmit.removeEventListener('click', this.handleAgregarIntegrante);
            btnSubmit.removeEventListener('click', this.handleActualizarIntegrante);

            if (isEditMode) {
                btnSubmit.addEventListener('click', this.handleActualizarIntegrante.bind(this));
            } else {
                btnSubmit.addEventListener('click', this.handleAgregarIntegrante.bind(this));
            }
        }
    },

    cargarUsuariosDisponibles: async function (disableSelect = false) {
        try {
            const select = document.getElementById('usuario_id');
            if (!select) return;

            const response = await fetch(`${APP_CONFIG.apiBase}/obtener-usuarios-disponibles.php`);
            const data = await response.json();

            select.innerHTML = '<option value="">-- Seleccione un usuario --</option>';

            if (Array.isArray(data)) {
                data.forEach(usuario => {
                    const option = document.createElement('option');
                    option.value = usuario.id;
                    option.textContent = usuario.nombre;
                    select.appendChild(option);
                });
            }

            if (disableSelect) {
                select.disabled = true;
            }
        } catch (error) {
            console.error('Error al cargar usuarios:', error);
            const select = document.getElementById('usuario_id');
            if (select) {
                const option = document.createElement('option');
                option.textContent = 'Error al cargar usuarios';
                select.appendChild(option);
            }
        }
    },

    cargarRolesDisponibles: async function () {
        try {
            const select = document.getElementById('rol_id');
            if (!select) return;

            const response = await fetch(`${APP_CONFIG.apiBase}/obtener-roles.php`);
            const data = await response.json();

            select.innerHTML = '<option value="">-- Seleccione un rol --</option>';

            if (data.success && Array.isArray(data.data)) {
                data.data.forEach(rol => {
                    const option = document.createElement('option');
                    option.value = rol.id;
                    option.textContent = rol.nombre;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error al cargar roles:', error);
            const select = document.getElementById('rol_id');
            if (select) {
                const option = document.createElement('option');
                option.textContent = 'Error al cargar roles';
                select.appendChild(option);
            }
        }
    },

    precargarFormularioIntegrante: function (data) {
        const form = document.getElementById('form-agregar-integrante');
        if (!form) return;

        if (form.asignacion_id) {
            form.asignacion_id.value = data.id || '';
        }

        if (form.usuario_id) {
            form.usuario_id.value = data.usuario_id || '';
            form.usuario_id.disabled = true;
        }

        if (form.rol_id) {
            form.rol_id.value = data.rol_id || '';
        }
    },

    handleAgregarIntegrante: async function () {
        const form = document.getElementById('form-agregar-integrante');
        const btnSubmit = document.getElementById('btn-agregar-integrante');

        if (!form || !btnSubmit) return;

        try {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="bx bx-loader-circle bx-spin"></i> Procesando...';

            const formData = {
                proyecto_id: APP_CONFIG.proyectoId,
                usuario_id: form.usuario_id.value,
                rol_id: form.rol_id.value
            };

            const response = await fetch(`${APP_CONFIG.apiBase}/agregar-integrante.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Error al agregar integrante');
            }

            this.showModal('Éxito', 'Integrante agregado correctamente');
            form.reset();

            if (this.currentView === 'reporte-equipo') {
                await this.cargarIntegrantes();
            }

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message || 'Ocurrió un error al agregar el integrante');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bx bx-user-plus"></i> Agregar Integrante';
        }
    },
    mostrarFormularioActualizacion: async function (asignacionId) {
        try {
            // Cargar datos del integrante actual
            const integranteResponse = await fetch(`${APP_CONFIG.apiBase}/obtener-integrante.php?asignacion_id=${asignacionId}`);
            const integranteData = await integranteResponse.json();

            if (!integranteData.success) {
                throw new Error(integranteData.message || 'Error al cargar datos del integrante');
            }

            // Cargar usuarios disponibles
            const usuariosResponse = await fetch(`${APP_CONFIG.apiBase}/obtener-usuarios-disponibles.php`);
            const usuarios = await usuariosResponse.json();

            // Cargar roles disponibles
            const rolesResponse = await fetch(`${APP_CONFIG.apiBase}/obtener-roles.php`);
            const rolesData = await rolesResponse.json();

            // Configurar el modal de edición
            const modal = document.getElementById('modal-confirmacion');
            modal.querySelector('#modal-title').textContent = 'Editar Integrante';

            modal.querySelector('#modal-mensaje').innerHTML = `
                <form id="form-editar-integrante">
                    <input type="hidden" id="edit-asignacion-id" value="${asignacionId}">
                    
                    <div class="form-group">
                        <label for="edit-usuario-id">Integrante:</label>
                        <select id="edit-usuario-id" class="form-control">
                            ${usuarios.map(usuario => `
                                <option value="${usuario.id}" ${usuario.id == integranteData.data.usuario_id ? 'selected' : ''}>
                                    ${usuario.nombre}
                                </option>
                            `).join('')}
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-rol-id">Rol:</label>
                        <select id="edit-rol-id" class="form-control">
                            ${rolesData.data.map(rol => `
                                <option value="${rol.id}" ${rol.id == integranteData.data.rol_id ? 'selected' : ''}>
                                    ${rol.nombre}
                                </option>
                            `).join('')}
                        </select>
                    </div>
                </form>
            `;

            // Configurar botón de guardar
            const btnGuardar = document.createElement('button');
            btnGuardar.textContent = 'Guardar Cambios';
            btnGuardar.className = 'btn-primary';
            btnGuardar.onclick = async () => {
                await this.guardarCambiosIntegrante(asignacionId);
            };

            modal.querySelector('.modal-footer').innerHTML = '';
            modal.querySelector('.modal-footer').appendChild(btnGuardar);

            // Mostrar modal
            modal.style.display = 'block';

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message);
        }
    },



    cargarRolesParaEdicion: async function (rolActualId) {
        try {
            const select = document.getElementById('edit-rol-id');
            if (!select) return;

            const response = await fetch(`${APP_CONFIG.apiBase}/obtener-roles.php`);
            const result = await response.json();

            select.innerHTML = '';

            if (result.success && Array.isArray(result.data)) {
                result.data.forEach(rol => {
                    const option = document.createElement('option');
                    option.value = rol.id;
                    option.textContent = rol.nombre;
                    option.selected = (rol.id == rolActualId);
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error al cargar roles:', error);
        }
    },




    guardarCambiosIntegrante: async function (asignacionId) {
        const form = document.getElementById('form-editar-integrante');
        const btnSubmit = document.querySelector('#modal-confirmacion .modal-footer button');

        if (!form) return;

        try {
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Guardando...';

            const formData = {
                asignacion_id: asignacionId,
                usuario_id: document.getElementById('edit-usuario-id').value,
                rol_id: document.getElementById('edit-rol-id').value
            };

            const response = await fetch(`${APP_CONFIG.apiBase}/actualizar-integrante.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Error al actualizar integrante');
            }

            this.showModal('Éxito', 'Integrante actualizado correctamente');
            document.getElementById('modal-confirmacion').style.display = 'none';

            // Recargar la lista de integrantes si es necesario
            if (this.currentView === 'reporte-equipo') {
                await this.cargarIntegrantes();
            }

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message || 'Ocurrió un error al actualizar el integrante');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar Cambios';
        }
    },

    eliminarIntegrante: async function (asignacionId) {
        try {
            const confirmacion = await this.showConfirmModal(
                'Confirmar eliminación',
                '¿Estás seguro de eliminar este integrante del proyecto?',
                'warning'
            );

            if (!confirmacion) return;

            const response = await fetch(`${APP_CONFIG.apiBase}/eliminar-integrante.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ asignacion_id: asignacionId })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al eliminar integrante');
            }

            await this.showModal('Éxito', 'Integrante eliminado correctamente', 'success');
            await this.cargarIntegrantes();

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message, 'error');
        }
    },


    //REPORTE ACTIVIDADES===================================
    initReporteActividades: function () {
        console.log("[Debug] initReporteActividades() ejecutándose"); // <--- AGREGAR ESTE LOG
        // 1. Forzar visibilidad del contenedor
        const container = document.getElementById('reporte-actividades');
        if (container) {
            container.style.display = 'block';
            container.style.opacity = 1;
            container.style.visibility = 'visible';
            container.style.height = 'auto';
        }

        // 2. Cargar datos
        this.cargarActividades();

        // 3. Configurar buscador
        const searchInput = document.getElementById('search-activities');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filtrarActividades(e.target.value.toLowerCase());
            });
        }

        // 4. Configurar eventos de la tabla
        document.getElementById('activities-report-table')?.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            const row = btn.closest('tr');
            const actividadId = row?.dataset.actividadId;

            if (btn.classList.contains('btn-update')) {
                this.mostrarFormularioEdicionActividad(actividadId);
            } else if (btn.classList.contains('btn-delete')) {
                this.eliminarActividad(actividadId);
            }
        });
    },
    cargarActividades: async function () {
        console.log("[Debug] cargarActividades() ejecutándose"); // <--- AGREGAR ESTE LOG
        try {
            const response = await fetch(`${APP_CONFIG.apiBase}/reporte-actividades.php`);
            console.log("[Debug] Respuesta de la API:", response); // <--- AGREGAR ESTE LOG
            const data = await response.json();
            console.log("[Debug] Datos de la API:", data); // <--- AGREGAR ESTE LOG

            if (!data.success) {
                throw new Error(data.message || 'Error al cargar actividades');
            }

            this.renderizarActividades(data.data);
        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message, 'error');
        }
    },

    renderizarActividades: function (actividades) {
        const tableBody = document.getElementById('activities-report-body');
        if (!tableBody) return;

        tableBody.innerHTML = '';

        if (actividades.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7"><span class="no-data">No hay actividades en este proyecto</span></td>
                </tr>
            `;
            return;
        }

        actividades.forEach(actividad => {
            const row = document.createElement('tr');
            row.dataset.actividadId = actividad.actividad_id;

            row.innerHTML = `
                <td>${actividad.actividad_nombre || 'Sin nombre'}</td>
                <td>${actividad.descripcion || 'Sin descripción'}</td>
                <td>${actividad.fecha_inicio}</td>
                <td>${actividad.fecha_fin}</td>
                <td>${actividad.horas_estimadas} hrs</td>
                <td>${actividad.responsable_nombre || 'Sin asignar'}</td>
                <td class="actions">
                    <button class="btn-update" title="Editar actividad">
                        <i class='bx bx-edit'></i>
                    </button>
                    <button class="btn-delete" title="Eliminar actividad">
                        <i class='bx bx-trash'></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(row);
        });
    },

    filtrarActividades: function (term) {
        const rows = document.querySelectorAll('#activities-report-body tr');

        rows.forEach(row => {
            if (row.querySelector('.no-data')) return;

            const nombre = row.cells[0].textContent.toLowerCase();
            const descripcion = row.cells[1].textContent.toLowerCase();
            const responsable = row.cells[5].textContent.toLowerCase();

            row.style.display = (nombre.includes(term) ||
                descripcion.includes(term) ||
                responsable.includes(term))
                ? ''
                : 'none';
        });
    },

    mostrarFormularioEdicionActividad: async function (actividadId) {
        try {
            // Cargar datos de la actividad
            const response = await fetch(`${APP_CONFIG.apiBase}/obtener-actividad.php?id=${actividadId}`);
            const actividadData = await response.json();

            if (!actividadData.success) {
                throw new Error(actividadData.message || 'Error al cargar datos de la actividad');
            }

            // Cargar responsables disponibles
            const responsablesResponse = await fetch(`${APP_CONFIG.apiBase}/obtener-integrantes-proyecto.php?proyecto_id=${APP_CONFIG.proyectoId}`);
            const responsables = await responsablesResponse.json();

            // Configurar el modal de edición
            const modal = document.getElementById('modal-confirmacion');
            modal.querySelector('#modal-title').textContent = 'Editar Actividad';

            modal.querySelector('#modal-mensaje').innerHTML = `
                <form id="form-editar-actividad">
                    <input type="hidden" id="edit-actividad-id" value="${actividadId}">
                    
                    <div class="form-group">
                        <label for="edit-actividad-nombre">Nombre:</label>
                        <input type="text" id="edit-actividad-nombre" class="form-control" 
                               value="${actividadData.data.nombre || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-actividad-descripcion">Descripción:</label>
                        <textarea id="edit-actividad-descripcion" class="form-control">${actividadData.data.descripcion || ''}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-actividad-fecha-inicio">Fecha Inicio:</label>
                        <input type="date" id="edit-actividad-fecha-inicio" class="form-control" 
                               value="${actividadData.data.fecha_inicio || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-actividad-fecha-fin">Fecha Fin:</label>
                        <input type="date" id="edit-actividad-fecha-fin" class="form-control" 
                               value="${actividadData.data.fecha_fin || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-actividad-horas">Horas Estimadas:</label>
                        <input type="number" id="edit-actividad-horas" class="form-control" 
                               value="${actividadData.data.horas_estimadas || ''}" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-actividad-responsable">Responsable:</label>
                        <select id="edit-actividad-responsable" class="form-control">
                            <option value="">Sin asignar</option>
                            ${responsables.data.map(responsable => `
                                <option value="${responsable.usuario_id}" 
                                    ${responsable.usuario_id == actividadData.data.responsable_id ? 'selected' : ''}>
                                    ${responsable.nombre_completo}
                                </option>
                            `).join('')}
                        </select>
                    </div>
                </form>
            `;

            // Configurar botón de guardar
            const btnGuardar = document.createElement('button');
            btnGuardar.textContent = 'Guardar Cambios';
            btnGuardar.className = 'btn-primary';
            btnGuardar.onclick = async () => {
                await this.guardarCambiosActividad(actividadId);
            };

            modal.querySelector('.modal-footer').innerHTML = '';
            modal.querySelector('.modal-footer').appendChild(btnGuardar);

            // Mostrar modal
            modal.style.display = 'block';

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message);
        }
    },

    guardarCambiosActividad: async function (actividadId) {
        const modal = document.getElementById('modal-confirmacion');
        const btnGuardar = modal.querySelector('.btn-primary');

        try {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="bx bx-loader bx-spin"></i> Guardando...';

            const nombre = document.getElementById('edit-actividad-nombre').value;
            const descripcion = document.getElementById('edit-actividad-descripcion').value;
            const fechaInicio = document.getElementById('edit-actividad-fecha-inicio').value;
            const fechaFin = document.getElementById('edit-actividad-fecha-fin').value;
            const horasEstimadas = document.getElementById('edit-actividad-horas').value;
            const responsableId = document.getElementById('edit-actividad-responsable').value;

            if (!nombre || !fechaInicio || !fechaFin || !horasEstimadas) {
                throw new Error('Todos los campos excepto responsable son requeridos');
            }

            const response = await fetch(`${APP_CONFIG.apiBase}/actualizar-actividad.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    actividad_id: actividadId,
                    nombre: nombre,
                    descripcion: descripcion,
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    horas_estimadas: horasEstimadas,
                    responsable_id: responsableId || null
                })
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Error al actualizar actividad');
            }

            // Éxito: actualizar tabla, cerrar modal y limpiar
            modal.style.display = 'none';
            modal.querySelector('#modal-title').textContent = '';
            modal.querySelector('#modal-mensaje').innerHTML = '';
            modal.querySelector('.modal-footer').innerHTML = '';

            this.showModal('Éxito', 'Actividad actualizada correctamente', 'success');
            await this.cargarActividades();

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message, 'error');
        } finally {
            if (btnGuardar) {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = 'Guardar Cambios';
            }
        }
    },

    eliminarActividad: async function (actividadId) {
        try {
            const confirmacion = await this.showConfirmModal(
                'Confirmar eliminación',
                '¿Estás seguro de eliminar esta actividad?',
                'warning'
            );

            if (!confirmacion) return;

            const response = await fetch(`${APP_CONFIG.apiBase}/eliminar-actividad.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ actividad_id: actividadId })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al eliminar actividad');
            }

            this.showModal('Éxito', 'Actividad eliminada correctamente', 'success');
            await this.cargarActividades();

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message, 'error');
        }
    },
    // REPORTE LIDER-PROYECTO========================
    initReporteLiderProyecto() {
        const tableBody = document.querySelector("#tabla-reporte-lider-proyecto tbody");

        // Cargar los datos con manejo mejorado de errores
        fetch("api/reporte-lider-proyecto.php")
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);

                tableBody.innerHTML = ""; // Limpiar contenido previo

                if (data.success && data.data) {
                    const proyecto = data.data;
                    console.log('Datos del proyecto:', proyecto);

                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${proyecto.nombre}</td>
                        <td>${proyecto.area}</td>
                        <td>${proyecto.descripcion}</td>
                        <td>${proyecto.objetivos}</td>
                        <td>${proyecto.fecha_inicio}</td>
                        <td>${proyecto.fecha_fin}</td>
                        <td>${proyecto.estado}</td>
                        <td>
                            <button class="btn-ver-cronograma" data-id="${proyecto.id}">
                                Ver
                            </button>
                        </td>
                        <td><a href="${proyecto.url_repositorio}" target="_blank">${proyecto.url_repositorio ? 'Ver' : '-'}</a></td>
                        <td>${proyecto.plataforma_repositorio}</td>
                    `;
                    tableBody.appendChild(row);

                    // Delegación de eventos para manejar clics dinámicos
                    document.addEventListener('click', (e) => {
                        if (e.target.closest('.btn-ver-cronograma')) {
                            const proyectoId = e.target.closest('.btn-ver-cronograma').dataset.id;
                            console.log('Mostrando cronograma para proyecto:', proyectoId);
                            this.mostrarCronograma(proyectoId);
                        }
                    });

                } else {
                    throw new Error(data.message || 'Datos del proyecto no disponibles');
                }
            })
            .catch(error => {
                console.error("Error al cargar el reporte:", error);
                this.mostrarErrorGlobal(`Error al cargar el reporte: ${error.message}`);
            });
    },


    
    getModalElements: function () {
        // Intenta encontrar el modal de varias formas
        let modal = document.getElementById('modal-cronograma');

        if (!modal) {
            console.warn('Modal no encontrado por ID, intentando con clase...');
            modal = document.querySelector('.overlay-crono');

            if (!modal) {
                console.error('Modal no encontrado después de reintento');
                return { modal: null, content: null, found: false };
            }
        }

        // Busca el contenido con múltiples selectores
        const content = modal.querySelector('#cronograma-content') ||
            modal.querySelector('.cuerpo-crono') ||
            modal;

        return {
            modal: modal,
            content: content,
            found: !!modal
        };
    },

    mostrarModal: function (modal) {
        if (!modal) return;

        modal.classList.add('activo-crono');
        modal.style.display = 'flex';
        modal.style.opacity = '1';
    },

    getLoaderHTML: function () {
        return `
            <div class="loader">
                <i class='bx bx-loader-alt bx-spin'></i>
                <p>Cargando diagrama de Gantt...</p>
            </div>
        `;
    },

    mostrarErrorEnModal: function (error, proyectoId) {
        const { modal, content } = this.getModalElements();

        if (!modal || !content) {
            this.mostrarErrorGlobal(error.message);
            return;
        }

        try {
            content.innerHTML = `
                <div class="error-message">
                    <i class='bx bx-error-circle'></i>
                    <h4>Error al cargar el cronograma</h4>
                    <p>${error.message || 'Error desconocido'}</p>
                    <button class="btn-retry" onclick="app.mostrarCronograma(${proyectoId})">
                        <i class='bx bx-refresh'></i> Reintentar
                    </button>
                </div>
            `;

            this.mostrarModal(modal);
        } catch (e) {
            console.error('Error al mostrar error:', e);
            this.mostrarErrorGlobal('Error crítico. Por favor recarga la página.');
        }
    },

    mostrarErrorGlobal: function (mensaje) {
        // Intenta usar el modal primero
        const { modal, content } = this.getModalElements();

        if (modal && content) {
            content.innerHTML = `
                <div class="error-message">
                    <h4>Error crítico</h4>
                    <p>${mensaje}</p>
                    <button onclick="location.reload()">Recargar Página</button>
                </div>
            `;
            modal.style.display = 'flex';
            return;
        }

        // Fallback directo al body
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            z-index: 99999;
            max-width: 80%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        `;
        errorDiv.innerHTML = `
            <strong>Error:</strong> ${mensaje}
            <button onclick="this.parentNode.remove()" 
                    style="margin-left: 10px; background: none; border: none; cursor: pointer;">
                ×
            </button>
        `;
        document.body.appendChild(errorDiv);
    },

    configurarCierreModal: function (modal) {
        if (!modal) return;

        // Limpiar eventos previos
        const oldCloseBtn = modal.querySelector('.cerrar-crono');
        if (oldCloseBtn) {
            oldCloseBtn.replaceWith(oldCloseBtn.cloneNode(true));
        }

        // Configurar nuevos eventos
        const closeBtn = modal.querySelector('.cerrar-crono');
        if (closeBtn) {
            closeBtn.onclick = () => {
                modal.classList.remove('activo-crono');
                modal.style.display = 'none';
            };
        }

        // Clic fuera del contenido
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('activo-crono');
                modal.style.display = 'none';
            }
        });
    },

    generarDiagramaGanttHTML: function (proyecto, actividades) {
        // ... (mantén tu implementación existente) ...
        // Devuelve el HTML generado para el diagrama
    },




    // ASIGNAR ACTIVIDADES =================================
    initAsignarActividades: function () {
        this.cargarDatosAsignacion();

        const btnAsignar = document.getElementById('btn-asignar-actividad');
        if (btnAsignar) {
            btnAsignar.addEventListener('click', this.handleAsignarActividad.bind(this));
        }
    },

    cargarDatosAsignacion: async function () {
        try {
            const response = await fetch(`${APP_CONFIG.apiBase}/obtener-datos-asignacion.php`);
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Error al cargar datos');
            }

            this.llenarSelect('actividad_id', result.data.actividades);
            this.llenarSelect('usuario_id', result.data.usuarios);

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message);
        }
    },

    llenarSelect: function (id, data) {
        const select = document.getElementById(id);
        if (!select) return;

        select.innerHTML = '';
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.nombre;
            select.appendChild(option);
        });
    },

    handleAsignarActividad: async function () {
        const form = document.getElementById('form-asignar-actividad');
        const btnAsignar = document.getElementById('btn-asignar-actividad');

        if (!form || !btnAsignar) return;

        try {
            btnAsignar.disabled = true;
            btnAsignar.textContent = 'Asignando...';

            const actividadId = form.actividad_id.value;
            const usuarioId = form.usuario_id.value;

            if (!actividadId || !usuarioId) {
                throw new Error('Todos los campos son requeridos');
            }

            const data = {
                actividad_id: actividadId,
                usuario_id: usuarioId
            };

            const response = await fetch('/Project-Manager/api/asignar-actividad.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Error al asignar actividad');
            }

            this.showModal('Éxito', result.message);
            form.reset();

            await this.cargarDatosAsignacion();

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message);
        } finally {
            btnAsignar.disabled = false;
            btnAsignar.textContent = 'Asignar Actividad';
        }
    },


    //REPORTE USUARIOS=================================================
    // En tu archivo main.js o donde manejas las vistas
    initReporteUsuarios: function () {
        console.log("[Debug] Ejecutando initReporteUsuarios");

        try {
            // 1. Cargar datos iniciales
            this.cargarUsuarios();

            // 2. Configurar buscador
            const searchInput = document.getElementById('search-users');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    this.filtrarUsuarios(e.target.value.toLowerCase());
                });
            }

            // 3. Configurar eventos de la tabla
            const table = document.getElementById('users-report-table');
            if (table) {
                table.addEventListener('click', (e) => {
                    const btn = e.target.closest('button');
                    if (!btn) return;

                    const row = btn.closest('tr');
                    const userId = row?.dataset.userId;

                    if (btn.classList.contains('btn-update')) {  // Cambiado de btn-edit a btn-update
                        this.mostrarFormularioEdicionUsuario(userId);
                    } else if (btn.classList.contains('btn-delete')) {
                        this.eliminarUsuario(userId);
                    }
                });
            }
        } catch (error) {
            console.error("Error en initReporteUsuarios:", error);
            this.showModal('Error', 'No se pudo inicializar el reporte de usuarios');
        }
    },
    cargarUsuarios: async function () {
        try {
            const response = await fetch(`${APP_CONFIG.apiBase}/obtener-usuarios.php`);
            const data = await response.json();

            if (!data.success) throw new Error(data.message);

            this.renderizarUsuarios(data.data);
        } catch (error) {
            console.error("Error cargando usuarios:", error);
            this.showModal('Error', 'No se pudieron cargar los usuarios');
        }
    },

    renderizarUsuarios: function (usuarios) {
        const tbody = document.getElementById('users-report-body');
        if (!tbody) return;

        tbody.innerHTML = usuarios.map(user => `
            <tr data-user-id="${user.id}">
                <td>${user.nombre}</td>
                <td>${user.apellido_paterno}</td>
                <td>${user.apellido_materno || ''}</td>
                <td>${user.edad ?? 'N/A'}</td> <!-- Usamos ?? para manejar null/undefined -->
                <td>${user.correo}</td>
                <td class="actions">
                    <button class="btn-update" title="Editar usuario">
                        <i class='bx bx-edit'></i>
                    </button>
                    <button class="btn-delete" title="Eliminar usuario">
                        <i class='bx bx-trash'></i>
                    </button>
                </td>
            </tr>
        `).join('');
    },

    filtrarUsuarios: function (term) {
        const rows = document.querySelectorAll('#users-report-body tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    },

    calcularEdad: function (fechaNacimiento) {
        if (!fechaNacimiento) return 'N/A';

        const nacimiento = new Date(fechaNacimiento);
        const hoy = new Date();
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();

        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }

        return edad;
    },

    filtrarUsuarios: function (term) {
        const rows = document.querySelectorAll('#users-report-body tr');

        rows.forEach(row => {
            if (row.querySelector('.no-data')) return;

            const nombre = row.cells[0].textContent.toLowerCase();
            const apellidoP = row.cells[1].textContent.toLowerCase();
            const apellidoM = row.cells[2].textContent.toLowerCase();
            const correo = row.cells[4].textContent.toLowerCase();

            row.style.display = (nombre.includes(term) ||
                apellidoP.includes(term) ||
                apellidoM.includes(term) ||
                correo.includes(term))
                ? ''
                : 'none';
        });
    },
    mostrarFormularioEdicionUsuario: async function (userId) {
        try {
            console.log("Intentando mostrar formulario para usuario ID:", userId);

            const response = await fetch(`${APP_CONFIG.apiBase}/obtener-usuario.php?id=${userId}`);
            if (!response.ok) throw new Error('Error en la respuesta del servidor');

            const data = await response.json();
            console.log("Datos recibidos:", data);

            if (!data.success) {
                throw new Error(data.message || 'Error al cargar datos del usuario');
            }

            const modal = document.getElementById('modal-confirmacion');
            if (!modal) {
                throw new Error('No se encontró el modal en el DOM');
            }

            // Configurar el formulario
            modal.querySelector('#modal-title').textContent = 'Editar Usuario';
            modal.querySelector('#modal-mensaje').innerHTML = `
                <form id="form-editar-usuario">
                    <input type="hidden" id="edit-user-id" value="${data.data.id}">
                    
                    <div class="form-group">
                        <label for="edit-nombre">Nombre:</label>
                        <input type="text" id="edit-nombre" class="form-control" 
                               value="${data.data.nombre || ''}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-apellido-p">Apellido Paterno:</label>
                        <input type="text" id="edit-apellido-p" class="form-control" 
                               value="${data.data.apellido_paterno || ''}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-apellido-m">Apellido Materno:</label>
                        <input type="text" id="edit-apellido-m" class="form-control" 
                               value="${data.data.apellido_materno || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-edad">Edad:</label>
                        <input type="number" id="edit-edad" class="form-control"
                               value="${data.data.edad || ''}" min="18" max="120">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-correo">Correo:</label>
                        <input type="email" id="edit-correo" class="form-control" 
                               value="${data.data.correo || ''}" required>
                    </div>
                </form>
            `;

            // Configurar botón de guardar
            const btnGuardar = modal.querySelector('#modal-aceptar');
            btnGuardar.onclick = async () => {
                await this.guardarCambiosUsuario(data.data.id);
            };

            // Configurar cierre del modal
            const closeModal = () => {
                modal.style.display = 'none';
            };

            modal.querySelector('.close-modal').onclick = closeModal;
            modal.onclick = (e) => {
                if (e.target === modal) closeModal();
            };

            // Mostrar modal
            modal.style.display = 'block';
            console.log("Modal debería estar visible ahora");

        } catch (error) {
            console.error('Error al mostrar formulario:', error);
            this.showModal('Error', error.message);
        }
    },

    // Función para mostrar el formulario de edición
    mostrarFormularioEdicionUsuario: async function (userId) {
        try {
            const response = await fetch(`${APP_CONFIG.apiBase}/obtener-usuario.php?id=${userId}`);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al cargar datos del usuario');
            }

            const modal = document.getElementById('confirmModal');
            const modalContent = modal.querySelector('.modal-content');

            // Configurar el modal como formulario de edición
            modalContent.innerHTML = `
            <div class="modal-header">
                <h2>Editar Usuario</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="form-editar-usuario">
                    <input type="hidden" id="edit-user-id" value="${data.data.id}">
                    
                    <div class="form-group">
                        <label for="edit-nombre">Nombre:</label>
                        <input type="text" id="edit-nombre" class="form-control" 
                               value="${data.data.nombre || ''}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-apellido-p">Apellido Paterno:</label>
                        <input type="text" id="edit-apellido-p" class="form-control" 
                               value="${data.data.apellido_paterno || ''}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-apellido-m">Apellido Materno:</label>
                        <input type="text" id="edit-apellido-m" class="form-control" 
                               value="${data.data.apellido_materno || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-edad">Edad:</label>
                        <input type="number" id="edit-edad" class="form-control"
                               value="${data.data.edad || ''}" min="18" max="120">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-correo">Correo:</label>
                        <input type="email" id="edit-correo" class="form-control" 
                               value="${data.data.correo || ''}" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="modal-guardar" class="btn-primary">Guardar Cambios</button>
            </div>
        `;

            // Configurar evento de guardado
            modal.querySelector('#modal-guardar').onclick = async () => {
                await this.guardarCambiosUsuario(data.data.id);
            };

            // Configurar cierre del modal
            modal.querySelector('.close-modal').onclick = () => {
                modal.style.display = 'none';
            };

            // Mostrar modal
            modal.style.display = 'block';

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message);
        }
    },

    // Función para mostrar mensajes simples (sin botones)
    showModal: function (title, message) {
        const modal = document.getElementById('confirmModal');
        const modalContent = modal.querySelector('.modal-content');

        modalContent.innerHTML = `
        <div class="modal-header">
            <h2>${title}</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>${message}</p>
        </div>
    `;

        // Configurar cierre del modal
        modal.querySelector('.close-modal').onclick = () => {
            modal.style.display = 'none';
        };

        modal.style.display = 'block';
    },

    // Función para guardar cambios
    guardarCambiosUsuario: async function (userId) {
        const modal = document.getElementById('confirmModal');
        const btnGuardar = modal.querySelector('#modal-guardar');

        try {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="bx bx-loader bx-spin"></i> Guardando...';

            const userData = {
                id: userId,
                nombre: document.getElementById('edit-nombre').value.trim(),
                apellido_paterno: document.getElementById('edit-apellido-p').value.trim(),
                apellido_materno: document.getElementById('edit-apellido-m').value.trim() || null,
                edad: document.getElementById('edit-edad').value ? parseInt(document.getElementById('edit-edad').value) : null,
                correo: document.getElementById('edit-correo').value.trim()
            };

            // Validaciones (las que ya tenías)

            const response = await fetch(`${APP_CONFIG.apiBase}/editar-usuario.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Error al actualizar usuario');
            }

            // Cerrar modal de edición
            modal.style.display = 'none';

            // Mostrar mensaje de éxito (sin botones)
            this.showModal('Éxito', 'Usuario actualizado correctamente');

            // Actualizar lista
            await this.cargarUsuarios();

        } catch (error) {
            console.error('Error al guardar cambios:', error);
            this.showModal('Error', error.message);
        } finally {
            if (btnGuardar) {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = 'Guardar Cambios';
            }
        }
    },






    eliminarUsuario: async function (userId) {
        try {
            const confirmacion = await this.showConfirmModal(
                'Confirmar eliminación',
                '¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.',
                'warning'
            );

            if (!confirmacion) return;

            const response = await fetch(`${APP_CONFIG.apiBase}/eliminar-usuario.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al eliminar usuario');
            }

            await this.showModal('Éxito', 'Usuario eliminado correctamente', 'success');
            await this.cargarUsuarios();

        } catch (error) {
            console.error('Error:', error);
            this.showModal('Error', error.message, 'error');
        }
    },
    // REPORTES PROYECTOS ADMIN================================
    initReporteProyectosAdmin: function () {
        console.log("Inicializando reporte de proyectos admin");
        this.cargarProyectos();
        this.configurarBuscador();
        this.configurarEventosTabla();
    },
    cargarProyectos: async function () {
        try {
            const response = await fetch(`${APP_CONFIG.apiBase}/obtener-proyectos.php`);
            const data = await response.json();

            if (!data.success) throw new Error(data.message);

            this.renderizarProyectos(data.data);
        } catch (error) {
            console.error("Error cargando proyectos:", error);
            this.showModal('Error', 'No se pudieron cargar los proyectos');
        }
    },

    renderizarProyectos: function (proyectos) {
        const tbody = document.getElementById('proyectos-report-body');
        if (!tbody) return;

        tbody.innerHTML = proyectos.map(proyecto => `
        <tr data-proyecto-id="${proyecto.id}">
            <td>${proyecto.nombre}</td>
            <td>${proyecto.area}</td>
            <td>${proyecto.lider_nombre || 'Sin líder'}</td>
            <td>${this.formatearFecha(proyecto.fecha_inicio)}</td>
            <td>${this.formatearFecha(proyecto.fecha_fin)}</td>
            <td><span class="badge ${this.getEstadoClass(proyecto.estado)}">${proyecto.estado}</span></td>
            <td>
                <button class="btn-ver-cronograma" data-cronograma="${proyecto.id}" title="Ver cronograma">
                    <i class='bx bx-calendar'></i> Ver
                </button>
            </td>
            <td>
                ${proyecto.url_repositorio ?
                `<a href="${proyecto.url_repositorio}" target="_blank" title="Abrir repositorio">
                        <i class='bx bx-link-external'></i>
                    </a>` : 'No disponible'}
            </td>
            <td class="actions">
                <button class="btn-update" title="Editar proyecto">
                    <i class='bx bx-edit'></i>
                </button>
                <button class="btn-delete" title="Eliminar proyecto">
                    <i class='bx bx-trash'></i>
                </button>
            </td>
        </tr>
    `).join('');
    },

    formatearFecha: function (fecha) {
        if (!fecha) return 'No definida';
        return new Date(fecha).toLocaleDateString('es-ES');
    },

    getEstadoClass: function (estado) {
        const estados = {
            'Activo': 'success',
            'En pausa': 'warning',
            'Completado': 'primary',
            'Cancelado': 'danger'
        };
        return estados[estado] || 'secondary';
    },

    configurarBuscador: function () {
        const searchInput = document.getElementById('search-projects');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase();
                this.filtrarProyectos(term);
            });
        }
    },

    filtrarProyectos: function (term) {
        const rows = document.querySelectorAll('#proyectos-report-body tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    },

    configurarEventosTabla: function () {
        const table = document.getElementById('proyectos-report-table');
        if (!table) return;

        table.addEventListener('click', async (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            const row = btn.closest('tr');
            const proyectoId = row ? parseInt(row.dataset.proyectoId) : null;

            if (btn.classList.contains('btn-ver-cronograma') && proyectoId) {
                this.mostrarCronograma(proyectoId);
            } else if (btn.classList.contains('btn-update')) {
                this.mostrarFormularioEdicionProyecto(proyectoId);
            } else if (btn.classList.contains('btn-delete')) {
                this.eliminarProyecto(proyectoId);
            }
        });
    },

    mostrarCronograma: async function (proyectoId) {
        const modal = document.getElementById('modal-cronograma');
        if (!modal) {
            console.error('Elemento modal no encontrado');
            return;
        }

        try {
            // 1. Mostrar loader
            modal.querySelector('#cronograma-content').innerHTML = `
                <div class="loader">
                    <i class='bx bx-loader-alt bx-spin'></i>
                    <p>Cargando diagrama de Gantt...</p>
                </div>
            `;

            // 2. Mostrar el modal
            modal.classList.add('activo-crono');

            // 3. Obtener datos
            const response = await fetch(`${APP_CONFIG.apiBase}/obtener-cronograma-admin.php?proyecto_id=${proyectoId}`);

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al obtener datos del cronograma');
            }

            // 4. Generar y mostrar el diagrama de Gantt
            modal.querySelector('#cronograma-content').innerHTML = this.generarDiagramaGanttHTML(
                data.data.proyecto,
                data.data.actividades
            );

            // 5. Configurar eventos de cierre
            this.configurarCierreModal(modal);

        } catch (error) {
            console.error('Error:', error);
            this.mostrarErrorEnModal(modal, error, proyectoId);
        }
    },

    // Función para generar el HTML del diagrama de Gantt
    generarDiagramaGanttHTML: function (proyecto, actividades) {
        const fechaInicio = new Date(proyecto.fecha_inicio);
        const fechaFin = new Date(proyecto.fecha_fin);
        const hoy = new Date();

        // Calcular días totales del proyecto
        const diasTotales = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24)) + 1;

        // Generar escala de tiempo
        let escalaHTML = '';
        for (let i = 0; i < diasTotales; i++) {
            const fecha = new Date(fechaInicio);
            fecha.setDate(fecha.getDate() + i);
            escalaHTML += `<div class="gantt-scale-day">${fecha.getDate()}/${fecha.getMonth() + 1}</div>`;
        }

        // Generar barras de actividades
        let actividadesHTML = '';
        actividades.forEach(actividad => {
            const inicio = new Date(actividad.fecha_inicio);
            const fin = new Date(actividad.fecha_fin);

            const diaInicio = Math.ceil((inicio - fechaInicio) / (1000 * 60 * 60 * 24));
            const duracion = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;

            const left = (diaInicio / diasTotales) * 100;
            const width = (duracion / diasTotales) * 100;

            // Determinar estado de la actividad
            let estadoClase = '';
            if (fin < hoy) estadoClase = 'completada';
            else if (inicio <= hoy && fin >= hoy) estadoClase = 'en-progreso';

            actividadesHTML += `
                <div class="gantt-row">
                    <div class="gantt-activity-name" title="${actividad.descripcion || 'Sin descripción'}">
                        ${actividad.nombre}
                        <div class="activity-meta">
                            <span>${actividad.horas_estimadas} hrs</span>
                        </div>
                    </div>
                    <div class="gantt-timeline">
                        <div class="gantt-bar ${estadoClase}" 
                             style="left: ${left}%; width: ${width}%;"
                             title="${actividad.nombre}\n${this.formatearFecha(actividad.fecha_inicio)} - ${this.formatearFecha(actividad.fecha_fin)}\n${actividad.horas_estimadas} horas">
                        </div>
                    </div>
                </div>
            `;
        });

        // Calcular posición del indicador "Hoy"
        const diaHoy = Math.ceil((hoy - fechaInicio) / (1000 * 60 * 60 * 24));
        const posicionHoy = (diaHoy / diasTotales) * 100;

        return `
            <div class="proyecto-header">
                <h3>${proyecto.nombre}</h3>
                <div class="proyecto-meta">
                    <span><i class='bx bx-user'></i> ${proyecto.lider_nombre}</span>
                    <span class="badge ${this.getEstadoClass(proyecto.estado)}">
                        <i class='bx bx-calendar'></i> ${proyecto.estado}
                    </span>
                    <span><i class='bx bx-time'></i> ${this.formatearFecha(proyecto.fecha_inicio)} - ${this.formatearFecha(proyecto.fecha_fin)}</span>
                </div>
            </div>
            
            <div class="gantt-container">
                <div class="gantt-header">
                    <div class="gantt-activity-name">Actividades</div>
                    <div class="gantt-scale">${escalaHTML}</div>
                </div>
                
                ${actividades.length > 0 ? actividadesHTML : `
                    <div class="gantt-row">
                        <div class="no-activities">No hay actividades registradas</div>
                    </div>
                `}
                
                <div class="gantt-current-day" style="left: ${posicionHoy}%;"></div>
            </div>
            
            <div class="gantt-legend">
                <div class="gantt-legend-item">
                    <div class="gantt-legend-color" style="background: #4f46e5;"></div>
                    <span>Pendiente</span>
                </div>
                <div class="gantt-legend-item">
                    <div class="gantt-legend-color" style="background: #10b981;"></div>
                    <span>Completada</span>
                </div>
                <div class="gantt-legend-item">
                    <div class="gantt-legend-color" style="background: #f59e0b;"></div>
                    <span>En progreso</span>
                </div>
                <div class="gantt-legend-item">
                    <div class="gantt-legend-color" style="background: #dc2626;"></div>
                    <span>Hoy</span>
                </div>
            </div>
        `;
    },

    // Función auxiliar para configurar el cierre del modal
    configurarCierreModal: function (modal) {
        modal.querySelector('.cerrar-crono').onclick = () => {
            modal.classList.remove('activo-crono');
        };

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('activo-crono');
            }
        });
    },

    // Función auxiliar para mostrar errores
    mostrarErrorEnModal: function (modal, error, proyectoId) {
        modal.querySelector('#cronograma-content').innerHTML = `
            <div class="error-message">
                <i class='bx bx-error-circle'></i>
                <h4>Error al cargar el diagrama</h4>
                <p>${error.message}</p>
                <button class="btn-retry" onclick="app.mostrarCronograma(${proyectoId})">
                    <i class='bx bx-refresh'></i> Reintentar
                </button>
            </div>
        `;
    },

    // Función para formatear fechas (ya existente en tu código)
    formatearFecha: function (fecha) {
        if (!fecha) return 'No definida';
        const opciones = { day: '2-digit', month: '2-digit', year: 'numeric' };
        return new Date(fecha).toLocaleDateString('es-ES', opciones);
    },

    mostrarFormularioEdicionProyecto: async function (proyectoId) {
        // Similar a mostrarFormularioEdicionUsuario pero para proyectos
        // Implementar según tus necesidades
    },

    eliminarProyecto: async function (proyectoId) {
        try {
            const confirmacion = await this.showConfirmModal(
                'Confirmar eliminación',
                '¿Estás seguro de eliminar este proyecto? Esta acción no se puede deshacer.'
            );

            if (!confirmacion) return;

            const response = await fetch(`${APP_CONFIG.apiBase}/eliminar-proyecto.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: proyectoId })
            });

            const data = await response.json();

            if (!data.success) throw new Error(data.message);

            this.showModal('Éxito', 'Proyecto eliminado correctamente');
            await this.cargarProyectos();
        } catch (error) {
            console.error('Error eliminando proyecto:', error);
            this.showModal('Error', error.message);
        }
    },

    //REPORTE-DETALLE MENUUSERS PROYECTO EN USUARIO=============================================
initReporteProyectoUsuario: function() {
    this.cargarProyectoUsuario();
},

cargarProyectoUsuario: async function() {
    try {
        const tableBody = document.querySelector("#mi-proyecto-body");
        tableBody.innerHTML = this.getLoaderHTML('Cargando detalles del proyecto...');

        const response = await fetch(`${APP_CONFIG.apiBase}/reporte-usuario-proyecto.php`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Error al cargar la información del proyecto');
        }

        if (!data.data) {
            tableBody.innerHTML = `<tr><td colspan="10" class="text-center">No se encontró información del proyecto.</td></tr>`;
            return;
        }

        const proyecto = data.data; // 'data' ahora debería ser un solo objeto de proyecto

        const row = `
            <tr>
                <td>${proyecto.nombre}</td>
                <td>${proyecto.area || '-'}</td>
                <td>${proyecto.descripcion || '-'}</td>
                <td>${proyecto.objetivos || '-'}</td>
                <td>${proyecto.fecha_inicio}</td>
                <td>${proyecto.fecha_fin}</td>
                <td class="${this.getClassPorEstado(proyecto.estado)}">${proyecto.estado}</td>
                <td>
                    <button class="btn-ver-cronograma" data-id="${proyecto.id}">
                        Ver
                    </button>
                </td>
                <td><a href="${proyecto.url_repositorio || '#'}" target="_blank">${proyecto.url_repositorio ? 'Ver' : '-'}</a></td>
                <td>${proyecto.plataforma_repositorio || '-'}</td>
            </tr>
        `;
        tableBody.innerHTML = row;

        this.configurarEventosCronograma(); // Asegúrate de que esta función esté definida para manejar los clics en "Ver"

    } catch (error) {
        console.error('Error al cargar información del proyecto:', error);
        this.mostrarErrorEnContenedor( // Asegúrate de que mostrarErrorEnContenedor esté definida
            'tabla-mi-proyecto', // O el contenedor padre de la tabla
            'Error al cargar la información del proyecto: ' + error.message
        );
    }
},

configurarEventosCronograma: function() {
    const botonesVerCronograma = document.querySelectorAll('.btn-ver-cronograma');
    botonesVerCronograma.forEach(boton => {
        boton.addEventListener('click', (event) => {
            const proyectoId = event.target.dataset.id;
            this.mostrarCronograma(proyectoId);
        });
    });

    const modalCronograma = document.getElementById('modal-cronograma');
    const cerrarModalBtn = modalCronograma.querySelector('#cerrar-modal');
    if (cerrarModalBtn) {
        cerrarModalBtn.addEventListener('click', () => {
            modalCronograma.style.display = 'none';
        });
    }

    modalCronograma.addEventListener('click', (event) => {
        if (event.target === modalCronograma) {
            modalCronograma.style.display = 'none';
        }
    });
},

// mostrarCronograma: async function (proyectoId) {
//     console.log('Cargando cronograma para proyecto ID:', proyectoId);

//     const modalCronograma = document.getElementById('modal-cronograma');
//     const cronogramaContent = document.getElementById('cronograma-content');

//     if (!modalCronograma || !cronogramaContent) {
//         console.error('Modal o contenido del cronograma no existen en el DOM');
//         return;
//     }

//     cronogramaContent.innerHTML = this.getLoaderHTML('Cargando cronograma...');
//     modalCronograma.style.display = 'flex';
//     modalCronograma.classList.add('activo-crono');  // Asegurarse de agregar la clase activa

//     try {
//         const response = await fetch(`${APP_CONFIG.apiBase}/obtener-actividades-proyecto.php?id=${proyectoId}`);
//         console.log('Respuesta recibida:', response);

//         if (!response.ok) throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);

//         const data = await response.json();
//         console.log('Datos JSON recibidos:', data);

//         if (!data || !data.success) {
//             cronogramaContent.innerHTML = `<div class="alert alert-danger">${data?.message || 'No se encontraron actividades para este proyecto.'}</div>`;
//             return;
//         }

//         const actividades = data?.data?.actividades || [];
//         if (actividades.length === 0) {
//             cronogramaContent.innerHTML = `<div class="alert alert-warning">No hay actividades disponibles para este proyecto.</div>`;
//             return;
//         }

//         cronogramaContent.innerHTML = `<div id="gantt_chart" style="width: 100%; height: 400px;"></div>`;
//         this.dibujarGantt(actividades);
//     } catch (error) {
//         console.error('Error al cargar el cronograma:', error);
//         cronogramaContent.innerHTML = `<div class="alert alert-danger">Error al cargar el cronograma: ${error.message}</div>`;
//     }
// },




dibujarGantt: function(actividades) {
    if (!actividades || actividades.length === 0) {
        document.getElementById('gantt_chart').innerHTML = '<p>No hay actividades para mostrar en el cronograma.</p>';
        return;
    }

    // Convertir actividades al formato que espera Frappe Gantt
    const tasks = data.map(actividad => ({
        id: actividad.nombre, // Usa un identificador único
        name: actividad.nombre,
        start: actividad.fecha_inicio, // Fecha de inicio en formato 'YYYY-MM-DD'
        end: actividad.fecha_fin, // Fecha de fin en formato 'YYYY-MM-DD'
        progress: 0 // Puedes añadir un progreso si es necesario
    }));

    // Limpiar el contenedor y crear nuevo gráfico Gantt
    const contenedor = document.getElementById('gantt_chart');
    contenedor.innerHTML = ''; // Limpia el div

    // Crear gráfico con Frappe Gantt
    const gantt = new Gantt("#gantt_chart", tasks);
    new Gantt("#gantt_chart", tareas, {
        view_mode: 'Day',  // O 'Month', 'Week', 'Quarter', según lo que prefieras
        language: 'es',    // Asegúrate de que esté en español
    });
},



getLoaderHTML: function(mensaje = 'Cargando...') {
    return `
        <div class="loader">
            <i class='bx bx-loader-alt bx-spin'></i>
            <p>${mensaje}</p>
        </div>
    `;
},


getClassPorEstado: function(estado) {
    const estados = {
        'En progreso': 'status-active',
        'Pendiente': 'status-pending',
        'Completado': 'status-completed'
    };
    return estados[estado] || '';
},

mostrarErrorEnContenedor: function(containerId, mensaje) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class='bx bx-error-alt'></i>
                ${mensaje}
            </div>
        `;
    } else {
        console.error('Contenedor no encontrado:', containerId);
        this.showModal('Error', mensaje); // Si tienes una función showModal global
    }
},

generarHTMLCronograma: function(actividades) {
    if (!actividades || actividades.length === 0) {
        return '<p>No hay actividades para mostrar en el cronograma.</p>';
    }
    let html = '<ul>';
    actividades.forEach(actividad => {
        html += `<li>${actividad.nombre}: ${actividad.fecha_inicio} - ${actividad.fecha_fin} (${actividad.estado})</li>`;
    });
    html += '</ul>';
    return html;
},


    // UTILITARIOS =========================================
    showErrorView: function (viewId, error) {
        const viewContainer = document.getElementById(viewId);
        if (viewContainer) {
            viewContainer.innerHTML = `
                <div class="error-view">
                    <h3>Error al cargar la vista</h3>
                    <p>${error.message}</p>
                    <button onclick="App.loadView('${viewId}')">Reintentar</button>
                </div>
            `;
            viewContainer.style.display = 'block';
        }
    },

    updateActiveMenu: function (viewId) {
        document.querySelectorAll('[data-view]').forEach(link => {
            link.classList.remove('active');
        });

        const activeLink = document.querySelector(`[data-view="${viewId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    },

    showModal: function (title, message, type = 'success') {
        const modal = document.getElementById('modal-confirmacion');
        if (!modal) {
            console.error('Modal no encontrado');
            return;
        }

        const modalTitle = modal.querySelector('#modal-title');
        const modalMessage = modal.querySelector('#modal-mensaje');

        if (modalTitle && modalMessage) {
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modalMessage.style.color = type === 'success' ? '#4c2252' : '#e74c3c';
            modal.style.display = 'block';
        }
    },

    showConfirmModal: function (title, message, type = 'warning') {
        return new Promise((resolve) => {
            try {
                const modal = document.getElementById('modal-confirmacion');
                if (!modal) {
                    console.error('Modal no encontrado en el DOM');
                    return resolve(false);
                }

                // Obtener elementos con verificación de nulidad
                const modalTitle = modal.querySelector('#modal-title');
                const modalMessage = modal.querySelector('#modal-mensaje');
                const btnAceptar = modal.querySelector('#modal-aceptar');
                const closeBtn = modal.querySelector('.close-modal');

                if (!modalTitle || !modalMessage || !btnAceptar) {
                    console.error('Elementos del modal no encontrados');
                    return resolve(false);
                }

                // Configurar contenido
                modalTitle.textContent = title;
                modalMessage.textContent = message;

                // Configurar clases
                modal.className = 'modal';
                modal.classList.add(type);

                // Configurar botón de aceptar
                btnAceptar.style.display = 'block';
                btnAceptar.textContent = 'Confirmar';

                // Limpiar eventos anteriores
                btnAceptar.replaceWith(btnAceptar.cloneNode(true));
                const newAcceptBtn = modal.querySelector('#modal-aceptar');

                newAcceptBtn.onclick = () => {
                    modal.style.display = 'none';
                    resolve(true);
                };

                // Configurar cierre del modal
                const closeModal = () => {
                    modal.style.display = 'none';
                    resolve(false);
                };

                // Configurar botón de cerrar si existe
                if (closeBtn) {
                    closeBtn.replaceWith(closeBtn.cloneNode(true));
                    modal.querySelector('.close-modal').onclick = closeModal;
                }

                // Cerrar al hacer clic fuera del contenido
                modal.onclick = (e) => {
                    if (e.target === modal) closeModal();
                };

                // Mostrar modal
                modal.style.display = 'block';

            } catch (error) {
                console.error('Error en showConfirmModal:', error);
                resolve(false);
            }
        });
    },

    setupModalHandlers: function () {
        const modal = document.getElementById('modal-confirmacion');
        if (!modal) return;

        modal.querySelector('.close-modal')?.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        document.getElementById('modal-aceptar')?.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    },

    showLoader: function () {
        const loader = document.createElement('div');
        loader.id = 'fullscreen-loader';
        loader.innerHTML = `
            <div class="loader-content">
                <div class="loader-spinner"></div>
                <p>Cargando...</p>
            </div>
        `;
        document.body.appendChild(loader);
    },

    hideLoader: function () {
        const loader = document.getElementById('fullscreen-loader');
        if (loader) {
            loader.remove();
        }
    }
};

// Inicializar la aplicación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    App.init();
});

// Debug temporal
document.addEventListener('DOMContentLoaded', () => {
    console.log('Contenido de reporte-actividades:', document.getElementById('reporte-actividades')?.innerHTML);

    // Verificar si los estilos se aplican
    const styles = window.getComputedStyle(document.getElementById('reporte-actividades'));
    console.log('Estilos computados:', {
        display: styles.display,
        visibility: styles.visibility,
        opacity: styles.opacity,
        height: styles.height
    });

});

