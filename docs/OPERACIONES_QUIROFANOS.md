# Módulo Operaciones (Gestión de Quirófanos)

## Resumen

Este módulo permite:

- Configurar cantidad de salas de operaciones (admin).
- Visualizar agenda diaria por quirófano con ventanas libres.
- Aplicar margen fijo de limpieza de 15 minutos entre operaciones.
- Programar operaciones con médico, paciente, tipo, duración y prioridad.
- Cargar requerimientos de farmacia por operación.
- Gestionar operaciones (editar, cancelar, cambiar estado).
- Insertar urgencias/emergencias con estrategia de reprogramación:
    - atrasar operaciones en conflicto (`forward`)
    - adelantar operaciones en conflicto (`backward`)

## Roles

Se agregó el nuevo rol:

- `operating_room_manager` (Encargado de Quirófano)

Permisos principales:

- `admin`: acceso total al módulo + configurar capacidad total de salas.
- `operating_room_manager`: gestionar agenda, salas y estados de operaciones.
- `doctor`: crear y gestionar sus propias operaciones.

## Estructura de base de datos

Migraciones nuevas:

1. `2026_03_07_000006_add_operating_room_manager_role_to_users.php`
2. `2026_03_07_000007_create_operation_rooms_table.php`
3. `2026_03_07_000008_create_operations_table.php`
4. `2026_03_07_000009_create_operation_pharmacy_items_table.php`

Tablas nuevas:

- `operation_rooms`
    - `name`, `code`, `display_order`, `status` (`active|maintenance|inactive`), `notes`
- `operations`
    - sala, médico, paciente, tipo de operación
    - `scheduled_start`, `scheduled_end`, `estimated_duration_minutes`
    - `cleaning_margin_minutes` (default 15)
    - `urgency` (`scheduled|urgent|emergency`)
    - `status` (`scheduled|in_progress|completed|cancelled`)
    - notas clínicas y de farmacia
- `operation_pharmacy_items`
    - insumos requeridos para cada operación

## Rutas

Prefijo principal: `operations`

- `GET operations` -> `operations.index`
- `GET operations/create` -> `operations.create`
- `POST operations` -> `operations.store`
- `GET operations/{operation}/edit` -> `operations.edit`
- `PATCH operations/{operation}` -> `operations.update`
- `DELETE operations/{operation}` -> `operations.destroy`
- `POST operations/{operation}/cancel` -> `operations.cancel`
- `POST operations/{operation}/quick-status` -> `operations.quick-status`
- `GET operations/rooms/settings` -> `operations.rooms.settings`
- `PATCH operations/rooms/{room}` -> `operations.rooms.update`
- `POST operations/rooms/capacity` -> `operations.rooms.capacity`

## Pantallas Inertia

- `resources/js/Pages/Operations/Index.vue`
    - Agenda diaria, ocupación, filtros y ventanas libres por sala.
- `resources/js/Pages/Operations/Create.vue`
    - Alta de operación con requerimientos de farmacia.
- `resources/js/Pages/Operations/Edit.vue`
    - Edición de operación y reprogramación por urgencia.
- `resources/js/Pages/Operations/RoomsSettings.vue`
    - Configuración de salas y capacidad.

## Lógica de agenda y limpieza

Servicio central:

- `app/Services/OperatingRoomSchedulerService.php`

Reglas principales:

- Entre operaciones de una misma sala se exige separación de 15 minutos.
- Validación de conflicto usa ventanas con margen de limpieza.
- Para emergencias se puede:
    - rechazar conflicto (`none`)
    - mover conflictos hacia adelante (`forward`)
    - intentar mover conflictos hacia atrás (`backward`)

## Integración con farmacia

Cada operación puede tener múltiples requerimientos:

- item del catálogo de farmacia (`pharmacy_item_id`) o carga manual
- cantidad, unidad y observaciones

Estos datos se muestran en la agenda para que el encargado de quirófano coordine retiro/preparación.

## Configuración inicial

1. Ejecutar migraciones:

```bash
php artisan migrate
```

2. Crear usuarios con rol `operating_room_manager` desde Admin > Usuarios.

3. Ingresar a `Operaciones > Salas Quirófano` y definir capacidad inicial.

4. Programar operaciones desde `Operaciones > Nueva Operación`.

## Nota de operación

- El horario visual de disponibilidad en agenda se calcula entre 07:00 y 22:00.
- Si se intenta desactivar una sala con operaciones futuras, el sistema lo bloquea.
