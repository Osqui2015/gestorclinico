# Gestión de Horarios y Excepciones del Médico

## Descripción

Se ha implementado un sistema completo para que los médicos puedan gestionar sus horarios de atención y excepciones (días no laborables). Este sistema controla qué días y en qué horarios el médico atiende, evitando que se agenden citas fuera de su disponibilidad.

## ✨ Funcionalidades Implementadas

### 1. **Horarios de Atención**

El médico puede configurar sus horarios semanales:

- ✅ Día de la semana (Lunes a Domingo)
- ✅ Hora de inicio (ej: 08:00)
- ✅ Hora de fin (ej: 17:00)
- ✅ Duración de cada cita (15, 20, 30, 45, 60 minutos)
- ✅ Estado activo/inactivo
- ✅ Editar y eliminar horarios existentes

### 2. **Excepciones (Días No Laborables)**

El médico puede cancelar días específicos:

- ✅ Fecha específica
- ✅ Tipo de excepción:
    - Vacaciones
    - Licencia médica
    - Feriado
    - Congreso/Conferencia
    - Otro
- ✅ Todo el día o rango horario específico
- ✅ Motivo/razón opcional
- ✅ Eliminar excepciones

### 3. **Integración con Sistema de Citas**

El sistema de agendamiento respeta automáticamente:

- ✅ Solo muestra horarios configurados por el médico
- ✅ Excluye días con excepciones (todo el día)
- ✅ Excluye rangos horarios con excepciones parciales
- ✅ Genera slots según la duración configurada
- ✅ Muestra mensajes informativos cuando no hay disponibilidad

## 📂 Archivos Creados/Modificados

### Backend

**Controladores:**

- [app/Http/Controllers/DoctorScheduleController.php](app/Http/Controllers/DoctorScheduleController.php) - Ya existía, actualizado
- [app/Http/Controllers/AppointmentController.php](app/Http/Controllers/AppointmentController.php) - Actualizado método `getAvailability()`

**Modelos:**

- [app/Models/DoctorSchedule.php](app/Models/DoctorSchedule.php) - Ya existía
- [app/Models/DoctorException.php](app/Models/DoctorException.php) - Ya existía

### Frontend

**Vistas Vue:**

- [resources/js/Pages/DoctorSchedules/Index.vue](resources/js/Pages/DoctorSchedules/Index.vue) - Lista de horarios ✨ NUEVO
- [resources/js/Pages/DoctorSchedules/Create.vue](resources/js/Pages/DoctorSchedules/Create.vue) - Crear horario ✨ NUEVO
- [resources/js/Pages/DoctorSchedules/Edit.vue](resources/js/Pages/DoctorSchedules/Edit.vue) - Editar horario ✨ NUEVO
- [resources/js/Pages/DoctorSchedules/Exceptions.vue](resources/js/Pages/DoctorSchedules/Exceptions.vue) - Gestionar excepciones ✨ NUEVO

**Componentes:**

- [resources/js/Components/Navigation.vue](resources/js/Components/Navigation.vue) - Agregados enlaces al menú

## 🚀 Uso

### Para Médicos

#### Configurar Horarios de Atención

1. **Acceder al menú:**
    - Clic en "Horarios" en el menú de navegación

2. **Agregar nuevo horario:**
    - Clic en "+ Nuevo Horario"
    - Seleccionar día de la semana (ej: Lunes)
    - Definir hora inicio (ej: 08:00)
    - Definir hora fin (ej: 17:00)
    - Seleccionar duración de cita (ej: 30 minutos)
    - Guardar

3. **Editar horario existente:**
    - Clic en "Editar" en el horario deseado
    - Modificar los campos necesarios
    - Marcar/desmarcar "Horario activo" para habilitar/deshabilitar temporalmente
    - Guardar cambios

4. **Eliminar horario:**
    - Clic en "Eliminar" en el horario deseado
    - Confirmar la eliminación

#### Configurar Excepciones (Días No Laborables)

1. **Acceder a excepciones:**
    - Clic en "Excepciones" en el menú de navegación
    - O desde la página de Horarios → "Ver Excepciones"

2. **Agregar excepción:**
    - Clic en "+ Nueva Excepción"
    - Seleccionar fecha
    - Seleccionar tipo (Vacaciones, Licencia, etc.)
    - Agregar motivo opcional
    - Marcar "Todo el día" o definir rango horario específico
    - Guardar

3. **Eliminar excepción:**
    - Clic en "Eliminar" en la excepción deseada
    - Confirmar la eliminación

### Para Pacientes/Secretarias

Cuando se intenta agendar una cita:

- Solo verán días y horarios según la configuración del médico
- No podrán seleccionar días con excepciones
- Recibirán mensajes informativos si el médico no está disponible

## 📊 Ejemplos de Configuración

### Ejemplo 1: Médico de Lunes a Viernes

```
Lunes:    08:00 - 13:00 (30 min por cita)
Martes:   08:00 - 13:00 (30 min por cita)
Miércoles: 08:00 - 13:00 (30 min por cita)
Jueves:   08:00 - 13:00 (30 min por cita)
Viernes:  08:00 - 13:00 (30 min por cita)

Excepciones:
- 15/03/2026: Vacaciones
- 20/03/2026: Congreso médico
```

### Ejemplo 2: Médico con Turnos Tarde

```
Lunes:    14:00 - 20:00 (45 min por cita)
Miércoles: 14:00 - 20:00 (45 min por cita)
Viernes:  14:00 - 20:00 (45 min por cita)

Excepciones:
- 25/03/2026 14:00-16:00: Reunión administrativa
```

## 🔄 Flujo del Sistema

### Agendamiento de Cita

1. Usuario selecciona médico y fecha
2. Sistema verifica:
    - ¿El médico tiene horario configurado para ese día de la semana?
    - ¿Existe una excepción para esa fecha específica?
3. Si todo está OK:
    - Genera slots según horario configurado
    - Excluye slots con citas existentes
    - Excluye slots que coincidan con excepciones parciales
4. Muestra horarios disponibles al usuario

## 🛠️ API Endpoint

### Obtener disponibilidad de un médico

```http
GET /api/appointments/{doctor_id}/availability/{date}
```

**Ejemplo:**

```http
GET /api/appointments/3/availability/2026-03-15
```

**Respuesta exitosa:**

```json
{
    "date": "2026-03-15",
    "doctor_id": 3,
    "doctor_name": "Dr. Juan Pérez",
    "slots": [
        {
            "time": "2026-03-15 08:00",
            "available": true,
            "display": "08:00"
        },
        {
            "time": "2026-03-15 08:30",
            "available": false,
            "display": "08:30"
        }
    ],
    "schedule": {
        "start": "08:00:00",
        "end": "17:00:00",
        "duration": 30
    }
}
```

**Respuesta cuando no hay horarios:**

```json
{
    "date": "2026-03-15",
    "doctor_id": 3,
    "doctor_name": "Dr. Juan Pérez",
    "slots": [],
    "message": "El médico no atiende los saturday"
}
```

**Respuesta cuando hay excepción:**

```json
{
    "date": "2026-03-20",
    "doctor_id": 3,
    "doctor_name": "Dr. Juan Pérez",
    "slots": [],
    "message": "El médico no está disponible este día: Vacaciones"
}
```

## 📋 Rutas

```php
// Ver horarios
GET /doctor-schedules

// Crear horario
GET /doctor-schedules/create
POST /doctor-schedules

// Editar horario
GET /doctor-schedules/{id}/edit
PATCH /doctor-schedules/{id}

// Eliminar horario
DELETE /doctor-schedules/{id}

// Ver excepciones
GET /doctor-exceptions

// Crear excepción
POST /doctor-exceptions

// Eliminar excepción
DELETE /doctor-exceptions/{id}
```

## 🔐 Permisos

- **Médicos:** Pueden gestionar sus propios horarios y excepciones
- **Administradores:** Pueden gestionar horarios y excepciones de cualquier médico
- **Secretarias:** Solo pueden ver, no modificar

## 💡 Consejos de Uso

1. **Configure sus horarios regulares primero** antes de agregar excepciones
2. **Use excepciones de "todo el día"** para vacaciones completas
3. **Use excepciones parciales** para reuniones o compromisos específicos
4. **Desactive un horario temporalmente** en lugar de eliminarlo si planea reactivarlo
5. **Agregue motivos a las excepciones** para recordar por qué no está disponible

## 🐛 Solución de Problemas

### No aparecen slots disponibles al agendar cita

**Verificar:**

1. ¿El médico tiene configurado un horario para ese día de la semana?
2. ¿El horario está marcado como "activo"?
3. ¿No hay una excepción para esa fecha?
4. ¿La hora de inicio es menor que la hora de fin?

### Los pacientes ven horarios incorrectos

**Solución:**

1. Revisar y actualizar los horarios en "Horarios"
2. Verificar las excepciones en "Excepciones"
3. Confirmar que el horario esté activo

---

**Versión:** 1.0  
**Última actualización:** 2 de marzo de 2026
