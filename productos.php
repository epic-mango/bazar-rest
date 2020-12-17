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
        isset($_POST['imagen'])
    ) {
        $productos = new DataBase('productos');
        $datos = array(
            'id' => $_POST['id'],
            'nombre' => $_POST['nombre'],
            'precioCompra' => $_POST['precioCompra'],
            'precioVenta' => $_POST['precioVenta'],
            'imagen' => $_POST['imagen'],
            'venta' => null
        );

        try {
            $reg =  $productos->create($datos);
            $res = array('resultado' => 'insertado', 'mensaje' => 'Se guardó el producto', 'id' => $reg);
        } catch (PDOException $e) {
            $res = array('resultado' => 'error', 'mensaje' => $e->getMessage());
        }
    } else {
        header("HTTP/1.1 401 Bad Request");
        $res = array('resultado' => 'error', 'mensaje' => 'Faltan datos');
    }

    header("HTTP/1.1 200 OK");
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $productos = new DataBase('productos');
    $res = $productos->readAll();
    header("HTTP/1.1 200 OK");
} else if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    if (isset($_GET['id'])) {
        $productos = new DataBase('productos');
        $where = array('id' => $_GET['id']);
        $datos = array(
            'nombre' => $_GET['nombre'],
            'precioCompra' => $_GET['precioCompra'],
            'precioVenta' => $_GET['precioVenta']
            //'imagen' => $_GET['imagen'],

        );

        if ($_GET['venta'] != "null" && $_GET['venta'] != "undefined" )
            $datos['venta'] = $_GET['venta'];

        $productos->update($datos, $where);

        $res = array('resultado' => 'actualizado', 'mensaje' => 'Se actualizó el producto');

        header("HTTP/1.1 200 OK");
    } else {
        header("HTTP/1.1 401 Bad Request");
        $res = array('resultado' => 'error', 'mensaje' => 'No se recibió la información necesaria');
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    if (isset($_GET['id'])) {
        $productos = new DataBase('productos');
        $where = array('id' => $_GET['id']);
        $cantidad = $productos->delete($where);

        $res = array('resultado' => 'eliminado', 'mensaje' => 'Eliminado correctamente', 'cantidad' => $cantidad);
        header("HTTP/1.1 200 OK");
    } else {
        header("HTTP/1.1 401 Bad Request");

        $res = array('resultado' => 'error', 'mensaje' => 'Faltan datos');
    }
} else {
    header("HTTP/1.1 401 Bad Request");
    $res = array('resultado' => 'error', 'mensaje' => 'No es un método conocido');
}

echo json_encode($res);
