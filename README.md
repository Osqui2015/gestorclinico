# 🏥 Gestor Clínico

Sistema integral de gestión para consultorios y clínicas médicas desarrollado con tecnologías web modernas.

## 📋 Descripción

**Gestor Clínico** es una aplicación web completa diseñada para digitalizar y optimizar la administración de consultorios médicos. Permite gestionar pacientes, doctores, turnos, historias clínicas, recetas médicas, obras sociales y facturación de manera eficiente y profesional.

### ✨ Características Principales

#### Módulos Clínicos

- 👨‍⚕️ **Gestión de Doctores**: Registro completo con datos profesionales (matrícula nacional, matrícula profesional, especialidad, DNI, teléfono, dirección)
- 👥 **Gestión de Pacientes**: Información completa incluyendo DNI, CUIL, fecha de nacimiento (con cálculo automático de edad), obra social, contactos de emergencia y alergias
- 📅 **Sistema de Turnos Inteligente**:
    - Programación y gestión de citas médicas con asignación de doctores
    - **Carga automática de obra social** del paciente al seleccionarlo
    - **Filtro inteligente de horarios** - oculta automáticamente horarios pasados del día actual
    - Validación de disponibilidad en tiempo real
    - Calendario con semana iniciando en lunes (formato argentino)
- 🩺 **Cola de Atención**: Gestión en tiempo real de pacientes en espera
- 📝 **Historias Clínicas**: Registro detallado de consultas médicas por paciente
- 💊 **Recetas Médicas**: Generación profesional de prescripciones con:
    - Diagnóstico médico
    - Medicamentos (nombre, dosis, frecuencia, duración)
    - Indicaciones y recomendaciones
    - Exportación a PDF con formato profesional
    - PDFs separados para receta e instrucciones

#### Módulos de Recepción y Administración

- 🏥 **Obras Sociales**: Gestión de seguros médicos y coberturas
    - Descarga de padrón actualizado (`obras-sociales:download`)
    - Importación masiva desde archivo XLS/TSV de SSSalud (`obras-sociales:import-xls`)
    - Búsqueda y autocompletado en formularios
- 📋 **Recepción**: Panel de control centralizado
    - Dashboard con vista de turnos del día
    - Check-in de pacientes y confirmación de turnos
    - Vista por médico con agenda individual
    - Sala de espera en tiempo real con tiempos de espera
    - Filtros por estado, fecha y profesional
- 💰 **Facturación y Contabilidad**:
    - Sistema completo de generación de facturas e invoices
    - Cuentas corrientes de pacientes
    - Registro de pagos y movimientos financieros
    - Seguimiento de deudores
    - Exportación de datos contables

#### Módulos Hospitalarios

- 🏥 **Internación**:
    - Gestión de camas por habitación
    - Control de ocupación y disponibilidad
    - Admisiones y egresos de pacientes
    - Seguimiento de evoluciones médicas
    - Estados de cama (limpia, ocupada, en limpieza, fuera de servicio)
- 🔪 **Quirófano**:
    - Configuración de salas quirúrgicas
    - Agenda de operaciones programadas
    - Pre-internación y validación de documentación
    - Asignación de salas y médicos cirujanos
    - Estados de operación (programada, en progreso, completada)
- 🚑 **Emergencias/Guardia**:
    - Tablero de guardia con clasificación de prioridad
    - Registro de admisiones de emergencia
    - Triage y seguimiento de evoluciones
    - Estados (en espera, en atención, hospitalizado, dado de alta)
    - Vista por prioridad (crítico, urgente, estable)

#### Módulos de Soporte

- 💊 **Farmacia**:
    - Gestión de inventario (medicamentos, insumos, instrumental)
    - Control de stock con alertas de mínimos
    - Solicitudes internas de materiales
    - Seguimiento de vencimientos
    - Control de esterilización de instrumental
    - Movimientos de stock (entradas, salidas, ajustes)
- 🚑 **Paramédicos**:
    - Gestión de móviles/ambulancias
    - Registro de traslados (interhospitalarios, domicilio-hospital, urgencias)
    - Seguimiento de estado y prioridad
    - Asignación de personal paramédico
- 🔧 **Mantenimiento**:
    - Gestión de equipos médicos
    - Órdenes de mantenimiento preventivo y correctivo
    - Historial de reparaciones
    - Control de calibraciones y vencimientos
    - Estados operacionales de equipamiento

#### Sistema y Reportes

- 📊 **Reportes Avanzados**:
    - Reporte C2 (REFES): consultaciones y derivaciones
    - Análisis epidemiológico: enfermedades notificables, distribución demográfica
    - Indicadores de calidad: tiempos de espera, satisfacción, productividad
    - Análisis por obra social: facturación y consultas
    - Análisis de facturación: cobranzas, envejecimiento de cartera
    - Ocupación de camas: tasas, rotación, estadías promedio
- 📅 **Dashboard Personalizado**:
    - Vista de agenda diaria con calendario lateral
    - **Filtrado automático por doctor** - cada doctor ve su propia agenda por defecto
    - Navegación por mes con corrección de zona horaria
    - Resumen de citas pendientes del día
- 🔐 **Control de Acceso**: Sistema de roles (Administrador, Doctor, Secretaria, Farmacia, Encargado de Quirófano, Enfermería, Emergencias, Contabilidad, Mantenimiento, Paramédico)
- 📊 **Auditoría**: Registro automático de cambios en entidades críticas
- 🌍 **Zona Horaria**: Configurado para **Argentina (UTC-3)** - horarios locales correctos

## 🛠️ Tecnologías Utilizadas

### Backend

- **Laravel 12** - Framework PHP moderno y robusto
- **MySQL** - Sistema de gestión de base de datos relacional
- **DomPDF** - Generación de documentos PDF profesionales

### Frontend

- **Inertia.js** - Bridge entre Laravel y Vue.js
- **Vue 3** - Framework JavaScript progresivo
- **TypeScript** - Tipado estático para JavaScript
- **Tailwind CSS** - Framework CSS utility-first
- **Vite** - Build tool moderno y rápido

### Arquitectura

- **SPA (Single Page Application)** con renderizado del lado del servidor
- **Validación en Frontend y Backend**
- **Diseño Responsive** adaptable a dispositivos móviles
- **Soft Deletes** para eliminación segura de registros
- **Relaciones Eloquent** para modelado de datos complejo

## 📦 Requisitos del Sistema

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- NPM >= 9.x
- MySQL >= 8.0
- Extensiones PHP: PDO, mbstring, tokenizer, xml, ctype, json, bcmath

## 🚀 Instalación

1. **Clonar el repositorio**

```bash
git clone <repository-url>
cd GestorClinico
```

2. **Instalar dependencias PHP**

```bash
composer install
```

3. **Instalar dependencias JavaScript**

```bash
npm install
```

4. **Configurar variables de entorno**

```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurar base de datos y zona horaria en `.env`**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestor_clinico
DB_USERNAME=root
DB_PASSWORD=

# Zona horaria (configurada para Argentina)
APP_TIMEZONE=America/Argentina/Buenos_Aires
```

**Nota**: El sistema está configurado para operar en zona horaria de Argentina (UTC-3). Esto asegura que:

- Los horarios de las citas se muestran correctamente
- El calendario no muestra horarios pasados del día actual
- Las fechas se interpretan en hora local argentina

6. **Ejecutar migraciones**

```bash
php artisan migrate
```

7. **Compilar assets**

```bash
npm run build
# o para desarrollo:
npm run dev
# en Windows PowerShell (si hay restricción de ejecución):
npm.cmd run build
```

8. **Iniciar servidor**

```bash
php artisan serve
```

## 🏥 Carga de Obras Sociales (SSSalud)

### 1. Descargar padrón oficial (opcional)

```bash
php artisan obras-sociales:download
```

### 2. Importar desde archivo XLS/TSV

```bash
php artisan obras-sociales:import-xls storage/app/listadoSSSalud.xls
```

Opciones disponibles:

```bash
php artisan obras-sociales:import-xls <ruta-archivo> --truncate
```

- `--truncate`: vacía la tabla `health_insurances` antes de importar.
- El import detecta cabecera `RNAS`, normaliza encoding y actualiza por `code` (RNOS/RNAS) o por `name` cuando no hay código.

## 🚚 Build para despliegue

Para generar assets productivos antes de subir al servidor:

```bash
npm run build
```

Archivos clave generados:

- `public/build/manifest.json`
- `public/build/assets/*`

## ⚙️ Configuración de Zona Horaria

El sistema está configurado para operar en **zona horaria de Argentina** (`America/Argentina/Buenos_Aires`, UTC-3).

### En `config/app.php`:

```php
'timezone' => 'America/Argentina/Buenos_Aires',
```

### Beneficios:

- ✅ Los horarios de citas se muestran en hora local
- ✅ El calendario filtra automáticamente horarios pasados del día actual
- ✅ Las fechas se interpretan correctamente sin desfases
- ✅ Validaciones de disponibilidad precisas

### Cambiar zona horaria (si es necesario):

1. Editar `config/app.php` y cambiar el valor de `timezone`
2. Ejecutar: `php artisan config:clear`
3. Reiniciar el servidor: `php artisan serve`

## 👥 Roles y Permisos

### Administrador

- Gestión completa de doctores y usuarios
- Acceso a todas las funcionalidades del sistema
- Configuración de obras sociales
- Reportes y estadísticas
- Auditoría de cambios
- Configuración de roles y permisos

### Doctor

- Gestión de pacientes asignados
- Creación y edición de historias clínicas
- Generación de recetas médicas
- Atención de turnos programados
- Consulta de historiales
- Vista personalizada de agenda
- Acceso a reportes de sus pacientes

### Secretaria

- Registro de nuevos pacientes
- Creación y gestión de turnos
- Gestión de cola de atención
- Panel de recepción con check-in de pacientes
- Sala de espera en tiempo real
- Vista de agenda por médico
- Gestión de obras sociales
- Facturación básica

### Farmacia

- Gestión completa de inventario (medicamentos, insumos, instrumental)
- Procesamiento de solicitudes internas de farmacia
- Control de stock con alertas de mínimos y reorden
- Seguimiento de vencimientos de medicamentos
- Control de esterilización de instrumental
- Registro de movimientos de stock (entradas, salidas, ajustes, devoluciones)
- Gestión de lotes y números de serie
- Reportes de inventario

### Encargado de Quirófano

- Configuración de salas quirúrgicas y recursos
- Gestión de agenda de operaciones
- Asignación de salas y verificación de disponibilidad
- Estados de operaciones (programada, en progreso, completada, cancelada)
- Registro de equipo quirúrgico
- Preparación de salas

### Enfermería

- Gestión de internaciones y camas
- Admisión y egreso de pacientes hospitalizados
- Registro de evoluciones médicas de internados
- Control de estados de cama (limpia, ocupada, en limpieza, fuera de servicio)
- Asignación de camas por habitación
- Seguimiento de tiempo de internación
- Limpieza y mantenimiento de infraestructura

### Emergencias

- Gestión del tablero de guardia
- Clasificación de prioridad (triage): crítico, urgente, estable
- Registro de admisiones de emergencia
- Seguimiento de evoluciones de guardia
- Estados de atención (en espera, en atención, hospitalizado, dado de alta)
- Derivaciones a especialidades
- Coordinación con internación

### Contabilidad

- Gestión de cuentas corrientes de pacientes
- Registro de movimientos financieros (cargos, pagos, ajustes)
- Seguimiento de deudores y facturación pendiente
- Reconciliación de pagos
- Exportes contables
- Reportes de facturación y cobranza
- Análisis de envejecimiento de cartera

### Mantenimiento

- Registro de equipos médicos e instrumental
- Gestión de órdenes de mantenimiento (preventivo y correctivo)
- Seguimiento de estado operacional de equipos
- Historial de reparaciones
- Control de calibraciones y certificaciones
- Programación de mantenimiento preventivo
- Reportes de disponibilidad de equipos

### Paramédico

- Gestión de móviles/ambulancias
- Registro de traslados (interhospitalario, domicilio-hospital, urgencias)
- Asignación de personal paramédico
- Seguimiento de estado de traslados (solicitado, en curso, completado, cancelado)
- Control de prioridad de traslados
- Registro de observaciones y evolución durante traslado
- Coordinación con emergencias e internación

## 📄 Funcionalidades Destacadas

### Sistema de Turnos Inteligente

- **Carga Automática de Datos**: Al seleccionar un paciente en el formulario de citas, el sistema carga automáticamente su obra social primaria y número de afiliado
- **Filtro de Horarios en Tiempo Real**:
    - Solo muestra horarios disponibles a futuro
    - Si seleccionas el día de hoy, automáticamente oculta las horas que ya pasaron
    - Ejemplo: si son las 13:00, solo mostrará horarios desde las 13:30 en adelante
- **Calendario Inteligente**:
    - Semana iniciando en lunes (formato argentino)
    - Resaltado del día actual
    - Visualización clara de días seleccionados
- **Validación de Disponibilidad**: Verifica que el doctor no tenga otra cita en el mismo horario

### Panel de Recepción

- **Dashboard Centralizado**:
    - Vista de todos los turnos del día con estados
    - Estadísticas en tiempo real (total, pendientes, en atención, completados)
    - Filtros por estado, fecha y búsqueda de pacientes
- **Check-in de Pacientes**:
    - Registro de llegada de pacientes
    - Confirmación de turnos
    - Vista de sala de espera con tiempos de espera calculados
- **Alertas de Tiempo**: Resalta pacientes con más de 30 minutos de espera
- **Vista por Médico**: Agenda individual por profesional con navegación por fecha

### Sistema de Farmacia

- **Gestión de Inventario**:
    - Catálogo completo de medicamentos, insumos e instrumental
    - Códigos únicos y categorización por tipo
    - Control de laboratorio, lote y vencimiento
- **Alertas Inteligentes**:
    - Stock bajo cuando llega al mínimo configurado
    - Próximos a vencer (30 días)
    - Items vencidos
    - Esterilización pendiente (7 días antes)
- **Solicitudes Internas**:
    - Sistema de pedidos desde consultorios/servicios
    - Estados: pendiente, en proceso, completada, cancelada
    - Prioridades: baja, normal, alta, urgente
    - Entrega parcial o total de items solicitados
- **Trazabilidad**: Historial completo de movimientos de stock con usuario y fecha

### Módulo de Internación

- **Gestión de Camas**:
    - Control por habitación y capacidad
    - Estados: disponible, ocupada, en limpieza, mantenimiento, fuera de servicio
    - Asignación automática por disponibilidad
- **Admisiones**:
    - Registro de ingreso de pacientes con médico tratante
    - Motivo de internación y observaciones
    - Seguimiento de tiempo de estadía
- **Evoluciones Médicas**:
    - Registro diario de evolución clínica
    - Estado del paciente y cambios en tratamiento
    - Historial completo por internación
- **Tablero de Ocupación**: Vista en tiempo real de disponibilidad de camas por habitación

### Módulo de Quirófano

- **Gestión de Salas**:
    - Configuración de salas quirúrgicas con capacidad
    - Estados: disponible, en uso, en limpieza, mantenimiento
    - Tipos de cirugía soportados
- **Agenda de Operaciones**:
    - Programación de cirugías con doctor, paciente y sala
    - Tipos: programada, urgente, emergencia
    - Duración estimada y hora de inicio
    - Estados: programada, en progreso, completada, cancelada
- **Pre-Internación**:
    - Validación de documentación pre-quirúrgica
    - Verificación de datos del paciente
    - Checklist de documentos requeridos
    - Confirmación final para cirugía

### Módulo de Emergencias/Guardia

- **Tablero de Guardia**:
    - Vista de todas las admisiones activas
    - Clasificación por prioridad (crítico, urgente, estable)
    - Estados: en espera, en atención, hospitalizado, dado de alta
- **Triage**:
    - Registro de motivo de consulta y urgencia
    - Signos vitales iniciales
    - Asignación de prioridad
- **Evoluciones**:
    - Seguimiento de atención en guardia
    - Registro de tratamientos aplicados
    - Decisión de alta o internación
- **Métricas**: Tiempo de espera y tiempo de atención por paciente

### Sistema de Contabilidad

- **Cuentas Corrientes**:
    - Una cuenta por paciente con balance real
    - Registro de movimientos (cargos, pagos, notas de crédito/débito)
    - Saldo actualizado automáticamente
- **Gestión de Deudores**:
    - Listado de pacientes con saldo pendiente
    - Orden por antigüedad de deuda
    - Filtros por rango de monto
- **Pagos**:
    - Registro de pagos con método (efectivo, tarjeta, transferencia)
    - Aplicación automática a cuenta corriente
    - Recibos y comprobantes

### Reportes Avanzados

- **Reporte C2 (REFES)**:
    - Consultas por especialidad y médico
    - Derivaciones y tasa de derivación
    - Cumplimiento de normativa SSSalud
- **Análisis Epidemiológico**:
    - Distribución demográfica (edad, género)
    - Enfermedades notificables
    - Top diagnósticos del período
- **Indicadores de Calidad**:
    - Tiempo promedio de espera
    - Tasa de satisfacción
    - Productividad del equipo (utilización de agenda)
    - Métricas de hospitalización (estadía promedio, ocupación)
- **Análisis Financiero**:
    - Facturación por obra social
    - Tasa de cobranza
    - Envejecimiento de cartera
    - Ingresos por médico

### Dashboard Personalizado por Rol

- **Vista de Doctor**:
    - Acceso directo a "Mi Agenda" con sus propias citas
    - Calendario lateral sincronizado con fecha seleccionada
    - Resumen de citas pendientes del día
    - Filtrado automático por su usuario (no necesita seleccionar manualmente)
- **Vista de Administrador**: Puede ver todos los doctores o filtrar por uno específico
- **Corrección de Zona Horaria**: Todos los calendarios y fechas muestran hora local de Argentina

### Recetas Médicas Profesionales

- Formato de prescripción médica con datos del doctor (M.N., M.P.)
- Información completa del paciente
- Sección de diagnóstico destacada
- Lista de medicamentos con dosificación detallada
- Documento de instrucciones separado para el paciente
- PDFs con tipografía monoespaciada profesional
- Firma digital y validación de autenticidad

### Sistema de Pacientes

- Cálculo automático de edad desde fecha de nacimiento
- Validación de DNI (mínimo 6 dígitos)
- Validación de CUIL (formato 11 dígitos)
- Asociación con obra social primaria y número de afiliado
- **Auto-carga de obra social** en formularios de citas
- Registro de alergias y notas médicas
- Historial completo de consultas
- Búsqueda rápida por DNI o nombre

### Gestión de Doctores

- Registro de datos profesionales completos
- Múltiples especialidades médicas disponibles
- Validación de matrículas únicas
- Control de credenciales de acceso

## 🗂️ Estructura del Proyecto

```
GestorClinico/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Controladores por módulo
│   │   │   ├── Admin/           # Administración de usuarios
│   │   │   ├── Accounting/      # Contabilidad y cuentas corrientes
│   │   │   ├── Emergency/       # Guardia y emergencias
│   │   │   ├── Hospitalization/ # Internación y camas
│   │   │   ├── Operating/       # Quirófano y operaciones
│   │   │   ├── Pharmacy/        # Farmacia e inventario
│   │   │   ├── Reception/       # Recepción y turnos
│   │   │   ├── Reports/         # Reportes avanzados
│   │   │   └── ...              # Otros módulos
│   │   ├── Middleware/          # Middlewares de autenticación y roles
│   │   └── Requests/            # Form Requests para validación
│   ├── Models/                  # Modelos Eloquent
│   │   ├── Patient.php         # Pacientes
│   │   ├── User.php            # Usuarios/Médicos
│   │   ├── Appointment.php     # Turnos
│   │   ├── MedicalRecord.php   # Historias clínicas
│   │   ├── Prescription.php    # Recetas
│   │   ├── PharmacyItem.php    # Items de farmacia
│   │   ├── Bed.php             # Camas
│   │   ├── Operation.php       # Operaciones quirúrgicas
│   │   ├── EmergencyAdmission.php  # Admisiones de guardia
│   │   ├── CurrentAccount.php  # Cuentas corrientes
│   │   └── ...                 # Otros modelos
│   ├── Policies/                # Políticas de autorización
│   ├── Observers/               # Observadores de modelos
│   │   ├── AuditableObserver.php     # Auditoría automática
│   │   └── PrescriptionObserver.php  # Lógica de recetas
│   └── Services/                # Servicios especializados
│       ├── ObrasSocialesService.php  # Integración SSSalud
│       ├── DigitalPrescriptionService.php  # Recetas digitales
│       └── ...
├── database/
│   ├── migrations/              # Migraciones de BD
│   ├── seeders/                # Seeders de datos iniciales
│   └── factories/              # Factories para testing
├── resources/
│   ├── js/
│   │   ├── Pages/              # Componentes Vue por módulo
│   │   │   ├── Accounting/    # Contabilidad
│   │   │   ├── Admin/         # Administración
│   │   │   ├── Appointments/  # Turnos
│   │   │   ├── Dashboard/     # Dashboard principal
│   │   │   ├── Emergency/     # Guardia
│   │   │   ├── Hospitalization/ # Internación
│   │   │   ├── Maintenance/   # Mantenimiento
│   │   │   ├── Operating/     # Quirófano
│   │   │   ├── Paramedic/     # Paramédicos
│   │   │   ├── Patients/      # Pacientes
│   │   │   ├── Pharmacy/      # Farmacia
│   │   │   ├── Queue/         # Cola de atención
│   │   │   ├── Reception/     # Recepción
│   │   │   ├── Reports/       # Reportes
│   │   │   └── ...            # Otros módulos
│   │   ├── Components/        # Componentes reutilizables
│   │   ├── Layouts/           # Layouts de la aplicación
│   │   └── types/             # Definiciones TypeScript
│   └── views/
│       └── prescriptions/     # Plantillas PDF de recetas
├── routes/
│   ├── web.php               # Rutas web principales
│   ├── auth.php              # Rutas de autenticación
│   └── api.php               # API endpoints
├── tests/
│   ├── Feature/              # Tests de integración
│   └── Unit/                 # Tests unitarios
├── docs/                     # Documentación del proyecto
│   ├── MAINTENANCE.md       # Módulo de mantenimiento
│   ├── PARAMEDIC.md         # Módulo de paramédicos
│   ├── HORARIOS_MEDICOS.md  # Gestión de horarios
│   └── OBRAS_SOCIALES.md    # Integración obras sociales
└── public/
    └── build/               # Assets compilados (Vite)
```

## 🔗 Módulos y Rutas Principales

### Módulos Clínicos

| Módulo                 | Prefijo            | Descripción                             | Roles con Acceso          |
| ---------------------- | ------------------ | --------------------------------------- | ------------------------- |
| **Dashboard**          | `/dashboard`       | Panel principal personalizado por rol   | Todos                     |
| **Pacientes**          | `/patients`        | Gestión de pacientes y datos personales | Admin, Doctor, Secretaria |
| **Turnos**             | `/appointments`    | Sistema de citas y agendado             | Admin, Doctor, Secretaria |
| **Cola de Atención**   | `/queue`           | Gestión de espera y atención            | Admin, Doctor, Secretaria |
| **Historias Clínicas** | `/medical-records` | Registros médicos e historial           | Admin, Doctor             |
| **Recetas**            | `/prescriptions`   | Generación y gestión de prescripciones  | Admin, Doctor             |

### Módulos Hospitalarios

| Módulo              | Prefijo            | Descripción                             | Roles con Acceso             |
| ------------------- | ------------------ | --------------------------------------- | ---------------------------- |
| **Internación**     | `/hospitalization` | Gestión de camas y pacientes internados | Admin, Enfermería            |
| **Quirófano**       | `/operating`       | Salas y agenda quirúrgica               | Admin, Quirófano             |
| **Pre-Internación** | `/pre-admissions`  | Validación pre-quirúrgica               | Admin, Quirófano, Secretaria |
| **Emergencias**     | `/emergency`       | Guardia y admisiones de urgencia        | Admin, Emergencias           |

### Módulos Administrativos

| Módulo             | Prefijo              | Descripción                   | Roles con Acceso                |
| ------------------ | -------------------- | ----------------------------- | ------------------------------- |
| **Recepción**      | `/reception`         | Panel de recepción y check-in | Admin, Secretaria               |
| **Obras Sociales** | `/health-insurances` | Gestión de coberturas médicas | Admin, Secretaria               |
| **Farmacia**       | `/pharmacy`          | Inventario y solicitudes      | Admin, Farmacia                 |
| **Contabilidad**   | `/accounting`        | Cuentas corrientes y pagos    | Admin, Contabilidad             |
| **Facturación**    | `/invoices`          | Generación de facturas        | Admin, Contabilidad, Secretaria |
| **Administración** | `/admin`             | Gestión de usuarios y roles   | Admin                           |

### Módulos de Soporte

| Módulo            | Prefijo        | Descripción                        | Roles con Acceso     |
| ----------------- | -------------- | ---------------------------------- | -------------------- |
| **Mantenimiento** | `/maintenance` | Equipos y órdenes de mantenimiento | Admin, Mantenimiento |
| **Paramédicos**   | `/paramedic`   | Ambulancias y traslados            | Admin, Paramédico    |

### Reportes

| Módulo                 | Prefijo                                | Descripción                    | Roles con Acceso |
| ---------------------- | -------------------------------------- | ------------------------------ | ---------------- |
| **Reportes Avanzados** | `/advanced-reports`                    | Dashboard de reportes          | Admin            |
| **Reporte C2**         | `/advanced-reports/c2`                 | Consultas y derivaciones REFES | Admin            |
| **Epidemiología**      | `/advanced-reports/epidemiology`       | Análisis epidemiológico        | Admin            |
| **Calidad**            | `/advanced-reports/quality-indicators` | KPIs de calidad asistencial    | Admin            |
| **Obras Sociales**     | `/advanced-reports/insurance`          | Facturación por cobertura      | Admin            |
| **Facturación**        | `/advanced-reports/billing`            | Análisis financiero y cobranza | Admin            |
| **Camas**              | `/advanced-reports/bed-occupancy`      | Ocupación y utilización        | Admin            |

## 🔒 Seguridad

- Autenticación mediante Laravel Breeze
- Protección CSRF en formularios
- Validación de datos en frontend y backend
- Control de acceso basado en roles
- Políticas de autorización para recursos sensibles
- Encriptación de contraseñas con bcrypt

## 📝 Licencia

Este proyecto es software privado desarrollado para uso interno de consultorios médicos.

## 👨‍💻 Desarrollo

Para contribuir al proyecto:

1. Crear una rama feature desde `main`
2. Realizar cambios con commits descriptivos
3. Ejecutar tests: `php artisan test`
4. Validar código: `npm run build`
5. Crear Pull Request

## 📞 Soporte

Para reportar bugs o solicitar nuevas funcionalidades, por favor contactar al equipo de desarrollo.
