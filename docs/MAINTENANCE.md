# Modulo Mantenimiento

## Objetivo

Gestionar equipamiento medico y ordenes de mantenimiento preventivo/correctivo.

## Acceso

- Roles habilitados: `maintenance`, `admin`.
- Middleware: `EnsureMaintenance`.
- Prefijo de rutas: `/maintenance`.

## Rutas principales

- `GET /maintenance` -> `maintenance.index`
- `POST /maintenance/equipments` -> `maintenance.equipments.store`
- `PATCH /maintenance/equipments/{equipment}/status` -> `maintenance.equipments.status`
- `POST /maintenance/orders` -> `maintenance.orders.store`
- `PATCH /maintenance/orders/{order}/status` -> `maintenance.orders.status`

## Entidades

- `MedicalEquipment`
- `MaintenanceOrder`

## Seeder demo

- `MaintenanceDemoSeeder`
- Incluido en `DatabaseSeeder`.
