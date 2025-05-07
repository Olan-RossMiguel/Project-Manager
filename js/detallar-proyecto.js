// Ya no necesitamos DOMContentLoaded porque el script se ejecutará después de cargar el HTML

/**
 * Función principal para inicializar el formulario de detalle de proyecto
 * @param {string} proyectoId - ID del proyecto a detallar
 */
export function inicializarFormulario(proyectoId) {
    const form = document.getElementById('form-detallar-proyecto');
    if (!form) {
        console.error('No se encontró el formulario');
        return;
    }

    // Configurar el formulario con el proyectoId
    form.querySelector('#proyecto-id').value = proyectoId;

    // Cargar datos del proyecto
    cargarDatosProyecto(proyectoId)
        .then(proyecto => {
            mostrarDatosProyecto(proyecto);
            configurarEnvioFormulario(form, proyectoId);
        })
        .catch(error => {
            console.error('Error:', error);
            showModal('Error', 'No se pudieron cargar los datos del proyecto', 'error');
        });
}

/**
 * Carga los datos del proyecto desde la API
 */
async function cargarDatosProyecto(proyectoId) {
    const response = await fetch(`/Project-Manager/api/obtener-proyecto.php?id=${proyectoId}`);
    if (!response.ok) throw new Error('Error al cargar proyecto');
    return await response.json();
}

/**
 * Muestra los datos del proyecto en el formulario
 */
function mostrarDatosProyecto(proyecto) {
    // Mostrar información básica
    document.getElementById('proyecto-nombre').textContent = proyecto.nombre || 'Sin nombre';
    document.getElementById('proyecto-area').textContent = proyecto.area || 'Sin área asignada';
    document.getElementById('proyecto-estado').textContent = proyecto.estado || 'Por iniciar';

    // Rellenar campos del formulario
    const campos = ['descripcion', 'objetivos', 'fecha_inicio', 'fecha_fin', 'estado', 
                   'url_repositorio', 'plataforma_repositorio'];
    
    campos.forEach(campo => {
        const element = document.getElementById(campo);
        if (!element) return;
        
        if (proyecto[campo]) {
            element.value = proyecto[campo];
            
            // Bloquear campos ya completados (excepto estado)
            if (campo !== 'estado') {
                element.classList.add('readonly');
                element.readOnly = true;
                element.disabled = true;
                element.title = 'Este campo ya fue completado';
            }
        }
    });
}

/**
 * Configura el envío del formulario
 */
function configurarEnvioFormulario(form, proyectoId) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const btnSubmit = form.querySelector('button[type="submit"]');
        const originalText = btnSubmit.textContent;
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Guardando...';
        
        try {
            const formData = {
                id: proyectoId,
                estado: form.estado.value // Estado siempre editable
            };
            
            // Recoger solo campos editables (no readonly)
            document.querySelectorAll('.editable-field:not(.readonly)').forEach(el => {
                if (el.value) formData[el.name] = el.value;
            });
            
            const response = await fetch('/Project-Manager/api/actualizar-proyecto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (!response.ok) throw new Error(result.message || 'Error al actualizar');
            
            showModal('Éxito', 'Proyecto actualizado correctamente', 'success', {
                onAccept: () => location.reload() // Recargar para ver cambios
            });
            
        } catch (error) {
            showModal('Error', error.message, 'error');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = originalText;
        }
    });
}

// Función para mostrar modal (debe coincidir con tu implementación existente)
function showModal(title, message, type = 'success', options = {}) {
    const modal = document.getElementById('modal-confirmacion');
    if (!modal) {
        alert(`${title}: ${message}`);
        return;
    }
    
    const modalTitle = modal.querySelector('#modal-title');
    const modalMessage = modal.querySelector('#modal-mensaje');
    const acceptBtn = modal.querySelector('#modal-aceptar');
    
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    modalMessage.style.color = type === 'success' ? '#4c2252' : 
                             type === 'error' ? '#e74c3c' : '#333';
    
    acceptBtn.style.display = type === 'success' ? 'block' : 'none';
    acceptBtn.onclick = () => {
        modal.style.display = 'none';
        if (options.onAccept) options.onAccept();
    };
    
    modal.style.display = 'block';
}


function bloquearCamposCompletados(proyecto) {
    const campos = ['descripcion', 'objetivos', 'fecha_inicio', 'fecha_fin', 'url_repositorio', 'plataforma_repositorio'];
    
    campos.forEach(campo => {
        const element = document.getElementById(campo);
        if (element && proyecto[campo]) {
            element.readOnly = true;
            element.disabled = true;
            element.classList.add('readonly');
        }
    });
}

function mostrarInformacionBasica(proyecto) {
    document.getElementById('proyecto-nombre-display').textContent = proyecto.nombre || 'No especificado';
    document.getElementById('proyecto-area-display').textContent = proyecto.area || 'No especificado';
    
    // Aquí deberías cargar el nombre del líder desde tu API
    document.getElementById('proyecto-lider-display').textContent = 'Cargando...';
}