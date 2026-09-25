# Documentación de la PWA — NegocioManager

## 1. Archivos nuevos creados

| Archivo                                  | Finalidad                                                                                                                                                                                            |
| ---------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `public/manifest.json`                   | Define la información de la PWA: nombre, descripción, iconos, colores, URL de inicio y modo de visualización. Permite que el navegador reconozca el sistema como una aplicación instalable.          |
| `public/sw.js`                           | Implementa el **Service Worker**, encargado de controlar las estrategias de caché, determinadas solicitudes, la actualización de versiones y los recursos utilizados durante la pérdida de conexión. |
| `public/js/pwa.js`                       | Registra el Service Worker, detecta actualizaciones y gestiona el proceso de instalación de la PWA mediante el navegador o el botón de instalación.                                                  |
| `public/offline.html`                    | Página que se muestra cuando el sistema no puede conectarse con el servidor. Incluye un mensaje personalizado de desconexión y una opción para reintentar la conexión.                               |
| `public/icons/icon-72x72.png`            | Icono PWA de 72 × 72 píxeles.                                                                                                                                                                        |
| `public/icons/icon-96x96.png`            | Icono PWA de 96 × 96 píxeles.                                                                                                                                                                        |
| `public/icons/icon-128x128.png`          | Icono PWA de 128 × 128 píxeles.                                                                                                                                                                      |
| `public/icons/icon-144x144.png`          | Icono PWA de 144 × 144 píxeles.                                                                                                                                                                      |
| `public/icons/icon-152x152.png`          | Icono PWA de 152 × 152 píxeles.                                                                                                                                                                      |
| `public/icons/icon-192x192.png`          | Icono principal de 192 × 192 píxeles utilizado para la instalación y reconocimiento de la PWA.                                                                                                       |
| `public/icons/icon-384x384.png`          | Icono PWA de 384 × 384 píxeles.                                                                                                                                                                      |
| `public/icons/icon-512x512.png`          | Icono principal de 512 × 512 píxeles utilizado para la instalación y diferentes tamaños de presentación.                                                                                             |
| `public/icons/icon-maskable-192x192.png` | Icono adaptable (*maskable*) de 192 × 192 píxeles. Permite que el sistema operativo adapte la forma del icono.                                                                                       |
| `public/icons/icon-maskable-512x512.png` | Icono adaptable (*maskable*) de 512 × 512 píxeles.                                                                                                                                                   |
| `public/icons/apple-touch-icon.png`      | Icono utilizado para la integración con dispositivos Apple/iOS cuando la aplicación se agrega a la pantalla de inicio.                                                                               |
| `public/icons/icon.svg`                  | Archivo vectorial utilizado como fuente gráfica del icono de la aplicación.                                                                                                                          |
| `public/icons/icon-maskable.svg`         | Archivo vectorial utilizado como fuente del icono adaptable (*maskable*).                                                                                                                            |
| `public/favicon.ico`                     | Favicon del sistema utilizado en la pestaña del navegador y determinados accesos.                                                                                                                    |

### Nota sobre los iconos

Los iconos no contienen la lógica de la PWA. Su finalidad es proporcionar la identidad visual de la aplicación en diferentes dispositivos, resoluciones y plataformas.

---

# 2. Archivos existentes modificados

| Archivo                                     | Cambios y finalidad                                                                                                                                                                          |
| ------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `resources/views/layouts/app.blade.php`     | Se agregaron las referencias al Manifest, el color de tema, metadatos para dispositivos y la inclusión del archivo `pwa.js`. Permite integrar la PWA en las páginas principales del sistema. |
| `resources/views/layouts/auth.blade.php`    | Se incorporaron los elementos necesarios de la PWA en las pantallas de autenticación, manteniendo la integración con el login existente de Laravel.                                          |
| `resources/views/dashboard/index.blade.php` | Se agregó el botón dinámico **“Instalar Aplicación”**, que aparece cuando el navegador indica que la aplicación puede instalarse.                                                            |

### Resumen de los cambios

Los archivos existentes fueron modificados para:

* Conectar las vistas Blade con `manifest.json`.
* Registrar e integrar el Service Worker mediante JavaScript.
* Configurar los metadatos necesarios para la PWA.
* Permitir la instalación desde la interfaz cuando el navegador lo permite.
* Mantener intactos el sistema Laravel, la autenticación y la lógica existente.

---

# 3. Requisitos y características de una PWA

| Requisito o característica                    | Finalidad / explicación                                                                                                                            | ¿NegocioManager lo cumple?                                                                                                   |
| --------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| **1. Web App Manifest**                       | Archivo que define el nombre, descripción, iconos, colores, URL inicial y comportamiento de instalación de la aplicación.                          | **Sí.** Se creó `public/manifest.json` y Chrome lo reconoció correctamente.                                                  |
| **2. Aplicación instalable**                  | El navegador debe reconocer que la aplicación cumple las condiciones necesarias para ofrecer la instalación.                                       | **Sí.** Chrome mostró la opción “Instalar NegocioManager - Gestor de Proyectos” y la aplicación fue instalada correctamente. |
| **3. Iconos de la aplicación**                | Permiten identificar la PWA en el escritorio, menú de aplicaciones y otros espacios del dispositivo.                                               | **Sí.** Se crearon iconos en diferentes resoluciones, incluidos iconos *maskable*.                                           |
| **4. Modo de visualización `standalone`**     | Permite abrir la aplicación en una ventana independiente, sin la interfaz habitual de navegación del navegador.                                    | **Sí.** La aplicación se abrió como una ventana independiente.                                                               |
| **5. Service Worker**                         | Script que permite controlar determinadas solicitudes, administrar caché y gestionar recursos cuando no existe conexión.                           | **Sí.** Se creó `public/sw.js` y se comprobó que apareciera activado y en ejecución.                                         |
| **6. Registro del Service Worker**            | El navegador debe registrar correctamente el Service Worker para que pueda controlar el alcance configurado de la aplicación.                      | **Sí.** El Service Worker fue registrado y Chrome mostró el cliente de la aplicación.                                        |
| **7. Estrategia de caché**                    | Define qué recursos se almacenan y cómo se obtienen para evitar información desactualizada o comportamientos incorrectos.                          | **Sí.** Se configuraron estrategias diferenciadas para páginas, recursos estáticos y solicitudes dinámicas.                  |
| **8. Network First para páginas dinámicas**   | Intenta consultar primero el servidor para obtener información actualizada y evita depender exclusivamente de una versión antigua en caché.        | **Sí.** Las páginas HTML utilizan una estrategia Network First.                                                              |
| **9. Protección de operaciones de escritura** | Las operaciones como POST, PUT, PATCH y DELETE deben llegar al backend para que Laravel procese los cambios en la base de datos.                   | **Sí.** Las operaciones de escritura se configuraron como Network Only y no se guardan como caché estática.                  |
| **10. Protección de datos privados**          | Evita almacenar indiscriminadamente respuestas privadas, datos autenticados o información sensible en la caché.                                    | **Sí.** Las respuestas privadas y dinámicas no se tratan como recursos estáticos de caché.                                   |
| **11. Página de respaldo offline**            | Permite mostrar una pantalla personalizada cuando el servidor no está disponible, en lugar de mostrar un error genérico del navegador.             | **Sí.** Se creó `offline.html` y se comprobó su funcionamiento mediante el modo Offline de DevTools.                         |
| **12. Reintento de conexión**                 | Permite al usuario intentar volver al sistema cuando la conexión se recupera.                                                                      | **Sí.** La pantalla offline incluye el botón “Reintentar conexión”.                                                          |
| **13. Actualización del Service Worker**      | Permite incorporar nuevas versiones de recursos y evitar que se utilicen archivos antiguos indefinidamente.                                        | **Sí.** Se configuró un sistema de versiones y limpieza de cachés anteriores.                                                |
| **14. Versionado de caché**                   | Identifica las versiones de los recursos almacenados, por ejemplo `v1.0.0` y `v1.0.1`.                                                             | **Sí.** Se definió `CACHE_VERSION = 'v1.0.0'`.                                                                               |
| **15. Eliminación de cachés antiguas**        | Ayuda a evitar que versiones anteriores de recursos permanezcan almacenadas después de una actualización.                                          | **Sí.** La limpieza se realiza durante la activación del Service Worker.                                                     |
| **16. Compatibilidad con autenticación**      | La PWA debe conservar el funcionamiento del login y las sesiones del sistema Laravel.                                                              | **Sí.** En las pruebas, la PWA abrió correctamente el dashboard utilizando la sesión existente.                              |
| **17. Compatibilidad con la base de datos**   | La PWA debe seguir utilizando el backend y la base de datos existentes, sin crear una base paralela innecesaria.                                   | **Sí.** Se probaron creaciones, ediciones y cambios de estado desde la aplicación.                                           |
| **18. Funcionamiento responsive**             | La aplicación debe adaptarse a diferentes tamaños de pantalla como PC, tablet y teléfono.                                                          | **Sí.** El proyecto ya contaba con la adaptación responsive realizada previamente.                                           |
| **19. Contexto seguro**                       | Las funciones de Service Worker requieren un contexto seguro. HTTPS es necesario en producción, mientras que localhost se permite para desarrollo. | **Sí en desarrollo.** Se probó utilizando `127.0.0.1`. Para producción se deberá utilizar HTTPS.                             |
| **20. Pruebas mediante DevTools**             | Permiten verificar Manifest, Service Worker, almacenamiento y comportamiento sin conexión.                                                         | **Sí.** Se revisaron el Manifest, Service Worker, almacenamiento y modo Offline.                                             |

---

# 4. Protocolos y tecnologías involucradas

| Tecnología o mecanismo             | Finalidad                                                                                                    |
| ---------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| **HTTPS**                          | Protege la comunicación entre navegador y servidor y permite utilizar Service Workers en producción.         |
| **HTTP/HTTPS**                     | Protocolo utilizado para solicitar páginas, recursos y enviar información al servidor Laravel.               |
| **Service Worker API**             | Permite registrar y ejecutar el Service Worker en el navegador.                                              |
| **Cache API**                      | Permite almacenar y recuperar determinados recursos controlados por el Service Worker.                       |
| **Web App Manifest**               | Define los datos y configuración de instalación de la aplicación web.                                        |
| **Fetch API / solicitudes de red** | Permite gestionar solicitudes y aplicar estrategias de red o caché.                                          |
| **JavaScript**                     | Se utiliza para registrar el Service Worker, controlar la instalación y gestionar comportamientos de la PWA. |
| **Laravel**                        | Mantiene la lógica del negocio, las rutas, la autenticación y el acceso a la base de datos.                  |
| **Blade**                          | Permite integrar el Manifest, los metadatos y los scripts PWA en las vistas del sistema.                     |

---


# 5. Características implementadas.

* Instalación de la aplicación desde el navegador.
* Web App Manifest.
* Iconos en diferentes resoluciones.
* Modo `standalone`.
* Service Worker.
* Versionado de caché.
* Estrategias de red y caché.
* Página personalizada offline.
* Botón de instalación.
* Integración con las vistas Laravel.
* Conservación de la lógica y la base de datos existentes.
* Pruebas de instalación y pérdida de conexión.
* Compatibilidad con el sistema responsive existente.

# 6. Características que no implementadas.

* Base de datos completamente offline.
* Sincronización automática de tareas creadas sin conexión.
* Sistema de conflictos entre datos locales y datos del servidor.
* Aplicación nativa Android o Windows.
* Archivo `.exe`.
* Sustitución de Laravel por una aplicación local independiente.

