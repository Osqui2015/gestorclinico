# Módulo de Emergencias/Urgencias - Documentación Completa

## Descripción General

El módulo de emergencias gestiona el flujo completo de atención de pacientes en urgencias, desde la recepción inicial (triaje) hasta el alta o internación, incluyendo registro automático en el historial médico del paciente.

---

## Flujo de Trabajo

### 1. 🚑 Recepción/Triaje

**Rol**: Enfermera, Personal de emergencias, Administrador

**Ruta**: `/emergency/create`

**Funcionalidades**:

- Selección del paciente (búsqueda por nombre/DNI)
- Asignación de médico tratante (opcional)
- **Nivel de Triaje** (Sistema Manchester):
    - 🔴 **Nivel 1 - Resucitación**: Atención inmediata
    - 🟠 **Nivel 2 - Emergencia**: < 10 minutos
    - 🟡 **Nivel 3 - Urgencia**: 30 minutos
    - 🟢 **Nivel 4 - Menor**: 1-3 horas
    - 🔵 **Nivel 5 - Sin urgencia**: 2-4 horas

- **Datos de ingreso**:
    - Motivo de consulta (obligatorio)
    - Signos vitales iniciales:
        - Presión arterial
        - Frecuencia cardíaca
        - Frecuencia respiratoria
        - Temperatura
        - Saturación de oxígeno
        - Glucemia
    - Nivel de conciencia
    - Observaciones iniciales

**Estado inicial**: `waiting` (En cola de espera)

---

### 2. 📋 Tablero de Emergencias

**Ruta**: `/emergency/board`

**Visualización**:

- **Estadísticas en tiempo real**:
    - Pacientes esperando atención
    - Pacientes en atención
    - Pacientes en observación
    - Alertas de nivel 1 (resucitación)

- **Lista ordenada por prioridad**:
    - Primero por nivel de triaje (menor número = más grave)
    - Luego por antigüedad de admisión
- **Estados de admisión**:
    - `waiting`: Esperando atención
    - `in_care`: En atención médica
    - `observation`: En observación
    - `discharged`: Dado de alta
    - `admitted`: Internado en hospital
    - `transferred`: Derivado a otra institución

---

### 3. 🩺 Atención Médica

**Ruta**: `/emergency/show/{id}`

**Funcionalidades del médico**:

#### A. Ver información completa

- **Datos del paciente**: Nombre, DNI, contacto, dirección
- **Información de ingreso**: Motivo, triaje, signos vitales
- **Historial médico previo**: ✨ **NUEVO**
    - Últimas 10 consultas del paciente
    - Diagnósticos anteriores
    - Tratamientos previos
    - Médicos tratantes

#### B. Acciones disponibles según estado

**Estado: `waiting` (Esperando)**

- 🩺 **Iniciar Atención**: Cambiar estado a `in_care`

**Estado: `in_care` (En atención)**

- ➕ **Registrar Evolución**: Seguimiento clínico con signos vitales actualizados
- 💊 **Crear Receta**: Prescripción médica
- 👁️ **Pasar a Observación**: Para pacientes que requieren monitoreo
- ✅ **Dar de Alta**: Finalizar atención y guardar en historial

**Estado: `observation` (En observación)**

- ➕ **Registrar Evolución**: Actualizaciones del estado del paciente
- ✅ **Dar de Alta**: Cuando el paciente esté estable

#### C. Registrar Evolución

**Ruta**: `/emergency/evolution/{id}`

Permite al médico registrar:

- Signos vitales actualizados
- Notas clínicas de evolución
- Tratamiento administrado
- Medicación suministrada
- Estudios realizados

**Guardado**: Cada evolución se guarda con timestamp y usuario que la registró.

---

### 4. 💊 Crear Receta

**Modal**: Desde vista de atención

**Campos**:

- Medicamento (obligatorio)
- Dosis (obligatorio)
- Cantidad
- Instrucciones de administración
- Duración del tratamiento

**Resultado**:

- Se genera receta digital asociada al paciente
- La receta queda disponible para consulta posterior
- Se puede imprimir o enviar electrónicamente

---

### 5. 🏥 Solicitar Medicación a Farmacia

**Funcionalidad**: Del médico tratante

**Casos de uso**:

- Medicación urgente durante la atención
- Administración inmediata de tratamiento
- Suministros para observación prolongada

**Datos**:

- Ítem de farmacia
- Cantidad requerida
- Urgencia: Normal, Urgente, Emergencia
- Notas adicionales

**Flujo**:

1. Médico solicita medicación
2. Farmacia recibe solicitud con prioridad "EMERGENCIA"
3. Farmacia prepara y envía medicación
4. Se registra el consumo

---

### 6. ✅ Alta del Paciente

**Modal**: Formulario de alta

**Información requerida**:

- **Diagnóstico de alta** (obligatorio)
- **Instrucciones de alta** (obligatorio):
    - Medicación a continuar
    - Controles recomendados
    - Señales de alarma
    - Cuidados en domicilio
- **¿Guardar en historial médico?** (checkbox, activado por defecto)

**Proceso automático**:

1. Se marca el paciente como `discharged`
2. Se registra fecha/hora de alta
3. **Se crea automáticamente un registro en el historial médico** con:
    - Motivo: "EMERGENCIA: [motivo de consulta]"
    - Diagnóstico: Diagnóstico de alta
    - Tratamiento: Compilación de tratamientos e instrucciones
    - Notas privadas: Nivel de triaje, evoluciones, observaciones
    - Doctor: Médico tratante de emergencia

**Resultado**: La consulta de emergencia queda permanentemente registrada en el historial del paciente para futuras referencias.

---

### 7. 🛏️ Internar Paciente

**Endpoint**: `/emergency/{admission}/admit`

**Proceso**:

1. **Selección de cama**: Se verifica disponibilidad
2. **Datos de internación**:
    - Diagnóstico de internación
    - Plan de tratamiento
    - Días estimados de internación
3. **Acciones automáticas**:
    - Se crea registro de hospitalización
    - Se ocupa la cama seleccionada
    - Se cambia estado de emergencia a `admitted`
    - Se vincula la historia de emergencia con la internación

**Redirección**: Al finalizar, redirige a la vista de internación del paciente.

---

## Integración con Historial Médico

### ¿Qué se guarda automáticamente?

Cuando se da de alta a un paciente (con checkbox activado):

```
Registro en medical_records:
├── patient_id: ID del paciente
├── doctor_id: Médico de emergencias
├── reason: "EMERGENCIA: [motivo consulta]"
├── diagnosis: Diagnóstico de alta
├── treatment:
│   - Tratamiento: [treatment_given]
│   - Instrucciones: [discharge_instructions]
│   - Evoluciones (X registradas)
├── private_notes:
│   - "Atención de emergencia"
│   - "Triage nivel X"
│   - [observaciones]
└── created_at: Fecha de alta
```

### Beneficios

✅ **Continuidad asistencial**: Otros médicos pueden ver atenciones de emergencia previas  
✅ **Historial completo**: Pacientes frecuentes tienen registro unificado  
✅ **Auditoría médica**: Trazabilidad de todas las atenciones  
✅ **Mejor diagnóstico**: Información de emergencias previas ayuda en futuras consultas

---

## Permisos y Roles

| Acción               | Emergency | Doctor | Nurse | Admin | Secretaria |
| -------------------- | --------- | ------ | ----- | ----- | ---------- |
| Ver tablero          | ✅        | ✅     | ✅    | ✅    | ❌         |
| Crear admisión       | ✅        | ✅     | ✅    | ✅    | ❌         |
| Ver detalle paciente | ✅        | ✅     | ✅    | ✅    | ❌         |
| Registrar evolución  | ✅        | ✅     | ❌    | ✅    | ❌         |
| Crear receta         | ❌        | ✅     | ❌    | ✅    | ❌         |
| Dar de alta          | ❌        | ✅     | ❌    | ✅    | ❌         |
| Solicitar farmacia   | ❌        | ✅     | ❌    | ✅    | ❌         |
| Internar paciente    | ❌        | ✅     | ✅    | ✅    | ❌         |

**Middleware aplicado**: `EnsureEmergency` (verifica roles permitidos)

---

## Rutas del Módulo

```php
// Grupo protegido por middleware EnsureEmergency
Route::prefix('emergency')->name('emergency.')->group(function () {
    // Vistas principales
    GET  /                           -> board()              // Tablero
    GET  /create                     -> create()             // Formulario nueva admisión
    GET  /history                    -> history()            // Historial de altas
    GET  /{admission}                -> show()               // Detalle admisión
    GET  /{admission}/edit           -> edit()               // Editar admisión
    GET  /{admission}/evolution      -> evolution()          // Formulario evolución

    // Acciones
    POST  /                          -> store()              // Crear admisión
    PATCH /{admission}               -> update()             // Actualizar admisión
    POST  /{admission}/evolution     -> recordEvolution()    // Guardar evolución
    PATCH /{admission}/status        -> changeStatus()       // Cambiar estado / Alta
    POST  /{admission}/prescription  -> createPrescription() // Crear receta
    POST  /{admission}/pharmacy      -> requestPharmacy()    // Solicitar farmacia
    POST  /{admission}/admit         -> admitToHospital()    // Internar
});
```

---

## Modelos y Relaciones

### EmergencyAdmission

```php
Campos principales:
- patient_id (FK)
- attending_doctor_id (FK)
- nurse_id (FK)
- admission_time (datetime)
- triage_time (datetime)
- discharged_at (datetime, nullable)
- triage_level (1-5)
- chief_complaint (motivo)
- signos vitales (BP, HR, RR, temp, O2, glucosa)
- consciousness_level
- status (enum)
- preliminary_diagnosis
- treatment_given
- discharge_diagnosis
- discharge_instructions
- observations

Relaciones:
- patient() -> Patient
- attendingDoctor() -> User
- nurse() -> User
- evolutions() -> EmergencyEvolution[]
```

### EmergencyEvolution

```php
Campos:
- emergency_admission_id (FK)
- recorded_by (FK User)
- recorded_at (datetime)
- signos vitales actualizados
- clinical_notes
- treatment_notes
- medications_given
- tests_performed

Relación:
- recordedBy() -> User
```

### MedicalRecord (Historial)

```php
Campos:
- patient_id (FK)
- doctor_id (FK)
- reason (motivo consulta)
- diagnosis
- treatment
- private_notes (encriptadas)
- is_first_consultation (bool)
- created_at

Relaciones:
- patient() -> Patient
- doctor() -> User
- prescriptions() -> Prescription[]
```

---

## Casos de Ejemplo

### Caso 1: Paciente con dolor abdominal severo

1. **Recepción** (10:30):
    - Paciente: Juan Pérez, DNI 12345678
    - Motivo: "Dolor abdominal intenso, 4 horas de evolución"
    - Triaje: Nivel 2 (Emergencia)
    - Signos vitales: PA 140/90, FC 110, Temp 38°C
    - Estado: `waiting`

2. **Atención médica** (10:35):
    - Doctor inicia atención → Estado: `in_care`
    - Examina paciente, sospecha apendicitis
    - Solicita análisis de sangre
    - Registra evolución

3. **Tratamiento** (11:00):
    - Solicita analgésico a farmacia (urgente)
    - Registra nueva evolución con signos vitales
    - Confirma diagnóstico: Apendicitis aguda
    - Decide internar para cirugía

4. **Internación** (11:30):
    - Selecciona cama en piso quirúrgico
    - Completa datos de internación
    - Estado: `admitted`
    - **Se guarda automáticamente en historial médico**

### Caso 2: Paciente con gripe

1. **Recepción** (14:00):
    - Triaje: Nivel 4 (Menor urgencia)
    - Motivo: "Fiebre, tos, malestar general"
    - Estado: `waiting`

2. **Atención** (14:45):
    - Doctor ve historial: paciente tuvo gripe hace 2 meses
    - Examina, confirma cuadro viral
    - Crea receta con antipiréticos y reposo
    - Estado: `in_care`

3. **Alta** (15:00):
    - Completa diagnóstico de alta: "Síndrome gripal"
    - Instrucciones: "Reposo, hidratación, paracetamol cada 8h"
    - Guarda en historial: ✅ activado
    - Estado: `discharged`
    - **Consulta queda registrada para futuras referencias**

---

## Navegación en el Sistema

### Menú de Emergencias

Los usuarios con acceso verán:

```
📋 Dashboard
🚑 Guardia (emergency.board)
  ├── ➕ Nueva Admisión
  ├── Tablero de Pacientes
  └── Historial de Emergencias
```

### Breadcrumbs típicos

```
Emergencias > Tablero
Emergencias > Nueva Admisión
Emergencias > Admisión #123 > Juan Pérez
Emergencias > Admisión #123 > Evolución
```

---

## Indicadores y Métricas

### En el tablero se muestran:

- **Tiempo en emergencia**: Desde admisión hasta estado actual
- **Prioridad visual**: Por color según triaje
- **Cantidad de evoluciones**: Número de seguimientos registrados
- **Estado actual**: Badge con color distintivo

### Alertas especiales:

- 🚨 Pacientes nivel 1 (resucitación) se destacan en rojo
- ⏰ Tiempo de espera excesivo según nivel de triaje
- 📊 Saturación del servicio (muchos pacientes en espera)

---

## Archivos del Sistema

### Backend

- `app/Http/Controllers/EmergencyController.php` - Lógica principal
- `app/Models/EmergencyAdmission.php` - Modelo de admisión
- `app/Models/EmergencyEvolution.php` - Modelo de evolución
- `app/Models/MedicalRecord.php` - Historial médico
- `app/Http/Middleware/EnsureEmergency.php` - Control de acceso
- `routes/web.php` - Rutas del módulo

### Frontend

- `resources/js/Pages/Emergency/Board.vue` - Tablero principal
- `resources/js/Pages/Emergency/Create.vue` - Formulario admisión
- `resources/js/Pages/Emergency/Show.vue` - Detalle paciente (MEJORADO)
- `resources/js/Pages/Emergency/Evolution.vue` - Registro evolución
- `resources/js/Pages/Emergency/History.vue` - Historial de altas

### Base de datos

- `emergency_admissions` - Admisiones
- `emergency_evolutions` - Evoluciones
- `medical_records` - Historial médico general
- `prescriptions` - Recetas
- `pharmacy_requests` - Solicitudes a farmacia
- `hospitalizations` - Internaciones

---

## Mejoras Implementadas ✨

### 1. Historial médico visible

Ahora los médicos pueden ver las últimas 10 consultas del paciente directamente en la vista de emergencias, facilitando el diagnóstico.

### 2. Guardado automático en historial

Las altas de emergencia se registran automáticamente en el historial médico del paciente, manteniendo continuidad asistencial.

### 3. Acciones contextuales

Los botones de acción se muestran dinámicamente según el estado del paciente:

- Esperando → Iniciar atención
- En atención → Evolución, Receta, Alta, Observación
- En observación → Evolución, Alta

### 4. Integración completa

- **Con farmacia**: Solicitudes urgentes desde emergencias
- **Con internación**: Ingreso directo sin duplicar datos
- **Con historias clínicas**: Registro unificado

---

## Validaciones y Seguridad

### Validaciones de negocio

- ✅ No se puede dar de alta sin diagnóstico
- ✅ No se puede internar sin cama disponible
- ✅ Solo médicos pueden crear recetas
- ✅ El historial se guarda con doctor tratante correcto

### Seguridad

- 🔒 Notas privadas del historial están encriptadas
- 🔒 Solo roles autorizados acceden al módulo
- 🔒 Auditoría de todas las acciones (timestamps + usuario)

---

## Troubleshooting

### "No puedo ver el historial médico del paciente"

**Solución**: El historial solo aparece si el paciente tiene consultas previas registradas.

### "No aparece el botón de dar de alta"

**Causa**: El paciente debe estar en estado `in_care` o `observation`.  
**Solución**: Primero iniciar la atención.

### "Error al internar paciente"

**Causa**: La cama seleccionada no está disponible.  
**Solución**: Verificar en el módulo de internación qué camas están libres.

### "La receta no se crea"

**Causa**: Usuario no tiene rol de doctor.  
**Solución**: Solo médicos pueden crear recetas.

---

## Changelog

### v2.0 (Marzo 2026) - ✨ Mejoras Completas

- ✅ Historial médico visible en vista de emergencias
- ✅ Guardado automático en historial al dar de alta
- ✅ Modal de alta con checkbox para guardar historial
- ✅ Creación de recetas desde emergencias
- ✅ Solicitud de medicación a farmacia
- ✅ Internación directa desde emergencias
- ✅ Botones de acción contextuales según estado
- ✅ Integración completa con otros módulos

### v1.0 (Inicial)

- Recepción y triaje
- Tablero de emergencias
- Registro de evoluciones
- Cambio de estados básico

---

## Próximas Mejoras Sugeridas

1. **Impresión de documentos**:
    - Informe de alta imprimible
    - Recetas con logo de la clínica
    - Resumen de evoluciones

2. **Estadísticas avanzadas**:
    - Tiempo promedio de atención por nivel de triaje
    - Tipos de patologías más frecuentes
    - Ocupación del servicio por horarios

3. **Alertas automáticas**:
    - Notificaciones cuando tiempo de espera excede límite
    - Avisos de pacientes deteriorándose (signos vitales críticos)

4. **Integración con laboratorio**:
    - Solicitud de estudios desde emergencias
    - Resultados en tiempo real

---

## Contacto y Soporte

Para dudas sobre el módulo de emergencias, contactar al equipo de desarrollo o consultar la documentación técnica del proyecto.

**Documentos relacionados**:

- `DEPLOYMENT_STATUS.md` - Estado del despliegue
- `HORARIOS_MEDICOS.md` - Gestión de agendas médicas
- `INTERNACION.md` - Módulo de internación
- `FARMACIA.md` - Sistema de farmacia
