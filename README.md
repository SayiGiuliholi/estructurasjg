# EstructurasJG

Sistema web de inventario en PHP para gestionar:
- autenticacion de usuarios
- entradas de inventario
- salidas de inventario
- consulta de productos
- proveedores
- configuracion de usuarios y permisos

## Stack y enfoque
- PHP sin framework (arquitectura MVC simple)
- MySQL (PDO)
- HTML + CSS + JavaScript vanilla
- Separacion por capas: controladores, modelos, vistas, helpers y filtros

## Flujo general
1. El usuario entra por `public/index.php`.
2. Si inicia sesion correctamente, se redirige a `public/panel.php`.
3. `ControladorPanel` valida permisos y resuelve el modulo.
4. Cada modulo arma datos con repositorios/modelos y renderiza su vista parcial dentro de `app/vistas/panel/pantallas/plantilla.php`.

## Convencion de endpoints
- Todo archivo accesible por URL vive en `public/`.
- Paginas HTML del sistema: `public/index.php`, `public/panel.php`, `public/salir.php`.
- Endpoints JSON (API interna del panel): `public/api/<modulo>/<accion>.php`.
- Ejemplo actual: `public/api/salidas/producto.php`.

## Estructura del proyecto

### Raiz
- `index.php`: puente de redireccion al login real en `public/index.php`.
- `panel.php`: puente de redireccion al panel real en `public/panel.php`.

### database
- `database/01_tablas_inventario.sql`: script SQL de creacion de tablas del sistema.

### public (entrypoints y assets)
- `public/index.php`: controlador de entrada del login.
- `public/panel.php`: entrypoint principal del panel autenticado.
- `public/salir.php`: cierre de sesion y redireccion al login.
- `public/api/salidas/producto.php`: endpoint JSON para autocompletar producto por codigo+bodega en modulo salidas.
- `public/css/autenticacion/login.css`: estilos del login.
- `public/css/panel/plantilla.css`: estilos globales del panel y modulos.
- `public/js/panel/entradas.js`: logica dinamica del formulario de entradas (lineas, totales).
- `public/js/panel/salidas.js`: logica dinamica de salidas (autocompletado, validacion de stock, totales).
- `public/imagenes/marca/logo-login-principal.png`: logo usado en login/panel.

### app/configuracion
- `app/configuracion/conexion.php`: crea y reutiliza conexion PDO a MySQL.
- `app/configuracion/rutas.php`: helpers de rutas publicas (`construirUrlPublica`, `redirigirA`).

### app/ayudantes
- `app/ayudantes/sesion.php`: helper de sesion segura y chequeo de usuario autenticado.

### app/filtros
- `app/filtros/autenticado.php`: filtro/middleware que bloquea acceso si no hay sesion.

### app/controladores
- `app/controladores/ControladorAutenticacion.php`: login, validacion de credenciales, carga de sesion y logout.
- `app/controladores/ControladorPanel.php`: controla acceso por permisos y resuelve vista del modulo.

### app/modelos (entidades y repositorios)
- `app/modelos/Usuario.php`: entidad de usuario.
- `app/modelos/Rol.php`: entidad de rol con permisos.
- `app/modelos/Producto.php`: entidad de producto.
- `app/modelos/Proveedor.php`: entidad de proveedor.
- `app/modelos/RepositorioUsuario.php`: consultas y operaciones de usuarios/roles/permisos.
- `app/modelos/RepositorioProducto.php`: consultas de catalogo, paginacion y metricas de productos.
- `app/modelos/RepositorioProveedor.php`: CRUD de proveedores y resumenes.
- `app/modelos/RepositorioEntrada.php`: registro de entradas (cabecera/detalle), stock y reportes.
- `app/modelos/RepositorioSalida.php`: registro de salidas, validacion de stock y reportes.
- `app/modelos/RepositorioBodega.php`: consulta de bodegas activas.

### app/vistas/autenticacion
- `app/vistas/autenticacion/pantallas/login.php`: plantilla principal de login.
- `app/vistas/autenticacion/pantallas/preparar_login.php`: prepara datos de la vista login.
- `app/vistas/autenticacion/bloques_login/marca_login.php`: bloque visual de marca/beneficios.
- `app/vistas/autenticacion/bloques_login/form_login.php`: formulario de usuario y contrasena.

### app/vistas/panel (modulos + plantilla)
- `app/vistas/panel/pantallas/plantilla.php`: layout principal del panel (sidebar, topbar, hero y contenido modulo).
- `app/vistas/panel/preparadores/preparar_plantilla.php`: normaliza datos base para plantilla.
- `app/vistas/panel/pantallas/inicio.php`: vista de inicio/estado general.

- `app/vistas/panel/pantallas/entradas.php`: orquesta el modulo de entradas (POST/GET, repositorios, render).
- `app/vistas/panel/preparadores/preparar_entradas.php`: transforma datos de entradas a formato de vista.
- `app/vistas/panel/modulos/vista_entradas.php`: HTML del modulo entradas.

- `app/vistas/panel/pantallas/salidas.php`: orquesta el modulo de salidas.
- `app/vistas/panel/preparadores/preparar_salidas.php`: prepara datos de salidas para la vista.
- `app/vistas/panel/modulos/vista_salidas.php`: HTML del modulo salidas.

- `app/vistas/panel/pantallas/productos.php`: orquesta modulo de consulta de productos.
- `app/vistas/panel/preparadores/preparar_productos.php`: normaliza datos para mostrar catalogo.
- `app/vistas/panel/modulos/vista_productos.php`: HTML del modulo productos.

- `app/vistas/panel/pantallas/proveedores.php`: orquesta modulo proveedores (CRUD).
- `app/vistas/panel/preparadores/preparar_proveedores.php`: prepara datos de proveedores para vista.
- `app/vistas/panel/modulos/vista_proveedores.php`: HTML del modulo proveedores.

- `app/vistas/panel/pantallas/configuracion.php`: orquesta modulo de configuracion (usuarios y permisos).
- `app/vistas/panel/preparadores/preparar_configuracion.php`: prepara datos de usuarios/roles para vista.
- `app/vistas/panel/modulos/vista_configuracion.php`: HTML del modulo configuracion.

- `app/vistas/panel/layout_panel/sidebar_panel.php`: menu lateral del panel.
- `app/vistas/panel/layout_panel/topbar_panel.php`: barra superior con sesion/rol/acciones.

## Donde cambiar textos rapidamente
- Textos de cada modulo: `app/vistas/panel/modulos/vista_<modulo>.php`
- Titulo/descripcion del hero del modulo: `app/vistas/panel/preparadores/preparar_<modulo>.php`
- Texto del sidebar: `app/vistas/panel/layout_panel/sidebar_panel.php`
- Texto del topbar: `app/vistas/panel/layout_panel/topbar_panel.php`
- Textos de login: componentes en `app/vistas/autenticacion/bloques_login/`

## Notas importantes
- Validaciones criticas se hacen en backend (repositorios/controladores), no solo en JS.
- Se usa `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` para salida segura en vistas.
- Los modulos de entradas/salidas actualizan stock general y stock por bodega.

## Ejecucion local (resumen)
1. Importa `database/01_tablas_inventario.sql` en MySQL.
2. Ajusta credenciales en `app/configuracion/conexion.php`.
3. Sirve el proyecto desde `htdocs/Estructurasjg` (XAMPP).
4. Abre `http://localhost/Estructurasjg/public/index.php`.
