<?php
require_once __DIR__ . '/conexao.php';
$erro = '';
$form = ['professor_id' => 0, 'turma_id' => 0, 'laboratorio_id' => 0, 'inicio' => '', 'termino' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'excluir') {
        $id = (int)($_POST['id'] ?? 0);
        $s = $conn->prepare('DELETE FROM reservas WHERE id = ?');
        $s->bind_param('i', $id);
        $s->execute();
        header('Location: reservas.php?ok=1');
        exit;
    }

    if ($acao === 'salvar') {
        $form['professor_id'] = (int)($_POST['professor_id'] ?? 0);
        $form['turma_id'] = (int)($_POST['turma_id'] ?? 0);
        $form['laboratorio_id'] = (int)($_POST['laboratorio_id'] ?? 0);
        $form['inicio'] = $_POST['inicio'] ?? '';
        $form['termino'] = $_POST['termino'] ?? '';

        $inicioSql = str_replace('T', ' ', $form['inicio']) . ':00';
        $terminoSql = str_replace('T', ' ', $form['termino']) . ':00';

        if (!$form['professor_id'] || !$form['turma_id'] || !$form['laboratorio_id'] || $form['inicio'] === '' || $form['termino'] === '') {
            $erro = 'Preencha todos os campos da reserva.';
        } elseif (strtotime($terminoSql) <= strtotime($inicioSql)) {
            $erro = 'O horário de término deve ser posterior ao horário de início.';
        } else {
            $s = $conn->prepare('SELECT COUNT(*) FROM reservas WHERE laboratorio_id = ? AND inicio < ? AND termino > ?');
            $s->bind_param('iss', $form['laboratorio_id'], $terminoSql, $inicioSql);
            $s->execute();
            $conflitos = $s->get_result()->fetch_row()[0];

            if ($conflitos > 0) {
                $erro = 'Este laboratório já está reservado em parte ou todo o período informado.';
            } else {
                $s = $conn->prepare('INSERT INTO reservas (professor_id, turma_id, laboratorio_id, inicio, termino) VALUES (?, ?, ?, ?, ?)');
                $s->bind_param('iiiss', $form['professor_id'], $form['turma_id'], $form['laboratorio_id'], $inicioSql, $terminoSql);
                $s->execute();
                header('Location: reservas.php?ok=1');
                exit;
            }
        }
    }
}

require 'header.php';

$professores = $conn->query('SELECT id, nome FROM professores ORDER BY nome')->fetch_all(MYSQLI_ASSOC);
$turmas = $conn->query('SELECT id, nome, turno FROM turmas ORDER BY nome')->fetch_all(MYSQLI_ASSOC);
$labs = $conn->query('SELECT id, nome, status FROM laboratorios ORDER BY nome')->fetch_all(MYSQLI_ASSOC);

$lista = $conn->query("SELECT r.id, r.inicio, r.termino, l.nome AS lab, p.nome AS professor, t.nome AS turma
    FROM reservas r
    JOIN laboratorios l ON l.id = r.laboratorio_id
    JOIN professores p ON p.id = r.professor_id
    JOIN turmas t ON t.id = r.turma_id
    ORDER BY r.inicio DESC");
$semDados = !$professores || !$turmas || !$labs;
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header">Nova reserva</div>
            <div class="card-body">
                <?php if ($semDados): ?>
                    <div class="alert alert-warning mb-0">Cadastre ao menos um professor, uma turma e um laboratório antes de reservar.</div>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="acao" value="salvar">
                        <div class="mb-3">
                            <label class="form-label">Laboratório</label>
                            <select name="laboratorio_id" class="form-select" required>
                                <option value="">Selecione</option>
                                <?php foreach ($labs as $l): ?>
                                    <option value="<?= (int)$l['id'] ?>" <?= $form['laboratorio_id'] == $l['id'] ? 'selected' : '' ?>>
                                        <?= e($l['nome']) ?> (<?= e($l['status']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Professor</label>
                            <select name="professor_id" class="form-select" required>
                                <option value="">Selecione</option>
                                <?php foreach ($professores as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>" <?= $form['professor_id'] == $p['id'] ? 'selected' : '' ?>><?= e($p['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Turma</label>
                            <select name="turma_id" class="form-select" required>
                                <option value="">Selecione</option>
                                <?php foreach ($turmas as $t): ?>
                                    <option value="<?= (int)$t['id'] ?>" <?= $form['turma_id'] == $t['id'] ? 'selected' : '' ?>><?= e($t['nome']) ?> - <?= e($t['turno']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Início</label>
                            <input type="datetime-local" name="inicio" class="form-control" required value="<?= e($form['inicio']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Término</label>
                            <input type="datetime-local" name="termino" class="form-control" required value="<?= e($form['termino']) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Reservar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">Reservas registradas</div>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr><th>Laboratório</th><th>Professor</th><th>Turma</th><th>Início</th><th>Término</th><th class="text-end">Ações</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($lista->num_rows === 0): ?>
                            <tr><td colspan="6" class="text-center text-muted">Nenhuma reserva registrada.</td></tr>
                        <?php endif; ?>
                        <?php while ($r = $lista->fetch_assoc()): ?>
                            <tr>
                                <td><?= e($r['lab']) ?></td>
                                <td><?= e($r['professor']) ?></td>
                                <td><?= e($r['turma']) ?></td>
                                <td><?= dataBr($r['inicio']) ?></td>
                                <td><?= dataBr($r['termino']) ?></td>
                                <td class="text-end">
                                    <form method="post" class="d-inline" onsubmit="return confirm('Cancelar esta reserva?');">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar</button>
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
