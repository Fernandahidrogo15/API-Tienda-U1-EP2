
<?php

require_once 'conexion.php';

function responder($codigo, $respuesta)
{
    http_response_code($codigo);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id'])
    ? filter_var($_GET['id'], FILTER_VALIDATE_INT)
    : null;

/* GET: consultar todos los clientes o uno por ID */
if ($metodo === 'GET') {

    if (isset($_GET['id'])) {
        if (!$id || $id < 1) {
            responder(400, ["error" => "El ID debe ser un entero positivo"]);
        }

        $stmt = $conn->prepare(
            "SELECT id_cliente, nombre, apellido, telefono, email
             FROM cliente WHERE id_cliente = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $cliente = $stmt->get_result()->fetch_assoc();

        if (!$cliente) {
            responder(404, ["error" => "Cliente no encontrado"]);
        }

        responder(200, ["data" => $cliente]);

    } else {
        $resultado = $conn->query(
            "SELECT id_cliente, nombre, apellido, telefono, email
             FROM cliente ORDER BY id_cliente"
        );

        $clientes = [];

        while ($fila = $resultado->fetch_assoc()) {
            $clientes[] = $fila;
        }

        responder(200, ["data" => $clientes]);
    }
}

/* POST: crear un cliente */
if ($metodo === 'POST') {

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {
        responder(400, ["error" => "El cuerpo debe contener un JSON válido"]);
    }

    $nombre = trim($datos['nombre'] ?? '');
    $apellido = trim($datos['apellido'] ?? '');
    $telefono = trim($datos['telefono'] ?? '');
    $email = trim($datos['email'] ?? '');

    if ($nombre === '' || $apellido === '' ||
        $telefono === '' || $email === '') {
        responder(400, [
            "error" => "nombre, apellido, telefono y email son obligatorios"
        ]);
    }

    if (strlen($nombre) > 50 || strlen($apellido) > 50 ||
        strlen($telefono) > 15 || strlen($email) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responder(400, [
            "error" => "Revisa la longitud de los campos y el formato del email"
        ]);
    }

    $stmt = $conn->prepare(
        "INSERT INTO cliente (nombre, apellido, telefono, email)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $nombre, $apellido, $telefono, $email);

    if (!$stmt->execute()) {
        if ($conn->errno === 1062) {
            responder(409, ["error" => "Ese email ya está registrado"]);
        }

        responder(500, ["error" => "No se pudo crear el cliente"]);
    }

    responder(201, [
        "message" => "Cliente creado correctamente",
        "data" => [
            "id_cliente" => $conn->insert_id,
            "nombre" => $nombre,
            "apellido" => $apellido,
            "telefono" => $telefono,
            "email" => $email
        ]
    ]);
}

/* PUT: actualizar un cliente */
if ($metodo === 'PUT') {

    if (!$id || $id < 1) {
        responder(400, ["error" => "Debes indicar un ID válido"]);
    }

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {
        responder(400, ["error" => "El cuerpo debe contener un JSON válido"]);
    }

    $nombre = trim($datos['nombre'] ?? '');
    $apellido = trim($datos['apellido'] ?? '');
    $telefono = trim($datos['telefono'] ?? '');
    $email = trim($datos['email'] ?? '');

    if ($nombre === '' || $apellido === '' ||
        $telefono === '' || $email === '') {
        responder(400, [
            "error" => "nombre, apellido, telefono y email son obligatorios"
        ]);
    }

    if (strlen($nombre) > 50 || strlen($apellido) > 50 ||
        strlen($telefono) > 15 || strlen($email) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responder(400, [
            "error" => "Revisa la longitud de los campos y el formato del email"
        ]);
    }

    $stmt = $conn->prepare(
        "UPDATE cliente
         SET nombre = ?, apellido = ?, telefono = ?, email = ?
         WHERE id_cliente = ?"
    );
    $stmt->bind_param("ssssi", $nombre, $apellido, $telefono, $email, $id);

    if (!$stmt->execute()) {
        if ($conn->errno === 1062) {
            responder(409, ["error" => "Ese email ya está registrado"]);
        }

        responder(500, ["error" => "No se pudo actualizar el cliente"]);
    }

    $stmt = $conn->prepare(
        "SELECT id_cliente FROM cliente WHERE id_cliente = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if (!$stmt->get_result()->fetch_assoc()) {
        responder(404, ["error" => "Cliente no encontrado"]);
    }

    responder(200, [
        "message" => "Cliente actualizado correctamente",
        "data" => [
            "id_cliente" => $id,
            "nombre" => $nombre,
            "apellido" => $apellido,
            "telefono" => $telefono,
            "email" => $email
        ]
    ]);
}

/* DELETE: eliminar un cliente */
if ($metodo === 'DELETE') {

    if (!$id || $id < 1) {
        responder(400, ["error" => "Debes indicar un ID válido"]);
    }

    $stmt = $conn->prepare(
        "SELECT id_cliente, nombre, apellido
         FROM cliente WHERE id_cliente = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $cliente = $stmt->get_result()->fetch_assoc();

    if (!$cliente) {
        responder(404, ["error" => "Cliente no encontrado"]);
    }

    $stmt = $conn->prepare(
        "DELETE FROM cliente WHERE id_cliente = ?"
    );
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        if ($conn->errno === 1451) {
            responder(409, [
                "error" => "No se puede eliminar el cliente porque tiene registros relacionados"
            ]);
        }

        responder(500, ["error" => "No se pudo eliminar el cliente"]);
    }

    responder(200, [
        "message" => "Cliente eliminado correctamente",
        "data" => [
            "id_cliente" => $id,
            "nombre" => $cliente['nombre'],
            "apellido" => $cliente['apellido']
        ]
    ]);
}

header('Allow: GET, POST, PUT, DELETE');
responder(405, ["error" => "Método HTTP no permitido"]);

?>
