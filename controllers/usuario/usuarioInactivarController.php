<?php
include_once  '../../models/usuarioModel.php';
echo UsuarioModel::inactivarUsuario($_POST['id']);