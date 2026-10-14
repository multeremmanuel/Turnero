Turnero - Sistema de Turnos

===========================



Sistema de gestión de turnos desarrollado en PHP y MariaDB, con integración OPCIONAL a EasyAppointments. Permite asignar turnos normales, preferentes y especiales según la cita del usuario o sus necesidades (adultos mayores, embarazadas, personas con discapacidad, etc.).





AVISO IMPORTANTE SOBRE LA BASE DE DATOS Y CONEXION.PHP

\------------------------------------------------------



Este repositorio NO incluye:



&#x20; - El archivo conexion.php (configuración de conexión a la base de datos)

&#x20; - El archivo .sql con la estructura y datos iniciales de la base de datos

&#x20; - El archivo video.mp4 (video de la pantalla de turnos)



Estos archivos se proporcionan únicamente a quienes aporten al proyecto

(económicamente, con código, con reportes de bugs, con traducciones,

o con cualquier otra contribución significativa).



Si deseas obtener el paquete completo para poner el sistema en producción,

contacta al autor:



&#x20; GitHub:  @multeremmanuel  (https://github.com/multeremmanuel)

&#x20; Email:   hosting.emmanuelbd@gmail.com





REQUISITOS

\----------



&#x20; - PHP 8.0 o superior

&#x20; - MariaDB 10.4 o superior (o MySQL equivalente)

&#x20; - Apache o Nginx con soporte PHP

&#x20; - Extensión PDO MySQL habilitada

&#x20; - Extensión mbstring habilitada (recomendado)





REQUISITO DE VIDEO PARA LA PANTALLA DE TURNOS

\---------------------------------------------



El sistema muestra un video en la pantalla pública de turnos (donde se

visualizan los turnos llamados).



Debes colocar un archivo de video llamado exactamente "video.mp4" en la

raíz del proyecto.



&#x20; Nombre del archivo:  video.mp4

&#x20; Ubicación:           Raíz del proyecto (junto a index.php)

&#x20; Formato:             MP4 (H.264 recomendado)

&#x20; Resolución:          1920x1080 o superior (recomendado)

&#x20; Duración:            Libre (se reproduce en loop)



Si el archivo no está presente, la pantalla de turnos no mostrará el video.



Nota: el archivo video.mp4 NO está incluido en el repositorio (por su

tamaño y por ser contenido personalizable). Debes añadirlo tú mismo.





ROLES DEL SISTEMA

\-----------------



&#x20; Despachador:    Otorga los turnos según la situación del usuario

&#x20; Operador:       Recibe y atiende los turnos asignados (cajero, asesor, etc.)

&#x20; Administrador:  Administra el sistema, genera reportes, gestiona usuarios,

&#x20;                 cajas y módulos





FLUJO DE TURNOS

\---------------



&#x20; 1. El usuario llega y proporciona su ID de cita.

&#x20; 2. El despachador ingresa el ID en Turnero:

&#x20;      - Dentro del rango de cita  ->  turno preferente

&#x20;      - Fuera del rango           ->  turno normal

&#x20; 3. Usuarios con necesidades especiales  ->  turno especial

&#x20; 4. Usuarios sin cita y sin necesidades especiales  ->  turno normal



Todos los turnos se registran y se pueden generar reportes por usuario,

caja y fechas.





INSTALACIÓN

\-----------



1\. Clonar el repositorio:



&#x20;    git clone https://github.com/multeremmanuel/Turnero.git

&#x20;    cd Turnero



2\. Obtener los archivos privados:



&#x20;  Solicita al autor el paquete con:

&#x20;    - conexion.php

&#x20;    - turnero.sql (estructura + datos iniciales)



&#x20;  Contacto: hosting.emmanuelbd@gmail.com



3\. Colocar los archivos:



&#x20;  Coloca conexion.php en la raíz del proyecto y edítalo con los datos

&#x20;  de tu base de datos:



&#x20;    <?php

&#x20;    $host    = 'localhost';

&#x20;    $db      = 'turnero';

&#x20;    $user    = 'tu\_usuario';

&#x20;    $pass    = 'tu\_password';

&#x20;    $charset = 'utf8mb4';

&#x20;    // ...



4\. Importar la base de datos:



&#x20;    mysql -u tu\_usuario -p turnero < turnero.sql



5\. Colocar el video:



&#x20;  Copia tu video.mp4 en la raíz del proyecto.



6\. Configurar el servidor web:



&#x20;  Apunta el VirtualHost de Apache/Nginx a la carpeta del proyecto.



7\. Acceder:



&#x20;    http://tu-servidor/turnero/





INTEGRACIÓN CON EASYAPPOINTMENTS (OPCIONAL)

\-------------------------------------------



Turnero puede conectarse a una base de datos de EasyAppointments para

consultar citas y asignar turnos prioritarios automáticamente.



Esta integración es 100% opcional. El sistema funciona sin ella, asignando

todos los turnos como normales o según necesidades especiales.



Si deseas activarla:



&#x20; 1. Configura los datos de conexión a EasyAppointments en conexion.php

&#x20;    (sección "Conexión 2").

&#x20; 2. La base de datos de EasyAppointments debe ser accesible desde el

&#x20;    servidor de Turnero.

&#x20; 3. El usuario de la BD de EasyAppointments debe tener permisos de SOLO

&#x20;    LECTURA sobre la tabla ea\_appointments.



Nota legal: Turnero es un software independiente que se comunica

opcionalmente con EasyAppointments a través de una conexión a base de

datos. No incluye, modifica ni distribuye código de EasyAppointments.





ESTRUCTURA DEL PROYECTO

\-----------------------



&#x20; Turnero/

&#x20;   admin/                  Panel de administración

&#x20;   get\_\*.php               Endpoints AJAX (turnos)

&#x20;   index.php               Pantalla pública de turnos

&#x20;   login.php               Login de usuarios

&#x20;   procesar\_cita.php       Integración con EasyAppointments

&#x20;   registrar\_turno.php     Registro de turnos

&#x20;   turno.php               Pantalla de turno individual

&#x20;   logo.png                Logo del sistema

&#x20;   tono.mp3                Sonido de llamado de turno

&#x20;   .gitignore

&#x20;   LICENSE.TXT

&#x20;   README.md



&#x20; El archivo video.mp4, conexion.php y los .sql NO están en el repositorio

&#x20; (ver sección superior).





CONTRIBUIR

\----------



Este proyecto NO es de código abierto. Sin embargo, aceptamos

contribuciones que serán recompensadas con acceso al paquete completo

(conexión + base de datos).



Formas de contribuir:



&#x20; - Reportar bugs (con detalle y pasos para reproducir)

&#x20; - Sugerir mejoras

&#x20; - Traducir la interfaz

&#x20; - Mejorar la documentación

&#x20; - Apoyo económico al proyecto

&#x20; - Aportar código (pull requests con mejoras)



Las contribuciones aceptadas recibirán:



&#x20; - Acceso al paquete completo (conexion.php + .sql)

&#x20; - Crédito en el README (si lo desean)

&#x20; - Soporte prioritario





LICENCIA

\--------



Este proyecto es software propietario. Todos los derechos reservados.



No se permite:



&#x20; - Redistribuir el código sin autorización

&#x20; - Venderlo como producto propio

&#x20; - Modificarlo y distribuirlo sin permiso



Se permite:



&#x20; - Usarlo en tu organización (previa adquisición)

&#x20; - Modificarlo para uso interno

&#x20; - Solicitar soporte y personalizaciones al autor



Ver archivo LICENSE.TXT para más detalles.





SOPORTE

\-------



Para soporte, personalización, implementación o para obtener el paquete

completo:



&#x20; GitHub:  @multeremmanuel  (https://github.com/multeremmanuel)

&#x20; Email:   hosting.emmanuelbd@gmail.com





AGRADECIMIENTOS

\---------------



&#x20; - EasyAppointments (https://easyappointments.org/) por su sistema de

&#x20;   citas (integración opcional)

&#x20; - TCPDF (https://tcpdf.org/) por la generación de PDFs

&#x20; - Bootstrap (https://getbootstrap.com/) por el framework CSS





Hecho con cariño para gestionar turnos de forma eficiente.

