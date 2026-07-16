# Arquitectura de software

## Arquitectura monolítica

Estructura clasica de software

**lo malo:** 

- escalabilidad costosa

- dificil incorporar tecnologias nuevas

## Arquitectura de microservicios

Divide la aplicacion en unidades mas pequeñas

- Los componentes son individuales

- La escalabilidad es efeciva  

- Se pueden usar multiples tecnologias  

**lo malo**:

- complejo de desarrollar al inicio

> nos quedamos con la arquitectura monolítica para el MVP

# Autenticación

## Por Cookies

El server valida las credenciales enviadas desde el usuario y crea una sesion, le asigna una `ID de sesion` y se la manda al cliente, a partir de entonces el cliente accede a funciones del servidor usando este ID guardado en las cookies del navegador

- es vulnerable a SCRF (*Cross-Site Request Forgery*) o *Falsificación de peticion por un sitio cruzado*: el atacante engaña al navegador de la victima para hacer acciones no deseadas en un sitio web con la sesion iniciada

- El **SERVIDOR** guarda las sesiones y tiene que buscarlas en su BD, consume recursos

## Por Tokens

El server crea un token firmado criptograficamente para el cliente (JWT *Json Web Token*), este se guarda en el lado del **CLIENTE**, por lo que es mas escalable, y el server solo tiene que verificar que el token del cliente sea valido 

# Endpoint y Webhook

El endpoint es una direccion URL al que se le hacen peticiones manualmente, puede ser ../pacientes al que si le hacemos una peticion GET, este nos devuelve una lista de todos los pacientes

Un webhook no es mas que un endpoint que se dedica unicamente a escuchar. Un segundo servicio lo usa, en nuestro caso Wpp actualiza el estado de cada mensaje mediante un webhook
