# Plataforma de Gestión Integral Teke'fritos

**Documento de información general de la plataforma**
**Fecha:** 31 de julio de 2026

---

## 1. Planteamiento del proyecto

Teke'fritos es un emprendimiento dedicado a la elaboración y venta de masas artesanales, pastelitos y tequeños, con atención tanto en local como por pedidos. Su operación diaria requiere gestionar un catálogo de productos, el control del inventario, el registro de ventas y pedidos, el manejo de materia prima e insumos, y el cumplimiento de obligaciones fiscales como el IVA.

Antes de la implementación de esta plataforma, el control de estas actividades dependía de registros manuales y herramientas aisladas, lo que dificultaba la toma de decisiones, la trazabilidad de las operaciones y la adaptación rápida a un entorno económico caracterizado por la fluctuación de la moneda y las tasas de cambio.

La plataforma Teke'fritos surge como respuesta a esa necesidad, ofreciendo una **solución de gestión integral** que centraliza la información del negocio en un único sistema. Su propósito es digitalizar y automatizar los procesos administrativos y comerciales para garantizar un control confiable, actualizado y seguro de la operación.

### Objetivo general

Diseñar e implementar un sistema de gestión que permita administrar de forma centralizada los productos, pedidos, clientes, proveedores, inventario, finanzas y documentación fiscal del negocio.

### Objetivos específicos

- Mantener un catálogo de productos con precios actualizados en dólares (USD) y bolívares (Bs.), sujetos a la tasa oficial.
- Registrar y dar seguimiento a los pedidos y ventas con su detalle de artículos e impuestos.
- Controlar el inventario de productos terminados y de materia prima, con alertas de existencias mínimas.
- Administrar el registro de clientes y proveedores.
- Registrar las pérdidas o mermas del inventario.
- Generar reportes de gestión, incluyendo el reporte de IVA para fines fiscales.
- Emitir documentos comerciales (facturas y notas de entrega) mediante un talonario digital.
- Mantener un registro de auditoría de todas las operaciones realizadas en el sistema.

---

## 2. Repositorio y código fuente

El proyecto se encuentra versionado y alojado en GitHub, en la siguiente dirección:

**Repositorio:** `https://github.com/adrianjesus1209-beep/Tekefritos-Sist.-administrativo.git`

- **Plataforma de alojamiento:** GitHub
- **Rama principal:** `main`
- La plataforma puede desplegarse en un entorno local mediante Apache y MySQL (XAMPP o Laragon) y accederse a través de `http://localhost/Tekefritos`.

*Nota: este documento no incluye credenciales, claves ni información de acceso de ningún tipo.*

---

## 3. Módulos principales de la plataforma

La plataforma está organizada en módulos funcionales que cubren las áreas clave del negocio:

| Módulo | Descripción |
|---|---|
| **Inicio** | Panel de indicadores: total de productos, ventas completadas, ventas del día, rentabilidad y alertas de inventario. |
| **Productos** | Administración del catálogo: nombre, código, categoría, peso, precios en USD y Bs., costo, IVA, stock y disponibilidad. |
| **Pedidos** | Registro y seguimiento de ventas y pedidos con su detalle de artículos, cliente, tipo de entrega y estado. |
| **Clientes y Proveedores** | Gestión de la cartera de contactos del negocio, con categorización de clientes. |
| **Materia Prima** | Control de insumos e ingredientes, sus cantidades, unidades y alertas de mínimos, vinculados a proveedores. |
| **Pérdidas** | Registro de pérdidas o mermas de productos, con motivo y descuento automático del stock. |
| **Personal** | Administración de usuarios del sistema y asignación de roles de acceso. |
| **Documentos y Talonario** | Carga de documentos y generación de facturas y notas de entrega con formato fiscal. |
| **Configuración** | Parámetros generales: tasa de IVA y redes sociales del negocio. |
| **Reportes** | Reporte de clientes, bitácora del sistema, reporte de IVA y reporte de pérdidas, con descarga en PDF. |
| **Papelera** | Registros desactivados, con opción de restaurarlos o eliminarlos definitivamente. |

---

## 4. Stack tecnológico

| Componente | Tecnología |
|---|---|
| Backend | PHP 8+ con arquitectura MVC nativa |
| Base de datos | MySQL / MariaDB |
| Frontend | HTML5, CSS3 y JavaScript |
| Interfaz | Bootstrap 5 e iconos de Bootstrap |
| Reportes PDF | Librería Dompdf |
| Tasas de cambio | Consumo de la API oficial de tasas (BCV) |

---

## 5. Roles y reglas de negocio clave

La plataforma contempla perfiles de acceso diferenciados para proteger la información según la responsabilidad de cada usuario.

### Roles

- **Administrador:** acceso total a la plataforma, incluida la administración del personal, la configuración y la papelera.
- **Vendedor:** acceso al inicio, catálogo de productos y registro de pedidos.
- **Trabajador:** perfil intermedio con acceso a las áreas operativas del sistema.

### Reglas de negocio principales

- Los precios se gestionan con un **precio canónico en dólares (USD)**; el equivalente en bolívares (Bs.) se recalcula automáticamente según la tasa oficial del BCV.
- El **IVA** es un porcentaje global configurable que se aplica a los productos que así lo indiquen, y queda registrado en cada detalle de venta.
- Los pedidos pueden registrarse como **Completado** o **Cancelado**; el stock se descuenta o restituye automáticamente según el estado del pedido.
- El inventario de productos y materia prima genera **alertas de existencias mínimas**.
- Las **pérdidas** registradas descuentan automáticamente las unidades del inventario.
- El sistema mantiene una **bitácora de auditoría** con todas las operaciones realizadas por los usuarios.
- La facturación externa y el talonario digital permiten emitir **facturas y notas de entrega** con formato fiscal, sin almacenar datos de acceso en ellos.

---

## 6. Base de datos

> **Espacio reservado:** insertar aquí la captura del diagrama de la base de datos.

*(Se deja el espacio anterior para incorporar la imagen del modelo relacional de la base de datos. A continuación se describe su estructura y conexiones.)*

La base de datos centraliza toda la información del negocio en torno a las siguientes entidades y sus relaciones:

- **Categorías y Productos:** la entidad `categorias` agrupa a los productos del catálogo. Un producto pertenece a una categoría y una categoría puede contener múltiples productos, estableciéndose una relación de **uno a muchos** entre ambas entidades.

- **Pedidos y Detalles:** cada venta o pedido se compone de una **cabecera** (`pedidos`) y de sus **líneas de detalle** (`detalles_pedido`). Un pedido contiene uno o varios detalles, y cada detalle referencia un producto del catálogo, formando la estructura típica de una venta con sus cantidades, precios e IVA aplicado.

- **Contactos y Pedidos:** los clientes, registrados en la entidad `contactos`, se vinculan con sus pedidos. Un cliente puede tener varios pedidos asociados a lo largo del tiempo.

- **Contactos y Materia Prima:** los proveedores también se registran en `contactos` y se relacionan con los insumos de la entidad `materia_prima`. Un proveedor puede suministrar varios insumos.

- **Productos y Pérdidas:** las pérdidas o mermas se registran en la entidad `perdidas`, vinculadas al producto afectado. Un producto puede tener varias pérdidas registradas en distintos momentos.

- **Usuarios y Auditoría:** la entidad `usuarios` representa a las personas que operan el sistema. Se relaciona con `bitacora`, donde queda el registro de cada acción, y con `documentos`, identificando al usuario que los genera. Un usuario puede tener múltiples registros de auditoría y múltiples documentos asociados.

- **Contactos y Documentos:** los documentos emitidos pueden estar asociados a un contacto (cliente), reflejando la relación entre la documentación comercial y la cartera de clientes.

- **Parámetros del Sistema:** la entidad `sistema_config` es una tabla independiente de tipo clave-valor que almacena los parámetros globales de la plataforma, como la tasa de IVA y los datos de contacto y redes sociales del negocio. No depende de otras entidades y es consultada por varios módulos.

En conjunto, estas entidades conforman un modelo relacional que permite registrar, consultar y auditar toda la operación del negocio de forma integrada y consistente.

---

## 7. Conclusión

La plataforma de gestión Teke'fritos constituye una solución integral que digitaliza los procesos administrativos, comerciales y fiscales del negocio. A través de sus módulos, permite mantener un control centralizado del inventario, los pedidos, la cartera de clientes y proveedores, la documentación y los reportes de gestión, adaptándose además a la dinámica económica del país mediante la actualización automática de precios según la tasa oficial. Se trata de una herramienta orientada a la operación diaria y a la toma de decisiones informadas.
