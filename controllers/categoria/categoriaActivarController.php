<?php
include_once  '../../models/categoriaModel.php';

echo CategoriaModel::activarCategoria($_POST['id']);