# Configuración de Infraestructura de la Clínica

## Descripción General

El sistema permite al administrador configurar la capacidad de infraestructura de la clínica en dos áreas principales:

1. **Quirófanos (Salas de Operaciones)**: Configuración de cantidad y estado de las salas quirúrgicas
2. **Habitaciones y Camas**: Gestión de habitaciones de internación y su capacidad de camas

## 🏥 Configuración de Quirófanos

### Acceso

**Ruta**: Menú Admin → "Salas Quirófano"  
**URL**: `/operations/rooms/settings`  
**Permisos**: Solo administrador

### Funcionalidades

#### 1. Visualización de Capacidad

- **Salas habilitadas**: Cantidad de quirófanos actualmente operativos
- **Salas totales**: Número total de salas configuradas
- **Rango horario**: Configuración de horarios de operación (07:00 - 22:00)

#### 2. Modificar Cantidad de Salas

- Campo numérico para definir cantidad de salas (1-30)
- Al reducir la cantidad, las salas excedentes se marcan como inactivas **solo si no tienen operaciones futuras programadas**
- Al aumentar, se crean automáticamente nuevas salas numeradas secuencialmente

#### 3. Gestión Individual de Salas

Cada sala puede editarse individualmente para modificar:

- **Nombre**: Identificador de la sala
- **Estado**:
    - `active` (Habilitada): Disponible para programar operaciones
    - `maintenance` (Mantenimiento): Temporalmente fuera de servicio
    - `inactive` (Inactiva): No disponible
- **Notas**: Observaciones adicionales

### Implementación Técnica

- **Controller**: `OperationRoomController`
- **Vista**: `resources/js/Pages/Operations/RoomsSettings.vue`
- **Rutas**:
    - GET `/operations/rooms/settings` - Visualización
    - POST `/operations/rooms/capacity` - Actualizar capacidad (solo admin)
    - PATCH `/operations/rooms/{room}` - Actualizar sala individual

---

## 🛏️ Configuración de Habitaciones y Camas

### Acceso

**Ruta**: Menú Admin → "Habitaciones y Camas"  
**URL**: `/rooms/settings`  
**Permisos**: Solo administrador

### Funcionalidades

#### 1. Estadísticas en Tiempo Real

- **Habitaciones activas / totales**
- **Camas activas / totales**
- **Camas disponibles**: Listas para asignar paciente
- **Camas ocupadas**: Con paciente internado
- **Tasa de ocupación**: Porcentaje calculado automáticamente

#### 2. Crear Nueva Habitación

Botón "+ Nueva Habitación" que permite configurar:

- **Nombre**: Ejemplo: "Habitación 101"
- **Código**: Identificador corto (opcional)
- **Tipo de habitación**:
    - Estándar
    - Terapia Intensiva
    - Aislamiento
    - Pediátrica
    - Psiquiátrica
    - Recuperación
- **Piso**: Número de piso (0-50)
- **Ala/Sector**: Norte, Sur, etc. (opcional)
- **Cantidad de camas**: 1-20 camas por habitación
- **Descripción**: Características, equipamiento especial, etc.

**Comportamiento automático al crear**:

- Se generan automáticamente todas las camas configuradas
- Las camas se numeran secuencialmente (1, 2, 3...)
- El tipo de cama se asigna automáticamente según el tipo de habitación
- Todas las camas se crean en estado "Disponible"

#### 3. Editar Habitación

Clic en "Editar" junto a cualquier habitación permite modificar:

- Todos los campos configurables
- **Al cambiar `max_beds`**:
    - Si se aumenta: se crean camas adicionales automáticamente
    - Si se reduce: se desactivan camas disponibles que excedan el nuevo límite
- Estado activo/inactivo de la habitación

#### 4. Desactivar Habitación

Botón "Desactivar habitación" que:

- **Valida** que no haya camas ocupadas
- Desactiva todas las camas de la habitación
- Marca la habitación como inactiva
- **No se puede desactivar** si hay pacientes internados

#### 5. Información por Habitación

La tabla muestra para cada habitación:

- **Nombre y código**
- **Tipo** (Estándar, UCI, etc.)
- **Ubicación**: Piso y ala
- **Estado de camas**:
    - X disponibles
    - Y ocupadas / Z activas
- **Estado general**:
    - 🟢 "En uso": Tiene camas ocupadas
    - 🔵 "Disponible": Activa sin pacientes
    - ⚫ "Inactiva": Deshabilitada

### Mapeo de Tipos Habitación → Cama

| Tipo de Habitación | Tipo de Cama Asignado |
| ------------------ | --------------------- |
| Estándar           | Estándar              |
| Terapia Intensiva  | Terapia Intensiva     |
| Aislamiento        | Aislamiento           |
| Pediátrica         | Pediátrica            |
| Psiquiátrica       | Psiquiátrica          |
| Recuperación       | Estándar              |

### Implementación Técnica

- **Controller**: `RoomController` (nuevo)
- **Policy**: `RoomPolicy` (autorización)
- **Vista**: `resources/js/Pages/Hospitalization/RoomsSettings.vue`
- **Rutas**:
    - GET `/rooms/settings` - Visualización (solo admin)
    - POST `/rooms` - Crear habitación (solo admin)
    - PATCH `/rooms/{room}` - Actualizar habitación (solo admin)
    - DELETE `/rooms/{room}` - Desactivar habitación (solo admin)

### Relaciones de Base de Datos

```
Room (Habitación)
├── beds (HasMany)
│   ├── activeBeds (camas activas)
│   ├── availableBeds (camas disponibles)
│   └── occupiedBeds (camas ocupadas)
└── Atributos:
    ├── name, code
    ├── room_type
    ├── floor, wing
    ├── max_beds
    └── is_active
```

```
Bed (Cama)
├── room (BelongsTo)
├── currentHospitalization (relación)
└── Atributos:
    ├── bed_number
    ├── bed_type
    ├── status (available, occupied, pending_cleaning, cleaning, maintenance)
    └── is_active
```

---

## Flujo de Trabajo Recomendado

### Configuración Inicial de Infraestructura

1. **Configurar Quirófanos**:
    - Ingresar a "Salas Quirófano"
    - Definir cantidad total de salas de operaciones
    - Personalizar nombre y estado de cada sala según necesidad

2. **Configurar Habitaciones**:
    - Ingresar a "Habitaciones y Camas"
    - Crear habitaciones por piso/sector
    - Definir tipo y cantidad de camas por habitación
    - Completar información de ubicación

3. **Ajustes Posteriores**:
    - Las camas se gestionan automáticamente al modificar `max_beds`
    - Las habitaciones pueden activarse/desactivarse según demanda
    - Los quirófanos pueden ponerse en mantenimiento temporalmente

### Ejemplo de Configuración Real

#### Hospital de 50 Camas

```
Piso 1 - Habitaciones Estándar
├── Hab. 101 (Ala Norte) - 4 camas estándar
├── Hab. 102 (Ala Norte) - 4 camas estándar
├── Hab. 103 (Ala Sur) - 2 camas estándar
└── ...

Piso 2 - Áreas Especializadas
├── UCI (Ala Este) - 6 camas terapia intensiva
├── Aislamiento - 2 camas aislamiento
├── Pediatría - 8 camas pediátricas
└── Recuperación - 4 camas estándar

Quirófanos
├── Quirófano 1 - Activo
├── Quirófano 2 - Activo
└── Quirófano 3 - Mantenimiento
```

---

## Validaciones y Restricciones

### Quirófanos

- ✅ Cantidad máxima: 30 salas
- ✅ No se pueden desactivar salas con operaciones futuras programadas
- ✅ Estado debe ser: active, maintenance o inactive

### Habitaciones y Camas

- ✅ Nombre de habitación único
- ✅ Código único (si se especifica)
- ✅ Cantidad de camas: 1-20 por habitación
- ✅ Piso: 0-50
- ❌ No se puede desactivar habitación con camas ocupadas
- ❌ No se pueden eliminar camas ocupadas al reducir `max_beds`

---

## Navegación en el Sistema

Los enlaces de configuración aparecen en el menú del **Administrador**:

```
📋 Auditoría
👨‍⚕️ Usuarios
🏥 Tablero de Colas
🏥 Quirófanos
⚙️ Salas Quirófano          ← Configuración Quirófanos
🛏️ Habitaciones y Camas    ← Configuración Habitaciones (NUEVO)
🛏️ Internación
🟢 Pre-Internación
...
```

---

## Permisos y Seguridad

| Acción                    | Admin | Enfermera | Doctor  | Secretaria |
| ------------------------- | ----- | --------- | ------- | ---------- |
| Ver quirófanos            | ✅    | ❌        | ✅      | ❌         |
| Configurar quirófanos     | ✅    | ❌        | ❌      | ❌         |
| Ver habitaciones          | ✅    | ✅        | ✅      | ❌         |
| Crear/editar habitaciones | ✅    | ❌        | ❌      | ❌         |
| Ver camas operativas      | ✅    | ✅        | ✅      | ❌         |
| Gestionar internaciones   | ✅    | ✅        | Parcial | ❌         |

---

## Troubleshooting

### "No se puede desactivar habitación con camas ocupadas"

**Solución**: Primero dar de alta a todos los pacientes de esa habitación antes de desactivarla.

### Al reducir camas, algunas no se desactivan

**Causa**: Las camas ocupadas nunca se desactivan automáticamente.  
**Solución**: Esperar a que los pacientes sean dados de alta, luego editar nuevamente la habitación.

### No aparece el menú "Habitaciones y Camas"

**Causa**: Usuario no tiene rol de administrador.  
**Solución**: Verificar que `user.role === 'admin'` en la base de datos.

---

## Archivos Relacionados

### Backend

- `app/Http/Controllers/RoomController.php` - Controlador principal
- `app/Policies/RoomPolicy.php` - Autorización
- `app/Models/Room.php` - Modelo de habitación
- `app/Models/Bed.php` - Modelo de cama
- `routes/web.php` - Rutas de configuración

### Frontend

- `resources/js/Pages/Hospitalization/RoomsSettings.vue` - Vista habitaciones
- `resources/js/Pages/Operations/RoomsSettings.vue` - Vista quirófanos
- `resources/js/Components/Navigation.vue` - Navegación

### Migraciones

- Tablas: `rooms`, `beds`, `operation_rooms`

---

## Changelog

### v1.0 (Marzo 2026)

- ✅ Implementado sistema de configuración de quirófanos
- ✅ Implementado sistema de configuración de habitaciones y camas
- ✅ Gestión automática de camas al modificar `max_beds`
- ✅ Validaciones de seguridad para prevenir desactivaciones incorrectas
- ✅ Estadísticas en tiempo real de ocupación
- ✅ Integración con módulo de internación existente
