# INSTRUCCIONES DE INSTALACIÓN - MEJORA DE REGISTRO DE GARAJE

## 📋 RESUMEN DE CAMBIOS
Se ha mejorado el sistema de registro de garaje para incluir información detallada del vehículo:
- ✅ Número de placa
- ✅ Tipo de vehículo (Automóvil, Camioneta, Vagoneta, Minibús, Motocicleta, Otro)

---

## 🗄️ PASO 1: ACTUALIZAR BASE DE DATOS

### Opción A: Ejecutar en phpMyAdmin
1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Selecciona la base de datos `hotel_cecil`
3. Ve a la pestaña "SQL"
4. Copia y pega el siguiente código:

```sql
-- Agregar campo para número de placa
ALTER TABLE `registro_garaje` 
ADD COLUMN `placa` VARCHAR(20) DEFAULT NULL COMMENT 'Número de placa del vehículo' AFTER `huesped_nombre`;

-- Agregar campo para tipo de vehículo
ALTER TABLE `registro_garaje` 
ADD COLUMN `tipo_vehiculo` VARCHAR(50) DEFAULT NULL COMMENT 'Tipo de vehículo' AFTER `placa`;
```

5. Haz clic en "Continuar"

### Opción B: Usar el archivo SQL
1. El archivo `actualizar_garaje.sql` ya fue creado en la raíz del proyecto
2. Impórtalo desde phpMyAdmin o ejecútalo con:
```bash
mysql -u root hotel_cecil < actualizar_garaje.sql
```

### Verificar que los cambios se aplicaron:
```sql
DESCRIBE registro_garaje;
```

Deberías ver la estructura con los nuevos campos:
- id
- ocupacion_id
- huesped_nombre
- **placa** ← NUEVO
- **tipo_vehiculo** ← NUEVO
- fecha
- costo
- observaciones
- created_at

---

## 📝 ARCHIVOS MODIFICADOS

### 1. views/huespedes/nuevo.php
- ✅ Formulario de garaje mejorado con campos visuales
- ✅ Toggle dinámico para mostrar/ocultar detalles del vehículo
- ✅ Validación de campos requeridos (placa y tipo)
- ✅ Función JavaScript `toggleGarajeDetalles()`
- ✅ Envío de datos de placa y tipo al backend

### 2. models/Garaje.php
- ✅ Método `registrar()` actualizado para guardar placa y tipo_vehiculo
- ✅ Compatibilidad con registros antiguos (campos opcionales)

### 3. actualizar_garaje.sql (NUEVO)
- ✅ Script SQL para actualizar la base de datos

---

## 🎨 CARACTERÍSTICAS VISUALES

### Antes:
```
[✓] Usa garaje
    Se registrará para control y pago posterior
```

### Ahora:
```
[✓] 🚗 Usa garaje
    Registrar vehículo del huésped (Bs. 10.00/día)
    
    ┌─────────────────────────────────────────┐
    │ Número de Placa *    │ Tipo de Vehículo * │
    │ Ej: 1234 ABC         │ [Selector]          │
    └─────────────────────────────────────────┘
```

### Tipos de vehículo disponibles:
- Automóvil
- Camioneta
- Vagoneta
- Minibús
- Motocicleta
- Otro

---

## ✅ VALIDACIONES

1. **Campos obligatorios**: Si se marca "Usa garaje", placa y tipo son requeridos
2. **Formato de placa**: Se convierte automáticamente a mayúsculas
3. **Campos opcionales**: Si no se marca el checkbox, no se registra nada

---

## 🧪 PRUEBAS

### Para probar los cambios:
1. Ejecuta el script SQL de actualización
2. Ve a: http://localhost/Sistem Hotel Cecil/views/huespedes/nuevo.php
3. Llena el formulario de nuevo huésped
4. Marca el checkbox "Usa garaje"
5. Verifica que aparezcan los campos de Placa y Tipo de Vehículo
6. Completa el registro
7. Verifica en la tabla `registro_garaje` que se guardaron placa y tipo_vehiculo

### Consulta para verificar:
```sql
SELECT * FROM registro_garaje ORDER BY id DESC LIMIT 5;
```

---

## 📌 NOTAS IMPORTANTES

- ✅ Los registros antiguos de garaje seguirán funcionando (campos NULL en placa y tipo)
- ✅ El sistema es retrocompatible
- ✅ Los campos son opcionales en la base de datos pero obligatorios en el formulario si se marca el checkbox
- ✅ La placa se convierte automáticamente a MAYÚSCULAS para estandarización

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Error: "Unknown column 'placa'"
- **Causa**: No se ejecutó el script SQL de actualización
- **Solución**: Ejecuta el archivo `actualizar_garaje.sql` en phpMyAdmin

### Error: "Field required" al enviar formulario
- **Causa**: Marcaste "Usa garaje" pero no llenaste placa o tipo
- **Solución**: Completa ambos campos o desmarca el checkbox

### Los campos no aparecen al marcar el checkbox
- **Causa**: Error de JavaScript
- **Solución**: Abre la consola del navegador (F12) y verifica errores

---

## 🎯 PRÓXIMOS PASOS (OPCIONAL)

Si quieres mejorar aún más el sistema:
1. Agregar reporte de vehículos en garaje
2. Mostrar placa y tipo en la vista de garajes
3. Búsqueda de vehículos por placa
4. Historial de uso de garaje por vehículo

---

**Desarrollado para Sistema Hotel Cecil**
Fecha: 9 de Febrero, 2026
