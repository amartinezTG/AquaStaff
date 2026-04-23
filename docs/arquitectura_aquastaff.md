# Arquitectura de Software — AquaStaff
**Versión:** 2.0  
**Fecha:** 2026-04-20  
**Proyecto:** AquaStaff — Sistema de Gestión Interno Aqua Car Club  

---

## 1. Descripción General del Sistema

AquaStaff es el sistema de gestión interno de **Aqua Car Club**, una cadena de autolavados de membresía ubicada en Chihuahua, México. Opera bajo un modelo de negocio híbrido: membresías recurrentes y servicios de pago por uso (paquetes).

El sistema es una aplicación web construida en **Laravel 10 (PHP 8)**, desplegada en un servidor **Windows Server con IIS**. Se accede exclusivamente por personal interno (staff) autenticado.

### Funciones principales

| Módulo | Descripción |
|--------|-------------|
| Dashboard | Métricas de ventas en tiempo real, tráfico por hora, membresías activas |
| Cajero | Registro y consulta de transacciones, importación de archivos TPV Procepago |
| Membresías | Gestión y análisis de membresías activas y renovaciones |
| Indicadores | Reportes operativos y financieros con exportación a Excel |
| Corte de Caja | Arqueos de caja por sucursal con exportación PDF/Excel |
| Caja Chica | Registro de gastos menores |
| Facturación Global | Generación de CFDI 4.0 agrupados por tipo de pago (FacturoPorTi) |
| Facturación Individual | Generación de CFDI 4.0 individuales para transacciones sin folio físico |
| Inventarios | Control de productos y transferencias entre sucursales |
| Promociones | Creación y seguimiento de promociones vigentes |
| Administración | Auditoría de transacciones, detección de huecos, monitor de sincronización |

### Proyecto hermano

**AquaFacturacion** (`C:\...\AquaFacturacion`) es el portal público para que los clientes generen su factura individual vía web. Comparte la misma base de datos MySQL y el mismo servidor IIS. Los archivos PDF/XML generados en AquaStaff se escriben también en el directorio de AquaFacturacion para que los clientes puedan descargarlos.

---

## 2. Casos de Uso

### CU-01 Autenticación de Staff
- **Actor:** Personal interno
- **Descripción:** El usuario ingresa su correo y contraseña. El sistema verifica credenciales contra la tabla `staff_users` y establece la sesión con su rol asignado.
- **Roles:** SUPERADMIN (1), ADMINISTRATOR (2), MANAGER (3)

### CU-02 Consulta de Dashboard
- **Actor:** Todos los roles
- **Descripción:** Al ingresar, el sistema muestra métricas del día actual: total de transacciones, ventas, membresías vendidas, comparativa vs día anterior, desglose por hora, distribución por tipo de pago.

### CU-03 Consulta de Transacciones (Cajero)
- **Actor:** Todos los roles
- **Descripción:** El personal consulta el historial de transacciones filtrando por rango de fechas, tipo de transacción, forma de pago y cajero. Exportación disponible en Excel/CSV.

### CU-04 Importación de Archivo Procepago
- **Actor:** Administrador
- **Descripción:** Se carga el archivo Excel del TPV Procepago. El sistema parsea las filas e inserta los registros en `procepago_pagos`. Luego se puede ejecutar análisis de conciliación para detectar discrepancias con `local_transaction`.

### CU-05 Generación de Factura Global
- **Actor:** SUPERADMIN, ADMINISTRATOR
- **Descripción:** El contador selecciona un rango de fechas. El sistema agrupa transacciones no facturadas por tipo de pago y cajero, calcula base + IVA 8%, y genera un CFDI 4.0 vía API FacturoPorTi. Guarda UUID, serie, folio, PDF y XML.

### CU-06 Generación de Factura Individual
- **Actor:** SUPERADMIN, ADMINISTRATOR
- **Descripción:** El staff selecciona transacciones individuales (renovaciones, compras de membresía) que no tienen `CadenaFacturacion` (folio físico). Ingresa o busca la cuenta fiscal del cliente, ajusta la fecha de emisión dentro de las 72 horas requeridas por el SAT, y genera el CFDI individual. Los archivos se guardan en ambos proyectos (AquaStaff y AquaFacturacion).

### CU-07 Corte de Caja
- **Actor:** Todos los roles
- **Descripción:** Al cierre del día, el cajero registra los totales de efectivo, tarjeta, cortesías y gastos por sucursal. El sistema genera el arqueo y permite exportarlo en PDF o Excel.

### CU-08 Gestión de Membresías
- **Actor:** Todos los roles
- **Descripción:** Consulta de membresías activas, ventas del período, distribución por tipo de paquete. Análisis de clientes recurrentes vs nuevos.

### CU-09 Indicadores Operativos
- **Actor:** MANAGER, ADMINISTRATOR, SUPERADMIN
- **Descripción:** Generación de reportes mensuales con totales por cajero, por tipo de membresía, ingresos por proveedor (CARRERA / INTERLOGIC). Exportación a Excel y PDF.

### CU-10 Gestión de Inventario
- **Actor:** MANAGER, ADMINISTRATOR, SUPERADMIN
- **Descripción:** Registro de productos, transferencias entre sucursales con seguimiento de estado (pendiente → entregado). Log de movimientos de inventario.

### CU-11 Auditoría de Transacciones
- **Actor:** SUPERADMIN, ADMINISTRATOR
- **Descripción:** El sistema detecta huecos (gaps) en la secuencia de `local_transaction._id` por día, indicando posibles transacciones eliminadas o no sincronizadas. Exportación de reporte en CSV.

### CU-12 Monitor de Sincronización
- **Actor:** SUPERADMIN
- **Descripción:** Visualización del estado de sincronización del `transactions_log` entre proveedores y la base de datos central.

---

## 3. Diagramas de Flujo del Proceso

### 3.1 Flujo de Generación de Factura Global

```
Contador selecciona rango de fechas
         ↓
Sistema consulta local_transaction
(sin global_invoice_id, PaymentType ≠ 3)
         ↓
Agrupa por PaymentType + Atm (cajero)
         ↓
Obtiene token FacturoPorTi (cache)
         ↓
Para cada grupo:
  Calcula Base = Total / 1.08
  Calcula IVA = Total - Base
  Construye JSON CFDI 4.0
  POST /servicios/timbrar/json
         ↓
¿Éxito?
  SÍ → Guarda GlobalInvoice
       Actualiza local_transaction.global_invoice_id
       Guarda PDF/XML en storage
  NO → Retorna error al frontend
```

### 3.2 Flujo de Generación de Factura Individual

```
Staff filtra transacciones (por fecha, tipo, cajero)
         ↓
Selecciona una o varias transacciones
         ↓
Abre modal: ingresa RFC o busca cuenta fiscal
Sistema autocompleta datos desde fiscal_accounts
         ↓
Staff verifica fecha de emisión (default = ahora - 3min)
Validación JS: máximo 72 horas hacia atrás, no futuro
         ↓
Confirma y envía al servidor
         ↓
Obtiene token FacturoPorTi (mismo cache)
         ↓
Para cada transacción seleccionada:
  Nombre de archivo = CadenaFacturacion ?: 'IND_' + local_transaction_id
  Construye CFDI individual
  POST /servicios/timbrar/json
         ↓
¿Éxito?
  SÍ → Actualiza local_transaction.fiscal_invoice = UUID
       Guarda PDF/XML en AquaStaff storage
       Guarda copia en AquaFacturacion storage
  NO → Retorna error al frontend
```

### 3.3 Flujo de Importación Procepago

```
Administrador carga archivo Excel (.xlsx)
         ↓
CajeroController::importarProcepago()
Lee filas con PhpSpreadsheet
         ↓
Valida columnas (folio, monto, fecha, referencia)
         ↓
Inserta batch en procepago_pagos
         ↓
Análisis de conciliación (opcional):
  JOIN local_transaction vs procepago_pagos
  por num_operacion / _id
         ↓
Retorna discrepancias:
  - Transacciones en local pero no en Procepago
  - Montos diferentes
  - Transacciones en Procepago pero no en local
```

---

## 4. Componentes Principales

### 4.1 Frontend

| Tecnología | Uso |
|-----------|-----|
| Bootstrap 5 | Layout responsive, componentes UI |
| jQuery 3.x | AJAX, manipulación DOM |
| DataTables + Scroller | Tablas con paginación, búsqueda y scroll virtual |
| SweetAlert2 | Modales de confirmación y formularios |
| Chart.js | Gráficas en dashboard e indicadores |
| Bootstrap Icons | Iconografía |

Las vistas están organizadas en `resources/views/` con layouts reutilizables:
- `layout/shared.blade.php` — Head, CSS, meta
- `layout/nav-header.blade.php` — Sidebar con menú por roles
- `layout/includes.blade.php` — Scripts comunes
- `layout/footer.blade.php` / `layout/footer_files.blade.php` — Scripts de cierre

### 4.2 Backend

| Capa | Detalle |
|------|---------|
| Framework | Laravel 10 (PHP 8.x) |
| ORM | Eloquent (relaciones, scopes, soft deletes) |
| Autenticación | Laravel Auth con modelo `StaffUser` |
| Cache | Laravel Cache (driver: file/redis) — token FacturoPorTi cacheado indefinidamente |
| Cola de trabajos | No implementada (generación síncrona) |
| Exportación | Maatwebsite/Laravel-Excel para Excel, DomPDF para PDF |

### 4.3 Servidor Web

| Componente | Detalle |
|-----------|---------|
| OS | Windows Server |
| Web Server | IIS (Internet Information Services) |
| PHP | PHP 8.x (FastCGI) |
| Base de Datos | MySQL 8.x (127.0.0.1:3306) |
| Almacenamiento | `storage/app/public/` — PDF, XML de facturas |
| Proyectos en mismo servidor | AquaStaff y AquaFacturacion comparten servidor y BD |

---

## 5. Arquitectura de Software

### 5.1 Diagrama de Capas

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENTE (Browser)                     │
│     Bootstrap 5 + jQuery + DataTables + SweetAlert2         │
└────────────────────────┬────────────────────────────────────┘
                         │ HTTPS
┌────────────────────────▼────────────────────────────────────┐
│                    IIS — Windows Server                      │
│  ┌─────────────────────────────────────────────────────┐    │
│  │                  Laravel 10 (PHP 8)                 │    │
│  │                                                     │    │
│  │  routes/web.php → Controllers → Views (Blade)       │    │
│  │                ↕                                    │    │
│  │           Models (Eloquent)                         │    │
│  │                ↕                                    │    │
│  │          MySQL Database                             │    │
│  │                                                     │    │
│  │  Storage: storage/app/public/{pdfs,xmls,invoices}   │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │         AquaFacturacion (proyecto hermano)          │    │
│  │   Portal público CFDI — misma BD — mismo storage    │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────┬───────────────────────────┘
                                  │ REST API (HTTPS)
┌─────────────────────────────────▼───────────────────────────┐
│              FacturoPorTi API (CFDI 4.0 SAT)                │
│   https://api.facturoporti.com.mx                           │
│   • GET  /token/crear                                       │
│   • POST /servicios/timbrar/json                            │
│   • POST /servicios/cancelar/csd                            │
└─────────────────────────────────────────────────────────────┘
```

### 5.2 Flujo de Información

```
Transacciones POS (CARRERA / INTERLOGIC)
           ↓
    local_transaction (MySQL)
           ↓
    ┌──────┴──────────────────┐
    │                         │
Dashboard              Facturación
Cajero                  ↓
Indicadores      FacturoPorTi API
Corte Caja             ↓
                  GlobalInvoice / fiscal_invoice
                        ↓
                  PDF + XML → storage
                        ↓
                  AquaFacturacion (descarga cliente)
```

---

## 6. Modelo de Datos

### Tablas Principales

#### `local_transaction`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `_id` | BIGINT PK | ID de transacción del POS |
| `TransationDate` | DATETIME | Fecha y hora de la transacción |
| `TransactionType` | TINYINT | 0=Compra Membresía, 1=Renovación, 2=Paquete |
| `Total` | DECIMAL | Monto total con IVA |
| `PaymentType` | TINYINT | 0=Efectivo, 1=Débito, 2=Crédito, 3=Cortesía |
| `Atm` | VARCHAR | Identificador del cajero/ATM |
| `CadenaFacturacion` | VARCHAR | Folio del ticket físico (NULL para renovaciones) |
| `global_invoice_id` | BIGINT FK | Factura global asociada |
| `fiscal_invoice` | VARCHAR | UUID del CFDI individual |
| `fiscal_account_id` | BIGINT FK | Cuenta fiscal del cliente |
| `deleted_at` | TIMESTAMP | Soft delete |

#### `global_invoice`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT PK | Autoincremental |
| `uuid` | VARCHAR | UUID del CFDI (SAT) |
| `serie` | VARCHAR | Serie de la factura |
| `folio` | VARCHAR | Folio de la factura |
| `file_name` | VARCHAR | Nombre del archivo PDF/XML |
| `total` | DECIMAL | Total facturado |
| `start_date_group` | DATE | Fecha inicio del período facturado |
| `end_date_group` | DATE | Fecha fin del período facturado |
| `paymentType` | TINYINT | Tipo de pago del grupo |
| `periodicidad` | VARCHAR | Periodicidad (04=Mensual) |
| `cancelada_at` | TIMESTAMP | Fecha de cancelación |
| `cancel_motivo` | VARCHAR | Motivo de cancelación SAT |

#### `fiscal_accounts`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT PK | Autoincremental |
| `account_id` | VARCHAR | ID de cuenta externa (portal) |
| `rfc` | VARCHAR | RFC del receptor |
| `company_name` | VARCHAR | Razón social |
| `fiscal_regime` | VARCHAR | Régimen fiscal SAT |
| `cfdi_use` | VARCHAR | Uso del CFDI |
| `zip_code` | VARCHAR | Código postal fiscal |

#### `clients`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `client_id` | BIGINT PK | ID de cliente |
| `first_name` | VARCHAR | Nombre |
| `last_name` | VARCHAR | Apellido |
| `is_recurrent` | TINYINT | 1=Cliente recurrente |

#### `client_membership`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT PK | Autoincremental |
| `client_id` | BIGINT FK | Cliente |
| `membership_id` | BIGINT FK | Tipo de membresía |
| `start_date` | DATE | Inicio de vigencia |
| `end_date` | DATE | Fin de vigencia |

#### `procepago_pagos`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT PK | Autoincremental |
| `num_operacion` | VARCHAR | Folio de la operación TPV |
| `monto` | DECIMAL | Monto de la operación |
| `fecha` | DATETIME | Fecha de la transacción |
| `referencia` | VARCHAR | Referencia bancaria |

#### `corte_caja`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT PK | Autoincremental |
| `sucursal` | VARCHAR | Sucursal del corte |
| `fecha` | DATE | Fecha del corte |
| `total_efectivo` | DECIMAL | Total en efectivo |
| `total_tarjeta` | DECIMAL | Total en tarjeta |
| `total_cortesias` | DECIMAL | Total en cortesías |

### Relaciones Clave

```
local_transaction ──FK──► global_invoice
local_transaction ──FK──► fiscal_accounts
local_transaction ──FK──► (fiscal_invoice = UUID del CFDI individual)
client_membership ──FK──► clients
client_membership ──FK──► memberships
transfers ──FK──► products
transfers ──FK──► facilities
```

---

## 7. Diseño

### 7.1 Convenciones de Código

- **Controladores:** PascalCase, sufijo `Controller`
- **Modelos:** PascalCase singular
- **Vistas:** snake_case, organizadas por módulo en subdirectorios
- **Rutas:** kebab-case, agrupadas por módulo con prefijo y middleware
- **JS por módulo:** `public/assets/js/[modulo].js` con lógica de DataTables y AJAX

### 7.2 Patrones Utilizados

| Patrón | Implementación |
|--------|---------------|
| MVC | Laravel MVC estándar |
| Repository implícito | Eloquent en Controllers (sin capa de repositorio separada) |
| Cache-aside | Token FacturoPorTi cacheado indefinidamente (`facturoporti_prod_token`) |
| Dual-write | Facturas individuales escritas en ambos proyectos simultáneamente |
| Soft deletes | `local_transaction.deleted_at` |
| Upsert | `fiscal_accounts` buscada/creada por RFC al facturar individualmente |

### 7.3 Control de Acceso

| Rol | ID | Acceso |
|-----|----|--------|
| SUPERADMIN | 1 | Todo el sistema |
| ADMINISTRATOR | 2 | Todo excepto configuración avanzada |
| MANAGER | 3 | Cajero, Membresías, Indicadores, Corte de Caja, Inventarios |

El middleware `auth` protege todas las rutas. La navegación filtra visualmente por `auth()->user()->role`.

---

## 8. Documentación de Servicios

### 8.1 Dashboard

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/dashboard` | GET | Vista principal con métricas del día |
| `/dashboard/info_dashboard` | POST | AJAX: resumen diario, horario, membresías, cajeros |
| `/dashboard/active_memberships` | GET | Conteo de membresías activas por paquete |

### 8.2 Cajero

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/cajero` | GET/POST | Vista principal con resumen de transacciones |
| `/cajero_transacciones` | GET | Listado de transacciones |
| `/cajero/CajerosTable` | POST | AJAX: tabla de transacciones por cajero |
| `/cajero/membership-packages` | GET | Desglose de paquetes de membresía |
| `/cajero/importacion` | GET | Vista de importación Procepago |
| `/cajero/importacion` | POST | Procesa y almacena archivo Procepago |
| `/cajero/importacion/table` | POST | AJAX: tabla de registros importados |
| `/cajero/analisis-procepago` | GET | Vista de análisis de conciliación |
| `/cajero/analisis-procepago` | POST | AJAX: datos de conciliación Procepago vs local |
| `/exportar-csv/{startDate}/{endDate}` | GET | Export Excel de indicadores |
| `/exportar-trafico-ventas/{startDate}/{endDate}` | GET | Export Excel tráfico por hora |
| `/exportar-listado-transacciones/{startDate}/{endDate}` | GET | Export lista transacciones INTERLOGIC |

### 8.3 Membresías

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/membresias` | GET/POST | Vista de membresías con filtros |
| `/exportar-membresias-ventas/{startDate}/{endDate}` | GET | Export Excel tráfico de membresías |
| `/membresias/cajero` | GET | Vista de membresías por cajero |
| `/membresias/membresias_cajero_table` | POST | AJAX: tabla de membresías por cajero |

### 8.4 Indicadores

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/indicadores` | GET | Vista de indicadores mensuales |
| `/indicadores/indicadores_table` | POST | AJAX: datos de indicadores |
| `/indicadores_cajero` | GET | Indicadores por cajero |
| `/indicadores/indicadores_pagos_table` | POST | AJAX: desglose por tipo de pago |
| `/indicadores/indicadores_membresias_table` | POST | AJAX: distribución de membresías |
| `/indicadores-membresias` | GET/POST | Vista de indicadores de membresías |
| `/indicadores_membresias` | GET/POST | Indicadores membresías (variante) |
| `/indicadores/clientes` | GET | Análisis de clientes |
| `/indicadores/clientes/table` | POST | AJAX: tabla de clientes |
| `/exportar-indicadores/` | GET | Export Excel indicadores operativos |
| `/exportar-membresias` | GET | Export Excel membresías |
| `/indicadores_operativos_pdf/` | GET | Export PDF indicadores operativos |
| `/exportar_membresias_pdf/` | GET | Export PDF membresías |

### 8.5 Corte de Caja

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/corte_caja` | GET | Vista de cortes registrados |
| `/corte_caja_sucursal` | GET | Formulario nuevo corte de caja |
| `/corte_caja_sucursal` | POST | Guardar corte de caja |
| `/validar-fecha-corte` | POST | Validar que no exista corte duplicado |
| `/detalle_corte/{corte_id}` | GET | Detalle de un corte específico |
| `/editar_corte/{corte_id}` | GET | Formulario de edición de corte |
| `/actualizar_corte/{corte_id}` | PUT | Actualizar corte |
| `/exportar_corte/{corte_id}` | GET | Export Excel del corte |
| `/detalle_corte_export/{corte_id}` | GET | Export Excel detalle del corte |
| `/exportar-corte-pdf/{corte_id}` | GET | Export PDF del corte |
| `/detalle_corte_pdf/{corte_id}` | GET | Generar PDF detalle del corte |

### 8.6 Caja Chica

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/caja_chica` | GET | Vista de caja chica |
| `/caja_chica_sucursal` | GET | Formulario de caja chica |
| `/caja_chica_sucursal` | POST | Guardar registro de caja chica |
| `/detalle_caja_chica/{caja_id}` | GET | Detalle de un registro de caja chica |

### 8.7 Facturación Global

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/facturacion` | GET | Vista de facturación global |
| `/facturacion/transacciones` | POST | AJAX: transacciones disponibles para facturar |
| `/facturacion/generar` | POST | Genera CFDI global vía FacturoPorTi |
| `/facturacion/historial` | POST | AJAX: historial de facturas generadas |
| `/facturacion/download/xml/{name}` | GET | Descarga XML de factura global |
| `/facturacion/download/pdf/{name}` | GET | Descarga PDF de factura global |
| `/facturacion/cancelar/{id}` | POST | Cancela CFDI global vía SAT |

### 8.8 Facturación Individual

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/facturacion-individual` | GET | Vista de facturación individual |
| `/facturacion-individual/transacciones` | POST | AJAX: transacciones sin CFDI individual |
| `/facturacion-individual/buscar-cuenta` | GET | Autocomplete de cuenta fiscal por RFC |
| `/facturacion-individual/generar` | POST | Genera CFDI individual vía FacturoPorTi |
| `/facturacion-individual/download/pdf/{fileName}` | GET | Descarga PDF de factura individual |
| `/facturacion-individual/download/xml/{fileName}` | GET | Descarga XML de factura individual |

**Notas técnicas:**
- Identifica la transacción por `local_transaction_id` en lugar de `CadenaFacturacion`
- Nombre de archivo: `CadenaFacturacion ?: 'IND_' + local_transaction_id`
- Emisión: campo editable en modal con validación JS (máximo 72 horas, SAT regla)
- Archivos guardados en AquaStaff y AquaFacturacion simultáneamente (dual-write)

### 8.9 Factura Global (Compaq / Legacy)

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/compaq` | GET/POST | Vista del sistema Compaq |
| `/process_compaq` | POST | Procesa factura vía Compaq |
| `/download/txt/{name}` | GET | Descarga archivo TXT de Compaq |
| `/compaq_detalle/{id}` | GET | Detalle de factura Compaq |
| `/compaq_archivo/{name}` | GET | Ver archivo Compaq |
| `/compaq/history` | GET | Historial de Compaq |
| `/process_global_invoice` | POST | Genera factura global (flujo Compaq) |
| `/global_invoice_download/xml/{name}` | GET | Descarga XML factura global |
| `/global_invoice_download/pdf/{name}` | GET | Descarga PDF factura global |
| `/global_invoice_detalle/{id}` | GET | Detalle de factura global |

### 8.10 Inventarios y Productos

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/inventarios` | GET | Vista de inventarios |
| `/productos` | GET | Listado de productos |
| `/agregar_producto` | GET | Formulario agregar producto |
| `/editar_producto/{product_id}` | GET | Formulario editar producto |
| `/eliminar_producto/{product_id}` | GET | Eliminar producto |
| `/producto_agregar` | POST | Guardar producto nuevo o editado |
| `/transferencias` | GET | Listado de transferencias |
| `/crear_transferencia` | GET | Formulario nueva transferencia |
| `/submit_transfer_form` | POST | Guardar nueva transferencia |
| `/transfer_detail/{transfer_id}` | GET | Detalle de transferencia |
| `/submit_transfer_logs` | POST | Guardar logs de transferencia |
| `/submit_transfer_update` | POST | Actualizar estado de transferencia |
| `/search/products` | GET | Búsqueda de productos (AJAX) |

### 8.11 Usuarios y Autenticación

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/login` | GET | Vista de inicio de sesión |
| `/login` | POST | Procesar login |
| `/logout` | GET | Cerrar sesión |
| `/usuarios` | GET | Listado de usuarios staff |
| `/usuario` | GET | Formulario nuevo usuario |
| `/usuario` | POST | Guardar nuevo usuario |
| `/editar_usuario/{usuario_id}` | GET | Formulario editar usuario |
| `/eliminar_usuario/{user_id}` | GET | Eliminar usuario |

### 8.12 Configuración y Administración

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/tipo_de_cambio` | GET | Vista tipo de cambio actual |
| `/tipo_de_cambio/actualizar` | POST | Actualizar tipo de cambio MXN/USD |
| `/administracion/` | GET | Vista principal de auditoría |
| `/administracion/transaction-gaps-summary` | POST | Resumen de huecos en transacciones por día |
| `/administracion/transaction-gaps-detail` | POST | Detalle de huecos específicos |
| `/administracion/export-transaction-gaps` | GET | Export CSV de reporte de huecos |
| `/administracion/quick-stats` | GET | Estadísticas rápidas (últimos 7 días) |
| `/administracion/sync-monitor` | GET | Vista monitor de sincronización |
| `/administracion/sync-monitor/table` | POST | AJAX: tabla de estado de sincronización |

### 8.13 Promociones

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/promociones` | GET | Listado de promociones |
| `/promociones/tabla` | POST | AJAX: tabla de promociones |
| `/promociones/store` | POST | Crear nueva promoción |
| `/promociones/{id}` | PUT | Actualizar promoción |
| `/promociones/{id}/pdf` | GET | Generar PDF de promoción |

---

## 9. Integración con FacturoPorTi (CFDI 4.0)

### Credenciales del Emisor

| Campo | Valor |
|-------|-------|
| Empresa | AQUA CAR CLUB |
| RFC | ACC220922GT0 |
| Régimen Fiscal | 601 — General de Ley Personas Morales |
| Código Postal | 32030 (Chihuahua) |
| API URL Producción | `https://api.facturoporti.com.mx` |

### Endpoints API

| Endpoint | Método | Uso |
|----------|--------|-----|
| `/token/crear?Usuario=X&Password=Y` | GET | Obtener Bearer token |
| `/servicios/timbrar/json` | POST | Timbrar CFDI (generar factura) |
| `/servicios/cancelar/csd` | POST | Cancelar CFDI ante el SAT |

### Estructura CFDI Individual (Facturación Individual)

```json
{
  "Serie": "IND",
  "Fecha": "2026-04-20T10:30:00",
  "FormaPago": "03",
  "MetodoPago": "PUE",
  "LugarExpedicion": "32030",
  "Emisor": {
    "Rfc": "ACC220922GT0",
    "Nombre": "AQUA CAR CLUB",
    "RegimenFiscal": "601"
  },
  "Receptor": {
    "Rfc": "[RFC del cliente]",
    "Nombre": "[Razón social]",
    "DomicilioFiscalReceptor": "[CP fiscal]",
    "RegimenFiscalReceptor": "[Régimen]",
    "UsoCFDI": "[Uso]"
  },
  "Conceptos": [{
    "ClaveProdServ": "84111506",
    "Descripcion": "Servicio de Membresía Renovación",
    "Cantidad": 1,
    "ValorUnitario": "[Base sin IVA]",
    "Importe": "[Base sin IVA]",
    "Impuestos": {
      "Traslados": [{
        "Base": "[Base]",
        "Impuesto": "002",
        "TipoFactor": "Tasa",
        "TasaOCuota": "0.080000",
        "Importe": "[IVA 8%]"
      }]
    }
  }]
}
```

### Regla SAT 72 horas

El SAT requiere que la `Fecha` de emisión del CFDI no sea más de **72 horas anterior** al momento del timbrado. Para transacciones antiguas (renovaciones de meses previos), el staff debe establecer la fecha de emisión al momento actual (no a la fecha de la transacción original).

---

## 10. Escalabilidad y Mantenimiento

### Puntos de Extensión

| Área | Oportunidad |
|------|------------|
| Nuevas sucursales | Agregar filtro de sucursal en `local_transaction.Atm` |
| Nuevos tipos de membresía | Actualizar `GeneralCatalogs::membership_types` |
| Nuevas formas de pago | Actualizar `PaymentType` enum y lógica de agrupación |
| Portal público | AquaFacturacion puede extenderse para más tipos de factura |

### Consideraciones de Mantenimiento

- **Token FacturoPorTi:** Cacheado indefinidamente. Si expira, limpiar con `php artisan cache:clear`
- **Rutas de archivos:** Los PDFs/XMLs de facturas globales van a `storage/app/public/invoices/`, los individuales a `storage/app/public/pdfs/` y `xmls/`
- **Dual-write:** Si AquaFacturacion cambia de ubicación en el servidor, actualizar `base_path('../AquaFacturacion/...')` en `FacturacionIndividualController::saveInvoiceFile()`
- **IVA:** Actualmente fijo al 8% (zona fronteriza). Actualizar en `GeneralCatalogs` si cambia la tasa
- **Sincronización de transacciones:** El monitor en `/administracion/sync-monitor` verifica la integridad del `transactions_log`. Revisar periódicamente para detectar transacciones no sincronizadas

### Dependencias Críticas

| Dependencia | Impacto en fallo |
|-------------|-----------------|
| FacturoPorTi API | Sin generación ni cancelación de CFDI |
| MySQL local | Sistema inoperativo completo |
| IIS / PHP | Sistema inoperativo completo |
| Procepago (TPV) | Sin importación de archivos de conciliación |

---

## 11. Pruebas

### Entorno de Pruebas FacturoPorTi

URL sandbox: `https://testapi.facturoporti.com.mx`  
Cambiar en `GeneralCatalogs::api_data_reference['url']` para pruebas de integración.

### Casos de Prueba Críticos

| Caso | Descripción | Resultado Esperado |
|------|-------------|-------------------|
| Factura global — período completo | Generar factura de todas las transacciones del mes | CFDI timbrado, UUID guardado, archivos generados |
| Factura individual — renovación sin folio | Seleccionar renovación sin `CadenaFacturacion` | Nombre `IND_XXXXXXX`, CFDI generado, archivo accesible en AquaFacturacion |
| Fecha emisión > 72 horas | Ingresar fecha de hace 73 horas en modal | Bloqueo en JS con mensaje explicativo |
| RFC no existente | Buscar RFC que no está en `fiscal_accounts` | Campos vacíos, staff ingresa datos manualmente |
| Cancelación de CFDI | Cancelar factura global existente | Estado actualizado, motivo SAT registrado |
| Hueco en transacciones | Eliminar transacción del día anterior | Auditoría detecta y reporta el hueco |

---

*Documento generado el 2026-04-20. Versión 2.0 incluye módulo de Facturación Individual (IND), corrección de regla SAT 72 horas y dual-write de archivos entre AquaStaff y AquaFacturacion.*
