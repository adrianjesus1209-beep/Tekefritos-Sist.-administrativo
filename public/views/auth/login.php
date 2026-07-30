<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="contenedor-principal seccion-autenticacion">
    <div class="tarjeta-acceso">
        
        <?php if (!empty($error)): ?>
            <div class="aviso aviso-error">
                <span class="aviso-cerrar" onclick="this.parentElement.style.display='none'">&times;</span>
                <div class="aviso-titulo">Acceso denegado</div>
                <div class="aviso-contenido">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="tarjeta">
            <div class="texto-centrado esp-bajo-2">
                <h2 class="titulo-primario titulo-mediano">Iniciar sesion</h2>
            </div>

            <form action="index.php?action=login" method="POST">
                <div class="campo-grupo">
                    <label for="correo" class="campo-etiqueta">Correo electronico</label>
                    <input type="text" id="correo" name="correo" value="<?php echo htmlspecialchars($correo ?? ''); ?>"
                        class="campo-entrada">
                </div>
                <div class="campo-grupo-grande">
                    <label for="clave" class="campo-etiqueta">Contrasena</label>
                    <div class="input-icono-wrapper">
                        <input type="password" id="clave" name="clave"
                            class="campo-entrada">
                        <button type="button" class="toggle-password" onclick="togglePassword('clave', this)" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="boton-estilo boton-oscuro boton-ancho-total">Ingresar</button>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>