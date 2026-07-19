<?php
// includes/tool_header.php
// Expects $toolTitle (plain string) and optionally $toolDesc (HTML-safe string, already escaped/formatted by caller).
$currentUrl = rtrim($_ENV['APP_URL'] ?? '', '/') . ($_SERVER['REQUEST_URI'] ?? '/');
$encodedUrl = urlencode($currentUrl);
?>
<div class="tool-tab-bar">
    <div class="tool-tab-active"><?= htmlspecialchars($toolTitle) ?></div>
    <div class="tool-share-icons">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>" target="_blank" rel="noopener" class="share-icon" title="Share on Facebook">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12"/></svg>
        </a>
        <a href="https://twitter.com/intent/tweet?url=<?= $encodedUrl ?>" target="_blank" rel="noopener" class="share-icon" title="Share on X">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2H22l-7.6 8.7L23.3 22h-7l-5.5-6.8L4.5 22H1.4l8.1-9.3L1 2h7.2l5 6.3zM17.6 20h1.7L7 3.9H5.2z"/></svg>
        </a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $encodedUrl ?>" target="_blank" rel="noopener" class="share-icon" title="Share on LinkedIn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9h4v12H3zm7 0h3.8v1.7h.05c.53-1 1.83-2 3.77-2 4.03 0 4.78 2.65 4.78 6.1V21h-4v-5.6c0-1.35-.02-3.1-1.89-3.1-1.9 0-2.19 1.48-2.19 3v5.7h-4z"/></svg>
        </a>
        <button type="button" class="share-icon" title="Copy link" onclick="navigator.clipboard.writeText('<?= addslashes($currentUrl) ?>'); this.title='Copied!'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        </button>
    </div>
</div>
<h1><?= htmlspecialchars($toolTitle) ?></h1>
<?php if (!empty($toolDesc)): ?><p class="tool-desc" style="color:var(--text-muted)"><?= $toolDesc ?></p><?php endif; ?>
