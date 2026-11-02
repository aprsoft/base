# GUÍA BÁSICA DE GIT Y GITHUB

## Proyecto: ~/Proyectos/base

<a id="indice"></a>
## ÍNDICE

1. [Consultar el repositorio](#1-consultar-el-repositorio)

2. [Consultar ramas en GitHub](#2-consultar-ramas-en-github)

3. [Cambiar y crear ramas](#3-cambiar-y-crear-ramas)

4. [Descargar y actualizar desde GitHub](#4-descargar-y-actualizar-desde-github)

5. [Guardar cambios y subirlos a GitHub](#5-guardar-cambios-y-subirlos-a-github)

6. [Consultar y recuperar commits](#6-consultar-y-recuperar-commits)

7. [Descartar cambios locales](#7-descartar-cambios-locales)

8. [Eliminar ramas](#8-eliminar-ramas)

9. [Comparar ramas](#9-comparar-ramas)

10. [Flujo recomendado antes de trabajar](#10-flujo-recomendado-antes-de-trabajar)

11. [Flujo recomendado para subir cambios](#11-flujo-recomendado-para-subir-cambios)

12. [Resumen de comandos](#12-resumen-de-comandos)

13. [Reglas importantes](#reglas-importantes)

---

Todos los comandos utilizan la ruta del proyecto, por lo que pueden ejecutarse desde cualquier directorio de Linux.

---

## 1. CONSULTAR EL REPOSITORIO

### Ver la rama actual

```bash
git -C ~/Proyectos/base branch --show-current
```

### Ver el estado del repositorio

```bash
git -C ~/Proyectos/base status --short --branch
```

### Ver los cambios locales sin preparar

```bash
git -C ~/Proyectos/base diff
```

### Ver los cambios preparados para commit

```bash
git -C ~/Proyectos/base diff --cached
```

### Ver los últimos 20 commits

```bash
git -C ~/Proyectos/base log --oneline --decorate -n 20
```

### Ver el historial gráfico de todas las ramas

```bash
git -C ~/Proyectos/base log --oneline --graph --decorate --all -n 20
```

### Ver la dirección del repositorio GitHub

```bash
git -C ~/Proyectos/base remote -v
```

[⬆ Volver al índice](#indice)

---

## 2. CONSULTAR RAMAS EN GITHUB

### Consultar ramas nuevas y limpiar referencias remotas obsoletas

```bash
git -C ~/Proyectos/base fetch --prune origin
```

### Mostrar todas las ramas locales y remotas

```bash
git -C ~/Proyectos/base branch -a
```

### Mostrar solamente las ramas locales

```bash
git -C ~/Proyectos/base branch
```

### Mostrar solamente las ramas remotas de GitHub

```bash
git -C ~/Proyectos/base branch -r
```

### Mostrar ramas locales y sus ramas remotas asociadas

```bash
git -C ~/Proyectos/base branch -vv
```

**NOTA:**

- fetch descarga información de GitHub.

- fetch no modifica los archivos de trabajo.

- --prune elimina referencias remotas obsoletas.

- --prune NO elimina ramas locales.

[⬆ Volver al índice](#indice)

---

## 3. CAMBIAR Y CREAR RAMAS

### Cambiar a la rama main

```bash
git -C ~/Proyectos/base switch main
```

### Cambiar a una rama local existente

```bash
git -C ~/Proyectos/base switch feature/pdf-documents
```

### Crear una rama local desde una rama existente en GitHub

```bash
git -C ~/Proyectos/base fetch origin
git -C ~/Proyectos/base switch --track origin/feature/pdf-documents
```

### Crear una nueva rama local y cambiarse a ella

```bash
git -C ~/Proyectos/base switch -c nombre-rama
```

### Regresar a la rama anterior

```bash
git -C ~/Proyectos/base switch -
```

**NOTA:**

Antes de cambiar de rama, revisar si existen cambios locales sin guardar mediante git status.

[⬆ Volver al índice](#indice)

---

## 4. DESCARGAR Y ACTUALIZAR DESDE GITHUB

### Actualizar la rama main

Primero cambiar a main:

```bash
git -C ~/Proyectos/base switch main
```

Después descargar e integrar los cambios:

```bash
git -C ~/Proyectos/base pull --ff-only origin main
```

### Actualizar feature/pdf-documents

Primero cambiar a la rama:

```bash
git -C ~/Proyectos/base switch feature/pdf-documents
```

Después descargar e integrar los cambios:

```bash
git -C ~/Proyectos/base pull --ff-only origin feature/pdf-documents
```

### Actualizar la rama actual

Requiere que tenga configurada una rama remota de seguimiento:

```bash
git -C ~/Proyectos/base pull --ff-only
```

**NOTA:**

- pull descarga e integra cambios desde GitHub.

- --ff-only evita crear commits de fusión automáticamente.

- Si existen cambios incompatibles, Git detiene la operación.

- pull actualiza la rama local actualmente seleccionada.

[⬆ Volver al índice](#indice)

---

## 5. GUARDAR CAMBIOS Y SUBIRLOS A GITHUB

### Verificar la rama antes de guardar

```bash
git -C ~/Proyectos/base branch --show-current
```

### Ver archivos modificados

```bash
git -C ~/Proyectos/base status
```

### Preparar todos los cambios

```bash
git -C ~/Proyectos/base add -A
```

### Crear un commit

```bash
git -C ~/Proyectos/base commit -m "Descripción de los cambios"
```

### Subir los commits de la rama actual

```bash
git -C ~/Proyectos/base push
```

### Subir una rama nueva y configurar su seguimiento

```bash
git -C ~/Proyectos/base push -u origin HEAD
```

**NOTA:**

- add prepara archivos para el commit.

- commit registra los cambios localmente.

- push envía los commits a GitHub.

[⬆ Volver al índice](#indice)

---

## 6. CONSULTAR Y RECUPERAR COMMITS

### Ver los últimos 20 commits

```bash
git -C ~/Proyectos/base log --oneline --decorate -n 20
```

### Ver el contenido de un commit

```bash
git -C ~/Proyectos/base show c0e092c
```

### Consultar un commit anterior temporalmente

```bash
git -C ~/Proyectos/base switch --detach c0e092c
```

### Regresar a main

```bash
git -C ~/Proyectos/base switch main
```

### Restaurar todos los archivos versionados desde un commit

```bash
git -C ~/Proyectos/base restore --source=c0e092c --staged --worktree -- :/
```

**ADVERTENCIA:**

- Este comando puede sobrescribir cambios locales.

- Restaura los archivos versionados al estado del commit indicado.

- No cambia la rama actual.

- No elimina commits posteriores del historial.

- No elimina los archivos sin seguimiento.

### Revertir un commit sin borrar el historial

```bash
git -C ~/Proyectos/base revert CODIGO_COMMIT
```

**NOTA:**

revert crea un nuevo commit que deshace los cambios del commit indicado, cuando es posible.

[⬆ Volver al índice](#indice)

---

## 7. DESCARTAR CAMBIOS LOCALES

**ADVERTENCIA:**

Los siguientes comandos pueden eliminar modificaciones no guardadas en commits.

### Descartar modificaciones de un archivo versionado

```bash
git -C ~/Proyectos/base restore -- ruta/archivo.php
```

### Descartar modificaciones no preparadas de todos los archivos versionados

```bash
git -C ~/Proyectos/base restore .
```

### Quitar un archivo del área de preparación

```bash
git -C ~/Proyectos/base restore --staged ruta/archivo.php
```

### Guardar cambios temporalmente

```bash
git -C ~/Proyectos/base stash push -u -m "Trabajo temporal"
```

### Ver cambios guardados temporalmente

```bash
git -C ~/Proyectos/base stash list
```

### Recuperar los últimos cambios guardados

```bash
git -C ~/Proyectos/base stash pop
```

**NOTA:**

stash permite guardar temporalmente cambios para cambiar de rama o realizar otras operaciones.

stash pop puede generar conflictos si los cambios no se pueden aplicar limpiamente.

[⬆ Volver al índice](#indice)

---

## 8. ELIMINAR RAMAS

### Eliminar una rama local ya fusionada

```bash
git -C ~/Proyectos/base branch -d nombre-rama
```

### Forzar eliminación de una rama local

```bash
git -C ~/Proyectos/base branch -D nombre-rama
```

**ADVERTENCIA:**

-D puede eliminar una rama aunque contenga commits no fusionados.

### Eliminar una rama en GitHub

```bash
git -C ~/Proyectos/base push origin --delete nombre-rama
```

### Limpiar referencias a ramas remotas eliminadas

```bash
git -C ~/Proyectos/base fetch --prune origin
```

**NOTA:**

- No se puede eliminar la rama actualmente seleccionada.

- Eliminar una rama local no elimina su rama remota.

- Eliminar una rama remota no elimina automáticamente su rama local.

[⬆ Volver al índice](#indice)

---

## 9. COMPARAR RAMAS

### Ver diferencias entre main y una rama

```bash
git -C ~/Proyectos/base diff main..feature/pdf-documents
```

### Ver commits presentes en feature/pdf-documents que no están en main

```bash
git -C ~/Proyectos/base log --oneline main..feature/pdf-documents
```

### Ver commits presentes en main que no están en feature/pdf-documents

```bash
git -C ~/Proyectos/base log --oneline feature/pdf-documents..main
```

### Ver diferencias entre la rama actual y su rama remota

```bash
git -C ~/Proyectos/base fetch origin
git -C ~/Proyectos/base diff @{upstream}
```

**NOTA:**

La última comparación requiere una rama remota de seguimiento configurada.

[⬆ Volver al índice](#indice)

---

## 10. FLUJO RECOMENDADO ANTES DE TRABAJAR

### PASO 1: Verificar la rama actual

```bash
git -C ~/Proyectos/base branch --show-current
```

### PASO 2: Consultar cambios y ramas nuevas en GitHub

```bash
git -C ~/Proyectos/base fetch --prune origin
```

### PASO 3: Mostrar las ramas disponibles

```bash
git -C ~/Proyectos/base branch -a
```

### PASO 4: Revisar el estado local

```bash
git -C ~/Proyectos/base status --short --branch
```

### PASO 5: Actualizar la rama actual

```bash
git -C ~/Proyectos/base pull --ff-only
```

**IMPORTANTE:**

El paso 5 requiere una rama remota de seguimiento configurada.

Antes de ejecutarlo, comprobar que no existan cambios locales incompatibles.

[⬆ Volver al índice](#indice)

---

## 11. FLUJO RECOMENDADO PARA SUBIR CAMBIOS

### PASO 1: Verificar rama actual

```bash
git -C ~/Proyectos/base branch --show-current
```

### PASO 2: Verificar cambios

```bash
git -C ~/Proyectos/base status
```

### PASO 3: Preparar archivos

```bash
git -C ~/Proyectos/base add -A
```

### PASO 4: Crear commit

```bash
git -C ~/Proyectos/base commit -m "Descripción de los cambios"
```

### PASO 5: Subir a GitHub

```bash
git -C ~/Proyectos/base push
```

### PASO 6: Verificar estado final

```bash
git -C ~/Proyectos/base status --short --branch
```

[⬆ Volver al índice](#indice)

---

## 12. RESUMEN DE COMANDOS

```text
git status       = Consultar estado del repositorio.
git branch       = Consultar ramas locales.
git branch -a    = Consultar ramas locales y remotas.
git branch -r    = Consultar ramas remotas.
git switch       = Cambiar de rama.
git switch -c    = Crear una rama local.
git fetch        = Descargar información remota.
git fetch --prune = Actualizar y limpiar referencias remotas obsoletas.
git pull         = Descargar e integrar cambios.
git add          = Preparar archivos.
git commit       = Registrar cambios localmente.
git push         = Subir commits a GitHub.
git log          = Consultar historial.
git show         = Consultar contenido de un commit.
git diff         = Comparar modificaciones.
git restore      = Restaurar archivos.
git revert       = Deshacer un commit con otro commit.
git stash        = Guardar cambios temporalmente.
git branch -d    = Eliminar una rama local fusionada.
git branch -D    = Forzar eliminación de una rama local.
```

[⬆ Volver al índice](#indice)

---

## REGLAS IMPORTANTES

1. Verificar siempre el repositorio y la rama actual.

2. Consultar el estado del repositorio antes de modificar archivos.

3. Ejecutar fetch para conocer los cambios y ramas remotas.

4. No ejecutar pull sobre una rama diferente de la que se pretende actualizar.

5. Utilizar --ff-only para evitar fusiones automáticas no deseadas.

6. No ejecutar restore, reset o branch -D sin comprender sus consecuencias.

7. Crear commits antes de realizar operaciones que puedan sobrescribir trabajo.

8. Verificar la rama antes de ejecutar push.

9. Evitar trabajar directamente sobre main cuando se desarrollan funcionalidades nuevas.

10. Comprobar siempre el resultado de los comandos antes de continuar.

[⬆ Volver al índice](#indice)