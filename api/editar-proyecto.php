<?php
// Verificar que el usuario logueado es el líder del proyecto
if ($_SESSION['usuario_id'] != $proyecto['lider_id']) {
    die(json_encode(['success' => false, 'message' => 'No autorizado']));
}// Verificar que el usuario logueado es el líder del proyecto
if ($_SESSION['usuario_id'] != $proyecto['lider_id']) {
    die(json_encode(['success' => false, 'message' => 'No autorizado']));
}