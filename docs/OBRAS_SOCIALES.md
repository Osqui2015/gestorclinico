# Integración de Obras Sociales Argentinas

## Descripción

Se ha integrado la funcionalidad de búsqueda y carga de obras sociales argentinas al sistema de registro de pacientes. Ahora los usuarios pueden buscar y seleccionar la obra social durante el registro o edición de pacientes.

## Características

✅ Búsqueda de obras sociales por nombre, sigla, provincia o RNOS  
✅ Vista previa con información completa de la obra social  
✅ Auto-creación de registros de HealthInsurance basados en datos de obras sociales  
✅ Opción para seleccionar obras sociales previamente registradas  
✅ API REST para acceder a datos de obras sociales

## Instalación

### 1. Descargar datos actualizados (Opcional)

Ejecuta el comando para descargar la base de datos más reciente de obras sociales desde GitHub:

```bash
php artisan obras-sociales:download
```

> **Nota:** Este comando descargará los datos del repositorio oficial de cluster311. Si no tienes conexión a Internet o prefieres usar los datos de ejemplo, puedes omitir este paso.

### 1.1 Importar padrón XLS/TSV a base de datos

Si descargaste el listado de SSSalud en formato `.xls` (delimitado por tabulaciones), puedes importarlo directamente a `health_insurances`:

```bash
php artisan obras-sociales:import-xls storage/app/listadoSSSalud.xls
```

Para reemplazar completamente los datos existentes:

```bash
php artisan obras-sociales:import-xls storage/app/listadoSSSalud.xls --truncate
```

El import:

- Detecta automáticamente la cabecera `RNAS`
- Normaliza encoding (UTF-8/Windows-1252/ISO-8859-1)
- Crea o actualiza registros por `code` (RNOS/RNAS)
- Si no hay código, intenta actualizar por `name`

### 2. Permisos en storage

Asegúrate de que el directorio `storage/app/` tiene permisos de lectura:

```bash
chmod -R 755 storage/app/
```

## Uso

### Crear un paciente con obra social

1. Ir a **Pacientes → Crear nuevo paciente**
2. Completar los datos básicos del paciente
3. En la sección **Obra Social (Cobertura Médica)**:
    - Escribir para buscar (mín. 2 caracteres)
    - Las búsquedas pueden hacerse por:
        - Nombre: "OSEP"
        - Sigla: "OSPREN"
        - Provincia: "Mendoza"
        - RNOS: "909001"
4. Seleccionar una obra social de los resultados
5. Revisar la información y confirmar
6. Guardar el paciente

### Editar obra social de un paciente

1. Ir a **Pacientes → Editar**
2. Actualizar la sección de obra social
3. Guardar cambios

## API Endpoints

### Buscar obras sociales

```
GET /api/obras-sociales/search?q=TERMO&limit=20
```

**Respuesta:**

```json
{
    "success": true,
    "count": 5,
    "data": [
        {
            "rnos": "909001",
            "nombre": "O.S.P. MENDOZA (OSEP)",
            "sigla": "OSEP",
            "provincia": "Mendoza",
            "localidad": "",
            "domicilio": "Sin especificar",
            "telefonos": [],
            "emails": [],
            "web": null
        }
    ]
}
```

### Obtener provincias

```
GET /api/obras-sociales/provincias
```

### Obtener por provincia

```
GET /api/obras-sociales/provincia/Mendoza
```

### Obtener por RNOS específico

```
GET /api/obras-sociales/{rnos}
```

## Estructura de datos

Cada obra social en el JSON contiene:

```json
{
    "rnos": "909001",
    "nombre": "O.S.P. MENDOZA (OSEP)",
    "sigla": "OSEP",
    "provincia": "Mendoza",
    "localidad": "GUAYMALLEN",
    "domicilio": "BANDERA DE LOS ANDES 239",
    "cp": "5521",
    "telefonos": [],
    "emails": [],
    "web": null
}
```

## Archivos modificados

### Backend

- **`app/Services/ObrasSocialesService.php`** - Servicio para buscar y gestionar obras sociales
- **`app/Console/Commands/DownloadObrasSociales.php`** - Comando para descargar datos actualizados
- **`app/Http/Controllers/ObrasSocialesController.php`** - Controlador API para obras sociales
- **`app/Http/Controllers/PatientController.php`** - Actualizado para crear HealthInsurance desde obras sociales
- **`routes/api.php`** - Nuevas rutas API para obras sociales

### Frontend

- **`resources/js/Pages/Patients/Create.vue`** - Formulario con búsqueda interactiva
- **`resources/js/Pages/Patients/Edit.vue`** - Formulario de edición con búsqueda

## Flujo de datos

1. Usuario escribe en el campo de búsqueda
2. Se realiza una solicitud AJAX a `/api/obras-sociales/search`
3. Se muestran los resultados en un dropdown
4. Al seleccionar una obra, se envía el objeto completo en `obra_social_data`
5. El controlador verifica si existe en `health_insurances` por código RNOS
6. Si no existe, crea un nuevo registro de HealthInsurance
7. Se asocia el paciente con la obra social

## Actualizar datos de obras sociales

Para mantener actualizada la base de datos de obras sociales:

```bash
php artisan obras-sociales:download
```

Si además quieres reflejarlo en la tabla `health_insurances`, ejecuta luego:

```bash
php artisan obras-sociales:import-xls storage/app/listadoSSSalud.xls
```

Esto descargará el archivo JSON más reciente desde:  
`https://raw.githubusercontent.com/cluster311/obras-sociales-argentinas/master/oss_ar/data/obras_sociales.json`

## Solución de problemas

### No aparecen resultados en búsqueda

1. Verifica que el archivo existe: `storage/app/obras_sociales.json`
2. Ejecuta: `php artisan obras-sociales:download`
3. Revisa los logs: `storage/logs/laravel.log`

### Error "Archivo no encontrado"

Ejecuta:

```bash
php artisan obras-sociales:download
```

O copia manualmente un archivo JSON válido a `storage/app/obras_sociales.json`

### Error "No se encontró la cabecera con columna RNAS"

1. Verifica que el archivo de entrada tenga una fila con columnas iniciando en `RNAS`
2. Confirma que el archivo esté delimitado por tabulaciones o por múltiples espacios
3. Vuelve a ejecutar:

```bash
php artisan obras-sociales:import-xls storage/app/listadoSSSalud.xls
```

### Permission denied en storage

Ejecuta:

```bash
chmod -R 755 storage/
```

## Fuente de datos

Los datos de obras sociales provienen del repositorio oficial de Cluster311:  
**https://github.com/cluster311/obras-sociales-argentinas**

Este repositorio mantiene una lista actualizada de todas las obras sociales argentinas registradas en:

- SISA (Sistema Integrado de Salud)
- SSSalud (Superintendencia de Sistemas de Salud)

## ⚡ Comandos rápidos de mantenimiento

Actualizar catálogo JSON:

```bash
php artisan obras-sociales:download
```

Importar listado XLS/TSV a `health_insurances`:

```bash
php artisan obras-sociales:import-xls storage/app/listadoSSSalud.xls
```

Reimportar desde cero (limpiando tabla):

```bash
php artisan obras-sociales:import-xls storage/app/listadoSSSalud.xls --truncate
```

Compilar assets para producción (Windows PowerShell):

```bash
npm.cmd run build
```

---

**Versión:** 1.0  
**Última actualización:** 3 de marzo de 2026
