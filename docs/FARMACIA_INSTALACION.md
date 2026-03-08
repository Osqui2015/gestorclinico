# Instalación del Sistema de Farmacia

## Pasos de Instalación

### 1. Ejecutar las Migraciones

```bash
php artisan migrate
```

Esto creará las siguientes tablas:

- Actualización del rol en `users` (agrega 'pharmacy')
- `pharmacy_items`
- `pharmacy_requests`
- `pharmacy_request_items`
- `pharmacy_stock_movements`

### 2. Crear Usuario de Farmacia

Puedes crear un usuario de farmacia de dos formas:

#### Opción A: Usando el panel de administración

1. Inicia sesión como administrador
2. Ve a "Doctores" (Gestión de usuarios)
3. Crea un nuevo usuario
4. Selecciona rol "Farmacia"

#### Opción B: Usando Tinker (Consola de Laravel)

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Farmacéutico';
$user->email = 'farmacia@clinica.com';
$user->password = bcrypt('password'); // Cambiar por una contraseña segura
$user->role = 'pharmacy';
$user->save();
```

### 3. Compilar los Assets de Frontend

```bash
npm install
npm run dev
```

O para producción:

```bash
npm run build
```

### 4. Verificar la Instalación

1. Inicia sesión con el usuario de farmacia creado
2. Deberías ser redirigido automáticamente al dashboard de farmacia
3. Verifica que puedes acceder a:
    - Dashboard de Farmacia
    - Inventario
    - Solicitudes

## Configuración Inicial

### Agregar Items al Inventario

1. Ve a "Inventario" > "Nuevo Item"
2. Completa la información:
    - **Medicamentos**: Nombre, código, laboratorio, stock, vencimiento
    - **Instrumentos**: Nombre, código, stock, esterilización si aplica
    - **Insumos**: Nombre, código, stock

### Configurar Alertas

Para cada item, configura:

- **Stock Mínimo**: Nivel en el que aparecerá alerta de stock bajo
- **Punto de Reorden**: Nivel en el que deberías reordenar el producto

Ejemplo:

- Stock Mínimo: 10 unidades
- Punto de Reorden: 20 unidades

Cuando el stock llegue a 10 o menos, aparecerá en la alerta de "Stock Bajo".

## Uso Básico

### Para Farmacéuticos

#### Gestión de Inventario

1. **Ver inventario**: Accede desde el menú "Inventario"
2. **Agregar item**: Clic en "Nuevo Item" y completa el formulario
3. **Editar item**: Clic en "Editar" en la lista de items
4. **Ajustar stock**: Desde el detalle del item, usa "Ajustar Stock"

#### Procesar Solicitudes

1. Ve al dashboard o "Solicitudes"
2. Las solicitudes pendientes aparecen ordenadas por prioridad
3. Clic en una solicitud para ver detalles
4. Clic en "Procesar" para iniciar
5. Ingresa las cantidades entregadas
6. Clic en "Entregar" para completar

#### Alertas

El dashboard muestra automáticamente:

- Items con stock bajo
- Items próximos a vencer (30 días)
- Items vencidos
- Instrumentos que necesitan esterilización (7 días)

### Para Médicos

#### Crear Solicitud de Farmacia

1. Desde el dashboard, busca la opción "Solicitudes de Farmacia"
2. Clic en "Nueva Solicitud"
3. Selecciona los items necesarios
4. Indica cantidades
5. Asigna prioridad:
    - **Urgente**: Emergencia
    - **Alta**: Necesario hoy
    - **Normal**: Necesario pronto
    - **Baja**: Puede esperar
6. Opcionalmente vincula con paciente/cita
7. Agrega notas si es necesario
8. Envía la solicitud

#### Ver Estado de Solicitudes

1. Ve a "Mis Solicitudes"
2. Verás el estado actual:
    - **Pendiente**: Aún no procesada
    - **En Proceso**: Farmacia está preparando
    - **Completada**: Ya entregada
    - **Cancelada**: Cancelada

## Flujos de Trabajo Comunes

### Flujo 1: Recepción de Nueva Compra

1. Ve a "Inventario"
2. Si el item ya existe:
    - Busca el item
    - Clic en "Ver" o "Editar"
    - Usa "Ajustar Stock" > "Entrada"
    - Ingresa cantidad y referencia (Nº factura)
3. Si es un item nuevo:
    - Clic en "Nuevo Item"
    - Completa todos los campos
    - El stock inicial se registra automáticamente

### Flujo 2: Descarte de Item Vencido

1. Ve a "Inventario"
2. Busca el item vencido (o revisa alertas)
3. Clic en "Ver" o "Editar"
4. Usa "Ajustar Stock" > "Vencido"
5. Ingresa la cantidad a descartar
6. Agrega notas sobre el descarte

### Flujo 3: Esterilización de Instrumento

1. Ve a "Inventario"
2. Busca el instrumento
3. Clic en "Editar"
4. Actualiza:
    - Última Esterilización: Fecha de hoy
    - Próxima Esterilización: Fecha futura según protocolo
5. Guarda cambios

### Flujo 4: Solicitud Urgente de Médico

1. El médico crea solicitud urgente
2. Aparece en el dashboard de farmacia con prioridad "Urgente"
3. Farmacia la ve destacada
4. Procesa inmediatamente
5. Entrega los items
6. El sistema actualiza stock automáticamente

## Reportes y Análisis

### Ver Historial de Movimientos

1. Ve al detalle de un item
2. Verás todos los movimientos históricos:
    - Entradas
    - Salidas
    - Ajustes
    - Devoluciones

### Estadísticas del Dashboard

El dashboard muestra:

- Total de items activos
- Desglose por tipo
- Solicitudes pendientes y en proceso
- Número de alertas activas

## Solución de Problemas

### No puedo ver el menú de farmacia

- Verifica que tu usuario tenga rol "pharmacy"
- Cierra sesión y vuelve a iniciar
- Limpia la caché del navegador

### Las alertas no aparecen

- Verifica que los items tengan configurados:
    - Stock mínimo
    - Fechas de vencimiento (si aplica)
    - Fechas de esterilización (instrumentos)
- Las alertas se calculan automáticamente

### Error al crear solicitud (médicos)

- Verifica que los items tengan stock disponible
- Asegúrate de seleccionar al menos un item
- Revisa que las cantidades sean válidas

### Stock negativo

- No es posible tener stock negativo
- El sistema ajusta automáticamente a 0 si se intenta
- Revisa los movimientos para identificar discrepancias

## Mantenimiento

### Limpieza de Datos

```bash
# Ver items discontinuados
# En Tinker:
App\Models\PharmacyItem::where('status', 'discontinued')->get();

# Ver movimientos antiguos (más de 1 año)
App\Models\PharmacyStockMovement::where('created_at', '<', now()->subYear())->count();
```

### Backup de Datos

Se recomienda hacer backup regular de:

- Tabla `pharmacy_items`
- Tabla `pharmacy_requests`
- Tabla `pharmacy_stock_movements`

### Optimización

Para mejor rendimiento:

- Mantén activos solo los items en uso
- Marca como "discontinuado" los items obsoletos
- Archiva solicitudes antiguas si es necesario

## Próximas Funcionalidades (Roadmap)

- [ ] Exportar inventario a Excel
- [ ] Generar reportes PDF
- [ ] Códigos de barras para items
- [ ] Notificaciones por email
- [ ] Integración con proveedores
- [ ] Sistema de órdenes de compra
- [ ] Estadísticas avanzadas
- [ ] Gráficos de consumo

## Soporte

Para problemas o preguntas:

1. Consulta la documentación completa en `docs/FARMACIA.md`
2. Revisa los logs en `storage/logs/`
3. Contacta al administrador del sistema

---

**Versión**: 1.0.0  
**Fecha**: Marzo 2026
