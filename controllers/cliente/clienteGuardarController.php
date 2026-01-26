<?php
include_once '../../models/clienteModel.php';

$arrayName = array(
    'dni' => $_POST['dni'],
    'nombres' => strtoupper($_POST['nombres']),
    'apellidos' => strtoupper($_POST['apellidos']),
    'direccion' => isset($_POST['direccion']) && !empty($_POST['direccion']) ? strtoupper($_POST['direccion']) : '',
    'telefono' => isset($_POST['telefono']) && !empty($_POST['telefono']) ? $_POST['telefono'] : '',
    'email' => isset($_POST['email']) && !empty($_POST['email']) ? strtolower($_POST['email']) : '',
    'rol' => $_POST['rol']
);

if (ClienteModel::existeClienteDni($_POST['dni'])) {
    echo json_encode(['success' => false, 'code' => 2]);
} else if (!empty($arrayName['email']) && ClienteModel::existeClienteEmail($arrayName['email'])) {
    echo json_encode(['success' => false, 'code' => 3]);
} else if (!empty($arrayName['telefono']) && ClienteModel::existeClienteTelefono($arrayName['telefono'])) {
    echo json_encode(['success' => false, 'code' => 4]);
} else {
    $clienteId = ClienteModel::guardarCliente($arrayName);
    if ($clienteId && $clienteId > 0) {
        echo json_encode(['success' => true, 'code' => 1, 'cliente_id' => (int)$clienteId]);
    } else {
        // Intentar obtener el ID del cliente recién insertado por DNI
        $clienteGuardado = ClienteModel::existeClienteDni($arrayName['dni']);
        if ($clienteGuardado && count($clienteGuardado) > 0) {
            echo json_encode(['success' => true, 'code' => 1, 'cliente_id' => (int)$clienteGuardado[0]['cliente_id']]);
        } else {
            echo json_encode(['success' => false, 'code' => 0]);
        }
    }
}
