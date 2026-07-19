<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\TrialGate;

$loggedIn = Auth::check();
$results = [];
$showPaywall = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!TrialGate::check('verify')) {
        $showPaywall = true;
    } else {
        $raw = $_POST['emails'] ?? '';
        $lines = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $raw)));

        foreach (array_unique($lines) as $email) {
            $validSyntax = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            $hasMx = false;

            if ($validSyntax) {
                $domain = substr(strrchr($email, '@'), 1);
                $hasMx = checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
            }

            $results[] = [
                'email' => $email,
                'valid' => $validSyntax && $hasMx,
                'reason' => !$validSyntax ? 'Invalid format' : (!$hasMx ? 'Domain has no mail server' : 'Looks valid'),
            ];
        }
    }
}

$validCount = count(array_filter($results, fn($r) => $r['valid']));

$pageTitle = 'Email Verifier';
$activeNav = 'tools_verify';
require __DIR__ . '/../includes/' . ($loggedIn ? 'header.php' : 'public_header.php');
?>

<?php if ($loggedIn): ?><p><a href="/tools.php" style="color:var(--text-muted);font-size:13px">← Tools</a></p><?php endif; ?>
<h1>Email List Verifier</h1>
<p style="color:var(--text-muted)">Paste emails, one per line or comma-separated. Checks format and whether the domain can actually receive mail.</p>
<?php if (!$loggedIn): ?>
    <p style="color:var(--text-muted);font-size:13px">Free to try <strong class="mono"><?= TrialGate::usesLeft('verify') ?></strong> more time(s) without an account.</p>
<?php endif; ?>

<form method="POST" class="tool-panel" style="max-width:520px">
    <label style="margin-top:20px">Emails</label>
    <textarea name="emails" rows="8" placeholder="jane@example.com&#10;john@company.com"><?= htmlspecialchars($_POST['emails'] ?? '') ?></textarea>
    <button type="submit">Verify</button>
</form>

<?php if ($results): ?>
    <h2 style="margin-top:36px"><?= $validCount ?> of <?= count($results) ?> valid</h2>
    <table>
        <thead><tr><th>Email</th><th>Status</th><th>Detail</th></tr></thead>
        <tbody>
        <?php foreach ($results as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td>
                    <?php if ($r['valid']): ?>
                        <span class="badge badge-completed">Valid</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Invalid</span>
                    <?php endif; ?>
                </td>
                <td style="color:var(--text-muted)"><?= htmlspecialchars($r['reason']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../includes/' . ($loggedIn ? 'footer.php' : 'public_footer.php'); ?>
