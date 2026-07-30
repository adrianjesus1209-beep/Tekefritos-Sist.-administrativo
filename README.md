# Teke'fritos — Sistema de Gestión

Sistema de gestión integral para **Teke'fritos** (masas, pastelitos y tequeños), desarrollado en PHP nativo bajo patrón MVC.

---

## Stack

| Componente | Tecnología |
|------------|-----------|
| Backend | PHP 8+ nativo (MVC) |
| BD | MySQL / MariaDB |
| Frontend | HTML5, CSS3, JavaScript vanilla |
| UI | Bootstrap 5 + Bootstrap Icons |
| Estilos | CSS personalizado con variables |

## Estructura

```
app/
├── Controllers/     # AdminController, AuthController, etc.
├── Helpers/         # Database, Session, Router, DivisasHelper
├── Models/          # Pedido, Producto, etc.
database/
├── tekefritos.sql   # Schema
├── migracion_*.php  # Migraciones
public/
├── views/
│   ├── layouts/     # admin.php (panel)
│   ├── landing/     # Página pública
│   └── cliente/     # Área del cliente
├── css/             # estilos_admin.css, estilos.css
└── uploads/         # Imágenes de productos
```

## Instalación

1. Apache + MySQL (XAMPP, Laragon).
2. BD `tekefritos`, importar `database/tekefritos.sql`.
3. Configurar conexión en `app/Helpers/Database.php`.
4. Acceder vía `http://localhost/Tekefritos`.
5. Registrarse → login para acceder al admin.

## Reglas de Negocio Clave

- **IVA global** configurable en `sistema_config.tasa_iva`. Producto tiene checkbox `aplica_iva`.
- **Precio canónico en USD** (`precio_usd`). Bs. se recalcula con tasa BCV al cargar admin.
- **Auto-sync precios**: si tasa BCV cambia > 0.01, se actualizan Bs. desde USD. Si nueva tasa < 50% de anterior, se omite.
- **Estados de pedido**: solo **Completado** y **Cancelado**.
- **Pedidos nuevos** se crean como Completado por defecto.
- **Stock**: se ajusta por delta al editar pedido Completado. No se valida stock al editar.
- **Código de producto**: manual al registrar, `PROD-{id}` auto-generado para existentes, único, editable.
- **Roles**: `admin` y `vendedor`. Vendedor accede al admin pero no a Personal.
- **R. IVA**: reporte para SENIAT con desglose de base imponible e IVA.
- **Facturación externa**: no hay facturas en el sistema, se emiten Excel fuera.
