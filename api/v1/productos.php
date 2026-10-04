<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'conexion.php';

$metodo = $_SERVER['REQUEST_METHOD'];


/*
|--------------------------------------------------------------------------
| GET - Consultar productos
|--------------------------------------------------------------------------
*/

if ($metodo === 'GET') {

    if (isset($_GET['id'])) {

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id <= 0) {

            http_response_code(400);

            echo json_encode([
                "error" => "El ID del producto no es válido"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $sql = "SELECT
                    id_producto,
                    nombre,
                    descripcion,
                    precio,
                    stock,
                    id_categoria,
                    id_proveedor
                FROM producto
                WHERE id_producto = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            http_response_code(500);

            echo json_encode([
                "error" => "Error al preparar la consulta"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {

            http_response_code(404);

            echo json_encode([
                "error" => "Producto no encontrado"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $producto = $resultado->fetch_assoc();

        http_response_code(200);

        echo json_encode([
            "data" => $producto
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $sql = "SELECT
                id_producto,
                nombre,
                descripcion,
                precio,
                stock,
                id_categoria,
                id_proveedor
            FROM producto
            ORDER BY id_producto";

    $resultado = $conn->query($sql);

    if (!$resultado) {

        http_response_code(500);

        echo json_encode([
            "error" => "Error al consultar los productos"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $productos = [];

    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }

    http_response_code(200);

    echo json_encode([
        "data" => $productos
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| POST - Crear producto
|--------------------------------------------------------------------------
*/

if ($metodo === 'POST') {

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {

        http_response_code(400);

        echo json_encode([
            "error" => "El cuerpo de la solicitud debe contener JSON válido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $campos = [
        "nombre",
        "descripcion",
        "precio",
        "stock",
        "id_categoria",
        "id_proveedor"
    ];

    foreach ($campos as $campo) {

        if (!isset($datos[$campo]) || $datos[$campo] === '') {

            http_response_code(400);

            echo json_encode([
                "error" => "Falta el campo: " . $campo
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }
    }

    $nombre = trim($datos["nombre"]);
    $descripcion = trim($datos["descripcion"]);
    $precio = $datos["precio"];
    $stock = $datos["stock"];
    $id_categoria = $datos["id_categoria"];
    $id_proveedor = $datos["id_proveedor"];

    if (!is_numeric($precio) || $precio < 0) {

        http_response_code(400);

        echo json_encode([
            "error" => "El precio no es válido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!filter_var($stock, FILTER_VALIDATE_INT) && $stock != 0) {

        http_response_code(400);

        echo json_encode([
            "error" => "El stock no es válido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!filter_var($id_categoria, FILTER_VALIDATE_INT) || $id_categoria <= 0) {

        http_response_code(400);

        echo json_encode([
            "error" => "La categoría no es válida"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!filter_var($id_proveedor, FILTER_VALIDATE_INT) || $id_proveedor <= 0) {

        http_response_code(400);

        echo json_encode([
            "error" => "El proveedor no es válido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $sql = "INSERT INTO producto
            (nombre, descripcion, precio, stock, id_categoria, id_proveedor)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        http_response_code(500);

        echo json_encode([
            "error" => "Error al preparar el registro"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $stmt->bind_param(
        "ssdiii",
        $nombre,
        $descripcion,
        $precio,
        $stock,
        $id_categoria,
        $id_proveedor
    );

    if (!$stmt->execute()) {

        if ($stmt->errno == 1452) {

            http_response_code(400);

            echo json_encode([
                "error" => "La categoría o el proveedor no existen"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        http_response_code(500);

        echo json_encode([
            "error" => "No se pudo crear el producto"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $nuevo_id = $stmt->insert_id;

    http_response_code(201);

    echo json_encode([
        "message" => "Producto creado correctamente",
        "data" => [
            "id_producto" => $nuevo_id,
            "nombre" => $nombre,
            "descripcion" => $descripcion,
            "precio" => $precio,
            "stock" => $stock,
            "id_categoria" => $id_categoria,
            "id_proveedor" => $id_proveedor
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| PUT - Actualizar producto
|--------------------------------------------------------------------------
*/

if ($metodo === 'PUT') {

    if (!isset($_GET['id'])) {

        http_response_code(400);

        echo json_encode([
            "error" => "Debes proporcionar el ID del producto"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {

        http_response_code(400);

        echo json_encode([
            "error" => "El ID del producto no es válido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {

        http_response_code(400);

        echo json_encode([
            "error" => "El cuerpo de la solicitud debe contener JSON válido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $campos = [
        "nombre",
        "descripcion",
        "precio",
        "stock",
        "id_categoria",
        "id_proveedor"
    ];

    foreach ($campos as $campo) {

        if (!isset($datos[$campo]) || $datos[$campo] === '') {

            http_response_code(400);

            echo json_encode([
                "error" => "Falta el campo: " . $campo
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }
    }

    $nombre = trim($datos["nombre"]);
    $descripcion = trim($datos["descripcion"]);
    $precio = $datos["precio"];
    $stock = $datos["stock"];
    $id_categoria = $datos["id_categoria"];
    $id_proveedor = $datos["id_proveedor"];

    if (!is_numeric($precio) || $precio < 0) {

        http_response_code(400);

        echo json_encode([
            "error" => "El precio no es válido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!filter_var($stock, FILTER_VALIDATE_INT) && $stock != 0) {

        http_response_code(400);

        echo json_encode([
            "error" => "El stock no es válido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!filter_var($id_categoria, FILTER_VALIDATE_INT) || $id_categoria <= 0) {

        http_response_code(400);

        echo json_encode([
            "error" => "La categoría no es válida"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (!filter_var($id_proveedor, FILTER_VALIDATE_INT) || $id_proveedor <= 0) {

        http_response_code(400);

        echo json_encode([
            "error" => "El proveedor no es válido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $sql = "UPDATE producto
            SET nombre = ?,
                descripcion = ?,
                precio = ?,
                stock = ?,
                id_categoria = ?,
                id_proveedor = ?
            WHERE id_producto = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        http_response_code(500);

        echo json_encode([
            "error" => "Error al preparar la actualización"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $stmt->bind_param(
        "ssdiiii",
        $nombre,
        $descripcion,
        $precio,
        $stock,
        $id_categoria,
        $id_proveedor,
        $id
    );

    if (!$stmt->execute()) {

        if ($stmt->errno == 1452) {

            http_response_code(400);

            echo json_encode([
                "error" => "La categoría o el proveedor no existen"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        http_response_code(500);

        echo json_encode([
            "error" => "No se pudo actualizar el producto"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if ($stmt->affected_rows === 0) {

        $consulta = $conn->prepare(
            "SELECT id_producto FROM producto WHERE id_producto = ?"
        );

        $consulta->bind_param("i", $id);
        $consulta->execute();

        $resultado = $consulta->get_result();

        if ($resultado->num_rows === 0) {

            http_response_code(404);

            echo json_encode([
                "error" => "Producto no encontrado"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }
    }

    http_response_code(200);

    echo json_encode([
        "message" => "Producto actualizado correctamente",
        "data" => [
            "id_producto" => $id,
            "nombre" => $nombre,
            "descripcion" => $descripcion,
            "precio" => $precio,
            "stock" => $stock,
            "id_categoria" => $id_categoria,
            "id_proveedor" => $id_proveedor
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| DELETE - Eliminar producto
|--------------------------------------------------------------------------
*/

if ($metodo === 'DELETE') {

    if (!isset($_GET['id'])) {

        http_response_code(400);

        echo json_encode([
            "error" => "Debes proporcionar el ID del producto"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {

        http_response_code(400);

        echo json_encode([
            "error" => "El ID del producto no es válido"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $consulta = $conn->prepare(
        "SELECT id_producto, nombre FROM producto WHERE id_producto = ?"
    );

    if (!$consulta) {

        http_response_code(500);

        echo json_encode([
            "error" => "Error al preparar la consulta"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $consulta->bind_param("i", $id);
    $consulta->execute();

    $resultado = $consulta->get_result();

    if ($resultado->num_rows === 0) {

        http_response_code(404);

        echo json_encode([
            "error" => "Producto no encontrado"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $producto = $resultado->fetch_assoc();

    $stmt = $conn->prepare(
        "DELETE FROM producto WHERE id_producto = ?"
    );

    if (!$stmt) {

        http_response_code(500);

        echo json_encode([
            "error" => "Error al preparar la eliminación"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {

        http_response_code(500);

        echo json_encode([
            "error" => "No se pudo eliminar el producto"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    http_response_code(200);

    echo json_encode([
        "message" => "Producto eliminado correctamente",
        "data" => [
            "id_producto" => $id,
            "nombre" => $producto["nombre"]
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Método no permitido
|--------------------------------------------------------------------------
*/

http_response_code(405);

echo json_encode([
    "error" => "Método no permitido"
], JSON_UNESCAPED_UNICODE);

?>
