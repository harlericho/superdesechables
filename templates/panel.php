<?php require_once '../config/empresa.php'; ?>
<ol class="breadcrumb">
  <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
  <li class="active">Panel <?= strtolower(Empresa::getComercio()) ?></li>
</ol>