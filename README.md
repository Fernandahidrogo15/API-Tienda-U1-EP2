# API Tienda - U1 EP2

API REST para gestionar productos, proveedores, categorías y clientes mediante PHP, MySQL y respuestas JSON.

## Tecnologías
- PHP
- MySQL
- Apache
- JSON
- Postman

## URL base
http://40.233.31.164/api/v1

## Endpoints

Cada módulo cuenta con cinco operaciones: consultar todos los registros, consultar por ID, crear, actualizar y eliminar.

### Productos
- GET /api/v1/productos
- GET /api/v1/productos/{id}
- POST /api/v1/productos
- PUT /api/v1/productos/{id}
- DELETE /api/v1/productos/{id}

### Proveedores
- GET /api/v1/proveedores
- GET /api/v1/proveedores/{id}
- POST /api/v1/proveedores
- PUT /api/v1/proveedores/{id}
- DELETE /api/v1/proveedores/{id}

### Categorías
- GET /api/v1/categorias
- GET /api/v1/categorias/{id}
- POST /api/v1/categorias
- PUT /api/v1/categorias/{id}
- DELETE /api/v1/categorias/{id}

### Clientes
- GET /api/v1/clientes
- GET /api/v1/clientes/{id}
- POST /api/v1/clientes
- PUT /api/v1/clientes/{id}
- DELETE /api/v1/clientes/{id}

## Códigos HTTP
- 200 OK: solicitud procesada correctamente.
- 201 Created: registro creado correctamente.
- 400 Bad Request: datos incorrectos.
- 404 Not Found: registro no encontrado.
- 405 Method Not Allowed: método no permitido.
- 409 Conflict: conflicto con datos existentes o registros relacionados.
- 500 Internal Server Error: error interno o de conexión.

## Pruebas con Postman
Se probaron las operaciones GET, POST, PUT y DELETE de los cuatro módulos. La colección exportada se encuentra en `API_Tienda_U1_EP2.postman_collection.json`.

## Estructura del proyecto
```text
API-Tienda-U1-EP2/
├── API_Tienda_U1_EP2.postman_collection.json
├── api/
│   └── v1/
│       ├── .htaccess
│       ├── conexion.example.php
│       ├── productos.php
│       ├── proveedores.php
│       ├── categorias.php
│       └── clientes.php
└── README.md
```

## Seguridad
`conexion.example.php` es una plantilla. Las credenciales reales de conexión a la base de datos no deben publicarse en GitHub.
