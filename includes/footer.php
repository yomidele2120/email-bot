        </main>
    </div>
</div>
<script>
const acctDd = document.getElementById('accountDropdown');
if (acctDd) {
    const acctTrigger = document.getElementById('accountTrigger');
    acctTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        acctDd.classList.toggle('open');
    });
    document.addEventListener('click', (e) => {
        if (!acctDd.contains(e.target)) acctDd.classList.remove('open');
    });
}

const menuTrigger = document.getElementById('menuTrigger');
const sidebarEl = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const sidebarClose = document.getElementById('sidebarClose');
if (menuTrigger && sidebarEl && sidebarOverlay) {
    const openMenu = () => {
        sidebarEl.classList.add('open');
        sidebarOverlay.classList.add('open');
        menuTrigger.setAttribute('aria-expanded', 'true');
        document.body.classList.add('no-scroll');
    };
    const closeMenu = () => {
        sidebarEl.classList.remove('open');
        sidebarOverlay.classList.remove('open');
        menuTrigger.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('no-scroll');
    };
    menuTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebarEl.classList.contains('open') ? closeMenu() : openMenu();
    });
    sidebarOverlay.addEventListener('click', closeMenu);
    if (sidebarClose) sidebarClose.addEventListener('click', closeMenu);
    // Close the drawer after tapping a nav link
    sidebarEl.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
    // Close on resize back to desktop
    window.addEventListener('resize', () => { if (window.innerWidth > 720) closeMenu(); });
}
</script>
</body>
</html>
