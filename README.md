# 🧹 Home Clean Calculator

Aplicación web sencilla para **llevar el control de lo que cuesta la persona que limpia tu casa**: marcas en un calendario los días que ha venido, la app calcula lo que se le debe, y registras los pagos (aunque no sean exactos, por falta de cambio o redondeos) para saber siempre **cuánto queda pendiente**.

Pensada para usarse **en local, en tu propio ordenador**: sin registro, sin login, sin servicios en la nube. Solo PHP + MySQL/MariaDB, HTML, CSS y JavaScript (sin frameworks).

## ✨ Qué hace

- 📅 **Calendario mensual**: toca un día para añadirlo al pedido del mes; tócalo otra vez para quitarlo.
- ⚡ **Cálculo automático**: cada día usa por defecto la tarifa y las horas de la configuración, y las puedes editar en cada línea. Los totales se actualizan y se guardan al momento.
- 🇪🇸 **Festivos de España**: se descargan solos (nacionales + tu comunidad autónoma) y se pueden añadir a mano los locales.
- 💶 **Pagos reales**: registra cuánto has pagado de verdad y qué días cubre. Si pagas 170 € por 168 €, la diferencia se arrastra al siguiente pago.
- 🔒 **Cierre de mes**: al terminar el mes cierras el pedido y queda bloqueado con su total.
- 📊 **Historial y resúmenes**: vista anual con gráfico, y resumen imprimible (o PDF) de cada mes.
- 🗑️ **Borrar todos los datos** desde Configuración, con doble confirmación.

## 📦 Qué necesitas

| Necesitas | Detalle |
|---|---|
| **PHP 8.1 o superior** | con la extensión `pdo_mysql` (viene activada en XAMPP y MAMP) |
| **MySQL o MariaDB** | cualquier versión reciente |
| Un navegador | Chrome, Firefox, Safari, Edge… |

La forma más fácil de tenerlo todo junto es instalar **XAMPP** (gratis), que incluye PHP, MySQL y un servidor web.

---

## 🚀 Instalación fácil (con XAMPP)

### 1. Instala XAMPP
Descárgalo de <https://www.apachefriends.org> (Windows, macOS o Linux) e instálalo con las opciones por defecto. Elige una versión con **PHP 8.1 o superior**.

### 2. Arranca los servicios
Abre el **panel de control de XAMPP** y pulsa **Start** en **Apache** y en **MySQL**. Ambos deben quedar en verde.

### 3. Descarga esta aplicación
En GitHub pulsa **Code → Download ZIP** y descomprime el archivo (o clona el repositorio con `git clone`).

### 4. Copia la carpeta a XAMPP
Copia la carpeta de la aplicación dentro de la carpeta `htdocs` de XAMPP y llámala **`homeCleanCalculator`**:

| Sistema | Carpeta `htdocs` |
|---|---|
| Windows | `C:\xampp\htdocs\` |
| macOS | `/Applications/XAMPP/xamppfiles/htdocs/` |
| Linux | `/opt/lampp/htdocs/` |

### 5. Ábrela
Entra en tu navegador a:

**<http://localhost/homeCleanCalculator/>**

¡Ya está! **La base de datos y las tablas se crean solas** la primera vez que abres la página. No tienes que importar nada.

Lo primero que conviene hacer: ir a **Configuración**, poner la **tarifa por hora**, las **horas por visita** y tu **comunidad autónoma** (para los festivos).

---

## 🧪 Probar con datos de ejemplo

Si quieres ver la app con datos antes de meter los tuyos, hay un fichero con datos ficticios (tres meses, con pagos redondeados y días pendientes): [`demo/demo_data.sql`](demo/demo_data.sql).

**Con phpMyAdmin (sin comandos):**
1. Abre la aplicación al menos una vez (así se crea la base de datos).
2. Entra en <http://localhost/phpmyadmin>.
3. En el menú de la izquierda elige la base **`home_clean_calculator`**.
4. Pestaña **Importar** → **Seleccionar archivo** → elige `demo/demo_data.sql` → **Importar**.

**Con la terminal:**
```bash
mysql -u root home_clean_calculator < demo/demo_data.sql
```

### Base de pruebas integrada
Sin importar nada a mano: en **Configuración → Base de datos** pulsa **Cambiar a la base de PRUEBAS**. La app crea sola una segunda base (`home_clean_calculator_demo`) con los datos de ejemplo. Mientras estés en pruebas verás un aviso naranja arriba en todas las pantallas. Puedes volver a la base real cuando quieras, y **Restaurar datos de ejemplo** deja la de pruebas como nueva. El cambio se guarda por navegador, y tu base real no se toca.

> Si importas el fichero de ejemplo a mano, hazlo **con la base vacía**. Cuando quieras empezar con tus datos reales: **Configuración → Borrar todos los datos**.

---

## 🔧 Si tu MySQL no es el de XAMPP por defecto

Por defecto la app se conecta con usuario `root`, sin contraseña, en `127.0.0.1:3306`. Si tu instalación es distinta (por ejemplo **MAMP**, que usa contraseña `root` y puerto `8889`):

1. Copia `config.sample.php` y llámalo `config.php`.
2. Edita los valores de conexión (host, puerto, usuario, contraseña y nombre de la base de datos).

`config.php` no se sube a git (está en `.gitignore`) y el fichero `.htaccess` incluido impide descargarlo desde el navegador, así que tus contraseñas no se publican por error.

### Alternativa sin XAMPP (usuarios técnicos)
Con PHP y un MySQL/MariaDB ya instalados, desde la carpeta del proyecto:

```bash
php -S localhost:8000
```
y abre <http://localhost:8000>. Ajusta `config.php` si hace falta. El usuario de MySQL debe poder crear bases de datos (solo la primera vez).

---

## 🧭 Cómo se usa

1. **Calendario**: navega al mes que quieras y toca los días trabajados. En la tabla de la derecha puedes cambiar horas y tarifa de cada día.
2. **Registrar pago**: pulsa el botón, marca los días que cubre, escribe lo que has pagado realmente y guarda. También puedes marcar la casilla de un día concreto.
3. **Saldo acumulado**: debajo de las cifras del mes verás cuánto queda por pagar en total, o cuánto has pagado de más.
4. **Cerrar pedido del mes**: bloquea el mes con su total. Puedes reabrirlo si te equivocas, y puedes seguir registrando pagos aunque esté cerrado.
5. **Historial**: resumen del año y detalle de cada mes (con botón para imprimir o guardar en PDF).

### Los festivos
Se descargan automáticamente del servicio público y gratuito [Nager.Date](https://date.nager.at) la primera vez que consultas un año. Es lo **único** que usa Internet; sin conexión, la app funciona igual, solo que sin esa lista. Los festivos de tu municipio no vienen incluidos: añádelos a mano en *Configuración → Festivos*.

## 💾 Copias de seguridad

Tus datos están en la base de datos `home_clean_calculator`. Para guardarlos:
- **phpMyAdmin**: selecciona la base → pestaña **Exportar** → **Exportar**.
- **Terminal**: `mysqldump -u root home_clean_calculator > copia.sql`

Para restaurar, importa ese fichero de la misma forma que el de ejemplo.

## ⚠️ Importante: uso local

La aplicación **no tiene login**, por diseño. Úsala solo en tu ordenador o en tu red doméstica. **No la publiques en un servidor accesible desde Internet** sin añadir antes autenticación.

Si quieres asegurarte de que solo se abre desde tu propio ordenador, en el fichero `.htaccess` hay un bloque `Require local` comentado: quítale los `#` y Apache rechazará cualquier otro equipo. Si algún día la usas fuera de tu ordenador, crea además un usuario de MySQL propio con acceso solo a esta base de datos, en vez de `root`, y ponlo en `config.php`.

## 🛠️ Problemas frecuentes

| Problema | Solución |
|---|---|
| Página en blanco o error de conexión | Comprueba que **Apache y MySQL** están en verde en XAMPP. |
| `Access denied for user…` | Tu MySQL tiene contraseña: crea `config.php` (ver arriba). |
| `could not find driver` | Activa la extensión `pdo_mysql` en tu `php.ini`. |
| Puerto 80 ocupado (Apache no arranca) | Cierra Skype/IIS u otro programa que lo use, o cambia el puerto de Apache en XAMPP. |
| Los festivos no aparecen | Necesitas Internet una vez; añádelos a mano en Configuración si no. |

## 📁 Estructura

```
index.php        Calendario y pedido del mes
history.php      Historial anual
summary.php      Resumen imprimible de un mes
settings.php     Configuración, festivos y borrado de datos
api.php          API JSON que usa la interfaz
db.php           Conexión y creación/migración automática de la base de datos
schema.sql       Esquema de la base de datos
config.sample.php  Plantilla de conexión (cópiala como config.php)
demo/demo_data.sql Datos de ejemplo
LICENCE.md       Licencia MIT
assets/          CSS y JavaScript
```

## 📄 Licencia

Distribuido bajo la licencia **MIT**: puedes usarlo, copiarlo y modificarlo libremente. Consulta el fichero [LICENCE.md](LICENCE.md).
