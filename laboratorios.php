<?php
require_once __DIR__ . '/conexao.php';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($acao === 'excluir' && $id > 0) {
        $s = $conn->prepare('DELETE FROM laboratorios WHERE id = ?');
        $s->bind_param('i', $id);
        $s->execute();
        header('Location: laboratorios.php?ok=1');
        exit;
    }

    if ($acao === 'salvar') {
        $nome = trim($_POST['nome'] ?? '');
        $capacidade = max(0, (int)($_POST['capacidade'] ?? 0));

        if ($nome === '') {
            $erro = 'Informe o nome do laboratório.';
        } else {
            if ($id > 0) {
                $s = $conn->prepare('UPDATE laboratorios SET nome = ?, capacidade = ? WHERE id = ?');
                $s->bind_param('sii', $nome, $capacidade, $id);
            } else {
                $s = $conn->prepare("INSERT INTO laboratorios (nome, capacidade, status) VALUES (?, ?, 'liberado')");
                $s->bind_param('si', $nome, $capacidade);
            }
            $s->execute();
            header('Location: laboratorios.php?ok=1');
            exit;
        }
    }
}

$edicao = ['id' => 0, 'nome' => '', 'capacidade' => 0];
if (isset($_GET['editar'])) {
    $s = $conn->prepare('SELECT * FROM laboratorios WHERE id = ?');
    $idEditar = (int)$_GET['editar'];
    $s->bind_param('i', $idEditar);
    $s->execute();
    $encontrado = $s->get_result()->fetch_assoc();
    if ($encontrado) {
        $edicao = $encontrado;
    }
}

require 'header.php';
$lista = $conn->query('SELECT * FROM laboratorios ORDER BY nome');
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header"><?= $edicao['id'] ? 'Editar laboratório' : 'Novo laboratório' ?></div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="id" value="<?= (int)$edicao['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required value="<?= e($edicao['nome']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacidade (alunos)</label>
                        <input type="number" min="0" name="capacidade" class="form-control" value="<?= (int)$edicao['capacidade'] ?>">
                    </div>
                    <p class="text-muted small">O status (liberado/reservado) é atualizado automaticamente conforme as reservas.</p>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <?php if ($edicao['id']): ?>
                        <a href="laboratorios.php" class="btn btn-outline-secondary">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">Laboratórios cadastrados</div>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr><th>Nome</th><th>Capacidade</th><th>Status</th><th class="text-end">Ações</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($lista->num_rows === 0): ?>
                            <tr><td colspan="4" class="text-center text-muted">Nenhum laboratório cadastrado.</td></tr>
                        <?php endif; ?>
                        <?php while ($l = $lista->fetch_assoc()): ?>
                            <tr>
                                <td><?= e($l['nome']) ?></td>
                                <td><?= (int)$l['capacidade'] ?></td>
                                <td>
                                    <?php if ($l['status'] === 'liberado'): ?>
                                        <span class="badge text-bg-success">Liberado</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-danger">Reservado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="laboratorios.php?editar=<?= (int)$l['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Excluir este laboratório e as reservas associadas?');">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>
