# Modulo Paramedico y Traslados

## Objetivo

Gestionar moviles de traslado y operaciones de derivacion/interhospitalario.

## Acceso

- Roles habilitados: `paramedic`, `emergency`, `admin`.
- Middleware: `EnsureParamedic`.
- Prefijo de rutas: `/paramedic`.

## Rutas principales

- `GET /paramedic` -> `paramedic.dashboard`
- `POST /paramedic/ambulances` -> `paramedic.ambulances.store`
- `PATCH /paramedic/ambulances/{ambulance}/status` -> `paramedic.ambulances.status`
- `POST /paramedic/transfers` -> `paramedic.transfers.store`
- `PATCH /paramedic/transfers/{transfer}/status` -> `paramedic.transfers.status`

## Entidades

- `Ambulance`
- `EmergencyTransfer`

## Seeder demo

- `ParamedicDemoSeeder`
- Incluido en `DatabaseSeeder`.
