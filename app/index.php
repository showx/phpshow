<?php
require './bone/core/bone.php';
header('Content-Type: text/html; charset=utf-8');

$app_config = array(
    'app_title' => 'phpbone',
    'app_name' => '',
    'purview_config' => '',
    'session_start' => false
);

$app = new bone( $app_config );

$app->run();
