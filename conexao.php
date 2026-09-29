<?php
$conn = new mysqli('localhost', 'root', 'usbw', 'controle_labs');
if ($conn->connect_error) {
    die('Erro de conexão: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

function e($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function atualizarStatusLabs($conn)
{
    $conn->query("UPDATE laboratorios SET status = 'liberado'");
    $conn->query("UPDATE laboratorios l SET l.status = 'reservado' WHERE EXISTS (SELECT 1 FROM reservas r WHERE r.laboratorio_id = l.id AND NOW() BETWEEN r.inicio AND r.termino)");
}

function dataBr($valor)
{
    return date('d/m/Y H:i', strtotime($valor));
}
