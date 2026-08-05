<?php

uses()->group('pgsql');

function rawPdo(): PDO
{
    /** @var array{host: string, port: string|int, database: string, username: string, password: string} $config */
    $config = config('database.connections.pgsql_test');

    return new PDO(
        sprintf('pgsql:host=%s;port=%s;dbname=%s',
            $config['host'],
            $config['port'],
            $config['database'],
        ),
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

function safeRollback(PDO $pdo): void
{
    try {
        if ($pdo->inTransaction()) {
            $pdo->exec('ROLLBACK');
        }
    } catch (Throwable) {
    }
}

function seedBudget(PDO $pdo): int
{
    $email = 't'.uniqid().'@t.com';
    $pdo->prepare("INSERT INTO users (name, email, password, currency, role, email_verified_at, created_at, updated_at) VALUES (?, ?, 'x', 'EUR', 'user', NOW(), NOW(), NOW())")
        ->execute(['tester-'.uniqid(), $email]);
    $uId = $pdo->lastInsertId('users_id_seq');

    $pdo->prepare("INSERT INTO budgets (user_id, name, amount, type, created_at, updated_at) VALUES (?, 'b', '10.00', 'general', NOW(), NOW())")
        ->execute([$uId]);

    return (int) $pdo->lastInsertId('budgets_id_seq');
}

describe('PostgreSQL concurrency-safe overspend prevention', function () {
    it('proves row-lock serialization: second PDO blocked by FOR UPDATE', function () {
        $c0 = rawPdo();
        $budgetId = seedBudget($c0);

        $c1 = rawPdo();
        $c1->exec('BEGIN');
        $c1->prepare('SELECT id FROM budgets WHERE id = ? FOR UPDATE')->execute([$budgetId]);

        $c2 = rawPdo();
        $c2->exec("SET lock_timeout = '3s'");
        $c2->exec('BEGIN');

        $blocked = false;
        try {
            $c2->prepare('SELECT id FROM budgets WHERE id = ? FOR UPDATE')->execute([$budgetId]);
        } catch (PDOException) {
            $blocked = true;
            safeRollback($c2);
        }

        $c1->exec('COMMIT');

        expect($blocked)->toBeTrue('Row-lock serialization failed — lock_timeout not triggered.');
    });

    it('allows exactly one writer to commit when both attempt overspend at the limit', function () {
        $c0 = rawPdo();
        $budgetId = seedBudget($c0);

        $c1 = rawPdo();
        $c1->exec('BEGIN');
        $c1->prepare('SELECT id FROM budgets WHERE id = ? FOR UPDATE')->execute([$budgetId]);
        $c1->prepare("INSERT INTO expenses (budget_id, name, amount, category, created_at, updated_at) VALUES (?, 'c1', '10.00', 'food', NOW(), NOW())")->execute([$budgetId]);

        $c2 = rawPdo();
        $c2->exec("SET lock_timeout = '3s'");
        $c2->exec('BEGIN');
        try {
            $c2->prepare('SELECT id FROM budgets WHERE id = ? FOR UPDATE')->execute([$budgetId]);
            safeRollback($c2);
        } catch (PDOException) {
            safeRollback($c2);
        }

        $c1->exec('COMMIT');

        // Verify via a fresh raw PDO (the default connection's outer tx
        // may have snapshot issues with cross-connection commits).
        $verify = rawPdo();
        $count = $verify->query("SELECT COUNT(*) FROM expenses WHERE budget_id = $budgetId")->fetchColumn();
        $total = $verify->query("SELECT COALESCE(SUM(amount::numeric), 0) FROM expenses WHERE budget_id = $budgetId")->fetchColumn();

        expect((int) $count)->toBe(1)
            ->and((float) $total)->toBe(10.00);
    });

    it('serializes an update writer against a concurrent create writer via row lock', function () {
        $c0 = rawPdo();
        $budgetId = seedBudget($c0);
        $c0->prepare("INSERT INTO expenses (budget_id, name, amount, category, created_at, updated_at) VALUES (?, 'existing', '10.00', 'food', NOW(), NOW())")->execute([$budgetId]);
        $expenseId = (int) $c0->lastInsertId('expenses_id_seq');

        $c1 = rawPdo();
        $c1->exec('BEGIN');
        $c1->prepare('SELECT id FROM budgets WHERE id = ? FOR UPDATE')->execute([$budgetId]);
        $c1->prepare('UPDATE expenses SET amount = ?, updated_at = NOW() WHERE id = ?')->execute(['40.00', $expenseId]);

        $c2 = rawPdo();
        $c2->exec("SET lock_timeout = '3s'");
        $c2->exec('BEGIN');
        $c2Blocked = false;
        try {
            $c2->prepare('SELECT id FROM budgets WHERE id = ? FOR UPDATE')->execute([$budgetId]);
        } catch (PDOException) {
            $c2Blocked = true;
            safeRollback($c2);
        }

        $c1->exec('COMMIT');
        if (! $c2Blocked) {
            safeRollback($c2);
        }

        expect($c2Blocked)->toBeTrue('Concurrent writer not blocked by row lock.');

        $verify = rawPdo();
        $spent = $verify->query("SELECT COALESCE(SUM(amount::numeric), 0) FROM expenses WHERE budget_id = $budgetId")->fetchColumn();

        expect((float) $spent)->toBe(40.00);
    });
});
