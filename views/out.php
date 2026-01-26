<?php
include_once '../config/session.php';

SessionManager::initSession();
SessionManager::destroySession();

header('Location: ../index.html');
exit();
