# AquaStaff - Sistema de Gestión Interna

## Descripcion General

AquaStaff es una aplicación web interna construida en **Laravel** para gestionar las operaciones de **Aqua Car Club SA de CV** (RFC: ACC220922GT0), una empresa de lavado de autos ubicada en Ciudad Juárez (CP 32030).

El sistema centraliza el control de transacciones de lavado, membresías de clientes, cortes de caja, inventario, facturación fiscal y reportes operativos.

---

## Conexión a Base de Datos

- **Host:** `10.200.1.227:3306`
- **DB:** `u514131194_d0CoK`
- **User:** `admin`
- **Password:** `T0t4lG4s2020`

---

## Stack Tecnológico

- **Framework:** Laravel (PHP)
- **Base de Datos:** MySQL
- **Exportes:** Maatwebsite/Excel (xlsx), DomPDF (PDF)
- **Facturación:** API Facturoporti (`api.facturoporti.com.mx`) - producción y sandbox
- **Autenticación:** Auth de Laravel con modelo `StaffUser` (tabla `staff_users`)
- **Scheduling:** Laravel Cron (`sync:check-status` diario a las 03:00)

---

## Proveedores de Terminales / Cajeros

El sistema maneja dos proveedores de cajeros automáticos de lavado:

- **INTERLOGIC**: IDs de transacción numéricos (ej: `95732`, `114898`) — longitud distinta a 36
- **CARRERA**: IDs de transacción tipo UUID (LENGTH = 36)

La distinción se hace con `CASE WHEN LENGTH(_id) = 36 THEN 'CARRERA' ELSE 'INTERLOGIC'`.

Los cajeros físicos se identifican por campo `Atm`:
- `AQUA01` — 75,797 transacciones
- `AQUA02` — 64,275 transacciones
- `NULL` — 53,463 (sin cajero registrado, probablemente Carrera)
- `501` — 6 transacciones (raro)

Toda la data es de la sucursal **MISIONES** (campo `facility = 'MISIONES'`). CEDIS existe en facilities pero no tiene transacciones aún.

---

## Volumen de Datos (verificado en DB)

| Tabla | Filas |
|---|---|
| `transactions_log` | 1,229,915 |
| `orders` | 195,036 |
| `local_transaction` | 193,541 |
| `special_orders` | 21,015 |
| `recurrent_logs` | 15,620 |
| `used_business_codes` | 12,641 |
| `client_membership` | 7,318 |
| `fiscal_accounts` | 530 |
| `weather_logs` | 561 |
| `clients` | 2,134 |
| `zip_codes_mx` | 148,321 |
| `staff_users` | 20 |
| `products` | 21 |
| `corte_de_caja` | 7 |

Rango de datos activos: **Enero 2025 – Marzo 2026**

---

## Estructura Real de Tablas Clave

### `local_transaction`
Fuente principal de verdad financiera.
```
local_transaction_id  INT PK AUTO
_id                   VARCHAR(180)   -- numérico (INTERLOGIC) o UUID (CARRERA)
TransationDate        TIMESTAMP      -- tiene índice
TransactionType       INT            -- 0=CompraMem, 1=RenovMem, 2=Lavado
PaymentType           INT            -- 0=Efectivo, 1=Débito, 2=Crédito, 3=Cortesía
Total                 DOUBLE
TotalPayed            DOUBLE
Change                DOUBLE
ChangeDelivered       DOUBLE
PaymentFolio          VARCHAR(220)
Membership            VARCHAR(180)   -- _id de client_membership (24 chars) o UUID especial
MembershipDuration    VARCHAR(180)
Package               VARCHAR(180)   -- _id del paquete (ObjectId)
fiscal_invoice        VARCHAR(180)
fiscal_account_id     INT
global_invoice        INT(1)
account_id            INT
facility              VARCHAR(60)    -- siempre 'MISIONES' actualmente
Atm                   VARCHAR(255)   -- AQUA01, AQUA02
CadenaFacturacion     VARCHAR(180)
integrate_cp          INT(1)         -- NULL = no integrado a Compaq
integrate_cp_date     DATETIME
updated_at / created_at  DATETIME
```

**Distribución TransactionType:**
- `2` (Lavado): 188,096
- `1` (Renovación): 3,358
- `0` (Compra): 2,087

**Distribución PaymentType:**
- `0` Efectivo: 109,930
- `1` Débito: 54,903
- `2` Crédito: 24,099
- `3` Cortesía: 4,609

### `orders`
```
order_id    INT PK AUTO
_id         TEXT         -- UUID
Turn        VARCHAR(220)
OrderType   INT(5)       -- 1=UsoMembresia, 2=UsoPaquete, NULL=sin tipo
MembershipId TEXT        -- _id del membership_type (ObjectId)
UserId      VARCHAR(80)  -- _id del cliente
package_id  TEXT         -- _id del paquete (ObjectId)
Extras      TEXT
retracts_id INT
OrderDate   DATETIME
invoice_id  VARCHAR(160)
Price       DOUBLE
IsSync      INT(1)
lastSync    DATETIME
created_at / updated_at  DATETIME
```

**Distribución OrderType:**
- `2` (Paquete): 118,531
- `NULL`: 43,115
- `1` (Membresía): 33,390

### `clients`
```
client_id         INT PK AUTO
_id               VARCHAR(200)   -- ObjectId (24 chars hex)
tag               VARCHAR(200)   -- código de tag NFC/RFID (ej: e300000000002331)
tag_code          VARCHAR(20)
full_name         VARCHAR(60)    -- puede ser NULL (usar first_name + last_name)
first_name        VARCHAR(60)
last_name         VARCHAR(60)
active_membership VARCHAR(60)    -- _id de client_membership activa
email / phone / plate / brand / model / color
is_recurrent      VARCHAR(50)    -- '0' o '1'
facility          VARCHAR(80)    -- 'AQUA.JRZ.PROD.LOCAL'
tipo_tarjeta / titular / prosepago_id / banco / tipoTarjeta
renewal_count     INT(10)
IsSync / lastSync
created_at / updated_at
```

### `client_membership`
```
id              INT PK AUTO
_id             VARCHAR(150)   -- ObjectId (24 chars hex)
membership_id   VARCHAR(180)   -- tipo de membresía (ObjectId del paquete)
client_id       VARCHAR(120)   -- _id del cliente (con índice)
start_date      DATETIME
end_date        DATETIME
months          INT
prosepago_id    INT            -- folio de domiciliación Prosepago
is_blocked      TINYINT(1)     -- siempre NULL actualmente
uses            INT
facility        VARCHAR(40)    -- 'AQUA.JRZ.PROD.LOCAL'
isBlocked       VARCHAR(50)
extension_days  INT
IsSync / lastSync / created_at / updated_at
```

**Distribución por tipo de membresía:**
- `61344bab37a5f00383106c88` (Delux): 3,270
- `61344ae637a5f00383106c7a` (Express): 1,747
- `61344b9137a5f00383106c84` (Ultra): 1,286
- `61344b5937a5f00383106c80` (Básico): 1,015

### `staff_users`
```
id          INT PK AUTO
name        VARCHAR(100)
email       VARCHAR(100)
password    VARCHAR(100)
role        INT(1)           -- 1=SuperAdmin, 2=Admin, 3=Manager
active      INT(1)           -- 0=inactivo, 1=activo
user_role   ENUM('Administrador','Visualizador')
created_at / updated_at
```

> El campo `username` mencionado en el código NO existe — se usa `email` o `name` para login.

### `corte_de_caja`
```
id                         INT PK AUTO
sucursal                   VARCHAR(11)
fecha_corte                DATE
total_ventas               INT
total_tickets              INT
dinero_acumulado_efectivo  INT
dinero_acumulado_tarjeta   INT
dinero_acumulado_usd       DECIMAL(12,2)
total_efectivo_en_mxn      DECIMAL(12,2)
dinero_acumulado_otros     INT
dinero_recibido            INT
tipo_cambio                DECIMAL(10,4)
usuario_que_hizo_corte     INT  -- FK a staff_users.id
usuario_que_edito          INT
estado                     ENUM('Cerrado','Editado')
observaciones              TEXT
total_efectivo_mxn / total_efectivo_usd / tipo_cambio_aplicado  FLOAT
updated_at / created_at
```

### `special_orders`
Códigos BUSINESS — lavados con precio especial para empresas (QR codes).
```
id              INT PK AUTO
_id             VARCHAR(200)
promotion_user  VARCHAR(180)  -- usuario que generó la promo
code            VARCHAR(180)  -- UUID del código de uso
expiration      VARCHAR(180)  -- fecha ISO string
package         VARCHAR(80)   -- _id del paquete
price           DOUBLE        -- precio especial (0, 50, 150...)
uses            INT           -- usos disponibles (100, 1000...)
type            VARCHAR(12)   -- siempre 'BUSINESS'
status          VARCHAR(12)   -- siempre NULL actualmente
```

### `transactions_log`
Cola de sincronización POS → MySQL. JSON con `structure` y `data`.
```
id          INT PK AUTO
data        TEXT (FULLTEXT index)  -- JSON: structure = 'local_transactions'|'orders'|'clients'|etc.
process     INT(1)                 -- 0=pendiente (¡TODOS están en 0!)
updated_at / created_at
```

### `recurrent_logs`
Logs de cobros recurrentes vía Prosepago.
```
id          INT PK AUTO
data        TEXT   -- JSON con resultado del cobro
created_at / updated_at  DATE
```

### `packages` (catálogo)
| _id | name | price |
|---|---|---|
| `612abcd1c4ce4c141237a356` | Deluxe | $400 |
| `612f057787e473107fda56aa` | Express | $100 |
| `612f067387e473107fda56b0` | Basico | $1 (precio de prueba) |
| `612f1c4f30b90803837e7969` | Ultra | $5 (precio de prueba) |

### `services` / `packageExtras` / `extras`
Servicios incluidos y extras opcionales por paquete:
- **Ultra extras pagados:** Lava Soap ($50), Triple Foam ($50), Super Wax ($50)
- **Express extras:** Triple Foam ($50), Super Wax ($50), Ceramic ($60), Tire Shine ($70)
- **Basico extras:** Buff n Shine ($80)

### `facilities`
| facility_id | name | type |
|---|---|---|
| 1 | CEDIS | CEDIS |
| 2 | MISIONES | SUCURSAL |

### `products` (21 productos activos)
Bug Squasher, Yellow Foam, Pink Foam, Blue Foam, Bubble Pop, Red Street, Hot Hot Lava, Zeramic, Miami Vibes, Mud Off, Snoaw Foam, Wheel & Rim Cleaner, Super Drying Agent, Armor Max, Super Dryin agent...

### `tipo_de_cambio`
Un registro. Valor actual: **$20.00 MXN/USD**

### `fiscal_accounts`
530 registros. Regímenes más comunes: 612 (169), 601 (160), 626 (148), 625 (27)

---

## Paquetes de Lavado — IDs ObjectId

| ID | Nombre | Tipo |
|---|---|---|
| `612f057787e473107fda56aa` | Express | Pago único ($100) |
| `61344ae637a5f00383106c7a` | Express | Membresía |
| `612f067787e473107fda56b0` | Básico | Pago único |
| `61344b5937a5f00383106c80` | Básico | Membresía |
| `612f1c4f30b90803837e7969` | Ultra | Pago único ($180) |
| `61344b9137a5f00383106c84` | Ultra | Membresía |
| `612abcd1c4ce4c141237a356` | Delux | Pago único ($400) |
| `61344bab37a5f00383106c88` | Delux | Membresía |

---

## Enums y Catálogos

### TransactionType
| Valor | Significado | Registros |
|---|---|---|
| `0` | Compra de Membresía | 2,087 |
| `1` | Renovación de Membresía | 3,358 |
| `2` | Lavado | 188,096 |

### PaymentType
| Valor | Significado | Registros |
|---|---|---|
| `0` | Efectivo | 109,930 |
| `1` | Tarjeta de Débito | 54,903 |
| `2` | Tarjeta de Crédito | 24,099 |
| `3` | Cortesía / Garantía | 4,609 |

### OrderType (tabla `orders`)
| Valor | Significado | Registros |
|---|---|---|
| `1` | Uso de Membresía | 33,390 |
| `2` | Uso de Paquete (pago único) | 118,531 |
| `NULL` | Sin tipo | 43,115 |

---

## Módulos del Sistema

| Ruta | Controlador | Descripción |
|---|---|---|
| `/dashboard` | `DashboardController` | Resumen general y membresías activas |
| `/cajero` | `CajeroController` | Transacciones, métricas, tráfico por hora, exportaciones |
| `/membresias` | `MembershipController` | Gestión y reportes de membresías |
| `/indicadores` | `IndicadoresController` | Indicadores diarios por paquete y cajero |
| `/corte_caja` | `CorteCajaController` | Arqueos de caja, Excel/PDF |
| `/caja_chica` | `CajaChicaController` | Gastos de caja chica |
| `/compaq` | `CompaqController` | Integración contable Compaq, facturas globales |
| `/facturacion` | `FacturacionController` | Emisión de CFDIs vía Facturoporti |
| `/productos` | `ProductosController` | CRUD productos e inventario |
| `/transferencias` | `ProductosController` | Transferencias entre sucursales |
| `/usuarios` | `StaffUserController` | CRUD usuarios del staff |
| `/tipo_de_cambio` | `TipoDeCambioController` | Tipo de cambio USD/MXN |
| `/promociones` | `PromocionesController` | Códigos BUSINESS y promociones |
| `/administracion` | `AdministracionController` | Auditoría de gaps y monitor de sync |

---

## Sistema de Cobro Recurrente (Prosepago)

Los clientes con membresía tienen `prosepago_id` (folio de domiciliación bancaria).
`recurrent_logs` registra cada intento de cobro mensual automático.
Una renovación exitosa crea un nuevo registro en `client_membership` con nuevas fechas `start_date`/`end_date`.

---

## Facturación Fiscal

- Proveedor: **Facturoporti** (`api.facturoporti.com.mx`)
- RFC: `ACC220922GT0` | Razón Social: `AQUA CAR CLUB` | Régimen: `601` | CP: `32030`
- Sandbox: `testapi.facturoporti.com.mx`
- **AVISO:** Credenciales (CSD, password, API key) hardcodeadas en `app/Models/GeneralCatalogs.php`

---

## Notas Importantes

1. **IDs tipo MongoDB heredados** — ObjectIds de 24 chars hex. La DB migró de MongoDB a MySQL pero conserva estos IDs como strings en todas las tablas.
2. **`transactions_log` tiene 1.2M registros todos `process=0`** — el proceso de sincronización no está marcando registros como procesados. Bug o deuda técnica pendiente.
3. **Deduplicar `local_transaction` por `_id`** — un mismo `_id` puede tener múltiples filas. Siempre usar `GROUP BY _id` + `SUM(Total)` para montos reales.
4. **Solo MISIONES tiene datos activos** — CEDIS existe en facilities pero sin transacciones.
5. **`clients.full_name` puede ser NULL** — usar `CONCAT(first_name, ' ', last_name)`.
6. **`staff_users` no tiene campo `username`** — el login usa `email` o `name`.
7. La ruta `/vending` está pendiente de desarrollar (solo carga vista, sin controlador).

---

## Hallazgos de Datos Reales (muestra 1000 filas por tabla)

### `local_transaction` (1000 más recientes)
- **`_id` INTERLOGIC**: numérico incremental (ej: `95740`, `114911`) — dos secuencias paralelas por cajero
- **`_id` CARRERA**: UUID (`f104f389-...`) — solo aparece en membresías especiales de QR
- **`CadenaFacturacion`**: string hex de 16 chars (ej: `2020391703261201`) — folio impreso en el ticket para facturar
- **`PaymentFolio`**: numérico de Prosepago (ej: `50307716`) — solo tiene valor en pagos con tarjeta (domiciliación); es `0` en efectivo
- **`Membership`**: puede ser ObjectId de 24 chars (membresía de cliente), UUID especial (QR fijo), o vacío/null
- **`Total` distribución real**: 50% de registros tienen Total=0 (lavados con membresía), media=$148, max=$699
- **`fiscal_invoice`**: solo 3 de 1000 recientes tienen CFDI — la facturación es muy poco usada
- **`integrate_cp`**: NULL en los 1000 más recientes — la integración Compaq está inactiva actualmente
- **Atm en recientes**: AQUA01=54%, AQUA02=46% — muy balanceado
- **Paquete más lavado**: Express (385/1000), seguido de Ultra (161), Delux (143)

### `orders` (1000 más recientes)
- **OrderType en recientes**: 71.5% son paquete (tipo 2), 28.5% membresía (tipo 1) — los NULL del total histórico ya no aparecen en recientes
- **`_id`**: siempre UUID (`7d1fee54-...`) — todos vienen de INTERLOGIC
- **`MembershipId` en OrderType=1**: usa los IDs del paquete de membresía (ej: `61344bab37a5f00383106c88` = Delux) no el `_id` del `client_membership`
- **`UserId`**: ObjectId de 24 chars — referencia a `clients._id`
- **Precios reales de paquetes**: Express=$130, Delux=$240, Ultra=$180, Básico=$0 (membresía gratuita)
- **`IsSync`**: NULL en todos — campo heredado de MongoDB, ya no se usa
- **Básico casi sin uso**: solo 3 de 1000 — el paquete Básico está prácticamente en desuso

### `clients` (1000 más recientes)
- **`tag`**: formato `e300000000XXXXXX` — código NFC/RFID de la tarjeta física del cliente (case-insensitive: hay mayúsculas y minúsculas)
- **`tag_code`**: los últimos 4 dígitos del tag (ej: `2331`) — número de tarjeta corto
- **`is_recurrent`**: 75.7% tienen `'1'` — la mayoría de clientes tienen domiciliación activa
- **`facility`**: siempre `'AQUA.JRZ.PROD.LOCAL'` — identificador del sistema POS
- **`renewal_count`**: todos en 0 en los 1000 más recientes — campo no actualizado
- **`active_membership`**: todos tienen valor (ninguno NULL en recientes) — todos tienen membresía
- **Marcas más comunes**: Nissan (65+45), Ford (52), Chevrolet (42+24), Toyota (35+25), GMC (33), Honda (33), Kia (30)

### `client_membership` (1000 más recientes)
- **Membresía más popular**: Delux 40.4%, Express 35.9%, Ultra 23.6%, Básico 0.1%
- **Duración**: casi siempre 1 mes (`months=1`), solo 3 registros de 3 meses
- **Duración real en días**: media=30.4 días, min=28, max=93 (extensiones)
- **`uses`**: siempre 0 en los 1000 recientes — el conteo de usos no se actualiza en MySQL (se lleva en MongoDB/POS)
- **`extension_days`**: siempre 0 en recientes — sin extensiones activas
- **`prosepago_id`**: todos tienen valor — confirma que todos tienen domiciliación bancaria
- **`facility`**: siempre `'AQUA.JRZ.PROD.LOCAL'`

### `special_orders` (1000 más recientes)
- **99.7% son códigos Delux** (`612abcd1c4ce4c141237a356`)
- **Precio dominante**: $50 (99.4%) — descuento especial para empresas en paquete Delux
- **`uses` mayoritario**: 1 uso por código (99.3%) — la mayoría son códigos de un solo uso
- **Solo 1 `promotion_user` único** (`678ab435ee1026a922940d5b`) — todos los códigos recientes los genera el mismo usuario de promociones
- Hay lotes de 1000 usos (6 registros) y uno de 100 usos — para cuentas corporativas grandes

### `recurrent_logs` — Estructura JSON real
```json
{
  "_id": "69bd7c3a...",
  "success": true,
  "message": "Recurrencia creada",
  "clientId": "69bd76b2...",
  "data": [{ "status": true, "mensaje": "OK",
             "datos": "[{\"FolioTransaccion\":50305519, \"FolioDomiciliacion\":66434, ...}]" }]
}
```
- `success: true` = cobro exitoso → se crea nueva `client_membership`
- `success: false, message: "Primera transaccion extendida"` = primer cobro fallido → se extiende la membresía gratis
- `FolioTransaccion` y `FolioDomiciliacion` son los folios de Prosepago del cargo bancario

### `transactions_log` — Distribución de structures (500 más recientes)
| structure | % |
|---|---|
| `local_transactions` | 48% |
| `orders` | 45.8% |
| `client_memberships` | 3.4% |
| `used_business_codes` | 2% |
| `recurrent_logs` | 0.4% |
| `clients` | 0.4% |

---

---

# AquaFacturacion — Subproyecto de Facturación al Cliente

**Ruta:** `C:\Users\alejandro.martinez\Desktop\codigo\AquaFacturacion`

## Descripción

AquaFacturacion es un proyecto Laravel **independiente** que actúa como el portal público de facturación para los clientes finales de Aqua Car Club. Usa la **misma base de datos** que AquaStaff (`u514131194_d0CoK` en `10.200.1.227`).

Su función principal es doble:
1. **Receptor de datos del POS** — recibe transacciones del sistema de cajeros automáticos vía endpoint HTTP (`/end_point`) y las guarda en las tablas compartidas (`local_transaction`, `orders`, `clients`, `client_membership`, etc.)
2. **Portal de facturación para clientes** — permite a los clientes registrarse, buscar su ticket por folio y generar su CFDI vía Facturoporti.

---

## Flujo de Datos (POS → DB)

```
Cajero físico (INTERLOGIC/CARRERA)
    │
    ▼
POST /end_point  (AquaFacturacion)
    │
    ├─ Guarda en transactions_log (cola)
    └─ Guarda directamente en la tabla correspondiente según 'structure':
        ├─ local_transactions  → tabla local_transaction
        ├─ orders              → tabla orders + packages + packageExtras + services + extras
        ├─ clients             → tabla clients (upsert por _id)
        ├─ client_memberships  → tabla client_membership
        ├─ promotion_users     → tabla promotion_users
        ├─ special_orders      → tabla special_orders
        ├─ used_business_codes → tabla used_business_codes
        └─ recurrent_logs      → tabla recurrent_logs
```

El endpoint acepta `POST /end_point` con body JSON:
```json
{
  "structure": "local_transactions",
  "data": { ... }
}
```

También existe `POST /end_point_sandbox/` que guarda en tablas `_test` (entorno de pruebas).

---

## Controladores

| Controlador | Responsabilidad |
|---|---|
| `TransactionsLogController` | Recibe datos del POS, guarda en `transactions_log` y en la tabla destino según `structure` |
| `TransactionsLogTestController` | Igual pero guarda en tablas `_test` (sandbox) |
| `FiscalInvoicesController` | Búsqueda de tickets, generación de CFDIs, descarga de PDF/XML |
| `FiscalDataController` | CRUD de cuentas fiscales del cliente (RFC, régimen, CFDI use, dirección) |
| `UserController` | Registro, login, verificación de email, recuperación de contraseña |

---

## Rutas Principales

### Públicas (sin auth)
| Ruta | Método | Descripción |
|---|---|---|
| `/` | GET | Página principal / login |
| `/login` | POST | Autenticación de clientes |
| `/create_account` | GET/POST | Registro de nuevo cliente |
| `/verify_account/{email}/{id}/{hid}` | GET | Verificación de email |
| `/lost_password` | GET | Recuperar contraseña |
| `/ticket/{reference}` | GET | Ver ticket por `CadenaFacturacion` |
| `/invoice_ticket` | POST | Buscar ticket para facturar (por folio) |
| `/ticket_no_account` | GET | Facturar sin cuenta registrada |
| `/generate_invoice_no_account` | POST | Generar CFDI sin cuenta |
| `/end_point/` | POST | **Endpoint del POS** — recibe datos de cajeros |
| `/end_point_sandbox/` | POST | Endpoint sandbox (tablas _test) |

### Requieren auth
| Ruta | Descripción |
|---|---|
| `/dashboard` | Panel del cliente |
| `/fiscal_data` | Ver/gestionar cuentas fiscales guardadas |
| `/fiscal_invoices` | Ver tickets pendientes de facturar |
| `/generate_invoice` | Generar CFDI con cuenta guardada |
| `/show_invoices` | Ver facturas emitidas |
| `/download/pdf/{folio}` | Descargar PDF de factura |
| `/download/xml/{folio}` | Descargar XML de factura |

---

## Autenticación (clientes públicos)

- Modelo: `User` (tabla `users`, 2 registros en DB)
- Requiere verificación de email (`activate = 1`)
- Login con email + password + reCAPTCHA
- Envía correo de bienvenida (`WelcomeEmail`) y recuperación (`ResetPassword`)
- **Diferente al `StaffUser` de AquaStaff** — son sistemas de auth separados aunque comparten DB

---

## Facturación CFDI — Lógica

### Buscar ticket
- El cliente ingresa su `CadenaFacturacion` (folio alfanumérico impreso en ticket)
- Se busca en `local_transaction.CadenaFacturacion`
- Valida que no tenga `fiscal_invoice` (no facturado aún) y `PaymentType != 3` (no cortesía)

### Generar CFDI (con cuenta guardada)
1. Busca `FiscalAccount` por RFC del usuario
2. Construye JSON CFDI 4.0 para Facturoporti
3. Llama `POST /servicios/timbrar/json` con Bearer token
4. Si éxito (`codigo == '000'`): guarda UUID en `local_transaction.fiscal_invoice`, guarda `fiscal_account_id`
5. Guarda PDF y XML en `storage/app/public/pdfs/` y `storage/app/public/xmls/`
6. Envía correo con adjuntos PDF+XML al cliente

### Generar CFDI (sin cuenta — `/ticket_no_account`)
- Cliente llena RFC y datos fiscales en el momento
- Crea o actualiza `FiscalAccount` con `account_id = NULL`
- Mismo proceso de timbrado

### Datos del CFDI generado
- Concepto: `"Lavado de automóviles"` (código `76111801`, unidad `E48`)
- IVA: 8% (`Tasa: 0.080000`)
- SubTotal: `Total / 1.08` (precio sin IVA)
- Serie: `AB`, FormaPago mapeado del `PaymentType`
- LugarExpedicion: `32330` (nota: GeneralCatalogs dice `32030`, aquí hay `32330` — posible discrepancia)

### Flujo token Facturoporti
1. `GET /token/crear?Usuario=...&Password=...` → obtiene Bearer token
2. Usa token en `POST /servicios/timbrar/json`
3. `DELETE /token/borrar` — elimina el token después de usarlo

---

## Modelo User (clientes públicos)

Tabla `users` (2 registros en DB):
```
id           INT PK AUTO
name         VARCHAR
last_name    VARCHAR
email        VARCHAR  unique
password     VARCHAR  (hashed)
activate     INT(1)   -- 1=activo, 0=pendiente verificación
process_token VARCHAR -- para recuperación de contraseña (MD5)
created_at / updated_at
```

---

## Tablas _test (Sandbox)

Para pruebas existe un juego paralelo de tablas con sufijo `_test`:
- `local_transaction_test`, `orders_test`, `clients_test`, `client_membership_test`
- `packages_test`, `package_extras_test`, `services_test`
- `promotion_users_test`, `special_orders_test`, `used_business_codes_test`
- `transactions_log_test`, `recurrent_logs_test`

El endpoint `POST /end_point_sandbox/` usa `TransactionsLogTestController` que escribe en estas tablas.

---

## Zip Codes

La tabla `zip_codes_mx` (148,321 registros) se usa para autocompletar estado, municipio y colonia al registrar una cuenta fiscal. Se consulta por `zip_code`, `state` y `municipality`.

---

## Notas Importantes de AquaFacturacion

1. **Comparte la misma DB que AquaStaff** — cualquier cambio en `local_transaction`, `clients`, `orders`, etc. afecta ambos sistemas.
2. **El endpoint `/end_point` es el punto de entrada de todos los datos del POS** — si falla, los datos no llegan a la DB. Es crítico.
3. **`transactions_log` como cola** — cada llamada al `/end_point` primero guarda el JSON raw en `transactions_log` y luego procesa. Esto explica los 1.2M registros con `process=0` en AquaStaff: el campo `process` nunca se actualiza a `1` tras procesarlo.
4. **Clientes (`structure = 'clients'`) hacen upsert** — si el `_id` ya existe, solo actualiza `is_recurrent`. Los demás structures siempre hacen INSERT.
5. **`Orders` también guarda `packages`, `packageExtras`, `services` y `extras`** — al recibir un `order`, desempaqueta y guarda cada sub-entidad si no existe.
6. **Facturación usa `CadenaFacturacion`** como referencia principal del ticket (no `PaymentFolio`). Es el folio impreso en el comprobante físico.
7. **CP en CFDI**: el controlador usa `LugarExpedicion: '32330'` pero `GeneralCatalogs` define `'32030'`. Revisar cuál es correcto para el SAT.
8. **SSL hardcodeado**: la ruta del certificado SSL está en `C:\php\extras\ssl\cacert.pem` — configuración específica del servidor de producción Windows.
