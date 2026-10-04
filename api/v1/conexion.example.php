<?php

header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$usuario = "USUARIO_MYSQL";
$contrasena = "CONTRASENA_MYSQL";
$base_datos = "tienda_db";

$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

if ($conn->connect_error) {
    http_response_code(500);

    echo json_encode([
        "error" => "Error de conexión a la base de datos"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$conn->set_charset("utf8mb4");
?>
