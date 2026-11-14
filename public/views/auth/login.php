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
                <a href="index.php" title="Teke'fritos" class="d-inline-block text-decoration-none">
                    <img src="public/img/Logo.webp" alt="Teke'fritos" class="logo-login" style="max-height: 85px; width: auto; margin: 0 auto 1.25rem; display: block; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.08));">
                </a>
                <h2 class="titulo-primario titulo-mediano">Iniciar sesion</h2>
            </div>

            <form action="index.php?action=login" method="POST" id="form-login" novalidate>
                <div class="campo-grupo">
                    <label for="correo" class="campo-etiqueta">Correo electronico</label>
                    <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo ?? ''); ?>"
                        class="campo-entrada" placeholder="ejemplo@tekefritos.com" required autocomplete="username">
                </div>
                <div class="campo-grupo">
                    <label for="clave" class="campo-etiqueta">Contrasena</label>
                    <div class="input-icono-wrapper">
                        <input type="password" id="clave" name="clave"
                            class="campo-entrada" placeholder="Ingresa tu contrasena" required autocomplete="current-password">
                        <button type="button" class="toggle-password" onclick="togglePassword('clave', this)" tabindex="-1" aria-label="Mostrar u ocultar contrasena">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="campo-grupo-grande">
                    <label for="verificar_clave" class="campo-etiqueta">Verificar contrasena</label>
                    <div class="input-icono-wrapper">
                        <input type="password" id="verificar_clave" name="verificar_clave"
                            class="campo-entrada" placeholder="Verificar contrasena" required autocomplete="new-password">
                        <button type="button" class="toggle-password" onclick="togglePassword('verificar_clave', this)" tabindex="-1" aria-label="Mostrar u ocultar verificacion de contrasena">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="mensaje_verificacion" class="mt-2 text-start" style="font-size: 0.85rem; display: none;"></div>
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

document.addEventListener('DOMContentLoaded', function() {
    const clave = document.getElementById('clave');
    const verificarClave = document.getElementById('verificar_clave');
    const mensaje = document.getElementById('mensaje_verificacion');
    const form = document.getElementById('form-login');

    // Limpiar advertencias al escribir sin dar pistas ni delatar nada en pantalla
    [clave, verificarClave].forEach(function(inp) {
        if (inp) {
            inp.addEventListener('input', function() {
                if (mensaje) {
                    mensaje.style.display = 'none';
                    mensaje.innerHTML = '';
                }
                if (verificarClave) verificarClave.style.borderColor = '';
            });
        }
    });

    // Validar únicamente al presionar el botón Ingresar
    form.addEventListener('submit', function(e) {
        const val1 = clave.value;
        const val2 = verificarClave.value;

        if (!val1) {
            e.preventDefault();
            clave.focus();
            return;
        }

        if (!val2) {
            e.preventDefault();
            mensaje.style.display = 'block';
            mensaje.innerHTML = '<span class="text-danger fw-medium"><i class="bi bi-exclamation-circle-fill me-1"></i> Debe verificar la contrasena ingresando la misma.</span>';
            verificarClave.style.borderColor = '#dc3545';
            verificarClave.focus();
            return;
        }

        if (val1 !== val2) {
            e.preventDefault();
            mensaje.style.display = 'block';
            mensaje.innerHTML = '<span class="text-danger fw-medium"><i class="bi bi-exclamation-circle-fill me-1"></i> Las contrasenas no coinciden.</span>';
            verificarClave.style.borderColor = '#dc3545';
            verificarClave.focus();
            return;
        }
    });
});
</script>