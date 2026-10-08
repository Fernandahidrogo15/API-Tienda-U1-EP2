# API Tienda - U1 EP2

API REST desarrollada para la gestión de productos de una tienda.

## Tecnologías

- PHP
- MySQL
- Apache
- JSON
- Postman

## URL base

http://40.233.31.164/api/v1/productos

## Endpoints

| Método | Endpoint | Descripción |
|---|---|---|
| GET | /api/v1/productos | Obtiene todos los productos |
| GET | /api/v1/productos/{id} | Obtiene un producto por ID |
| POST | /api/v1/productos | Crea un nuevo producto |
| PUT | /api/v1/productos/{id} | Actualiza un producto |
| DELETE | /api/v1/productos/{id} | Elimina un producto |

## Códigos HTTP utilizados

- 200 OK: solicitud procesada correctamente.
- 201 Created: producto creado correctamente.
- 400 Bad Request: datos enviados incorrectamente.
- 404 Not Found: producto no encontrado.
- 405 Method Not Allowed: método HTTP no permitido.
- 500 Internal Server Error: error interno o de conexión.

## Estructura del proyecto

```text
API-Tienda-U1-EP2/
├── api/
│   └── v1/
│       ├── .htaccess
│       ├── conexion.example.php
│       └── productos.php
└── README.md
