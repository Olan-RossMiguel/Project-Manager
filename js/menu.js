document.addEventListener("DOMContentLoaded", () => {
    const botonesMenu = document.querySelectorAll(".boton-menu");
    const contenedorProductos = document.querySelector("#contenedor-productos");

    function cargarContenido(id) {
        let contenidoHTML = "";
        switch (id) {
            case "proyectos":
                contenidoHTML = `
                    <center><h2>Proyectos</h2></center>
                    <br>
                    <form class="formulario">
                        <label for="objetivo">Objetivo</label>
                        <input type="text" id="objetivo" required>

                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" rows="3" required></textarea>

                        <label for="estado">Estado del Proyecto</label>
                        <select id="estado">
                            <option value="pendiente">Pendiente</option>
                            <option value="en-progreso">En Progreso</option>
                            <option value="finalizado">Finalizado</option>
                        </select>

                        <label for="inicio">Fecha de Inicio</label>
                        <input type="date" id="inicio" required>

                        <label for="fin">Fecha de Finalización</label>
                        <input type="date" id="fin">

                        <button type="submit">Guardar Proyecto</button>
                    </form>
                `;
                break;
            case "actividades":
                contenidoHTML = `
                    <center><h2>Actividades</h2></center>
                    <br>
                    <form id="actividades-form" class="formulario">
                        <label for="num-actividades">Número de Actividades:</label>
                        <input type="number" id="num-actividades" min="1" required>
                        <button type="button" id="generar-actividades">Generar Actividades</button>
                        <div id="campos-actividades"></div>
                        <br>
                        <button type="submit">Guardar Actividades</button>
                    </form>
                `;
                break;
            case "equipos":
                contenidoHTML = `
                    <center><h2>Equipos</h2></center>
                    <br>
                    <form id="equipos-form" class="formulario">
                        <label for="num-integrantes">Cantidad de Integrantes:</label>
                        <input type="number" id="num-integrantes" min="1" required>
                        <button type="button" id="generar-integrantes">Generar Integrantes</button>
                        <div id="campos-integrantes"></div>
                        <br>
                        <button type="submit">Guardar Equipo</button>
                    </form>
                `;
                break;
        }
        contenedorProductos.innerHTML = contenidoHTML;

        if (id === "actividades") {
            const generarActividadesBtn = document.getElementById("generar-actividades");
            const camposActividadesDiv = document.getElementById("campos-actividades");
            const actividadesForm = document.getElementById("actividades-form");

            generarActividadesBtn.addEventListener("click", () => {
                const numActividades = document.getElementById("num-actividades").value;
                camposActividadesDiv.innerHTML = ""; // Limpiar campos anteriores

                for (let i = 1; i <= numActividades; i++) {
                    camposActividadesDiv.innerHTML += `
                        <h3>Actividad ${i}</h3>
                        <label for="nombre-actividad-${i}">Nombre de la Actividad</label>
                        <input type="text" id="nombre-actividad-${i}" required>

                        <label for="tipo-actividad-${i}">Tipo de Actividad</label>
                        <input type="text" id="tipo-actividad-${i}" required>

                        <label for="inicio-actividad-${i}">Fecha de Inicio</label>
                        <input type="date" id="inicio-actividad-${i}" required>

                        <label for="fin-actividad-${i}">Fecha de Finalización</label>
                        <input type="date" id="fin-actividad-${i}">

                        <label for="estado-actividad-${i}">Estado de la Actividad</label>
                        <select id="estado-actividad-${i}">
                            <option value="pendiente">Pendiente</option>
                            <option value="en-progreso">En Progreso</option>
                            <option value="finalizado">Finalizado</option>
                        </select>

                        <label for="descripcion-actividad-${i}">Descripción</label>
                        <textarea id="descripcion-actividad-${i}" rows="3" required></textarea>
                    `;
                }
            });

            actividadesForm.addEventListener("submit", (event) => {
                event.preventDefault();
                // Aquí puedes procesar los datos del formulario
                alert("Formulario de actividades enviado!");
            });
        }

        if (id === "equipos") {
            const generarIntegrantesBtn = document.getElementById("generar-integrantes");
            const camposIntegrantesDiv = document.getElementById("campos-integrantes");
            const equiposForm = document.getElementById("equipos-form");

            generarIntegrantesBtn.addEventListener("click", () => {
                const numIntegrantes = document.getElementById("num-integrantes").value;
                camposIntegrantesDiv.innerHTML = ""; // Limpiar campos anteriores

                for (let i = 1; i <= numIntegrantes; i++) {
                    camposIntegrantesDiv.innerHTML += `
                        <h3>Integrante ${i}</h3>
                        <label for="usuario-${i}">Usuario</label>
                        <input type="text" id="usuario-${i}" required>

                        <label for="rol-${i}">Rol</label>
                        <select id="rol-${i}">
                            <option value="Gerente de Proyecto">Gerente de Proyecto</option>
                            <option value="Scrum Master">Scrum Master</option>
                            <option value="Dueño del Producto">Dueño del Producto</option>
                            <option value="Arquitecto de Software">Arquitecto de Software</option>
                            <option value="Desarrollador de Software">Desarrollador de Software</option>
                            <option value="Ingeniero de Pruebas">Ingeniero de Pruebas</option>
                            <option value="Ingeniero DevOps">Ingeniero DevOps</option>
                            <option value="Analista de Negocio">Analista de Negocio</option>
                            <option value="Diseñador UX/UI">Diseñador UX/UI</option>
                        </select>

                        <label for="actividades-${i}">Actividades Asignadas</label>
                        <select id="actividades-${i}" multiple>
                            <option value="actividad1">Actividad 1</option>
                            <option value="actividad2">Actividad 2</option>
                            <option value="actividad3">Actividad 3</option>
                        </select>
                    `;
                }
            });

            equiposForm.addEventListener("submit", (event) => {
                event.preventDefault();
                // Aquí puedes procesar los datos del formulario
                alert("Formulario de equipos enviado!");
            });
        }
    }

    botonesMenu.forEach(boton => {
        boton.addEventListener("click", () => {
            botonesMenu.forEach(btn => btn.classList.remove("active"));
            boton.classList.add("active");
            cargarContenido(boton.id);
        });
    });

    // Marcar el botón de "Proyectos" como activo al cargar la página
    const botonProyectos = document.getElementById("proyectos");
    botonProyectos.classList.add("active");
    cargarContenido("proyectos");
});

// Función para mostrar/ocultar el menú desplegable del perfil
function toggleDropdown() {
    const dropdown = document.getElementById("profileDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

// Cerrar el menú desplegable si se hace clic fuera de él
window.onclick = function(event) {
    if (!event.target.matches('.user-icon')) {
        const dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
            const openDropdown = dropdowns[i];
            if (openDropdown.style.display === "block") {
                openDropdown.style.display = "none";
            }
        }
    }
}