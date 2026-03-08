# Módulo: Internación (Gestión de Camas e Historial)

## Descripción General

El módulo de **Internación** permite gestionar camas hospitalarias, internaciones de pacientes, control de altas médicas y limpieza de camas. Es utilizado principalmente por enfermeros/personal de internación, médicos y administradores para controlar todo el flujo de hospitalización de pacientes.

### Funcionalidades Principales

1. **Gestión de Camas**: Visualización del estado de todas las camas (disponibles, ocupadas, pendientes de limpieza, en limpieza, mantenimiento)
2. **Internaciones**: Registro de ingresos de pacientes, asignación de camas
3. **Alta Médica**: Control de fechas estimadas de alta y ejecución de altas
4. **Limpieza de Camas**: Gestión del proceso de limpieza post-alta
5. **Historial**: Registro completo de todas las internaciones
6. **Observaciones Diarias**: Seguimiento del estado del paciente

---

## Estados de Cama

### Estados Posibles

| Estado             | Descripción                                    | Color Badge |
| ------------------ | ---------------------------------------------- | ----------- |
| `available`        | Cama disponible y limpia para recibir paciente | Verde       |
| `occupied`         | Cama ocupada con paciente                      | Rojo        |
| `pending_cleaning` | Libre pero necesita limpieza (post-alta)       | Amarillo    |
| `cleaning`         | En proceso de limpieza                         | Azul        |
| `maintenance`      | Fuera de servicio por mantenimiento            | Gris        |

### Tipos de Cama

- **Standard**: Cama estándar de internación general
- **Intensive Care**: Camas de terapia intensiva (UTI/UCI)
- **Isolation**: Camas de aislamiento
- **Pediatric**: Camas pediátricas
- **Psychiatric**: Camas de internación psiquiátrica

---

## Estados de Internación

### Estados del Paciente

| Estado        | Descripción                       |
| ------------- | --------------------------------- |
| `active`      | Paciente actualmente internado    |
| `discharged`  | Alta médica ejecutada             |
| `transferred` | Transferido a otra cama           |
| `deceased`    | Fallecimiento durante internación |

### Tipos de Admisión

- **Emergency**: Ingreso por emergencia
- **Scheduled**: Internación programada
- **Post-surgical**: Internación post-operatoria
- **Transfer**: Transferencia desde otro servicio/institución

---

## Flujo de Trabajo

### 1. Internación de Paciente

```
1. Enfermero crea nueva internación desde "Nueva Internación"
2. Selecciona paciente, cama disponible y médico responsable
3. Completa datos: motivo, tipo de admisión, diagnóstico, tratamiento
4. Sistema marca cama como "Ocupada"
5. Internación queda en estado "Active"
```

### 2. Seguimiento Diario

```
1. Personal de internación/médico accede a la cama
2. Actualiza "Observaciones Diarias" con evolución del paciente
3. Médico puede actualizar fecha estimada de alta en cualquier momento
```

### 3. Alta del Paciente

```
1. Médico responsable indica fecha estimada de alta
2. Sistema marca paciente como "Listo para Alta" cuando se alcanza la fecha
3. Enfermero ejecuta el alta desde la vista de la cama
4. Sistema:
   - Cambia estado internación a "Discharged"
   - Marca cama como "Pending Cleaning"
   - Registra usuario que dio el alta y timestamp
```

### 4. Limpieza de Cama

```
1. Personal de limpieza inicia proceso: "Iniciar Limpieza"
2. Sistema cambia estado a "Cleaning"
3. Al finalizar, marca como "Limpia" indicando tipo de limpieza
4. Sistema registra:
   - Tipo de limpieza (rutina/profunda/post-alta/desinfección)
   - Usuario que realizó limpieza
   - Duración del proceso
5. Cama vuelve a estado "Available"
```

### 5. Transferencia de Paciente

```
1. Se solicita transferencia a otra cama (motivo opcional)
2. Sistema:
   - Marca internación actual como "Transferred"
   - Crea nueva internación en la cama destino
   - Libera cama original (Pending Cleaning)
   - Ocupa cama destino
3. Se mantiene continuidad en el historial del paciente
```

---

## Estructura de Archivos

### Backend (Laravel)

#### Migraciones

```
database/migrations/
├── 2026_03_07_000012_create_rooms_table.php          # Habitaciones/salas
├── 2026_03_07_000013_create_beds_table.php           # Camas
├── 2026_03_07_000014_create_hospitalizations_table.php # Internaciones
└── 2026_03_07_000015_create_bed_cleaning_logs_table.php # Logs de limpieza
```

#### Modelos

```
app/Models/
├── Room.php                  # Modelo de habitación
├── Bed.php                   # Modelo de cama con lógica de estados
├── Hospitalization.php       # Modelo de internación con workflow
└── BedCleaningLog.php        # Registro de limpiezas
```

**Relaciones**:

- `Room` hasMany `Bed`
- `Bed` hasMany `Hospitalization`
- `Bed` hasOne `currentHospitalization` (internación activa)
- `Bed` hasMany `BedCleaningLog`
- `Hospitalization` belongsTo `Patient`, `Bed`, `Doctor`, `Operation`
- `Patient` hasMany `Hospitalization`
- `Patient` hasOne `currentHospitalization` (activa)

#### Controlador

```
app/Http/Controllers/
└── HospitalizationController.php  # Controlador principal (11 métodos)
```

**Métodos del Controlador**:

1. `index()` - Lista de camas con filtros y estadísticas
2. `show($bed)` - Detalle de cama con información completa
3. `create()` - Formulario de nueva internación
4. `store()` - Registrar internación
5. `updateDischargeDate($hospitalization)` - Actualizar fecha estimada de alta (médico)
6. `discharge($hospitalization)` - Ejecutar alta
7. `transfer($hospitalization)` - Transferir a otra cama
8. `updateObservations($hospitalization)` - Actualizar observaciones diarias
9. `startCleaning($bed)` - Iniciar limpieza
10. `markCleaned($bed)` - Completar limpieza
11. `history()` - Historial de internaciones

#### Middleware

```
app/Http/Middleware/
└── EnsureHospitalizationAccess.php  # Control de acceso (nurse/doctor/admin)
```

### Frontend (Vue 3 + TypeScript)

```
resources/js/Pages/Hospitalization/
├── Index.vue     # Vista principal con grid de camas (460+ líneas)
├── Show.vue      # Detalle de cama con tabs (600+ líneas)
├── Create.vue    # Formulario nueva internación (350+ líneas)
└── History.vue   # Historial completo (400+ líneas)
```

**Vistas**:

1. **Index.vue**:
    - Cards de estadísticas (Total, Disponibles, Ocupadas, Pendiente Limpieza, Listos para Alta)
    - Filtros: Estado, Tipo de cama, Habitación, Piso, Búsqueda de paciente
    - Tabla con información de cada cama
    - Paginación

2. **Show.vue** (3 Tabs):
    - **Tab "Internación Actual"**:
        - Info del paciente (nombre, DNI, teléfono, email)
        - Info de internación (médico, tipo admisión, fechas, motivo, diagnóstico, tratamiento)
        - Observaciones diarias (editable)
        - Gestión de alta (actualizar fecha estimada, dar alta)
    - **Tab "Historial"**: Internaciones pasadas en esa cama
    - **Tab "Limpiezas"**: Registros de limpieza de la cama

3. **Create.vue**:
    - Selección de paciente (dropdown con búsqueda)
    - Selección de cama disponible (filtradas)
    - Selección de médico responsable
    - Tipo de admisión, fechas, motivo, diagnóstico, tratamiento

4. **History.vue**:
    - Filtros: Estado, Paciente, Médico, Rango de fechas
    - Tabla con todas las internaciones del sistema
    - Paginación

---

## Rutas

### Rutas Registradas (11 total)

```php
GET    /hospitalizations                                      -> index
GET    /hospitalizations/create                              -> create
POST   /hospitalizations                                      -> store
GET    /hospitalizations/history                             -> history
GET    /hospitalizations/{bed}                               -> show

POST   /hospitalizations/hospitalizations/{hospitalization}/update-discharge-date  -> updateDischargeDate
POST   /hospitalizations/hospitalizations/{hospitalization}/discharge              -> discharge
POST   /hospitalizations/hospitalizations/{hospitalization}/transfer               -> transfer
POST   /hospitalizations/hospitalizations/{hospitalization}/update-observations    -> updateObservations

POST   /hospitalizations/beds/{bed}/start-cleaning           -> startCleaning
POST   /hospitalizations/beds/{bed}/mark-cleaned             -> markCleaned
```

**Middleware**: `EnsureHospitalizationAccess` (verifica rol: nurse, doctor o admin)

**Nombres de Rutas**:

```
hospitalizations.index
hospitalizations.create
hospitalizations.store
hospitalizations.show
hospitalizations.history
hospitalizations.hospitalizations.update-discharge-date
hospitalizations.hospitalizations.discharge
hospitalizations.hospitalizations.transfer
hospitalizations.hospitalizations.update-observations
hospitalizations.beds.start-cleaning
hospitalizations.beds.mark-cleaned
```

---

## Roles y Permisos

| Rol                     | Permisos                                                                                                       |
| ----------------------- | -------------------------------------------------------------------------------------------------------------- |
| **Nurse (Enfermero/a)** | Ver camas, crear internaciones, dar altas, transferir pacientes, gestionar limpiezas, actualizar observaciones |
| **Doctor (Médico)**     | Ver camas de sus pacientes, actualizar fecha estimada de alta, actualizar observaciones, autorizar altas       |
| **Admin**               | Acceso total a todas las funcionalidades                                                                       |

**Acceso en Navegación**:

- Enfermeros: "🛏️ Gestión de Camas" (menú principal)
- Médicos: "🛏️ Internación" (en menú de doctor)
- Admin: "🛏️ Internación" (en menú admin)

---

## Instalación y Deployment

### 1. Ejecutar Migraciones

```bash
php artisan migrate
```

Esto creará 4 tablas:

- `rooms` - Habitaciones/salas
- `beds` - Camas
- `hospitalizations` - Internaciones
- `bed_cleaning_logs` - Registros de limpieza

Tambien actualiza el enum `users.role` para incluir el rol `nurse`.

### 2. Crear Datos Iniciales

**Opcion recomendada (automatica):**

```bash
php artisan db:seed --class=HospitalizationSetupSeeder
```

Este seeder crea/actualiza:

- 4 habitaciones base (`H201`, `H202`, `UCI301`, `A401`)
- 6 camas disponibles iniciales
- 2 usuarios de enfermeria de prueba con rol `nurse`

**Opcion manual (SQL):**

**Habitaciones de Ejemplo**:

```sql
INSERT INTO rooms (name, code, room_type, floor, wing, max_beds, is_active, created_at, updated_at) VALUES
('Habitación 201', 'H201', 'standard', 2, 'Norte', 2, 1, NOW(), NOW()),
('Habitación 202', 'H202', 'standard', 2, 'Norte', 2, 1, NOW(), NOW()),
('UCI 301', 'UCI301', 'intensive_care', 3, 'Sur', 1, 1, NOW(), NOW()),
('Aislamiento 401', 'A401', 'isolation', 4, 'Oeste', 1, 1, NOW(), NOW());
```

**Camas de Ejemplo**:

```sql
INSERT INTO beds (room_id, bed_number, status, bed_type, is_active, created_at, updated_at) VALUES
(1, 'A', 'available', 'standard', 1, NOW(), NOW()),
(1, 'B', 'available', 'standard', 1, NOW(), NOW()),
(2, 'A', 'available', 'standard', 1, NOW(), NOW()),
(2, 'B', 'available', 'standard', 1, NOW(), NOW()),
(3, '1', 'available', 'intensive_care', 1, NOW(), NOW()),
(4, '1', 'available', 'isolation', 1, NOW(), NOW());
```

### 3. Compilar Frontend

```bash
npm run build
```

### 4. Verificar Rutas

```bash
php artisan route:list --name=hospitalizations
```

Debe mostrar 11 rutas registradas.

---

## Testing Manual

### Test 1: Nueva Internación

1. Acceder a `/hospitalizations`
2. Click en "Nueva Internación"
3. Completar formulario:
    - Seleccionar paciente
    - Seleccionar cama disponible
    - Seleccionar médico
    - Tipo: "Programada"
    - Fecha actual
    - Motivo: "Control post-operatorio"
4. Guardar
5. Verificar que cama aparece como "Ocupada" en el listado

### Test 2: Actualizar Fecha de Alta (Médico)

1. Como médico, acceder a una cama ocupada
2. En sección "Gestión de Alta", ingresar fecha estimada (mañana)
3. Guardar
4. Verificar que aparece la fecha en el listado

### Test 3: Dar Alta

1. Modificar fecha estimada a hoy (o fecha pasada)
2. Verificar que aparece mensaje "Lista para alta"
3. Completar notas de alta
4. Click en "Dar Alta al Paciente"
5. Confirmar
6. Verificar que:
    - Internación cambia a "Alta"
    - Cama cambia a "Pendiente limpieza"

### Test 4: Limpiar Cama

1. En cama con estado "Pendiente limpieza"
2. Click "Iniciar Limpieza" → Estado cambia a "En limpieza"
3. Seleccionar tipo: "Post-alta"
4. Agregar notas opcionales
5. Click "Marcar como Limpia"
6. Verificar que cama vuelve a "Disponible"

### Test 5: Transferencia

1. En una internación activa
2. Solicitar transferencia a otra cama disponible
3. Ingresar motivo: "Requiere aislamiento"
4. Confirmar
5. Verificar:
    - Cama original: Pendiente limpieza
    - Cama nueva: Ocupada
    - Historial muestra ambas internaciones

---

## Relaciones con Otros Módulos

### Integración con Módulo de Quirófanos

- Al crear operación, opcionalmente puede generarse internación post-quirúrgica
- Campo `operation_id` en `hospitalizations` vincula cirugía con internación
- Tipo de admisión: `post_surgical`

### Integración con Pacientes

- Toda internación está vinculada a un paciente
- Los médicos pueden ver historial de internaciones del paciente
- Paciente puede tener una internación activa simultáneamente

### Integración con Usuarios (Médicos)

- Cada internación tiene un médico responsable
- Médicos solo pueden actualizar fechas de alta para sus pacientes
- Historial de qué médico autorizó/dio el alta

---

## Consideraciones Técnicas

### Validación de Camas Disponibles

El método `store()` valida que la cama esté disponible antes de asignarla:

```php
if (!$bed->isAvailable()) {
    return back()->withErrors(['bed_id' => 'La cama seleccionada no está disponible.']);
}
```

### Transaction Safety

Operaciones críticas (internación, alta, transferencia) usan transacciones DB:

```php
DB::beginTransaction();
try {
    // ... operaciones
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    return back()->withErrors(['error' => $e->getMessage()]);
}
```

### Cálculo Automático de Días

El modelo `Hospitalization` calcula automáticamente días de internación:

```php
public function getDaysHospitalizedAttribute(): int
{
    $endDate = $this->actual_discharge_date ?? now();
    return $this->admission_date->diffInDays($endDate);
}
```

### Scopes Útiles

```php
// Camas disponibles
Bed::available()->get();

// Camas ocupadas
Bed::occupied()->get();

// Camas pendientes de limpieza
Bed::pendingCleaning()->get();

// Internaciones activas
Hospitalization::active()->get();

// Pacientes listos para alta
Hospitalization::readyForDischarge()->get();

// Internaciones de un médico
Hospitalization::byDoctor($doctorId)->get();
```

---

## Extensiones Futuras

### 1. **Alertas Automáticas**

- Notificar a enfermería cuando se alcanza fecha de alta
- Alertas de camas pendientes de limpieza por más de X horas

### 2. **Integración con Farmacia**

- Medicación asignada a pacientes internados
- Control de stock por piso/habitación

### 3. **Reportes Avanzados**

- Tasa de ocupación por piso/tipo de cama
- Tiempo promedio de internación por patología
- Eficiencia de limpieza (tiempo promedio)

### 4. **Gestión de Visitas**

- Control de horarios de visita
- Registro de visitantes por paciente
- Restricciones de visitas (aislamiento, UCI)

### 5. **Dashboard de Enfermería**

- Vista de plantas/pisos con mapa visual de camas
- Código de colores por estado
- Alertas priorizadas

---

## Troubleshooting

### Problema: No aparecen camas disponibles

**Solución**: Verificar que existan registros en tablas `rooms` y `beds` con `is_active = 1` y `status = 'available'`

### Problema: Error al dar alta - "No tiene permiso"

**Solución**: Verificar que el usuario sea enfermero o admin. Solo estos roles pueden ejecutar altas.

### Problema: No se puede limpiar cama

**Solución**: Verificar que la cama esté en estado `pending_cleaning` o `cleaning`.

### Problema: Fecha estimada de alta no aparece

**Solución**: Solo los médicos pueden establecer fechas de alta. Verificar rol del usuario.

---

## Resumen de Archivos Creados

### Migraciones (4)

- `2026_03_07_000012_create_rooms_table.php`
- `2026_03_07_000013_create_beds_table.php`
- `2026_03_07_000014_create_hospitalizations_table.php`
- `2026_03_07_000015_create_bed_cleaning_logs_table.php`

### Modelos (4)

- `app/Models/Room.php`
- `app/Models/Bed.php`
- `app/Models/Hospitalization.php`
- `app/Models/BedCleaningLog.php`

### Controlador (1)

- `app/Http/Controllers/HospitalizationController.php`

### Middleware (1)

- `app/Http/Middleware/EnsureHospitalizationAccess.php`

### Vistas Vue (4)

- `resources/js/Pages/Hospitalization/Index.vue`
- `resources/js/Pages/Hospitalization/Show.vue`
- `resources/js/Pages/Hospitalization/Create.vue`
- `resources/js/Pages/Hospitalization/History.vue`

### Modificaciones

- `routes/web.php` - 11 rutas agregadas
- `resources/js/Components/Navigation.vue` - Enlaces agregados para nurse, doctor y admin
- `app/Models/Patient.php` - Relación `hospitalizations()` agregada
- `app/Models/User.php` - Relación `hospitalizationsAsDoctor()` y método `isNurse()` agregados
- `app/Models/Operation.php` - Relación `hospitalization()` agregada

**Total de archivos nuevos**: 14  
**Total de archivos modificados**: 4  
**Total de rutas**: 11  
**Líneas de código**: ~5000+

---

## Soporte

Para consultas o problemas con el módulo de internación, contactar al equipo de desarrollo.

**Última actualización**: Marzo 2026  
**Versión del módulo**: 1.0.0
