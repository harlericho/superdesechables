<?php
include_once '../../models/usuarioModel.php';
echo json_encode(UsuarioModel::obtenerUsuarios());
