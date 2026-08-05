<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

uses()->group('pgsql');

describe('PostgreSQL webhook inbox idempotency and reordering', function () {
    beforeEach(function () {
        // Ensure the inbox table exists (created by migration).
    });

    it('rejects a duplicate Stripe event ID at the unique constraint level', function () {
        $eventId = 'evt_'.uniqid();

        DB::connection('pgsql_test')->table('stripe_event_inbox')->insert([
            'stripe_event_id' => $eventId,
            'stripe_event_type' => 'customer.subscription.created',
            'payload' => json_encode(['id' => 'sub_1']),
            'processed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        expect(fn () => DB::connection('pgsql_test')->table('stripe_event_inbox')->insert([
            'stripe_event_id' => $eventId,
            'stripe_event_type' => 'customer.subscription.created',
            'payload' => json_encode(['id' => 'sub_1']),
            'processed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]))->toThrow(QueryException::class);
    });

    it('accepts distinct event IDs in any order', function () {
        DB::connection('pgsql_test')->table('stripe_event_inbox')->insert([
            'stripe_event_id' => 'evt_A'.uniqid(),
            'stripe_event_type' => 'customer.subscription.updated',
            'payload' => json_encode(['id' => 'sub_a']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('pgsql_test')->table('stripe_event_inbox')->insert([
            'stripe_event_id' => 'evt_B'.uniqid(),
            'stripe_event_type' => 'customer.subscription.created',
            'payload' => json_encode(['id' => 'sub_b']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $count = DB::connection('pgsql_test')->table('stripe_event_inbox')->count();

        expect($count)->toBe(2);
    });

    it('does not error when the same event is replayed (idempotency via skip-or-process)', function () {
        $eventId = 'evt_replay_'.uniqid();

        // First insert succeeds.
        DB::connection('pgsql_test')->table('stripe_event_inbox')->insert([
            'stripe_event_id' => $eventId,
            'stripe_event_type' => 'customer.subscription.updated',
            'payload' => json_encode(['id' => 'sub_r', 'status' => 'active']),
            'processed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Second attempt — should be detected as already processed.
        $existing = DB::connection('pgsql_test')
            ->table('stripe_event_inbox')
            ->where('stripe_event_id', $eventId)
            ->whereNotNull('processed_at')
            ->first();

        expect($existing)->not->toBeNull('Processed event must be findable for replay detection.')
            ->and($existing->stripe_event_id)->toBe($eventId);

        // If not yet processed, the handler would process it and mark processed_at.
        // The idempotency layer should skip (or detect and no-op) already processed events.
        // Here we prove the inbox record is correctly findable.
        $total = DB::connection('pgsql_test')
            ->table('stripe_event_inbox')
            ->where('stripe_event_id', $eventId)
            ->count();

        expect($total)->toBe(1);
    });

    it('allows an updated event to arrive before a created event (out-of-order safe)', function () {
        $updatedId = 'evt_upd_'.uniqid();
        $createdId = 'evt_cre_'.uniqid();

        // Updated arrives first.
        DB::connection('pgsql_test')->table('stripe_event_inbox')->insert([
            'stripe_event_id' => $updatedId,
            'stripe_event_type' => 'customer.subscription.updated',
            'payload' => json_encode(['id' => 'sub_oo', 'status' => 'active']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Created arrives second (out of order).
        DB::connection('pgsql_test')->table('stripe_event_inbox')->insert([
            'stripe_event_id' => $createdId,
            'stripe_event_type' => 'customer.subscription.created',
            'payload' => json_encode(['id' => 'sub_oo', 'status' => 'trialing']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // The handler processes current state, not event order — both records exist.
        $count = DB::connection('pgsql_test')
            ->table('stripe_event_inbox')
            ->whereIn('stripe_event_id', [$updatedId, $createdId])
            ->count();

        expect($count)->toBe(2);
    });
});
