<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teke'fritos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/estilos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/webp" href="data:image/webp;base64,<?php echo base64_encode(file_get_contents(__DIR__ . '/../img/Logo.webp')); ?>">
</head>

<body class="body-custom">

<!-- ==========================================
BARRA SUPERIOR Y NAVEGACION
========================================== -->
    <header class="barra-superior navbar-landing-unificada">
        <div class="logo-contenedor">
            <a href="index.php" class="logo-enlace">
                <img src="public/img/Logo.webp" alt="Teke'fritos" class="logo-imagen">
            </a>
        </div>

        <button class="hamburger" onclick="toggleMenu()" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>

        <div class="nav-mobile" id="navMobile">
            <nav class="navbar-links">
                <a href="index.php#historia" class="nav-link">Nosotros</a>
                <a href="index.php#productos" class="nav-link">Menu</a>
                <a href="index.php#pedidos" class="nav-link">Como Pedir</a>
                <a href="index.php#contacto" class="nav-link">Contacto</a>
            </nav>
            <div class="navbar-acciones">
            <?php if (Session::isLoggedIn()) { ?>
                <div class="usuario-info-landing">
                    <span class="nav-usuario">Hola, <strong><?php echo htmlspecialchars(Session::nombre()); ?></strong></span>
                    <a href="index.php?action=admin" class="nav-btn-adm">Panel</a>
                    <a href="index.php?action=logout" class="boton-salir">Salir</a>
                </div>
            <?php } else { ?>
                <div class="menu-inicio">
                    <div class="acceder-contenedor">
                        <a href="index.php?action=login" class="enlace-menu">Acceder</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="menu-backdrop" id="menuBackdrop" onclick="toggleMenu()"></div>
</header>

    <script>
    function toggleMenu() {
        const nav = document.getElementById('navMobile');
        const backdrop = document.getElementById('menuBackdrop');
        const icon = document.querySelector('.hamburger i');
        const abierto = nav.classList.toggle('open');
        backdrop.classList.toggle('visible');
        icon.classList.toggle('bi-list');
        icon.classList.toggle('bi-x');
        document.body.style.overflow = abierto ? 'hidden' : '';
    }
    </script>
