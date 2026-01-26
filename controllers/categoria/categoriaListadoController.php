<?php
include_once  '../../models/categoriaModel.php';
echo json_encode(CategoriaModel::obtenerCategorias());