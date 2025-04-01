<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   <link rel="stylesheet" href="./css/bootstrap.css">
   <link rel="stylesheet" type="text/css" href="./css/style.css">
   <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
   <!-- <link rel="stylesheet" href="css/all.min.css"> -->
   <!-- <link rel="stylesheet" href="css/fontawesome.min.css"> -->
   <link href="https://tresplazas.com/web/img/big_punto_de_venta.png" rel="shortcut icon">
   <title>Inicio de sesión</title>
</head>

<body>
   <img class="wave" src="img/wave.png">
   <div class="container">
      <div class="img">
         <img src="./img/gestion.png">
      </div>
      <div class="login-content">
         <form method="post" action="login_process.php">
            <img  class="avatar" src="./img/avataruser.png">
            <h2 class="title">BIENVENIDO</h2>

            <!-- Mostrar mensajes de error -->
            <?php
            if (isset($_GET['error'])) {
                $error = $_GET['error'];
                $mensaje = "";

                switch ($error) {
                    case 'campos_vacios':
                        $mensaje = "Por favor, complete todos los campos.";
                        break;
                    case 'contrasena_incorrecta':
                        $mensaje = "Contraseña incorrecta.";
                        break;
                    case 'usuario_no_encontrado':
                        $mensaje = "Usuario no encontrado.";
                        break;
                    case 'error_servidor':
                        $mensaje = "Error en el servidor. Inténtelo de nuevo más tarde.";
                        break;
                    default:
                        $mensaje = "Error desconocido.";
                        break;
                }

                echo "<div style='color: red; text-align: center; margin-bottom: 15px;'>$mensaje</div>";
            }
            ?>

            <div class="input-div one">
               <div class="i">
                  <i class="fas fa-user"></i>
               </div>
               <div class="div">
                  <h5>Usuario</h5>
                  <input id="usuario" type="text" class="input" name="usuario">
               </div>
            </div>
            <div class="input-div pass">
               <div class="i">
                  <i class="fas fa-lock"></i>
               </div>
               <div class="div">
                  <h5>Contraseña</h5>
                  <input type="password" id="input" class="input" name="password">
               </div>
            </div>
            <div class="view">
               <div class="fas fa-eye verPassword" onclick="vista()" id="verPassword"></div>
            </div>

            <div class="text-center">
               <a class="font-italic isai5" href="">Olvidé mi contraseña</a>
               <a class="font-italic isai5" href="">Registrarse</a>
            </div>
            <input name="btningresar" class="btn" type="submit" value="INICIAR SESION">
         </form>
      </div>
   </div>
   <script src="./js/js/fontawesome.js"></script>
   <script src="./js/js/main.js"></script>
   <script src="./js/js/main2.js"></script>
   <script src="./js/js/jquery.min.js"></script>
   <script src="./js/js/bootstrap.js"></script>
 
  
  
   <script src="./js/bootstrap.bundle.js"></script>

</body>

</html>