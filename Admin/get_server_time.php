<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'server_time' => date('H:i')
]);
