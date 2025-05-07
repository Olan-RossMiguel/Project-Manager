document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-crear-usuario');
    
    if (!form) {
        console.error("No se encontró el formulario con ID 'form-crear-usuario'");
        return;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log("Formulario enviado");

        // Validación básica
        if (!validateForm(form)) {
            console.log("Validación fallida");
            return;
        }

        try {
            // Crear objeto con los datos del formulario
            const formData = {
                nombre: form.nombre.value,
                apellido_paterno: form.apellido_paterno.value,
                apellido_materno: form.apellido_materno?.value || '', // Opcional
                edad: form.edad.value,
                correo: form.correo.value,
                contrasena: form.contrasena.value,
                rol: form.rol.value
            };

            console.log("Datos a enviar:", formData);

            const url = 'http://localhost:50/Project-Manager/api/usuarios.php';
            console.log("URL de destino:", url);

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            console.log("Respuesta recibida, status:", response.status);

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(errorText || `Error HTTP: ${response.status}`);
            }

            const result = await response.json();
            console.log("Resultado completo:", result);
            
            showModal('Éxito', 'Usuario creado correctamente');
            form.reset();
        } catch (error) {
            console.error('Error completo:', error);
            showModal('Error', error.message || 'Error al comunicarse con el servidor', 'error');
        }
    });
});