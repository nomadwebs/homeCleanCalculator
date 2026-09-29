<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function out($data, int $code = 200): never {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

/** Saldo (debido − pagado) acumulado de todos los meses anteriores a year/month. Negativo = pagado de más. */
function carry_in(int $year, int $month): float {
    $pdo = db(); $key = $year * 12 + $month;
    $q = $pdo->prepare('SELECT COALESCE(SUM(ROUND(l.hours*l.hourly_rate,2)),0) FROM order_lines l JOIN orders o ON o.id = l.order_id WHERE o.year*12+o.month < ?');
    $q->execute([$key]); $due = (float)$q->fetchColumn();
    $q = $pdo->prepare('SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN orders o ON o.id = p.order_id WHERE o.year*12+o.month < ?');
    $q->execute([$key]); $paid = (float)$q->fetchColumn();
    return round($due - $paid, 2);
}

function order_state(int $year, int $month): array {
    $pdo = db();
    $st = $pdo->prepare('SELECT * FROM orders WHERE year = ? AND month = ?');
    $st->execute([$year, $month]);
    $order = $st->fetch();
    $lines = [];
    if ($order) {
        $st = $pdo->prepare('SELECT id, work_date, hours, hourly_rate, paid_at FROM order_lines WHERE order_id = ? ORDER BY work_date');
        $st->execute([$order['id']]);
        foreach ($st->fetchAll() as $l) {
            $lines[] = ['id' => (int)$l['id'], 'date' => $l['work_date'],
                        'hours' => (float)$l['hours'], 'rate' => (float)$l['hourly_rate'],
                        'paid' => $l['paid_at'] !== null];
        }
    }
    $total = 0.0; $paid = 0.0;
    foreach ($lines as $l) {
        $amt = round($l['hours'] * $l['rate'], 2);
        $total += $amt;
        if ($l['paid']) $paid += $amt;
    }
    $pending = $pdo->query('SELECT COUNT(*) FROM order_lines WHERE paid_at IS NULL')->fetchColumn();
    $due_all = (float)$pdo->query('SELECT COALESCE(SUM(ROUND(hours*hourly_rate,2)),0) FROM order_lines')->fetchColumn();
    $paid_all = (float)$pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments')->fetchColumn();
    $payments = [];
    if ($order) {
        $ps = $pdo->prepare('SELECT p.id, p.paid_on, p.amount, p.note, COUNT(l.id) days FROM payments p
                             LEFT JOIN order_lines l ON l.payment_id = p.id WHERE p.order_id = ? GROUP BY p.id ORDER BY p.paid_on, p.id');
        $ps->execute([$order['id']]);
        foreach ($ps->fetchAll() as $p)
            $payments[] = ['id' => (int)$p['id'], 'date' => $p['paid_on'], 'amount' => (float)$p['amount'], 'note' => $p['note'], 'days' => (int)$p['days']];
    }
    $paid_real = array_sum(array_column($payments, 'amount'));
    $closed = $order && $order['status'] === 'closed';
    return [
        'year' => $year, 'month' => $month,
        'status' => $order['status'] ?? 'open',
        'closed_at' => $order['closed_at'] ?? null,
        'lines' => $lines,
        'total' => $closed && $order['total'] !== null ? (float)$order['total'] : round($total, 2),
        'paid_total' => round($paid, 2),
        'holidays' => array_values(array_filter(holidays_for_year($year), fn($h) => (int)substr($h['date'], 5, 2) === $month)),
        'carry_in' => carry_in($year, $month),
        'payments' => $payments,
        'paid_real' => round($paid_real, 2),
        'balance_all' => ['due' => round($due_all, 2), 'paid' => round($paid_all, 2), 'pending' => round($due_all - $paid_all, 2), 'unpaid_days' => (int)$pending],
        'settings' => get_settings(),
    ];
}

/** Devuelve el pedido del mes, creándolo si no existe. */
function ensure_order(int $year, int $month): array {
    $pdo = db();
    $pdo->prepare('INSERT IGNORE INTO orders (year, month) VALUES (?, ?)')->execute([$year, $month]);
    $st = $pdo->prepare('SELECT * FROM orders WHERE year = ? AND month = ?');
    $st->execute([$year, $month]);
    return $st->fetch();
}

try {
    $in = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $_GET['action'] ?? ($in['action'] ?? 'state');
    $pdo = db();

    if ($action === 'state') {
        $y = (int)($_GET['year'] ?? date('Y')); $m = (int)($_GET['month'] ?? date('n'));
        if ($m < 1 || $m > 12) out(['error' => 'Mes inválido'], 400);
        out(order_state($y, $m));
    }

    if ($action === 'history') {
        $y = (int)($_GET['year'] ?? date('Y'));
        $st = $pdo->prepare("SELECT o.month, o.status, o.total AS frozen, COUNT(l.id) days, COALESCE(SUM(l.hours),0) hours,
              COALESCE(SUM(ROUND(l.hours*l.hourly_rate,2)),0) amt,
              (SELECT COALESCE(SUM(amount),0) FROM payments p WHERE p.order_id = o.id) paid
            FROM orders o LEFT JOIN order_lines l ON l.order_id = o.id WHERE o.year = ? GROUP BY o.id");
        $st->execute([$y]);
        $months = array_fill(1, 12, ['status' => null, 'days' => 0, 'hours' => 0.0, 'total' => 0.0, 'paid' => 0.0]);
        foreach ($st->fetchAll() as $r) {
            $total = $r['status'] === 'closed' && $r['frozen'] !== null ? (float)$r['frozen'] : (float)$r['amt'];
            $months[(int)$r['month']] = ['status' => $r['status'], 'days' => (int)$r['days'], 'hours' => (float)$r['hours'],
                                         'total' => round($total, 2), 'paid' => round((float)$r['paid'], 2)];
        }
        $years = $pdo->query('SELECT DISTINCT year FROM orders ORDER BY year DESC')->fetchAll(PDO::FETCH_COLUMN);
        out(['year' => $y, 'carry_in' => carry_in($y, 1), 'months' => $months, 'years' => array_map('intval', $years)]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $action !== 'holidays') out(['error' => 'Método no permitido'], 405);

    if ($action === 'save_settings') {
        $rate = round((float)$in['hourly_rate'], 2); $hours = round((float)$in['default_hours'], 2);
        if ($rate < 0 || $rate > 999999 || $hours <= 0 || $hours > 24) out(['error' => 'Valores fuera de rango'], 400);
        $region = $in['region'] ?? '';
        if ($region !== '' && !isset(REGIONS[$region])) out(['error' => 'Comunidad inválida'], 400);
        $changed = ($region ?: null) !== get_settings()['region'];
        $pdo->prepare('UPDATE settings SET hourly_rate = ?, default_hours = ?, region = ? WHERE id = 1')->execute([$rate, $hours, $region ?: null]);
        if ($changed) $pdo->exec("DELETE FROM holidays WHERE source = 'api'"); // se reimportan con la nueva comunidad
        out(['ok' => true, 'settings' => get_settings()]);
    }

    if ($action === 'set_mode') {
        $demo = ($in['mode'] ?? '') === 'demo';
        setcookie('hcc_mode', $demo ? 'demo' : 'real', ['expires' => time() + 86400 * 365, 'path' => '/', 'samesite' => 'Lax']);
        out(['ok' => true, 'mode' => $demo ? 'demo' : 'real']);
    }
    if ($action === 'reset_demo') {
        // Solo se puede borrar la base de pruebas, nunca la real
        if (!is_demo()) out(['error' => 'Solo disponible en modo pruebas'], 409);
        $name = active_db_name();
        if ($name === DB_NAME) out(['error' => 'Operación no permitida'], 409);
        $pdo->exec('DROP DATABASE `' . $name . '`');
        create_database($pdo, $name);
        out(['ok' => true]);
    }

    if ($action === 'reset_data') {
        // Doble comprobación también en el servidor: hay que enviar la frase exacta
        $phrase = mb_strtolower(trim($in['confirm'] ?? ''));
        if (!in_array($phrase, ['sí borrar', 'si borrar'], true)) out(['error' => 'Falta la confirmación'], 400);
        $pdo->beginTransaction();
        $pdo->exec('DELETE FROM payments');
        $pdo->exec('DELETE FROM order_lines');
        $pdo->exec('DELETE FROM orders');
        if (!empty($in['everything'])) {
            $pdo->exec('DELETE FROM holidays');
            $pdo->exec('UPDATE settings SET hourly_rate = 10.00, default_hours = 4.00, region = NULL WHERE id = 1');
        }
        $pdo->commit();
        foreach (['payments', 'order_lines', 'orders'] as $t) $pdo->exec("ALTER TABLE $t AUTO_INCREMENT = 1");
        out(['ok' => true]);
    }

    if ($action === 'holidays') {
        $y = (int)($_GET['year'] ?? $in['year'] ?? date('Y'));
        out(['year' => $y, 'holidays' => holidays_for_year($y), 'regions' => REGIONS]);
    }
    if ($action === 'import_holidays') {
        $y = (int)$in['year'];
        try { $n = import_holidays($y); } catch (Throwable $e) { out(['error' => $e->getMessage()], 502); }
        out(['imported' => $n, 'year' => $y, 'holidays' => holidays_for_year($y)]);
    }
    if ($action === 'add_holiday') {
        $d = DateTime::createFromFormat('Y-m-d', $in['date'] ?? ''); $name = trim($in['name'] ?? '');
        if (!$d || $name === '') out(['error' => 'Fecha y nombre obligatorios'], 400);
        $pdo->prepare("INSERT INTO holidays (holiday_date, name, source) VALUES (?, ?, 'manual')
                       ON DUPLICATE KEY UPDATE name = VALUES(name), source = 'manual'")->execute([$d->format('Y-m-d'), mb_substr($name, 0, 120)]);
        out(['holidays' => holidays_for_year((int)$d->format('Y'))]);
    }
    if ($action === 'delete_holiday') {
        $pdo->prepare('DELETE FROM holidays WHERE id = ?')->execute([(int)$in['id']]);
        out(['ok' => true]);
    }

    // Acciones sobre pedidos: se identifica por año/mes
    $y = (int)($in['year'] ?? 0); $m = (int)($in['month'] ?? 0);
    if ($m < 1 || $m > 12 || $y < 2000) out(['error' => 'Mes inválido'], 400);
    $order = ensure_order($y, $m);
    $isClosed = $order['status'] === 'closed';

    if ($action === 'close') {
        $s = order_state($y, $m);
        $pdo->prepare("UPDATE orders SET status='closed', total=?, closed_at=NOW() WHERE id=?")->execute([$s['total'], $order['id']]);
    } elseif ($action === 'reopen') {
        $pdo->prepare("UPDATE orders SET status='open', total=NULL, closed_at=NULL WHERE id=?")->execute([$order['id']]);
    } elseif ($action === 'register_payment') {
        // Se puede registrar aunque el pedido esté cerrado. Puede no incluir días (pago a cuenta).
        $amount = round((float)($in['amount'] ?? -1), 2);
        $d = DateTime::createFromFormat('Y-m-d', $in['date'] ?? '');
        if ($amount < 0 || $amount > 9999999 || !$d) out(['error' => 'Cantidad o fecha inválida'], 400);
        $ids = array_values(array_unique(array_map('intval', $in['line_ids'] ?? [])));
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO payments (order_id, paid_on, amount, note) VALUES (?,?,?,?)')
            ->execute([$order['id'], $d->format('Y-m-d'), $amount, mb_substr(trim($in['note'] ?? ''), 0, 200) ?: null]);
        $pid = (int)$pdo->lastInsertId();
        if ($ids) {
            $mark = $pdo->prepare('UPDATE order_lines SET paid_at = ?, payment_id = ? WHERE id = ? AND order_id = ? AND paid_at IS NULL');
            foreach ($ids as $id) $mark->execute([$d->format('Y-m-d') . ' 12:00:00', $pid, $id, $order['id']]);
        }
        $pdo->commit();
    } elseif ($action === 'update_payment') {
        $amount = round((float)$in['amount'], 2); $d = DateTime::createFromFormat('Y-m-d', $in['date'] ?? '');
        if ($amount < 0 || !$d) out(['error' => 'Cantidad o fecha inválida'], 400);
        $pdo->prepare('UPDATE payments SET amount = ?, paid_on = ?, note = ? WHERE id = ? AND order_id = ?')
            ->execute([$amount, $d->format('Y-m-d'), mb_substr(trim($in['note'] ?? ''), 0, 200) ?: null, (int)$in['id'], $order['id']]);
    } elseif ($action === 'delete_payment') {
        $pdo->prepare('UPDATE order_lines SET paid_at = NULL, payment_id = NULL WHERE payment_id = ? AND order_id = ?')->execute([(int)$in['id'], $order['id']]);
        $pdo->prepare('DELETE FROM payments WHERE id = ? AND order_id = ?')->execute([(int)$in['id'], $order['id']]);
    } elseif ($action === 'unpay_line') {
        // Quita la marca de "día pagado"; el dinero del pago se conserva como saldo
        $pdo->prepare('UPDATE order_lines SET paid_at = NULL, payment_id = NULL WHERE id = ? AND order_id = ?')->execute([(int)$in['id'], $order['id']]);
    } else {
        if ($isClosed) out(['error' => 'El pedido está cerrado. Reábrelo para modificarlo.'], 409);

        if ($action === 'toggle_day') {
            $date = $in['date'] ?? '';
            $d = DateTime::createFromFormat('Y-m-d', $date);
            if (!$d || $d->format('Y-m-d') !== $date || (int)$d->format('Y') !== $y || (int)$d->format('n') !== $m)
                out(['error' => 'Fecha inválida para este mes'], 400);
            $st = $pdo->prepare('SELECT id FROM order_lines WHERE order_id = ? AND work_date = ?');
            $st->execute([$order['id'], $date]);
            if ($id = $st->fetchColumn()) {
                $pdo->prepare('DELETE FROM order_lines WHERE id = ? AND paid_at IS NULL')->execute([$id]);
                if ($pdo->query("SELECT COUNT(*) FROM order_lines WHERE id = $id")->fetchColumn())
                    out(['error' => 'Ese día ya está pagado. Desmárcalo como pagado para quitarlo.'], 409);
            } else {
                $cfg = get_settings();
                $pdo->prepare('INSERT INTO order_lines (order_id, work_date, hours, hourly_rate) VALUES (?,?,?,?)')
                    ->execute([$order['id'], $date, $cfg['default_hours'], $cfg['hourly_rate']]);
            }
        } elseif ($action === 'update_line') {
            $hours = round((float)$in['hours'], 2); $rate = round((float)$in['rate'], 2);
            if ($hours < 0 || $hours > 24 || $rate < 0 || $rate > 999999) out(['error' => 'Valores fuera de rango'], 400);
            $pdo->prepare('UPDATE order_lines SET hours = ?, hourly_rate = ? WHERE id = ? AND order_id = ? AND paid_at IS NULL')
                ->execute([$hours, $rate, (int)$in['id'], $order['id']]);
        } elseif ($action === 'delete_line') {
            $pdo->prepare('DELETE FROM order_lines WHERE id = ? AND order_id = ? AND paid_at IS NULL')->execute([(int)$in['id'], $order['id']]);
        } else {
            out(['error' => 'Acción desconocida'], 400);
        }
    }
    out(order_state($y, $m));
} catch (Throwable $e) {
    out(['error' => 'Error del servidor: ' . $e->getMessage()], 500);
}
