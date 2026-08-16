# Estación Terrena Hidrochallenge

Aplicación de estación terrena para recibir, almacenar y visualizar la telemetría del cohete Bravo II. El proyecto utiliza Laravel para la interfaz y los servicios web, PostgreSQL para almacenar los datos, y una API de Python para ejecutar la simulación física.

## Requisitos

Antes de iniciar, instala:

- PHP 8.3 o superior, con las extensiones `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `fileinfo` y `curl`.
- [Composer](https://getcomposer.org/) para las dependencias de Laravel.
- [Node.js](https://nodejs.org/) y npm para los recursos del frontend.
- Python 3.10 o superior y `pip` para la API de simulación.
- PostgreSQL.
- Git, si el proyecto se obtendrá desde el repositorio.

Para comprobar las instalaciones:

```bash
php --version
composer --version
node --version
npm --version
python --version
psql --version
```

## Instalación del proyecto

Desde la raíz del proyecto, instala las dependencias de PHP y JavaScript:

```bash
composer install
npm install
```

Crea el archivo de configuración local. En Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

En Linux o macOS:

```bash
cp .env.example .env
```

Genera la clave de Laravel:

```bash
php artisan key:generate
```

## Configuración de PostgreSQL

Crea una base de datos para la estación. Por ejemplo, desde PostgreSQL:

```sql
CREATE DATABASE estacion_terrena;
```

Después, edita `.env` con los datos reales de tu servidor:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=estacion_terrena
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

Con PostgreSQL activo, crea las tablas:

```bash
php artisan migrate
```

## Instalación de la API de Python

La página de simulación necesita una API independiente. Entra a su directorio, crea un entorno virtual e instala las librerías:

```bash
cd python_api
python -m venv .venv
```

Activa el entorno virtual en Windows PowerShell:

```powershell
.\.venv\Scripts\Activate.ps1
```

En Linux o macOS:

```bash
source .venv/bin/activate
```

Instala las dependencias:

```bash
python -m pip install numpy scipy fastapi uvicorn
```

Luego regresa a la raíz del proyecto:

```bash
cd ..
```

Comprueba que `.env` apunte al servicio de simulación:

```env
PY_SIM_API_URL=http://127.0.0.1:8002/simular
```

## Cómo iniciar la estación

La estación necesita tres procesos ejecutándose al mismo tiempo. Abre tres terminales en la raíz del proyecto.

### 1. API de simulación

```bash
cd python_api
python -m uvicorn api:app --host 127.0.0.1 --port 8002 --reload
```

La API estará disponible en `http://127.0.0.1:8002`. Puedes verificarla en `http://127.0.0.1:8002/health`.

### 2. Servidor Laravel

```bash
php artisan serve --host=0.0.0.0 --port=8001
```

La interfaz estará disponible en:

- En la misma computadora: `http://127.0.0.1:8001`
- Desde otro dispositivo de la red: `http://IP_DE_LA_COMPUTADORA:8001`

### 3. Recursos del frontend

Para desarrollo:

```bash
npm run dev
```

Para generar los archivos de producción:

```bash
npm run build
```

## Configuración del receptor de telemetría

Antes de cargar `Arduino/Receptor_telemetria.ino` en el ESP32, cambia estos valores:

```cpp
const char* ssid = "NOMBRE_DEL_WIFI";
const char* password = "CONTRASEÑA_DEL_WIFI";
const char* serverUrl = "http://IP_DE_LA_COMPUTADORA:8001/api/lecturas-multi";
```

La computadora y el ESP32 deben estar conectados a la misma red. No uses `127.0.0.1` en el ESP32, porque esa dirección apuntaría al propio microcontrolador. Si el firewall lo solicita, permite las conexiones entrantes al puerto `8001`.

## Librerías de Arduino

El transmisor `Arduino/Telemetria_Hidrochallenge.ino` requiere instalar desde el gestor de librerías del Arduino IDE:

- Adafruit BME280 Library
- Adafruit Unified Sensor
- TinyGPSPlus
- Servo

Las librerías `Wire`, `SPI` y `SoftwareSerial` normalmente se incluyen con el paquete de la placa Arduino. El receptor ESP32 usa `WiFi` y `HTTPClient`, incluidas con el paquete de placas ESP32.

## Resumen de comandos

Primera instalación:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
cd python_api
python -m venv .venv
python -m pip install numpy scipy fastapi uvicorn
```

Cada vez que se quiera ejecutar la estación:

```bash
# Terminal 1, desde python_api
python -m uvicorn api:app --host 127.0.0.1 --port 8002 --reload

# Terminal 2, desde la raíz
php artisan serve --host=0.0.0.0 --port=8001

# Terminal 3, desde la raíz
npm run dev
```

## Solución de problemas

- Si Laravel no se conecta a PostgreSQL, revisa las variables `DB_*` de `.env` y confirma que PostgreSQL esté activo.
- Si modificaste `.env` y Laravel conserva valores anteriores, ejecuta `php artisan config:clear`.
- Si faltan tablas, ejecuta `php artisan migrate`.
- Si la simulación falla, comprueba que la API responda en `http://127.0.0.1:8002/health`.
- Si no llega telemetría, revisa el Wi‑Fi, la IP y el puerto configurados en el receptor, y confirma que Laravel se inició con `--host=0.0.0.0`.
- Si Vite no carga estilos o scripts, mantén `npm run dev` activo o ejecuta `npm run build`.
