<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Allow, Access-Control-Allow-Origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
header("Allow: GET, POST, PUT, DELETE, OPTIONS, HEAD");
require_once 'database.php';
require_once 'jwt.php';
if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    exit();
}

$header = apache_request_headers();
$jwt = trim($header['Authorization']);

switch (JWT::verify($jwt, CONFIG::SECRET_JWT)) {
    case 1:
        header("HTTP/1.1 401 Unauthorized");
        echo "El token no es válido";
        exit();
        break;
    case 2:
        header("HTTP/1.1 408 Request Timeout");
        echo "La sesión caduco";
        exit();
        break;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (
        isset($_POST['id']) &&
        isset($_POST['nombre']) &&
        isset($_POST['precioCompra']) &&
        isset($_POST['precioVenta']) &&
        isset($_FILES['imagen'])
    ) {
        $productos = new DataBase('productos');
        $datos = array(
            'id' => $_POST['id'],
            'nombre' => $_POST['nombre'],
            'precioCompra' => $_POST['precioCompra'],
            'precioVenta' => $_POST['precioVenta'],
            'imagen' => $_FILES['imagen'],
            'venta' => null
        );

        try {
            $reg =  $productos->create($datos);
            $res = array('resultado' => 'insertado', 'mensaje' => 'Se gardó el tema', 'id' => $reg);
        } catch (PDOException $e) {
            $res = array('resultado' => 'error', 'mensaje' => $e->getMessage());
        }
    } else {
        $res = array('resultado' => 'error', 'mensaje' => 'Faltan datos');
    }

    //   header("HTTP/1.1 200 OK");
    echo json_encode($res);
}
