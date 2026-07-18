<?php
require __DIR__ . '/../includes/bootstrap.php';

use App\Auth;
use App\Contact;

Auth::requireLogin();

$contacts = Contact::allForUser(Auth::id());

$pageTitle = 'Contacts';
$activeNav = 'contacts';
require __DIR__ . '/../includes/header.php';
?>

<h1>Contacts</h1>
<p style="color:var(--text-muted)">Everyone you've imported. Add more from the New Campaign page.</p>

<?php if (empty($contacts)): ?>
    <div class="empty-state">
        <h3>No contacts yet</h3>
        <p>Upload a CSV from the New Campaign page to add contacts.</p>
        <a href="/campaign_create.php" class="btn">New Campaign</a>
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Email</th><th>Name</th><th>Status</th><th>Added</th></tr>
        </thead>
        <tbody>
        <?php foreach ($contacts as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['name'] ?: '—') ?></td>
                <td>
                    <?php if ($c['unsubscribed']): ?>
                        <span class="badge badge-danger">Unsubscribed</span>
                    <?php else: ?>
                        <span class="badge badge-completed">Active</span>
                    <?php endif; ?>
                </td>
                <td style="color:var(--text-muted)"><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
