<!-- ==========================================
SISTEMA TEKE'FRITOS — FOOTER UNIFICADO
========================================== -->
<footer id="contacto" class="footer-sitio">
    <div class="footer-contenedor">
        <div class="footer-grid">
            <!-- Branding -->
            <div class="footer-seccion footer-marca">
                <h3>Teke'<span>fritos</span></h3>
                <p>El sabor artesanal que te conecta con la tradicion venezolana. Calidad premium en cada masa, siempre fresca y lista para disfrutar.</p>
            </div>

            <!-- Enlaces Rapidos -->
            <div class="footer-seccion">
                <h4>Navegacion</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="index.php#productos">Productos</a></li>
                    <li><a href="index.php#contacto">Contacto</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div class="footer-seccion">
                <h4>Legal</h4>
                <ul class="footer-links">
                    <li><a href="index.php?action=politicas-privacidad" target="_blank">Politicas de Privacidad</a></li>
                    <li><a href="index.php?action=terminos-uso" target="_blank">Terminos de Uso</a></li>
                </ul>
            </div>

            <!-- Redes Sociales -->
            <div class="footer-seccion">
                <h4>Siguenos</h4>
                <?php
                // Obtener configuracion de las redes directamente de la BD (si se ha configurado)
                $redes_res = Database::query("SELECT clave, valor FROM sistema_config WHERE clave IN ('social_whatsapp', 'social_instagram', 'social_facebook', 'social_tiktok')");
                $r_footer = ['social_whatsapp' => '#', 'social_instagram' => '#', 'social_facebook' => '#', 'social_tiktok' => '#'];
                if ($redes_res) {
                    $r_datos = Database::getResult($redes_res);
                    if ($r_datos && mysqli_num_rows($r_datos) > 0) {
                        while($f_row = mysqli_fetch_assoc($r_datos)) {
                            if(!empty(trim($f_row['valor']))) {
                                $r_footer[$f_row['clave']] = trim($f_row['valor']);
                            }
                        }
                    }
                }
                
                // Formatear enlace de WhatsApp
                $wa_url = $r_footer['social_whatsapp'];
                if ($wa_url !== '#') {
                    $num_limpio = preg_replace('/[^0-9]/', '', $wa_url);
                    $wa_url = "https://wa.me/" . $num_limpio;
                }
                ?>
                <div class="footer-social">
                    <a href="<?php echo htmlspecialchars($r_footer['social_facebook']); ?>" class="social-link facebook" title="Facebook" <?php echo $r_footer['social_facebook'] === '#' ? '' : 'target="_blank"'; ?>><i class="bi bi-facebook"></i></a>
                    <a href="<?php echo htmlspecialchars($r_footer['social_instagram']); ?>" class="social-link instagram" title="Instagram" <?php echo $r_footer['social_instagram'] === '#' ? '' : 'target="_blank"'; ?>><i class="bi bi-instagram"></i></a>
                    <a href="<?php echo htmlspecialchars($wa_url); ?>" class="social-link whatsapp" title="WhatsApp" <?php echo $wa_url === '#' ? '' : 'target="_blank"'; ?>><i class="bi bi-whatsapp"></i></a>
                    <a href="<?php echo htmlspecialchars($r_footer['social_tiktok']); ?>" class="social-link tiktok" title="TikTok" <?php echo $r_footer['social_tiktok'] === '#' ? '' : 'target="_blank"'; ?>><i class="bi bi-tiktok"></i></a>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-inferior">
            <p class="footer-copy">&copy; <?php echo date('Y'); ?> Teke'fritos — Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<!-- Scripts Globales -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // Inicializar iconos de Lucide si estan presentes
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>

</body>
</html>
