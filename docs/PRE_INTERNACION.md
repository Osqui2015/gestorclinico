# Módulo Pre-Internación - Validación y Documentación

## Descripción General

El módulo de Pre-Internación permite gestionar el proceso de validación de datos y documentación de pacientes antes de operaciones quirúrgicas. Las secretarias verifican información completa del paciente, validan documentación requerida y confirman el estado "Lista para cirugía".

## Flujo de Trabajo

### 1. Creación Automática

- Al **crear o editar una operación**, el sistema genera automáticamente un registro de pre-internación en estado `pending_assignment`.
- Cada operación tiene una pre-internación asociada (relación 1:1).

### 2. Asignación de Secretaria (Admin)

- Solo **administradores** pueden asignar secretarias.
- Desde el listado de pre-internaciones, el admin selecciona una secretaria del dropdown y presiona "Asignar".
- El estado cambia a `data_pending`.

### 3. Verificación de Datos (Secretaria)

La secretaria accede al detalle de la pre-internación y completa:

- **Número de urgencia**: Identificador único del paciente para el día.
- **Teléfono de contacto**: Para comunicaciones inmediatas.
- **Contacto de emergencia**: Nombre y teléfono.
- **Historia médica verificada**: Confirma si la historia clínica está completa.
- **Observaciones del paciente**: Notas adicionales (alergias, observaciones especiales).

Al guardar, el estado cambia a `documents_pending` y se marca `data_verified_at`.

### 4. Gestión de Documentos (Secretaria)

La secretaria verifica que el paciente haya traído todos los documentos obligatorios:

**Documentos requeridos por defecto** (ver `RequiredDocumentSeeder.php`):

1. DNI / Cédula de Identidad
2. Consentimiento Informado Firmado
3. Historia Clínica Completa
4. Análisis de Laboratorio
5. Estudios de Imágenes (Rx, TAC, RMI, etc.)
6. Evaluación Cardiológica (si corresponde)
7. Autorización de Obra Social (si corresponde)
8. Declaración Jurada de Ayuno
9. Control de Presión Arterial Pre-Quirúrgico
10. Test COVID-19 (si corresponde)

**Acciones por documento:**

- **Subir archivo**: La secretaria carga el documento digitalizado.
- **Verificar**: Confirma que el documento está correcto y completo.
- **Rechazar**: Marca como rechazado con motivo (documento incompleto, vencido, etc.).
- **Ver archivo**: Descarga/visualiza el documento subido.

### 5. Confirmación Final (Secretaria)

Cuando:

- ✅ Todos los datos están verificados (`data_verified_at` != null)
- ✅ Todos los documentos obligatorios están verificados o marcados como N/A

El botón **"✅ Confirmar para cirugía"** se habilita. Al presionarlo:

- El estado de la pre-internación cambia a `ready_for_surgery`.
- El estado de la operación asociada cambia a `ready_for_surgery`.
- La sala de operaciones puede ver el paciente en **verde** (listo).
- Se registra `ready_for_surgery_at` con timestamp.

### 6. Cancelación

En cualquier momento, la secretaria o admin puede **cancelar** la pre-internación con un motivo. Esto:

- Cambia el estado a `cancelled`.
- Resetea la operación a `scheduled`.
- Registra `cancelled_at` y `cancellation_reason`.

---

## Estados del Flujo

| Estado               | Descripción                                           |
| -------------------- | ----------------------------------------------------- |
| `pending_assignment` | Sin secretaria asignada                               |
| `data_pending`       | Secretaria asignada, esperando verificación de datos  |
| `documents_pending`  | Datos verificados, esperando validación de documentos |
| `ready_for_surgery`  | ✅ Todo validado, paciente listo para quirófano       |
| `cancelled`          | Pre-internación cancelada                             |

---

## Roles y Permisos

| Rol            | Permisos                                                                                                           |
| -------------- | ------------------------------------------------------------------------------------------------------------------ |
| **Admin**      | Asignar secretarias, ver todas las pre-internaciones, verificar, cancelar                                          |
| **Secretaria** | Ver pre-internaciones asignadas a ella (o sin asignar), verificar datos, gestionar documentos, confirmar, cancelar |
| **Doctor**     | No tiene acceso directo (solo ve el estado en la operación)                                                        |

---

## Archivos del Módulo

### Backend

```
app/Models/
  ├── PreAdmission.php              # Modelo principal
  ├── PreAdmissionDocument.php      # Documentos por pre-internación
  └── RequiredDocument.php          # Catálogo de documentos requeridos

app/Http/Controllers/
  └── PreAdmissionController.php    # Controlador principal (9 rutas)

app/Http/Middleware/
  └── EnsurePreAdmissionAccess.php  # Middleware de autorización

database/migrations/
  ├── 2026_03_07_000010_create_pre_admissions_table.php
  ├── 2026_03_07_000011_create_required_documents_table.php
  └── 2026_03_07_000012_create_pre_admission_documents_table.php

database/seeders/
  └── RequiredDocumentSeeder.php    # 10 documentos por defecto
```

### Frontend

```
resources/js/Pages/PreAdmission/
  ├── Index.vue                     # Listado con filtros y asignación
  └── Show.vue                      # Detalle con tabs y verificación

resources/js/Components/
  └── Navigation.vue                # Menú actualizado (secretaria/admin)
```

### Rutas

```
GET    /pre-admissions                              # Listado
GET    /pre-admissions/{id}                         # Detalle
POST   /pre-admissions/{id}/assign                  # Asignar secretaria
POST   /pre-admissions/{id}/verify-data             # Verificar datos paciente
POST   /pre-admissions/{id}/upload-document         # Subir documento
POST   /pre-admissions/{id}/verify-document         # Verificar documento
POST   /pre-admissions/{id}/reject-document         # Rechazar documento
POST   /pre-admissions/{id}/confirm                 # Confirmar listo para cirugía
POST   /pre-admissions/{id}/cancel                  # Cancelar pre-internación
```

---

## Instalación / Despliegue

### 1. Ejecutar Migraciones

```bash
php artisan migrate
```

Crea las 3 tablas: `pre_admissions`, `required_documents`, `pre_admission_documents`.

### 2. Ejecutar Seeder

```bash
php artisan db:seed --class=RequiredDocumentSeeder
```

Carga los 10 documentos requeridos por defecto.

### 3. Compilar Frontend

```bash
npm run build
# o si PowerShell bloquea:
npm.cmd run build
```

### 4. Verificar Rutas

```bash
php artisan route:list --name=pre-admissions
```

Debe mostrar las 9 rutas del módulo.

---

## Validación de Funcionamiento

### Test Manual Básico

1. **Como Admin:**
    - Crear una nueva operación desde "Quirófanos".
    - Ir a "Pre-Internación" en el menú.
    - Verificar que aparece la nueva operación en estado "Sin asignar".
    - Seleccionar una secretaria del dropdown y presionar "Asignar".
    - El estado debe cambiar a "Datos pendientes".

2. **Como Secretaria:**
    - Acceder a "Pre-Internación".
    - Ver la operación asignada.
    - Hacer clic en "Ver detalles".
    - En la pestaña "Datos del paciente", completar todos los campos obligatorios (número urgencia, teléfonos, etc.).
    - Presionar "✅ Verificar datos" → debe cambiar a "Documentos pendientes".

3. **Gestión de Documentos:**
    - Ir a la pestaña "Documentación".
    - Para cada documento: presionar "📤 Subir" y cargar un archivo PDF/JPG.
    - Después de subir, presionar "✅ Verificar".
    - Repetir para todos los documentos obligatorios.

4. **Confirmación:**
    - Cuando todos los documentos estén verificados, el botón "✅ Confirmar para cirugía" se habilitará.
    - Presionarlo → la pre-internación pasa a "Lista para cirugía" (verde).
    - La operación en el módulo "Quirófanos" debe mostrar el estado actualizado.

---

## Relaciones con Otros Módulos

- **Operaciones (Quirófanos)**: Al crear/editar una operación se genera automáticamente la pre-internación.
- **Pacientes**: Cada pre-internación está vinculada a un paciente específico.
- **Usuarios (Secretarias)**: Las secretarias gestionan el proceso de validación.

---

## Consideraciones Técnicas

### Validación de Documentos Obligatorios

La lógica de confirmación (`PreAdmission::canConfirmForSurgery()`) verifica que:

- Todos los documentos marcados como `is_mandatory = true` en `required_documents` estén en estado `verified` o `not_applicable`.
- La cuenta de documentos verificados coincida con la cantidad de documentos obligatorios activos.

### Almacenamiento de Archivos

- Los archivos se guardan en `storage/app/public/pre-admissions/{pre_admission_id}/`.
- Se usa el método `store()` de Laravel con el disco `public`.
- Para acceder, configurar el link simbólico: `php artisan storage:link`.

### Índice MySQL Compuesto

La migración `000012` usa un nombre de índice corto (`pad_pre_req_unique`) para evitar exceder el límite de 64 caracteres de MySQL en índices compuestos.

### Sincronización Automática

El `OperationController` tiene ganchos en `store()` y `update()` para crear/actualizar la pre-internación vinculada usando `updateOrCreate()`.

---

## Extensiones Futuras

- **Notificaciones**: Alertar a la secretaria cuando se le asigna una pre-internación.
- **Documentos por Tipo de Cirugía**: Usar el campo `applicability` para requerir documentos específicos según el tipo de operación o la obra social del paciente.
- **Auditoría**: Registrar quién verificó cada documento y cuándo (ya se almacenan `uploaded_at`, `verified_at`).
- **Integración con Farmacia**: Validar que los insumos requeridos por la operación estén disponibles antes de confirmar.
- **Dashboard para Secretarias**: Vista resumen con métricas de pre-internaciones pendientes, verificadas hoy, etc.

---

## Troubleshooting

### Error: "No autorizado" al acceder

- Verificar que el usuario tenga rol `secretary` o `admin`.
- El middleware `EnsurePreAdmissionAccess` bloquea otros roles.

### No aparece la pre-internación después de crear la operación

- Verificar que el `OperationController` tenga el gancho `updateOrCreate` en `store()` y `update()`.
- Revisar logs: `storage/logs/laravel.log`.

### El botón "Confirmar" no se habilita

- Verificar que `data_verified_at` esté registrado (tab "Datos" completado).
- Verificar que todos los documentos obligatorios estén en estado `verified` o `not_applicable`.
- Revisar la lógica en `PreAdmission::areDocumentsVerified()`.

### Error al subir archivos

- Confirmar que el directorio `storage/app/public/pre-admissions/` tiene permisos de escritura.
- Ejecutar `php artisan storage:link` si no existe el symlink.

---

## Soporte

Para consultas técnicas o reportes de bugs, contactar al equipo de desarrollo.

**Fecha de Creación:** 7 de marzo de 2026
