    </main>

    <div class="modal-overlay" id="paywallModal">
        <div class="modal-box">
            <div class="modal-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0969da" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <h3>You've used your 3 free tries</h3>
            <p>Create a free account to keep using this tool, plus unlock campaigns, contacts, and everything else.</p>
            <div class="modal-actions">
                <a href="/register.php" class="btn" style="margin-top:0">Create free account</a>
                <button type="button" class="btn btn-secondary" style="margin-top:0" onclick="document.getElementById('paywallModal').classList.remove('open')">Maybe later</button>
            </div>
        </div>
    </div>

<script>
// Tools mega-dropdown: click to toggle (works on touch), hover works via CSS on desktop
(function() {
    const dd = document.getElementById('toolsMegaDropdown');
    if (!dd) return;
    const trigger = document.getElementById('toolsMegaTrigger');
    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        dd.classList.toggle('open');
    });
    document.addEventListener('click', (e) => {
        if (!dd.contains(e.target)) dd.classList.remove('open');
    });
})();

function openPaywall() {
    const m = document.getElementById('paywallModal');
    if (m) m.classList.add('open');
}

<?php if (!empty($showPaywall)): ?>
document.addEventListener('DOMContentLoaded', openPaywall);
<?php endif; ?>
</script>

</body>
</html>
