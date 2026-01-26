<?php
include_once  '../../models/usuarioModel.php';
echo UsuarioModel::activarUsuario($_POST['id']);