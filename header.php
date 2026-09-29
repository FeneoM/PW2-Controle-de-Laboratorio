<?php
require_once __DIR__ . '/conexao.php';
atualizarStatusLabs($conn);
$paginaAtual = basename($_SERVER['PHP_SELF']);
$menu = [
    'index.php' => 'Painel',
    'reservas.php' => 'Reservas',
    'laboratorios.php' => 'Laboratórios',
    'professores.php' => 'Professores',
    'turmas.php' => 'Turmas/Salas'
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Controle de Laboratórios de Informática</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Laboratórios</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto">
                <?php foreach ($menu as $arquivo => $rotulo): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $paginaAtual === $arquivo ? 'active' : '' ?>" href="<?= $arquivo ?>"><?= $rotulo ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container pb-5">
<?php if (!empty($_GET['ok'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Operação realizada com sucesso.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($erro)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= e($erro) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
