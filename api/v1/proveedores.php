<?php

require_once 'conexion.php';

function responder($codigo, $respuesta)
{
    http_response_code($codigo);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

/* GET: consultar todos los proveedores o uno por ID */
if ($metodo === 'GET') {

    if (isset($_GET['id'])) {

        if (!$id || $id < 1) {
            responder(400, ["error" => "El ID debe ser un entero positivo"]);
        }

        $stmt = $conn->prepare(
            "SELECT id_proveedor, nombre, telefono, email
             FROM proveedor WHERE id_proveedor = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $proveedor = $resultado->fetch_assoc();

        if (!$proveedor) {
            responder(404, ["error" => "Proveedor no encontrado"]);
        }

        responder(200, ["data" => $proveedor]);

    } else {

        $resultado = $conn->query(
            "SELECT id_proveedor, nombre, telefono, email
             FROM proveedor ORDER BY id_proveedor"
        );

        $proveedores = [];

        while ($fila = $resultado->fetch_assoc()) {
            $proveedores[] = $fila;
        }

        responder(200, ["data" => $proveedores]);
    }
}

/* POST: crear proveedor */
if ($metodo === 'POST') {

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {
        responder(400, ["error" => "El cuerpo debe contener un JSON válido"]);
    }

    $nombre = trim($datos['nombre'] ?? '');
    $telefono = trim($datos['telefono'] ?? '');
    $email = trim($datos['email'] ?? '');

    if ($nombre === '' || $telefono === '' || $email === '') {
        responder(400, ["error" => "nombre, telefono y email son obligatorios"]);
    }

    if (strlen($nombre) > 100 || strlen($telefono) > 15 ||
        strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responder(400, ["error" => "Revisa la longitud de los campos y el formato del email"]);
    }

    $stmt = $conn->prepare(
        "INSERT INTO proveedor (nombre, telefono, email)
         VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $nombre, $telefono, $email);

    if (!$stmt->execute()) {
        if ($conn->errno === 1062) {
            responder(400, ["error" => "Ese email ya está registrado"]);
        }

        responder(500, ["error" => "No se pudo crear el proveedor"]);
    }

    responder(201, [
        "message" => "Proveedor creado correctamente",
        "data" => [
            "id_proveedor" => $conn->insert_id,
            "nombre" => $nombre,
            "telefono" => $telefono,
            "email" => $email
        ]
    ]);
}

/* PUT: actualizar proveedor */
if ($metodo === 'PUT') {

    if (!$id || $id < 1) {
        responder(400, ["error" => "Debes indicar un ID válido"]);
    }

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {
        responder(400, ["error" => "El cuerpo debe contener un JSON válido"]);
    }

    $nombre = trim($datos['nombre'] ?? '');
    $telefono = trim($datos['telefono'] ?? '');
    $email = trim($datos['email'] ?? '');

    if ($nombre === '' || $telefono === '' || $email === '') {
        responder(400, ["error" => "nombre, telefono y email son obligatorios"]);
    }

    if (strlen($nombre) > 100 || strlen($telefono) > 15 ||
        strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responder(400, ["error" => "Revisa la longitud de los campos y el formato del email"]);
    }

    $stmt = $conn->prepare(
        "UPDATE proveedor
         SET nombre = ?, telefono = ?, email = ?
         WHERE id_proveedor = ?"
    );
    $stmt->bind_param("sssi", $nombre, $telefono, $email, $id);

    if (!$stmt->execute()) {
        if ($conn->errno === 1062) {
            responder(400, ["error" => "Ese email ya está registrado"]);
        }

        responder(500, ["error" => "No se pudo actualizar el proveedor"]);
    }

    $stmt = $conn->prepare(
        "SELECT id_proveedor FROM proveedor WHERE id_proveedor = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if (!$stmt->get_result()->fetch_assoc()) {
        responder(404, ["error" => "Proveedor no encontrado"]);
    }

    responder(200, [
        "message" => "Proveedor actualizado correctamente",
        "data" => [
            "id_proveedor" => $id,
            "nombre" => $nombre,
            "telefono" => $telefono,
            "email" => $email
        ]
    ]);
}

/* DELETE: eliminar proveedor */
if ($metodo === 'DELETE') {

    if (!$id || $id < 1) {
        responder(400, ["error" => "Debes indicar un ID válido"]);
    }

    $stmt = $conn->prepare(
        "SELECT id_proveedor, nombre
         FROM proveedor WHERE id_proveedor = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $proveedor = $stmt->get_result()->fetch_assoc();

    if (!$proveedor) {
        responder(404, ["error" => "Proveedor no encontrado"]);
    }

    $stmt = $conn->prepare(
        "DELETE FROM proveedor WHERE id_proveedor = ?"
    );
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        if ($conn->errno === 1451) {
            responder(409, [
                "error" => "No se puede eliminar el proveedor porque tiene productos relacionados"
            ]);
        }

        responder(500, ["error" => "No se pudo eliminar el proveedor"]);
    }

    responder(200, [
        "message" => "Proveedor eliminado correctamente",
        "data" => [
            "id_proveedor" => $id,
            "nombre" => $proveedor['nombre']
        ]
    ]);
}

header('Allow: GET, POST, PUT, DELETE');
responder(405, ["error" => "Método HTTP no permitido"]);

?>
