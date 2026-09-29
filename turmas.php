<?php
require_once __DIR__ . '/conexao.php';
$erro = '';
$turnos = ['Manhã', 'Tarde', 'Noite'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($acao === 'excluir' && $id > 0) {
        $s = $conn->prepare('DELETE FROM turmas WHERE id = ?');
        $s->bind_param('i', $id);
        $s->execute();
        header('Location: turmas.php?ok=1');
        exit;
    }

    if ($acao === 'salvar') {
        $nome = trim($_POST['nome'] ?? '');
        $turno = $_POST['turno'] ?? 'Manhã';
        $qtd = max(0, (int)($_POST['qtd_alunos'] ?? 0));

        if ($nome === '' || !in_array($turno, $turnos, true)) {
            $erro = 'Informe o nome e um turno válido.';
        } else {
            if ($id > 0) {
                $s = $conn->prepare('UPDATE turmas SET nome = ?, turno = ?, qtd_alunos = ? WHERE id = ?');
                $s->bind_param('ssii', $nome, $turno, $qtd, $id);
            } else {
                $s = $conn->prepare('INSERT INTO turmas (nome, turno, qtd_alunos) VALUES (?, ?, ?)');
                $s->bind_param('ssi', $nome, $turno, $qtd);
            }
            $s->execute();
            header('Location: turmas.php?ok=1');
            exit;
        }
    }
}

$edicao = ['id' => 0, 'nome' => '', 'turno' => 'Manhã', 'qtd_alunos' => 0];
if (isset($_GET['editar'])) {
    $s = $conn->prepare('SELECT * FROM turmas WHERE id = ?');
    $idEditar = (int)$_GET['editar'];
    $s->bind_param('i', $idEditar);
    $s->execute();
    $encontrado = $s->get_result()->fetch_assoc();
    if ($encontrado) {
        $edicao = $encontrado;
    }
}

$lista = $conn->query('SELECT * FROM turmas ORDER BY nome');
require 'header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header"><?= $edicao['id'] ? 'Editar turma/sala' : 'Nova turma/sala' ?></div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="id" value="<?= (int)$edicao['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Nome da turma/sala</label>
                        <input type="text" name="nome" class="form-control" required value="<?= e($edicao['nome']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Turno</label>
                        <select name="turno" class="form-select">
                            <?php foreach ($turnos as $t): ?>
                                <option value="<?= $t ?>" <?= $edicao['turno'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantidade de alunos</label>
                        <input type="number" min="0" name="qtd_alunos" class="form-control" value="<?= (int)$edicao['qtd_alunos'] ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <?php if ($edicao['id']): ?>
                        <a href="turmas.php" class="btn btn-outline-secondary">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">Turmas/salas cadastradas</div>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr><th>Nome</th><th>Turno</th><th>Alunos</th><th class="text-end">Ações</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($lista->num_rows === 0): ?>
                            <tr><td colspan="4" class="text-center text-muted">Nenhuma turma cadastrada.</td></tr>
                        <?php endif; ?>
                        <?php while ($t = $lista->fetch_assoc()): ?>
                            <tr>
                                <td><?= e($t['nome']) ?></td>
                                <td><?= e($t['turno']) ?></td>
                                <td><?= (int)$t['qtd_alunos'] ?></td>
                                <td class="text-end">
                                    <a href="turmas.php?editar=<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Excluir esta turma e as reservas associadas?');">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
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
