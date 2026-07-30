<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teke'fritos — Sabor venezolano en un solo lugar</title>
    <link rel="stylesheet" href="public/css/estilos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/webp" href="data:image/webp;base64,<?php echo base64_encode(file_get_contents(__DIR__ . '/../../img/Logo.webp')); ?>">
</head>

<body class="body-landing">

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
            <a href="#historia" class="nav-link">Nosotros</a>
            <a href="#productos" class="nav-link">Menu</a>
            <a href="#pedidos" class="nav-link">Como Pedir</a>
            <a href="#contacto" class="nav-link">Contacto</a>
        </nav>
        <div class="navbar-acciones">
            <?php if (Session::isLoggedIn()): ?>
                <div class="usuario-info-landing">
                    <span class="nav-usuario">Hola, <strong><?php echo htmlspecialchars(Session::nombre()); ?></strong></span>
                    <a href="index.php?action=admin" class="nav-btn-adm">Panel</a>
                    <a href="index.php?action=logout" class="boton-salir">Salir</a>
                </div>
            <?php else: ?>
                <div class="menu-inicio">
                    <div class="acceder-contenedor">
                        <a href="index.php?action=login" class="enlace-menu">Acceder</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="menu-backdrop" id="menuBackdrop" onclick="toggleMenu()"></div>
</header>

<section id="inicio" class="hero-seccion">
    <video autoplay muted loop playsinline class="hero-video">
        <source src="public/video/fondo.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-contenido">
        <h1 class="hero-titulo">Bienvenidos a <span>Teke'fritos</span></h1>
        <p class="hero-subtitulo">La mejor masa artesanal para tus comidas.</p>
        <div class="hero-botones">
            <a href="#productos" class="hero-boton">Explorar Menu</a>
            <a href="#historia" class="hero-boton-secundario">Conocenos</a>
        </div>
    </div>
</section>

<section id="historia" class="seccion-institucional">
    <div class="contenedor-institucional grid-2">
        <div class="texto-col">
            <span class="subtitulo-seccion">Artesania y Sabor</span>
            <h2 class="titulo-seccion">Nuestra <span>Historia</span></h2>
            <p class="parrafo-seccion">
                Teke'fritos nacio de la pasion por los sabores tradicionales venezolanos. Lo que comenzo en una cocina familiar, hoy es el punto de referencia para quienes buscan la masa perfecta: crujiente, dorada y con ese toque artesanal que solo las manos expertas pueden lograr.
            </p>
            <p class="parrafo-seccion">
                Cada uno de nuestros productos es elaborado con ingredientes seleccionados, respetando las recetas de antano pero con el dinamismo que el paladar actual exige.
            </p>
        </div>
        <div class="imagen-col">
            <img src="public/img/historia.png" alt="Preparacion artesanal" class="imagen-decorativa">
        </div>
    </div>
</section>

<section class="seccion-institucional fondo-gris">
    <div class="contenedor-institucional">
        <div class="texto-centrado esp-bajo-2">
            <h2 class="titulo-seccion">Nuestra <span>Propuesta</span></h2>
            <p class="subtitulo-ancho">De nuestra cocina a tu mesa, garantizamos una experiencia inigualable.</p>
        </div>
        <div class="grid-3">
            <div class="tarjeta-valor">
                <div class="icono-valor">
                    <i class="bi bi-award-fill"></i>
                </div>
                <h3 class="tarjeta-titulo">Sabor Tradicional</h3>
                <p class="tarjeta-desc">Mantenemos la esencia de la receta venezolana original en cada bocado.</p>
            </div>
            <div class="tarjeta-valor">
                <div class="icono-valor">
                    <i class="bi bi-fire"></i>
                </div>
                <h3 class="tarjeta-titulo">Masa Siempre Fresca</h3>
                <p class="tarjeta-desc">Nuestras masas se elaboran diariamente para garantizar la textura perfecta.</p>
            </div>
            <div class="tarjeta-valor">
                <div class="icono-valor">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <h3 class="tarjeta-titulo">Rapidez y Calidad</h3>
                <p class="tarjeta-desc">Entregas puntuales con productos que mantienen su temperatura y frescura.</p>
            </div>
        </div>
    </div>
</section>

<section id="productos" class="productos-seccion">
    <div class="productos-contenedor">
        <div class="texto-centrado esp-bajo-2">
            <span class="subtitulo-seccion">Para disfrutar</span>
            <h2 class="productos-titulo">Nuestro <span>Menu</span></h2>
        </div>
        <div class="productos-grid" id="productosGrid">
            <?php
            $res_productos = Database::query("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.estado = 'Disponible' ORDER BY p.fecha_creacion DESC");
            $resultado = Database::getResult($res_productos);
            $total = 0;
            $productos = [];
            if ($resultado && mysqli_num_rows($resultado) > 0):
                while ($prod = mysqli_fetch_assoc($resultado)):
                    $productos[] = $prod;
                endwhile;
            endif;
            $total = count($productos);
            foreach ($productos as $i => $prod):
                $oculto = $i >= 6 ? 'producto-oculto' : '';
                $img_src = $prod['imagen'] && $prod['imagen'] !== 'placeholder.png'
                    ? 'public/uploads/productos/' . $prod['imagen']
                    : 'public/img/masa-tequenos.png';
            ?>
                <div class="producto-tarjeta <?php echo $oculto; ?>">
                    <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($prod['nombre']); ?>" class="producto-imagen" onerror="this.src='public/img/masa-tequenos.png'">
                    <div class="producto-cuerpo">
                        <h3 class="producto-nombre"><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                        <p class="producto-descripcion"><?php echo htmlspecialchars($prod['descripcion'] ?: 'Sabor artesanal garantizado.'); ?></p>
                        <p class="producto-precio">Bs. <?php echo number_format($prod['precio'], 2); ?> / <?php echo htmlspecialchars($prod['categoria_nombre'] ?? '') == 'Bebidas' ? 'unidad' : 'kg'; ?></p>
                    </div>
                </div>
            <?php endforeach; if ($total === 0): ?>
                <p class="texto-suave ocupa-2">Proximamente mas productos deliciosos...</p>
            <?php endif; ?>
        </div>
        <?php if ($total > 6): ?>
        <div class="productos-toggle">
            <button onclick="toggleProductos()" id="btnToggle">Mostrar más (<?php echo $total - 6; ?> productos)</button>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="seccion-institucional fondo-dinamico-verde">
    <div class="contenedor-institucional grid-2">
        <div class="imagen-col">
            <img src="public/img/calidad.png" alt="Ingredientes de calidad" class="imagen-decorativa">
        </div>
        <div class="texto-col">
            <span class="subtitulo-seccion">Compromiso</span>
            <h2 class="titulo-seccion">Pilares de <span>Calidad</span></h2>
            <ul class="lista-pilares">
                <li><i class="bi bi-check-circle-fill" style="color:var(--color-primario);margin-right:8px"></i><strong>Ingredientes Premium:</strong> Solo usamos harinas y quesos de primera calidad.</li>
                <li><i class="bi bi-check-circle-fill" style="color:var(--color-primario);margin-right:8px"></i><strong>Proceso Artesanal:</strong> Nada de procesos industriales masivos.</li>
                <li><i class="bi bi-check-circle-fill" style="color:var(--color-primario);margin-right:8px"></i><strong>Higiene Rigurosa:</strong> Cumplimos con los mas altos estandares de sanidad.</li>
                <li><i class="bi bi-check-circle-fill" style="color:var(--color-primario);margin-right:8px"></i><strong>Control de Sabor:</strong> Cada lote es probado para asegurar la excelencia.</li>
            </ul>
        </div>
    </div>
</section>

<section class="seccion-institucional" style="background:#f8fafc; text-align:center;">
    <div class="contenedor-institucional">
        <div class="texto-centrado esp-bajo-2">
            <span class="subtitulo-seccion">En plena accion</span>
            <h2 class="titulo-seccion">Así preparamos tus <span>Favoritos</span></h2>
            <p class="subtitulo-ancho">Un pequeño vistazo al trabajo artesanal que hay detras de cada producto que llega a tu mesa.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); max-width: 1200px; margin: 0 auto; gap: 20px;">
            <!-- Video 1 -->
            <div style="border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; background: #fff;">
                <div style="padding: 12px 15px; font-weight: 700; color: #1e293b; font-size: 1.1rem; border-bottom: 1px solid #e2e8f0;">
                    <i class="bi bi-play-circle text-danger me-2"></i>Masas de Tequeños
                </div>
                <video playsinline onclick="this.paused ? this.play() : this.pause(); this.setAttribute('controls', 'controls');" onmouseleave="this.removeAttribute('controls')" onmouseenter="this.setAttribute('controls', 'controls')" style="width: 100%; height: auto; display: block; aspect-ratio: 16/9; background: #000; cursor: pointer;">
                    <source src="public/video/video1.mp4" type="video/mp4">
                    Tu navegador no soporta el formato de video.
                </video>
            </div>
            
            <!-- Video 2 -->
            <div style="border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; background: #fff;">
                <div style="padding: 12px 15px; font-weight: 700; color: #1e293b; font-size: 1.1rem; border-bottom: 1px solid #e2e8f0;">
                    <i class="bi bi-play-circle text-danger me-2"></i>Masas de Pastelitos
                </div>
                <video playsinline onclick="this.paused ? this.play() : this.pause(); this.setAttribute('controls', 'controls');" onmouseleave="this.removeAttribute('controls')" onmouseenter="this.setAttribute('controls', 'controls')" style="width: 100%; height: auto; display: block; aspect-ratio: 16/9; background: #000; cursor: pointer;">
                    <source src="public/video/video2.mp4" type="video/mp4">
                    Tu navegador no soporta el formato de video.
                </video>
            </div>

            <!-- Video 3 -->
            <div style="border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; background: #fff;">
                <div style="padding: 12px 15px; font-weight: 700; color: #1e293b; font-size: 1.1rem; border-bottom: 1px solid #e2e8f0;">
                    <i class="bi bi-play-circle text-danger me-2"></i>Fritura y Preparados
                </div>
                <video playsinline onclick="this.paused ? this.play() : this.pause(); this.setAttribute('controls', 'controls');" onmouseleave="this.removeAttribute('controls')" onmouseenter="this.setAttribute('controls', 'controls')" style="width: 100%; height: auto; display: block; aspect-ratio: 16/9; background: #000; cursor: pointer;">
                    <source src="public/video/video3.mp4" type="video/mp4">
                    Tu navegador no soporta el formato de video.
                </video>
            </div>
        </div>
    </div>
</section>

<section id="pedidos" class="seccion-como-pedir">
    <div class="contenedor-institucional">
        <div class="texto-centrado esp-bajo-2">
            <span class="subtitulo-seccion">Paso a paso</span>
            <h2 class="titulo-seccion">Cómo <span>Pedir</span></h2>
            <p class="subtitulo-ancho">Disfrutar de Teke'fritos es así de sencillo.</p>
        </div>
        <div class="timeline-pasos">
            <div class="timeline-linea"></div>
            <div class="timeline-paso">
                <div class="timeline-icono">
                    <i class="bi bi-book-half"></i>
                </div>
                <div class="timeline-numero">1</div>
                <h4>Explora el Menú</h4>
                <p>Conoce nuestras masas, pastelitos y tequeños.</p>
            </div>
            <div class="timeline-paso">
                <div class="timeline-icono">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <div class="timeline-numero">2</div>
                <h4>Escríbenos</h4>
                <p>Envíanos tu pedido por WhatsApp.</p>
            </div>
            <div class="timeline-paso">
                <div class="timeline-icono">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="timeline-numero">3</div>
                <h4>Coordinamos</h4>
                <p>Acordamos fecha, hora y lugar de entrega.</p>
            </div>
        </div>
        <div class="cta-whatsapp">
            <?php
            $wa_cta_val = '#';
            $wa_cta_res = Database::query("SELECT valor FROM sistema_config WHERE clave = 'social_whatsapp'");
            if ($wa_cta_res) {
                $wa_datos = Database::getResult($wa_cta_res);
                if ($wa_datos && mysqli_num_rows($wa_datos) > 0) {
                    $fila = mysqli_fetch_assoc($wa_datos);
                    if (!empty(trim($fila['valor']))) {
                        $wa_cta_val = "https://wa.me/" . preg_replace('/[^0-9]/', '', $fila['valor']);
                    }
                }
            }
            ?>
            <a href="<?php echo htmlspecialchars($wa_cta_val); ?>" target="_blank" class="boton-whatsapp" <?php echo $wa_cta_val === '#' ? 'style="display:none;"' : ''; ?>>
                <i class="bi bi-whatsapp"></i> Pedir por WhatsApp
            </a>
        </div>
    </div>
</section>

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

function toggleProductos() {
    const grid = document.getElementById('productosGrid');
    const btn = document.getElementById('btnToggle');
    const todos = grid.querySelectorAll('.producto-tarjeta');
    const primerOculto = grid.querySelector('.producto-oculto');
    if (primerOculto) {
        todos.forEach(el => el.classList.remove('producto-oculto'));
        btn.textContent = 'Ocultar';
    } else {
        todos.forEach((el, i) => { if (i >= 6) el.classList.add('producto-oculto'); });
        btn.textContent = 'Mostrar más (<?php echo $total - 6; ?> productos)';
    }
}

// Evitar que dos videos manuales suenen al mismo tiempo
document.addEventListener('play', function(e) {
    if (e.target.tagName !== 'VIDEO') return;
    const videos = document.querySelectorAll('video');
    videos.forEach(function(vid) {
        // Pausamos los demas videos siempre y cuando no sean el hero-video del inicio (que tiene autoplay)
        if (vid !== e.target && !vid.hasAttribute('autoplay')) {
            vid.pause();
        }
    });
}, true);

</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
