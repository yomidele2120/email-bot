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
</script>
</body>
</html>
