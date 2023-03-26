<?php
require_once 'vendor/autoload.php';
$app = new \Slim\App();
//Configuración cabeceras http
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}


$db = new mysqli('localhost', 'root', 'root', 'curso_angular4');
$app->get("/pruebas", function () use ($app) {
    echo "hola desde slim";
});
//Devolver listado de productos
$app->get("/productos", function () use ($db, $app) {
    $sql = 'select * from productos order by id desc;';
    $query = $db->query($sql);
    $productos = array();
    while ($producto = $query->fetch_assoc()) {
        $productos[] = $producto;
    }
    $result = array(
        'status' => 'success',
        'code' => 200,
        'data' => $productos
    );

    echo json_encode($result);
});
//Subir una imagen a un producto
$app->post("/upload-file", function ($request, $response, $args) use ($db, $app) {
    $piramideUploader = new PiramideUploader();
    if (isset($_FILES['uploads'])) {
        $upload = $piramideUploader->upload('image', "uploads", "uploads", array('image/jpeg', 'image/png', 'image/gif'));
        $result = 'llegan los datos';
    }
    if (!(isset($upload) && !$upload['uploaded'])) {
        $file = $piramideUploader->getInfoFile();
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El archivo se ha subido',
            'filename' => $file['complete_name']
    );
    } else {
        var_dump($upload["error"]);
        $result = array(
            'status' => 'error',
            'code' => 500,
            'message', 'El archivo no ha podido subirse'
        );
    }
    echo json_encode($result);
});
//Actualizar un producto
$app->post("/update-producto/{id}", function ($request, $response, $args) use ($db, $app) {
    $data = json_decode($request->getParsedBodyParam('json'), true);
    if (!isset($data['nombre'])) {
        $data['nombre'] = null;
    }
    if (!isset($data['descripcion'])) {
        $data['descripcion'] = null;
    }
    if (!isset($data['precio'])) {
        $data['precio'] = null;
    }

    $sql = "update productos set " .
        "nombre = '{$data['nombre']}'," .
        "descripcion = '{$data['descripcion']}',";
    if (isset($data['imagen'])) {
        $sql .= "imagen = '{$data['imagen']}',";
    };
    $sql .= "precio = '{$data['precio']}' " .
        "where id = " . $args['id'];
    $query = $db->query($sql);
    if ($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El producto se ha actualizado correctamente'
        );
    } else {
        $result = array(
            'status' => 'success',
            'code' => 500,
            'message', 'El producto no se ha actualizado correctamente'
        );
    }
    echo json_encode($result);
});
//Eliminar un producto
$app->get("/delete-producto/{id}", function ($request, $response, $args) use ($db, $app) {
    $sql = 'delete p from productos p where id = ' . $args['id'];
    $query = $db->query($sql);
    if ($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El producto se ha eliminado correctamente'
        );
    } else {
        $result = array(
            'status' => 'success',
            'code' => 500,
            'message', 'El producto no se ha eliminado correctamente'
        );
    }
    echo json_encode($result);
});
//Devolver un producto
$app->get("/producto/{id}", function ($request, $response, $args) use ($db, $app) {
    $sql = 'select * from productos where id = ' . $args['id'];
    $query = $db->query($sql);
    if ($query->num_rows == 1) {
        $producto = $query->fetch_assoc();
        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => $producto
        );
    } else {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message', 'Producto no encontrado'
        );
    }
    echo json_encode($result);
});
//Añadir un producto
$app->post("/productos", function ($request, $response, $args) use ($app, $db) {
    //d$json = $app->$request->getAttribute('json');
    $data = json_decode($request->getParsedBodyParam('json'), true);
    if (!isset($data['nombre'])) {
        $data['nombre'] = null;
    }
    if (!isset($data['descripcion'])) {
        $data['descripcion'] = null;
    }
    if (!isset($data['precio'])) {
        $data['precio'] = null;
    }
    if (!isset($data['imagen'])) {
        $data['imagen'] = null;
    }
    $query = "insert into productos values(null," .
        "'{$data['nombre']}'," .
        "'{$data['descripcion']}'," .
        "'{$data['precio']}'," .
        "'{$data['imagen']}'" .
        ");";
    $insert = $db->query($query);
    if ($insert) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'Producto creado correctamente'
        );
    } else {
        $result = array(
            'status' => 'error',
            'code' => 500,
            'message' => 'Producto no creado correctamente'
        );

    }
    echo json_encode($result);

});
$app->run();