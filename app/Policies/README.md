# BASE — propuesta de políticas y seguridad

Repositorio verificado: `aprsoft/base`; rama: `feature/pdf-documents`; HEAD base: `5de85224e9c114c355c4eb127067bdd49f3a5d76`.

## Alcance

El paquete contiene **código fuente completo propuesto** para estos archivos, conservando sus rutas relativas:

- `app/Providers/AppServiceProvider.php`
- `app/Policies/UserPolicy.php`
- `app/Policies/RolePolicy.php` (nuevo)
- `app/Policies/PermissionPolicy.php` (nuevo)
- `app/Policies/ProfilePolicy.php` (nuevo)
- `app/Http/Controllers/Settings/ProfileController.php`
- `app/Http/Controllers/Settings/PasswordController.php`
- `app/Http/Controllers/RoleController.php`
- `app/Http/Controllers/PermissionController.php`
- `app/Services/Permission/UpdatePermissionService.php`
- `app/Services/Permission/DeletePermissionService.php`
- `app/Livewire/User/UserTable.php`
- `resources/views/pages/auth/settings/profile.blade.php`
- `database/seeders/RolePermissionSeeder.php`
- `tests/Feature/AuthorizationPoliciesTest.php` (nuevo)

La implementación mantiene `SuperAdmin` con acceso global a permisos ordinarios a través de `Gate::before` y deja que las políticas evalúen operaciones sobre modelos y perfil. La cuenta ID 1 no se puede gestionar en el módulo Usuarios y solo puede cambiar su contraseña desde Settings. No pueden autodestruirse cuentas SuperAdmin. Los roles SuperAdmin no pueden editarse/eliminarse.

**No incluye todavía los botones Suspender/Eliminar**: el botón Suspender requiere conectar el método Livewire y su modal; `Eliminar` (borrado definitivo) permanece denegado en `UserPolicy` hasta auditar relaciones, claves foráneas, eventos y retención de datos. No se ha ejecutado `forceDelete`.

El paquete también oculta ID 1 del listado de usuarios a todos los roles y condiciona las acciones de edición del listado por Policy. La vista Settings de perfil oculta los formularios que la Policy niega. Sigue pendiente adaptar acciones Livewire de Role y Permission para invocar las Policies de manera consistente y completar las pruebas funcionales y de regresión. No se debe interpretar el paquete como una implementación integral de esas pantallas.

## Permisos nuevos propuestos

El seeder propuesto registra `users.suspend` y `users.restore` sin concederlos automáticamente al rol Admin. Conserva `users.destroy` para futuras eliminaciones definitivas, y mantiene los cuatro permisos existentes del rol Admin. `firstOrCreate` permite volver a ejecutar el seeder sin registros duplicados. Aplicarlo modifica datos de roles/permisos; respaldar y autorizar antes.

## Comprobaciones

Se ha ejecutado `php -l` sobre los archivos PHP del paquete. **No** se han ejecutado pruebas de Laravel, migraciones, seeds, operaciones en base de datos ni comprobación del entorno real de BASE.

## Advertencia de coherencia

Las políticas necesitan integrarse también en las acciones Livewire y la navegación de cada módulo. Los controladores y dos servicios de permisos aquí incluidos sí invocan Gate; los Livewire de roles/permisos inspeccionados siguen utilizando comprobaciones directas con `hasRole('SuperAdmin')`, que mantienen la restricción actual pero no invocan aún las nuevas policies en todos los puntos. Corregir eso antes de considerar completada la adaptación.

## Aplicación

Se recomienda revisar el diff contra el HEAD indicado y aplicar los archivos por etapas en una rama de trabajo con respaldo. Nunca reemplazar el repositorio ni ejecutar el seeder directamente en producción sin verificación y autorización explícita.
