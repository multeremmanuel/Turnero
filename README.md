\# Turnero - Sistema de Turnos



Sistema de gestión de turnos desarrollado en \*\*PHP\*\* y \*\*MariaDB\*\*, con integración \*\*opcional\*\* a \[EasyAppointments](https://easyappointments.org/). Permite asignar turnos normales, preferentes y especiales según la cita del usuario o sus necesidades (adultos mayores, embarazadas, personas con discapacidad, etc.).



!\[PHP](https://img.shields.io/badge/PHP-8.0%2B-blue)

!\[MariaDB](https://img.shields.io/badge/MariaDB-10.4%2B-brown)

!\[License](https://img.shields.io/badge/license-Proprietary-red)



\---



\## ⚠️ Nota importante sobre la base de datos y `conexion.php`



\*\*Este repositorio NO incluye:\*\*



\- El archivo `conexion.php` (configuración de conexión a la base de datos)

\- El archivo `.sql` con la estructura y datos iniciales de la base de datos

\- El archivo `video.mp4` (video de la pantalla de turnos)



\*\*Estos archivos se proporcionan únicamente a quienes aporten al proyecto\*\* (económicamente, con código, con reportes de bugs, con traducciones, o con cualquier otra contribución significativa).



Si deseas obtener el paquete completo para poner el sistema en producción, contacta al autor:



\- \*\*GitHub:\*\* \[@multeremmanuel](https://github.com/multeremmanuel)

\- \*\*Email:\*\* \[hosting.emmanuelbd@gmail.com](mailto:hosting.emmanuelbd@gmail.com)



\---



\## 📋 Requisitos



\- PHP \*\*8.0\*\* o superior

\- MariaDB \*\*10.4\*\* o superior (o MySQL equivalente)

\- Apache o Nginx con soporte PHP

\- Extensión \*\*PDO MySQL\*\* habilitada

\- Extensión \*\*mbstring\*\* habilitada (recomendado)



\---



\## 🎬 Requisito de video para la pantalla de turnos



El sistema muestra un video en la \*\*pantalla pública de turnos\*\* (donde se visualizan los turnos llamados).



\*\*Debes colocar un archivo de video llamado exactamente `video.mp4` en la raíz del proyecto.\*\*



| Característica | Valor |

|---|---|

| Nombre del archivo | `video.mp4` |

| Ubicación | Raíz del proyecto (junto a `index.php`) |

| Formato | MP4 (H.264 recomendado para compatibilidad con navegadores) |

| Resolución recomendada | 1920x1080 o superior |

| Duración | Libre (se reproduce en loop) |



Si el archivo no está presente, la pantalla de turnos no mostrará el video.



> \*\*Nota:\*\* el archivo `video.mp4` \*\*no está incluido\*\* en el repositorio (por su tamaño y por ser contenido personalizable). Debes añadirlo tú mismo.



\---



\## 👥 Roles del sistema



| Rol | Descripción |

|---|---|

| \*\*Despachador\*\* | Otorga los turnos según la situación del usuario |

| \*\*Operador\*\* | Recibe y atiende los turnos asignados (cajero, asesor, etc.) |

| \*\*Administrador\*\* | Administra el sistema, genera reportes, gestiona usuarios, cajas y módulos |



\---



\## 🔄 Flujo de turnos



1\. El usuario llega y proporciona su ID de cita.

2\. El despachador ingresa el ID en Turnero:

&#x20;  - Dentro del rango de cita → \*\*turno preferente\*\*.

&#x20;  - Fuera del rango → \*\*turno normal\*\*.

3\. Usuarios con necesidades especiales → \*\*turno especial\*\*.

4\. Usuarios sin cita y sin necesidades especiales → \*\*turno normal\*\*.



Todos los turnos se registran y se pueden generar reportes por usuario, caja y fechas.



\---



\## 🚀 Instalación



\### 1. Clonar el repositorio



```bash

git clone https://github.com/multeremmanuel/Turnero.git

cd Turnero

```



\### 2. Obtener los archivos privados



Solicita al autor el paquete con:

\- `conexion.php`

\- `turnero.sql` (estructura + datos iniciales)



Contacto: \[hosting.emmanuelbd@gmail.com](mailto:hosting.emmanuelbd@gmail.com)



\### 3. Colocar los archivos



Coloca `conexion.php` en la raíz del proyecto y edítalo con los datos de tu base de datos:



```php

<?php

$host    = 'localhost';

$db      = 'turnero';

$user    = 'tu\_usuario';

$pass    = 'tu\_password';

$charset = 'utf8mb4';

// ...

```



\### 4. Importar la base de datos



```bash

mysql -u tu\_usuario -p turnero < turnero.sql

```



\### 5. Colocar el video



Copia tu `video.mp4` en la raíz del proyecto.



\### 6. Configurar el servidor web



Apunta el VirtualHost de Apache/Nginx a la carpeta del proyecto.



\### 7. Acceder



Abre en el navegador:



```

http://tu-servidor/turnero/

```



\---



\## 🔗 Integración con EasyAppointments (opcional)



Turnero puede conectarse a una base de datos de \*\*EasyAppointments\*\* para consultar citas y asignar turnos prioritarios automáticamente.



\*\*Esta integración es 100% opcional.\*\* El sistema funciona sin ella, asignando todos los turnos como normales o según necesidades especiales.



Si deseas activarla:



1\. Configura los datos de conexión a EasyAppointments en `conexion.php` (sección "Conexión 2").

2\. La base de datos de EasyAppointments debe ser accesible desde el servidor de Turnero.

3\. El usuario de la BD de EasyAppointments debe tener permisos de \*\*solo lectura\*\* sobre la tabla `ea\_appointments`.



> \*\*Nota legal:\*\* Turnero es un software independiente que se comunica opcionalmente con EasyAppointments a través de una conexión a base de datos. No incluye, modifica ni distribuye código de EasyAppointments.



\---



\## 📁 Estructura del proyecto



```

Turnero/

├── admin/                  # Panel de administración

├── get\_\*.php               # Endpoints AJAX (turnos)

├── index.php               # Pantalla pública de turnos

├── login.php               # Login de usuarios

├── procesar\_cita.php       # Integración con EasyAppointments

├── registrar\_turno.php     # Registro de turnos

├── turno.php               # Pantalla de turno individual

├── logo.png                # Logo del sistema

├── tono.mp3                # Sonido de llamado de turno

├── .gitignore

├── LICENSE.TXT

└── README.md

```



> El archivo `video.mp4`, `conexion.php` y los `.sql` \*\*no están en el repositorio\*\* (ver sección superior).



\---



\## 🤝 Contribuir



Este proyecto \*\*no es de código abierto\*\*. Sin embargo, aceptamos contribuciones que serán recompensadas con acceso al paquete completo (conexión + base de datos).



\*\*Formas de contribuir:\*\*



\- 🐛 Reportar bugs (con detalle y pasos para reproducir)

\- 💡 Sugerir mejoras

\- 🌐 Traducir la interfaz

\- 📝 Mejorar la documentación

\- 💰 Apoyo económico al proyecto

\- 🔧 Aportar código (pull requests con mejoras)



Las contribuciones aceptadas recibirán:

\- Acceso al paquete completo (`conexion.php` + `.sql`)

\- Crédito en el README (si lo desean)

\- Soporte prioritario



\---



\## 📄 Licencia



Este proyecto es \*\*software propietario\*\*. Todos los derechos reservados.



\*\*No se permite:\*\*



\- Redistribuir el código sin autorización

\- Venderlo como producto propio

\- Modificarlo y distribuirlo sin permiso



\*\*Se permite:\*\*



\- Usarlo en tu organización (previa adquisición)

\- Modificarlo para uso interno

\- Solicitar soporte y personalizaciones al autor



Ver archivo `LICENSE.TXT` para más detalles.



\---



\## 📞 Soporte



Para soporte, personalización, implementación o para obtener el paquete completo:



\- \*\*GitHub:\*\* \[@multeremmanuel](https://github.com/multeremmanuel)

\- \*\*Email:\*\* \[hosting.emmanuelbd@gmail.com](mailto:hosting.emmanuelbd@gmail.com)



\---



\## 🙏 Agradecimientos



\- \[EasyAppointments](https://easyappointments.org/) por su sistema de citas (integración opcional)

\- \[TCPDF](https://tcpdf.org/) por la generación de PDFs

\- \[Bootstrap](https://getbootstrap.com/) por el framework CSS



\---



\*\*Hecho con ❤️ para gestionar turnos de forma eficiente.\*\*

