<?php
require_once __DIR__ . '/conexao.php';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($acao === 'excluir' && $id > 0) {
        $s = $conn->prepare('DELETE FROM professores WHERE id = ?');
        $s->bind_param('i', $id);
        $s->execute();
        header('Location: professores.php?ok=1');
        exit;
    }

    if ($acao === 'salvar') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $disciplina = trim($_POST['disciplina'] ?? '');

        if ($nome === '') {
            $erro = 'Informe o nome do professor.';
        } else {
            if ($id > 0) {
                $s = $conn->prepare('UPDATE professores SET nome = ?, email = ?, disciplina = ? WHERE id = ?');
                $s->bind_param('sssi', $nome, $email, $disciplina, $id);
            } else {
                $s = $conn->prepare('INSERT INTO professores (nome, email, disciplina) VALUES (?, ?, ?)');
                $s->bind_param('sss', $nome, $email, $disciplina);
            }
            $s->execute();
            header('Location: professores.php?ok=1');
            exit;
        }
    }
}

$edicao = ['id' => 0, 'nome' => '', 'email' => '', 'disciplina' => ''];
if (isset($_GET['editar'])) {
    $s = $conn->prepare('SELECT * FROM professores WHERE id = ?');
    $idEditar = (int)$_GET['editar'];
    $s->bind_param('i', $idEditar);
    $s->execute();
    $encontrado = $s->get_result()->fetch_assoc();
    if ($encontrado) {
        $edicao = $encontrado;
    }
}

$lista = $conn->query('SELECT * FROM professores ORDER BY nome');
require 'header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header"><?= $edicao['id'] ? 'Editar professor' : 'Novo professor' ?></div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="id" value="<?= (int)$edicao['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required value="<?= e($edicao['nome']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="<?= e($edicao['email']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Disciplina</label>
                        <input type="text" name="disciplina" class="form-control" value="<?= e($edicao['disciplina']) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <?php if ($edicao['id']): ?>
                        <a href="professores.php" class="btn btn-outline-secondary">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">Professores cadastrados</div>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr><th>Nome</th><th>E-mail</th><th>Disciplina</th><th class="text-end">Ações</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($lista->num_rows === 0): ?>
                            <tr><td colspan="4" class="text-center text-muted">Nenhum professor cadastrado.</td></tr>
                        <?php endif; ?>
                        <?php while ($p = $lista->fetch_assoc()): ?>
                            <tr>
                                <td><?= e($p['nome']) ?></td>
                                <td><?= e($p['email']) ?></td>
                                <td><?= e($p['disciplina']) ?></td>
                                <td class="text-end">
                                    <a href="professores.php?editar=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Excluir este professor e as reservas associadas?');">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
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
