# Sistema de Permisos por Módulos

## Descripción

Este sistema permite al administrador controlar a qué módulos específicos tiene acceso cada usuario del sistema. Por ejemplo, un doctor puede tener acceso solo a "Pacientes" e "Internación", o cualquier combinación de módulos.

## Características

- ✅ Control granular de acceso por módulo
- ✅ Aplicado tanto en frontend (menú de navegación) como en backend (middleware)
- ✅ Retrocompatible: si no se especifican módulos, el usuario tiene acceso a todo
- ✅ Los administradores siempre tienen acceso total

## Módulos Disponibles

Los módulos configurables son:

| ID                  | Nombre               | Descripción                       |
| ------------------- | -------------------- | --------------------------------- |
| `patients`          | Pacientes            | Ver y gestionar pacientes         |
| `appointments`      | Citas                | Gestionar citas médicas           |
| `calendar`          | Calendario           | Vista de calendario de citas      |
| `reports`           | Reportes             | Ver reportes y estadísticas       |
| `schedules`         | Horarios             | Gestionar horarios médicos        |
| `pharmacy_requests` | Solicitudes Farmacia | Solicitudes a farmacia            |
| `operations`        | Quirófanos           | Gestionar operaciones quirúrgicas |
| `hospitalizations`  | Internación          | Gestionar internaciones           |
| `pre_admissions`    | Pre-Internación      | Gestionar pre-internaciones       |
| `emergency`         | Emergencias          | Módulo de guardia/emergencias     |
| `accounting`        | Contabilidad         | Gestión contable y financiera     |
| `maintenance`       | Mantenimiento        | Gestión de mantenimiento          |
| `paramedic`         | Paramédicos          | Gestión de traslados              |
| `admin`             | Administración       | Panel de administración           |

## Uso desde el Panel de Administración

### Crear Usuario con Permisos

1. Ir a **Doctores** (Admin > Usuarios)
2. Hacer clic en **Crear Nuevo Usuario**
3. Completar los datos del usuario
4. En la sección **🔑 Permisos de Módulos**, seleccionar los módulos permitidos
5. Si no se selecciona ninguno, el usuario tendrá acceso a todos (por defecto)
6. Hacer clic en **Crear Usuario**

### Editar Permisos de Usuario Existente

1. Ir a **Doctores** (Admin > Usuarios)
2. Hacer clic en **Editar** en el usuario deseado
3. Modificar la selección de módulos en **🔑 Permisos de Módulos**
4. Hacer clic en **Guardar Cambios**

## Uso Técnico

### Verificar Permisos en el Modelo User

```php
// Verificar si el usuario tiene acceso a un módulo específico
if ($user->hasModuleAccess('patients')) {
    // Usuario tiene acceso al módulo de pacientes
}

// Obtener todos los módulos permitidos
$allowedModules = $user->getAllowedModules();
```

### Proteger Rutas con Middleware

Para proteger una ruta en el backend, agregar el middleware `module.access`:

```php
// En routes/web.php
Route::get('/patients', [PatientController::class, 'index'])
    ->middleware(['auth', 'module.access:patients']);

Route::get('/operations', [OperationController::class, 'index'])
    ->middleware(['auth', 'module.access:operations']);
```

### Verificar Permisos en el Frontend

El componente `Navigation.vue` ya filtra automáticamente los items del menú según los permisos del usuario. Para verificar manualmente en otro componente:

```vue
<script setup>
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const user = page.props.auth?.user;

// Verificar acceso
const hasAccess = (module) => {
    // Admin siempre tiene acceso
    if (user?.role === "admin") return true;

    // Si no hay módulos especificados, permitir todo
    if (!user?.allowed_modules || user.allowed_modules.length === 0) {
        return true;
    }

    // Verificar si el módulo está en la lista
    return user.allowed_modules.includes(module);
};
</script>

<template>
    <div v-if="hasAccess('patients')">
        <!-- Solo se muestra si tiene acceso al módulo de pacientes -->
    </div>
</template>
```

## Base de Datos

La columna `allowed_modules` en la tabla `users` almacena un array JSON de los módulos permitidos:

```json
["patients", "appointments", "hospitalizations"]
```

## Ejemplos de Uso

### Ejemplo 1: Doctor con Acceso Limitado

Un doctor que solo puede ver pacientes y atenderlos:

```
Módulos habilitados:
- ✅ Pacientes
- ✅ Citas
- ✅ Calendario

El doctor NO verá en el menú:
- ❌ Quirófanos
- ❌ Internación
- ❌ Reportes
- ❌ etc.
```

### Ejemplo 2: Doctor con Acceso a Internación

Un doctor que puede atender pacientes y gestionar internaciones:

```
Módulos habilitados:
- ✅ Pacientes
- ✅ Citas
- ✅ Calendario
- ✅ Internación

El doctor NO verá:
- ❌ Quirófanos
- ❌ Emergencias
- ❌ etc.
```

### Ejemplo 3: Usuario con Acceso Total (por defecto)

Si no se especifican módulos, el usuario tiene acceso a todo lo permitido por su rol:

```
Módulos habilitados: (ninguno seleccionado)
= Acceso a todos los módulos disponibles para su rol
```

## Migración

Para aplicar los cambios en la base de datos:

```bash
php artisan migrate
```

Esto agregará la columna `allowed_modules` a la tabla `users`.

## Retrocompatibilidad

Los usuarios existentes sin módulos configurados seguirán teniendo acceso completo a todas las funcionalidades de su rol. Esto garantiza que el sistema no rompa la funcionalidad existente.

## Notas Importantes

- 🔐 **Los administradores SIEMPRE tienen acceso a todos los módulos**, independientemente de la configuración
- ✅ **El sistema es retrocompatible**: usuarios sin módulos configurados tienen acceso total
- 🎯 **Verificación doble**: tanto el frontend (menú) como el backend (middleware) verifican permisos
- 🔄 **Los cambios son inmediatos**: al editar permisos, el usuario verá los cambios en su próximo inicio de sesión
