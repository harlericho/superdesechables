<?php
include_once '../../models/rolModel.php';
echo json_encode(RolModel::obtenerRoles());
