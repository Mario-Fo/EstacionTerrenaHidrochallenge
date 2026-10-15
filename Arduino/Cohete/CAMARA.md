# Camara por API

Enviar `POST /api/comandos/camara` sin cuerpo al servidor existente.
La estacion consulta `/api/comandos/pendiente` cada segundo y guarda el
comando hasta recibir la siguiente telemetria del cohete. Entonces lo
transmite por LoRa. El cohete activa GPIO 7 durante 1000 ms y vuelve al
nivel de reposo. Las lecturas de sensores continuan durante el pulso.

Hay que cargar los firmwares actualizados de **Cohete y EstacionTerrena**.
La URL se deriva de `SERVER_URL` en `EstacionTerrena/src/config.h`.
La letra `c` es solo el identificador interno API/LoRa: GPIO 7 produce un
pulso electrico, no una transmision serial.

## Conexion prevista

El boton de la camara se activa al conectarlo a GND. GPIO 7 usa
`OUTPUT_OPEN_DRAIN`: permanece liberado (alta impedancia) en reposo y
se conecta a GND durante un segundo al recibir el comando. No se usa
`INPUT_PULLDOWN`: hace falta simular una pulsacion, no fijar una entrada.

Para conexion directa, el contacto debe tener voltaje y corriente compatibles
con el GPIO del ESP32; verificarlo antes de conectar y compartir GND entre
camara y ESP32. Drenador abierto no protege contra un voltaje externo excesivo.
Si se desconocen esos valores, usar una interfaz aislada adecuada al boton;
su entrada debe ser compatible con esta salida activa en LOW.

`CAMERA_ACTIVE_LEVEL` es `LOW`; `CAMERA_PULSE_MS` define la duracion.
Verificar tambien que el circuito deja el boton liberado durante arranque/reset.

## Verificacion fisica pendiente

1. Con la camara desconectada y un pull-up externo a 3.3 V para medir,
   comprobar que GPIO 7 queda liberado al arrancar.
2. Enviar el POST y medir un solo pulso de aproximadamente un segundo.
3. Comprobar que vuelve al reposo y que la telemetria sigue llegando.
4. Una vez comprobada la compatibilidad electrica o instalada la interfaz
   aislada, conectar el boton y verificar la respuesta real de la camara.

Una camara generica puede alternar encendido/apagado con el mismo boton:
el firmware simula una pulsacion, no puede confirmar si quedo encendida.
Las solicitudes recibidas durante un pulso se ignoran sin prolongarlo.

## Limites

- La API confirma que se encolo el comando, no que la camara se encendio.
- No hay confirmacion de recepcion ni reintento LoRa; un paquete perdido
  requiere una nueva solicitud. No reenviar automaticamente porque otro
  pulso podria apagar la camara.
- La cola del servidor conserva un solo comando y lo consume al consultarlo.
- GPIO 7 controla exclusivamente la camara. GPIO 4 es ahora la senal PWM
  del servo, no una salida de alimentacion ni un control de MOSFET.

## Servo por comando e

Enviar `POST /api/comandos/desplegar` sin cuerpo. La estacion consulta el
comando `e` y lo transmite por LoRa al cohete, que ordena el despliegue en
GPIO 4. El despliegue automatico al detectar apogeo usa la misma funcion.
Tras la primera activacion, las siguientes no vuelven a accionar el servo
ni reinician el estado de vuelo. Al reiniciar el cohete se vuelve a la
posicion inicial.

Conectar la senal del servo a GPIO 4, alimentarlo con una fuente adecuada
y compartir GND con el ESP32. El PWM es de 50 Hz, con pulsos de aproximadamente
1 ms en reposo y 2 ms al desplegar, como en el codigo original. Ajustar
`SERVO_CLOSED_DUTY` y `SERVO_DEPLOYED_DUTY` al servo y al recorrido real.
No se garantiza un angulo concreto sin calibracion.

La telemetria `deployed=true` indica que el firmware ordeno el movimiento;
no hay sensor que confirme que el mecanismo se abrio. Probar en banco con
el mecanismo desacoplado: reposo, comando `e`, repeticion de `e`, comando
`c` independiente y despliegue automatico. Se requiere cargar ambos firmwares.
