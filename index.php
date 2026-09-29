<?php
require_once __DIR__ . '/conexao.php';
require 'header.php';

$labs = $conn->query('SELECT * FROM laboratorios ORDER BY nome');
$proximas = $conn->query("SELECT r.inicio, r.termino, l.nome AS lab, p.nome AS professor, t.nome AS turma
    FROM reservas r
    JOIN laboratorios l ON l.id = r.laboratorio_id
    JOIN professores p ON p.id = r.professor_id
    JOIN turmas t ON t.id = r.turma_id
    WHERE r.termino >= NOW()
    ORDER BY r.inicio LIMIT 10");
?>
<h2 class="mb-3">Situação dos laboratórios</h2>
<div class="row g-3 mb-5">
    <?php if ($labs->num_rows === 0): ?>
        <div class="col-12"><div class="alert alert-info">Nenhum laboratório cadastrado.</div></div>
    <?php endif; ?>
    <?php while ($l = $labs->fetch_assoc()): ?>
        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><?= e($l['nome']) ?></h5>
                    <p class="text-muted mb-2">Capacidade: <?= (int)$l['capacidade'] ?> alunos</p>
                    <?php if ($l['status'] === 'liberado'): ?>
                        <span class="badge text-bg-success">Liberado</span>
                    <?php else: ?>
                        <span class="badge text-bg-danger">Reservado</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<h2 class="mb-3">Próximas reservas</h2>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr><th>Laboratório</th><th>Professor</th><th>Turma</th><th>Início</th><th>Término</th></tr>
            </thead>
            <tbody>
                <?php if ($proximas->num_rows === 0): ?>
                    <tr><td colspan="5" class="text-center text-muted">Nenhuma reserva futura.</td></tr>
                <?php endif; ?>
                <?php while ($r = $proximas->fetch_assoc()): ?>
                    <tr>
                        <td><?= e($r['lab']) ?></td>
                        <td><?= e($r['professor']) ?></td>
                        <td><?= e($r['turma']) ?></td>
                        <td><?= dataBr($r['inicio']) ?></td>
                        <td><?= dataBr($r['termino']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require 'footer.php'; ?>
