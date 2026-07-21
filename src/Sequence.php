<?php
namespace App;

class Sequence
{
    /**
     * @param array $contactIds  Which contacts this sequence targets, every run.
     * @param array $steps       [['subject' => ..., 'template_html' => ...], ...] in send order.
     */
    public static function create(int $userId, string $name, int $intervalDays, array $contactIds, array $steps): int
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare(
            "INSERT INTO sequences (user_id, name, interval_days, contact_ids, status, current_step, next_run_date)
             VALUES (:uid, :name, :interval, :contacts, 'active', 0, CURDATE())"
        );
        $stmt->execute([
            ':uid' => $userId,
            ':name' => $name,
            ':interval' => $intervalDays,
            ':contacts' => json_encode(array_values(array_map('intval', $contactIds))),
        ]);
        $sequenceId = (int)$pdo->lastInsertId();

        $stepStmt = $pdo->prepare(
            "INSERT INTO sequence_steps (sequence_id, subject, template_html, step_order) VALUES (:sid, :subj, :html, :ord)"
        );
        foreach (array_values($steps) as $i => $step) {
            $stepStmt->execute([
                ':sid' => $sequenceId,
                ':subj' => $step['subject'],
                ':html' => $step['template_html'],
                ':ord' => $i,
            ]);
        }

        return $sequenceId;
    }

    public static function allForUser(int $userId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            "SELECT s.*,
                (SELECT COUNT(*) FROM sequence_steps st WHERE st.sequence_id = s.id) AS step_count,
                (SELECT COUNT(*) FROM campaigns c WHERE c.sequence_id = s.id) AS runs_so_far
             FROM sequences s
             WHERE s.user_id = :uid
             ORDER BY s.created_at DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function stepsFor(int $sequenceId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM sequence_steps WHERE sequence_id = :id ORDER BY step_order ASC");
        $stmt->execute([':id' => $sequenceId]);
        return $stmt->fetchAll();
    }

    public static function setStatus(int $sequenceId, int $userId, string $status): void
    {
        $pdo = Database::connect();
        $pdo->prepare("UPDATE sequences SET status = :status WHERE id = :id AND user_id = :uid")
            ->execute([':status' => $status, ':id' => $sequenceId, ':uid' => $userId]);
    }

    /**
     * Called once a day by the sequence-runner cron. Finds every active sequence
     * that's due, fires its current step as a fresh campaign, advances to the
     * next step (looping back to the start automatically), and reschedules.
     */
    public static function runDueSequences(): int
    {
        $pdo = Database::connect();
        $due = $pdo->query("SELECT * FROM sequences WHERE status = 'active' AND next_run_date <= CURDATE()")->fetchAll();

        $processed = 0;

        foreach ($due as $seq) {
            $steps = self::stepsFor((int)$seq['id']);
            if (empty($steps)) {
                continue;
            }

            $stepIndex = ((int)$seq['current_step']) % count($steps);
            $step = $steps[$stepIndex];
            $contactIds = json_decode($seq['contact_ids'], true) ?: [];

            $campaignId = Campaign::create((int)$seq['user_id'], $step['subject'], $step['template_html'], (int)$seq['id']);
            Campaign::queueSelected($campaignId, $contactIds);

            $pdo->prepare(
                "UPDATE sequences SET current_step = current_step + 1, next_run_date = DATE_ADD(CURDATE(), INTERVAL :interval DAY) WHERE id = :id"
            )->execute([':interval' => (int)$seq['interval_days'], ':id' => $seq['id']]);

            $processed++;
        }

        return $processed;
    }
}
