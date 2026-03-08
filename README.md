# 🏥 Gestor Clínico

Sistema integral de gestión para consultorios y clínicas médicas desarrollado con tecnologías web modernas.

## 📋 Descripción

**Gestor Clínico** es una aplicación web completa diseñada para digitalizar y optimizar la administración de consultorios médicos. Permite gestionar pacientes, doctores, turnos, historias clínicas, recetas médicas, obras sociales y facturación de manera eficiente y profesional.

### ✨ Características Principales

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
- 🏥 **Obras Sociales**: Gestión de seguros médicos y coberturas
    - Descarga de padrón actualizado (`obras-sociales:download`)
    - Importación masiva desde archivo XLS/TSV de SSSalud (`obras-sociales:import-xls`)
    - Búsqueda y autocompletado en formularios
- 💰 **Facturación**: Sistema completo de generación de facturas e invoices
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

### Doctor

- Gestión de pacientes asignados
- Creación y edición de historias clínicas
- Generación de recetas médicas
- Atención de turnos programados
- Consulta de historiales

### Secretaria

- Registro de nuevos pacientes
- Creación y gestión de turnos
- Gestión de cola de atención
- Facturación básica

### Farmacia

- Gestión de inventario de medicamentos, insumos e instrumental
- Procesamiento de solicitudes internas
- Control de stock, vencimientos y esterilización

### Encargado de Quirófano

- Configuración de salas quirúrgicas
- Gestión de agenda de operaciones y disponibilidad de salas

### Enfermería

- Gestión de internaciones y seguimiento de camas
- Registro operativo de limpieza de camas

### Emergencias

- Gestión del tablero de guardia y evoluciones
- Seguimiento de admisiones y cambios de estado

### Contabilidad

- Gestión de cuentas corrientes y movimientos financieros
- Seguimiento de deudores y exportes contables

### Mantenimiento

- Gestión de equipos médicos
- Registro y seguimiento de órdenes de mantenimiento

### Paramédico

- Gestión de móviles/ambulancias
- Registro y seguimiento de traslados

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
│   ├── Http/Controllers/     # Controladores
│   ├── Models/               # Modelos Eloquent
│   ├── Policies/             # Políticas de autorización
│   └── Observers/            # Observadores de modelos
├── database/
│   ├── migrations/           # Migraciones de BD
│   ├── seeders/             # Seeders
│   └── factories/           # Factories para testing
├── resources/
│   ├── js/
│   │   ├── Pages/           # Componentes Vue por módulo
│   │   ├── Components/      # Componentes reutilizables
│   │   └── Layouts/         # Layouts de la aplicación
│   └── views/
│       └── prescriptions/   # Plantillas PDF de recetas
├── routes/
│   ├── web.php             # Rutas web
│   └── auth.php            # Rutas de autenticación
└── public/
    └── build/              # Assets compilados
```

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
