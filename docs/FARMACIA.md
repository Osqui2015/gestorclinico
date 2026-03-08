# Sistema de Farmacia - Gestor Clínico

## Descripción General

El sistema de farmacia es un módulo completo para gestionar el inventario de medicamentos, instrumentos médicos e insumos dentro del centro clínico. Incluye control de stock, alertas automáticas, gestión de solicitudes de médicos y seguimiento de vencimientos y esterilizaciones.

## Características Principales

### 1. Gestión de Inventario

- **Medicamentos**: Control de stock, vencimientos, lotes y laboratorios
- **Instrumentos**: Gestión de equipamiento médico con control de esterilización
- **Insumos**: Materiales necesarios para procedimientos y operaciones

### 2. Sistema de Alertas

El sistema genera alertas automáticas para:

- **Stock Bajo**: Items con stock igual o inferior al mínimo configurado
- **Próximos a Vencer**: Items que vencen en los próximos 30 días
- **Vencidos**: Items que ya superaron su fecha de vencimiento
- **Esterilización Pendiente**: Instrumentos que requieren esterilización en los próximos 7 días

### 3. Solicitudes de Farmacia

Los médicos pueden crear solicitudes de materiales que la farmacia procesa y entrega, manteniendo un registro completo de cada transacción.

### 4. Trazabilidad Completa

Cada movimiento de stock queda registrado con:

- Tipo de movimiento (entrada, salida, ajuste, devolución, vencido, dañado)
- Usuario que realizó el movimiento
- Fecha y hora
- Cantidades antes y después del movimiento
- Referencias y notas

## Estructura de Base de Datos

### Tablas Creadas

#### 1. `pharmacy_items`

Almacena todos los items del inventario (medicamentos, instrumentos, insumos).

**Campos principales:**

- `name`: Nombre del item
- `type`: Tipo (medication, instrument, supply)
- `code`: Código único interno
- `laboratory`: Laboratorio fabricante (para medicamentos)
- `current_stock`: Stock actual
- `minimum_stock`: Stock mínimo para alertas
- `reorder_point`: Punto de reorden
- `expiration_date`: Fecha de vencimiento
- `requires_sterilization`: Indica si requiere esterilización
- `last_sterilization_date`: Última fecha de esterilización
- `next_sterilization_date`: Próxima fecha de esterilización programada
- `status`: Estado (active, inactive, discontinued)

#### 2. `pharmacy_requests`

Solicitudes de materiales realizadas por médicos.

**Campos principales:**

- `requested_by`: ID del médico que solicita
- `patient_id`: Paciente relacionado (opcional)
- `appointment_id`: Cita relacionada (opcional)
- `processed_by`: ID del farmacéutico que procesa
- `priority`: Prioridad (low, normal, high, urgent)
- `status`: Estado (pending, processing, completed, cancelled)
- `requested_at`: Fecha de solicitud
- `processed_at`: Fecha de inicio de procesamiento
- `completed_at`: Fecha de finalización

#### 3. `pharmacy_request_items`

Items individuales dentro de cada solicitud.

**Campos principales:**

- `pharmacy_request_id`: ID de la solicitud
- `pharmacy_item_id`: ID del item solicitado
- `quantity_requested`: Cantidad solicitada
- `quantity_delivered`: Cantidad entregada
- `notes`: Notas sobre el item

#### 4. `pharmacy_stock_movements`

Registro de todos los movimientos de stock.

**Campos principales:**

- `pharmacy_item_id`: ID del item
- `movement_type`: Tipo de movimiento
- `quantity`: Cantidad (positiva o negativa)
- `stock_before`: Stock antes del movimiento
- `stock_after`: Stock después del movimiento
- `user_id`: Usuario que realizó el movimiento
- `pharmacy_request_id`: Solicitud relacionada (si aplica)
- `reference`: Referencia externa (factura, orden, etc.)
- `notes`: Notas adicionales

## Roles y Permisos

### Rol: Pharmacy

Nuevo rol agregado al sistema con acceso completo a:

- Dashboard de farmacia
- Gestión de items (crear, editar, eliminar)
- Procesamiento de solicitudes
- Ajustes de stock
- Actualización de esterilizaciones

### Rol: Doctor

Los médicos pueden:

- Crear solicitudes de farmacia
- Ver sus propias solicitudes
- Ver el estado de sus solicitudes

### Rol: Admin

Los administradores tienen acceso completo a todas las funcionalidades de farmacia.

## Flujo de Trabajo

### Creación de Items

1. El farmacéutico accede a "Nuevo Item"
2. Completa la información básica (nombre, tipo, código)
3. Configura stock y alertas (stock actual, mínimo, punto de reorden)
4. Agrega información de vencimiento y lote (si aplica)
5. Para instrumentos, configura esterilización (si aplica)
6. Guarda el item

### Solicitudes de Médicos

1. El médico crea una solicitud desde su dashboard
2. Selecciona los items necesarios y cantidades
3. Asigna prioridad (baja, normal, alta, urgente)
4. Opcionalmente vincula con paciente/cita
5. Envía la solicitud

### Procesamiento en Farmacia

1. La farmacia ve solicitudes pendientes en orden de prioridad
2. Marca la solicitud como "En Proceso"
3. Prepara los items solicitados
4. Registra las cantidades entregadas
5. El sistema actualiza automáticamente el stock
6. Se registra el movimiento de stock
7. La solicitud se marca como "Completada"

### Ajustes de Stock

1. Acceder al item desde el inventario
2. Usar "Ajustar Stock"
3. Seleccionar tipo de movimiento:
    - **Entrada**: Nueva compra o recepción
    - **Salida**: Consumo no registrado por solicitud
    - **Ajuste**: Corrección de inventario
    - **Devolución**: Retorno de item
    - **Vencido**: Descarte por vencimiento
    - **Dañado**: Descarte por daño
4. Ingresar cantidad y notas
5. Confirmar ajuste

## Rutas del Sistema

### Rutas de Farmacia (Requieren rol pharmacy o admin)

```
GET  /pharmacy/dashboard                  - Dashboard principal
GET  /pharmacy/items                      - Listado de items
GET  /pharmacy/items/create               - Formulario nuevo item
POST /pharmacy/items                      - Guardar nuevo item
GET  /pharmacy/items/{id}                 - Ver detalle de item
GET  /pharmacy/items/{id}/edit            - Formulario editar item
PUT  /pharmacy/items/{id}                 - Actualizar item
DELETE /pharmacy/items/{id}               - Eliminar item
POST /pharmacy/items/{id}/adjust-stock    - Ajustar stock
POST /pharmacy/items/{id}/update-sterilization - Actualizar esterilización

GET  /pharmacy/requests                   - Listado de solicitudes
GET  /pharmacy/requests/{id}              - Ver detalle de solicitud
POST /pharmacy/requests/{id}/process      - Iniciar procesamiento
POST /pharmacy/requests/{id}/deliver      - Entregar items
POST /pharmacy/requests/{id}/cancel       - Cancelar solicitud
```

### Rutas para Médicos (Requieren rol doctor o admin)

```
GET  /pharmacy-requests/my-requests       - Mis solicitudes
GET  /pharmacy-requests/create            - Nueva solicitud
POST /pharmacy-requests                   - Guardar solicitud
GET  /pharmacy-requests/{id}              - Ver solicitud
```

## Modelos y Relaciones

### PharmacyItem

```php
// Relaciones
- stockMovements() -> PharmacyStockMovement[]
- requestItems() -> PharmacyRequestItem[]

// Métodos útiles
- isLowStock(): bool
- isExpiringSoon(): bool
- isExpired(): bool
- isSterilizationDue(): bool
- getTypeLabel(): string
- getStatusLabel(): string

// Scopes
- lowStock()
- expiringSoon()
- expired()
- sterilizationDue()
```

### PharmacyRequest

```php
// Relaciones
- requestedBy() -> User
- processedBy() -> User
- patient() -> Patient
- appointment() -> Appointment
- items() -> PharmacyRequestItem[]
- stockMovements() -> PharmacyStockMovement[]

// Métodos útiles
- getPriorityLabel(): string
- getStatusLabel(): string

// Scopes
- pending()
- processing()
- urgent()
```

### PharmacyRequestItem

```php
// Relaciones
- pharmacyRequest() -> PharmacyRequest
- pharmacyItem() -> PharmacyItem

// Métodos útiles
- isFullyDelivered(): bool
- getPendingQuantity(): int
```

### PharmacyStockMovement

```php
// Relaciones
- pharmacyItem() -> PharmacyItem
- user() -> User
- pharmacyRequest() -> PharmacyRequest

// Métodos útiles
- getMovementTypeLabel(): string
```

## Dashboard de Farmacia

El dashboard principal muestra:

### Estadísticas

- Total de items activos
- Desglose por tipo (medicamentos, instrumentos, insumos)
- Solicitudes pendientes
- Solicitudes en proceso
- Total de alertas activas

### Panel de Alertas

- **Stock Bajo**: Lista de items con stock crítico
- **Próximos a Vencer**: Items que vencen pronto
- **Vencidos**: Items ya vencidos
- **Esterilización Pendiente**: Instrumentos que necesitan esterilización

### Solicitudes Pendientes

- Lista de solicitudes ordenadas por prioridad
- Información del médico solicitante
- Paciente relacionado (si aplica)
- Cantidad de items solicitados
- Acceso rápido para procesar

### Acciones Rápidas

- Crear nuevo item
- Ver inventario completo
- Ver todas las solicitudes
- Ver alertas específicas

## Migraciones

Para implementar el sistema, ejecutar:

```bash
php artisan migrate
```

Las migraciones crearán:

1. Actualización del enum de roles en `users` (agrega 'pharmacy')
2. Tabla `pharmacy_items`
3. Tabla `pharmacy_requests`
4. Tabla `pharmacy_request_items`
5. Tabla `pharmacy_stock_movements`

## Componentes de Vista

### Vistas Creadas

- `resources/js/Pages/Pharmacy/Dashboard.vue` - Dashboard principal
- `resources/js/Pages/Pharmacy/Items/Index.vue` - Listado de items
- `resources/js/Pages/Pharmacy/Items/Create.vue` - Crear/Editar item

### Vistas Pendientes (para implementar según necesidad)

- `Pharmacy/Items/Show.vue` - Detalle de item con historial
- `Pharmacy/Items/Edit.vue` - Edición de item (puede reutilizar Create.vue)
- `Pharmacy/Requests/Index.vue` - Lista de solicitudes
- `Pharmacy/Requests/Show.vue` - Detalle y procesamiento de solicitud
- `Doctor/PharmacyRequests.vue` - Solicitudes del médico

## Políticas de Acceso

### PharmacyItemPolicy

Controla el acceso a items de farmacia:

- Ver: pharmacy, admin, doctor
- Crear: pharmacy, admin
- Editar: pharmacy, admin
- Eliminar: pharmacy, admin
- Ajustar stock: pharmacy, admin

### PharmacyRequestPolicy

Controla el acceso a solicitudes:

- Ver todas: pharmacy, admin
- Ver propias: doctor
- Crear: doctor, admin
- Procesar: pharmacy, admin
- Entregar: pharmacy, admin
- Cancelar: pharmacy, admin, doctor (propias)

## Middleware

### EnsurePharmacy

Middleware que verifica que el usuario tenga rol 'pharmacy' o 'admin'.

- Redirige a login si no está autenticado
- Retorna 403 si no tiene permisos

## Próximos Pasos Sugeridos

1. **Crear vistas faltantes**:
    - Show de items con historial de movimientos
    - Index y Show de solicitudes con procesamiento
    - Vista de médico para crear solicitudes

2. **Reportes**:
    - Reporte de consumo por período
    - Reporte de items más solicitados
    - Reporte de vencimientos del mes
    - Estadísticas de stock

3. **Funcionalidades adicionales**:
    - Exportación a Excel/PDF
    - Códigos de barras para items
    - Sistema de notificaciones por email
    - Recordatorios automáticos de esterilización
    - Integración con proveedores

4. **Optimizaciones**:
    - Caché de alertas
    - Jobs para limpieza de items vencidos
    - Notificaciones push

## Soporte y Mantenimiento

### Verificar Errores

```bash
php artisan route:list | grep pharmacy
php artisan make:user --role=pharmacy  # Crear usuario de farmacia
```

### Logs

Los movimientos de stock y cambios importantes quedan registrados en:

- `pharmacy_stock_movements` tabla
- Laravel logs en `storage/logs/`

### Base de Datos

Para resetear solo las tablas de farmacia (¡CUIDADO: borra datos!):

```bash
php artisan migrate:rollback --step=5
php artisan migrate
```

## Contacto y Ayuda

Para soporte adicional o reportar problemas con el módulo de farmacia, contactar al administrador del sistema.

---

**Última actualización**: Marzo 2026
**Versión**: 1.0.0
