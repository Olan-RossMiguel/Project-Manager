const CronogramaUsuario = {
    mostrar: function(proyectoId, actividades) {
        // Convertir string JSON a objeto si es necesario
        if (typeof actividades === 'string') {
            try {
                actividades = JSON.parse(decodeURIComponent(actividades));
            } catch (e) {
                console.error('Error al parsear actividades:', e);
                return;
            }
        }

        const modal = document.getElementById('modal-cronograma');
        if (!modal) {
            console.error('Modal no encontrado');
            return;
        }

        // Mostrar loader
        const content = modal.querySelector('#cronograma-content');
        if (content) {
            content.innerHTML = `
                <div class="loader">
                    <i class='bx bx-loader-alt bx-spin'></i>
                    <p>Generando cronograma...</p>
                </div>
            `;
        }

        // Mostrar modal
        modal.style.display = 'flex';

        try {
            // Formatear actividades para el gráfico
            const tasks = actividades.map(act => ({
                id: act.id.toString(),
                name: act.nombre || 'Actividad sin nombre',
                start: act.fecha_inicio || new Date().toISOString().split('T')[0],
                end: act.fecha_fin || new Date(Date.now() + 86400000).toISOString().split('T')[0],
                progress: act.progreso || 0,
                dependencies: act.dependencias || ''
            }));

            // Crear contenido del gráfico
            if (content) {
                content.innerHTML = `
                    <div id="gantt-chart-usuario" style="width: 100%; height: 400px;"></div>
                `;
            }

            // Inicializar gráfico
            if (window.Gantt && document.getElementById('gantt-chart-usuario')) {
                new Gantt("#gantt-chart-usuario", tasks, {
                    header_height: 50,
                    column_width: 30,
                    step: 24,
                    view_modes: ['Day', 'Week', 'Month'],
                    bar_height: 20,
                    bar_corner_radius: 3,
                    padding: 18,
                    view_mode: 'Week',
                    date_format: 'YYYY-MM-DD',
                    custom_popup_html: function(task) {
                        return `
                            <div class="gantt-tooltip">
                                <h5>${task.name}</h5>
                                <p><strong>Inicio:</strong> ${task.start}</p>
                                <p><strong>Fin:</strong> ${task.end}</p>
                                <p><strong>Progreso:</strong> ${task.progress}%</p>
                            </div>
                        `;
                    }
                });
            }

            // Configurar cierre del modal
            const closeBtn = document.getElementById('cerrar-modal-crono');
            if (closeBtn) {
                closeBtn.onclick = () => {
                    modal.style.display = 'none';
                };
            }

            modal.onclick = (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            };

        } catch (error) {
            console.error('Error al generar cronograma:', error);
            if (content) {
                content.innerHTML = `
                    <div class="error-message">
                        <i class='bx bx-error'></i>
                        <p>Error al generar el cronograma</p>
                        <button onclick="CronogramaUsuario.mostrar(${proyectoId}, '${encodeURIComponent(JSON.stringify(actividades))}')">
                            Reintentar
                        </button>
                    </div>
                `;
            }
        }
    }
};

// Hacer disponible globalmente
if (typeof window !== 'undefined') {
    window.CronogramaUsuario = CronogramaUsuario;
}