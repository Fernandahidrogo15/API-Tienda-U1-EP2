
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

/* GET: consultar todas las categorías o una por ID */
if ($metodo === 'GET') {

    if (isset($_GET['id'])) {
        if (!$id || $id < 1) {
            responder(400, ["error" => "El ID debe ser un entero positivo"]);
        }

        $stmt = $conn->prepare(
            "SELECT id_categoria, nombre, descripcion
             FROM categoria WHERE id_categoria = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $categoria = $stmt->get_result()->fetch_assoc();

        if (!$categoria) {
            responder(404, ["error" => "Categoría no encontrada"]);
        }

        responder(200, ["data" => $categoria]);

    } else {
        $resultado = $conn->query(
            "SELECT id_categoria, nombre, descripcion
             FROM categoria ORDER BY id_categoria"
        );

        $categorias = [];

        while ($fila = $resultado->fetch_assoc()) {
            $categorias[] = $fila;
        }

        responder(200, ["data" => $categorias]);
    }
}

/* POST: crear una categoría */
if ($metodo === 'POST') {

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {
        responder(400, ["error" => "El cuerpo debe contener un JSON válido"]);
    }

    $nombre = trim($datos['nombre'] ?? '');
    $descripcion = trim($datos['descripcion'] ?? '');

    if ($nombre === '') {
        responder(400, ["error" => "El nombre es obligatorio"]);
    }

    if (strlen($nombre) > 50 || strlen($descripcion) > 150) {
        responder(400, ["error" => "Se excedió la longitud permitida de los campos"]);
    }

    $stmt = $conn->prepare(
        "INSERT INTO categoria (nombre, descripcion) VALUES (?, ?)"
    );
    $stmt->bind_param("ss", $nombre, $descripcion);

    if (!$stmt->execute()) {
        if ($conn->errno === 1062) {
            responder(409, ["error" => "Ya existe una categoría con ese nombre"]);
        }

        responder(500, ["error" => "No se pudo crear la categoría"]);
    }

    responder(201, [
        "message" => "Categoría creada correctamente",
        "data" => [
            "id_categoria" => $conn->insert_id,
            "nombre" => $nombre,
            "descripcion" => $descripcion
        ]
    ]);
}

/* PUT: actualizar una categoría */
if ($metodo === 'PUT') {

    if (!$id || $id < 1) {
        responder(400, ["error" => "Debes indicar un ID válido"]);
    }

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {
        responder(400, ["error" => "El cuerpo debe contener un JSON válido"]);
    }

    $nombre = trim($datos['nombre'] ?? '');
    $descripcion = trim($datos['descripcion'] ?? '');

    if ($nombre === '') {
        responder(400, ["error" => "El nombre es obligatorio"]);
    }

    if (strlen($nombre) > 50 || strlen($descripcion) > 150) {
        responder(400, ["error" => "Se excedió la longitud permitida de los campos"]);
    }

    $stmt = $conn->prepare(
        "UPDATE categoria
         SET nombre = ?, descripcion = ?
         WHERE id_categoria = ?"
    );
    $stmt->bind_param("ssi", $nombre, $descripcion, $id);

    if (!$stmt->execute()) {
        if ($conn->errno === 1062) {
            responder(409, ["error" => "Ya existe una categoría con ese nombre"]);
        }

        responder(500, ["error" => "No se pudo actualizar la categoría"]);
    }

    $stmt = $conn->prepare(
        "SELECT id_categoria FROM categoria WHERE id_categoria = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if (!$stmt->get_result()->fetch_assoc()) {
        responder(404, ["error" => "Categoría no encontrada"]);
    }

    responder(200, [
        "message" => "Categoría actualizada correctamente",
        "data" => [
            "id_categoria" => $id,
            "nombre" => $nombre,
            "descripcion" => $descripcion
        ]
    ]);
}

/* DELETE: eliminar una categoría */
if ($metodo === 'DELETE') {

    if (!$id || $id < 1) {
        responder(400, ["error" => "Debes indicar un ID válido"]);
    }

    $stmt = $conn->prepare(
        "SELECT id_categoria, nombre
         FROM categoria WHERE id_categoria = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $categoria = $stmt->get_result()->fetch_assoc();

    if (!$categoria) {
        responder(404, ["error" => "Categoría no encontrada"]);
    }

    $stmt = $conn->prepare(
        "DELETE FROM categoria WHERE id_categoria = ?"
    );
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        if ($conn->errno === 1451) {
            responder(409, [
                "error" => "No se puede eliminar la categoría porque tiene productos relacionados"
            ]);
        }

        responder(500, ["error" => "No se pudo eliminar la categoría"]);
    }

    responder(200, [
        "message" => "Categoría eliminada correctamente",
        "data" => [
            "id_categoria" => $id,
            "nombre" => $categoria['nombre']
        ]
    ]);
}

header('Allow: GET, POST, PUT, DELETE');
responder(405, ["error" => "Método HTTP no permitido"]);

?>
