

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--=============== REMIXICONS ===============-->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet">
    <!--=============== CSS ===============-->
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>Responsive dropdown menu - Bedimcode</title>
</head>
<body>
    <!--=============== HEADER ===============-->
    <header class="header">
        <nav class="nav container">
            <div class="nav__data">
                <a href="#" class="nav__logo">
                    <i class="ri-pie-chart-fill"></i> PROJECT MANAGER
                </a>
                <div class="nav__toggle" id="nav-toggle">
                    <i class="ri-menu-line nav__burger"></i>
                    <i class="ri-close-line nav__close"></i>
                </div>
            </div>

            <!--=============== NAV MENU ===============-->
            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li><a href="#" class="nav__link">Inicio</a></li>

                    <li class="dropdown__item">
                        <div class="nav__link">
                            Tablas <i class="ri-arrow-down-s-line dropdown__arrow"></i>
                        </div>
                        <ul class="dropdown__menu">
                            <li>
                                <a href="#" class="dropdown__link">
                                    <i class=" ri-file-user-line"></i> Usuarios
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown__link">
                                    <i class="ri-folders-line"></i> Proyectos
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown__link" style="white-space: nowrap;">
                                    <i class="ri-folder-user-line"></i> Usuarios por Proyecto
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown__link" style="white-space: nowrap;">
                                    <i class="ri-task-line"></i> Actividades por Proyecto
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown__link">
                                    <i class=" ri-file-paper-2-line"></i> Roles
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!--=============== DROPDOWN 1 ===============-->
                    <li class="dropdown__item">
                        <div class="nav__link">
                            Reportes <i class="ri-arrow-down-s-line dropdown__arrow"></i>
                        </div>
                        <ul class="dropdown__menu">
                            <li>
                                <a href="#" class="dropdown__link">
                                    <i class="ri-pie-chart-line"></i> Proyectos
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown__link">
                                    <i class="ri-pie-chart-line"></i> Overview
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!--=============== DROPDOWN 2 ===============-->
                    <li class="dropdown__item">
                        <div class="nav__link">
                            Usuario <i class="ri-arrow-down-s-line dropdown__arrow"></i>
                        </div>
                        <ul class="dropdown__menu">
                            <li>
                                <a href="perfil.php" class="dropdown__link">
                                    <i class="ri-user-line"></i> Perfil
                                </a>
                            </li>
                            <li>
                                <a href="logout.php" class="dropdown__link">
                                    <i class="ri-logout-box-line"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <!--=============== MAIN JS ===============-->
    <script src="assets/js/main.js"></script>
</body>
</html>