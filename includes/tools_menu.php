<?php
// includes/tools_menu.php
// Shared "Tools" nested dropdown: category -> flyout of tools.
// Used by both the landing page nav and the public tool page header.
?>
<div class="landing-nav-links">
    <a href="/#features">Features</a>
    <div class="mega-dropdown" id="toolsMegaDropdown">
        <button type="button" class="nav-dropdown-trigger" id="toolsMegaTrigger">
            Tools
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="mega-dropdown-panel">
            <div class="mega-category">
                <span class="mega-category-label">Archive Files</span>
                <div class="mega-flyout">
                    <a href="/tools_archive.php?mode=zip" class="mega-item">Extract ZIP</a>
                    <a href="/tools_archive.php?mode=tar" class="mega-item">Extract TAR</a>
                    <a href="/tools_archive.php?mode=targz" class="mega-item">Extract TAR.GZ</a>
                    <a href="/tools_archive.php?mode=jar" class="mega-item">Extract JAR</a>
                    <span class="mega-item disabled">Extract RAR <em>Soon</em></span>
                    <span class="mega-item disabled">Extract 7Z <em>Soon</em></span>
                    <span class="mega-item disabled">Extract ZIPX <em>Soon</em></span>
                </div>
            </div>
            <div class="mega-category">
                <span class="mega-category-label">Convert Archive Formats</span>
                <div class="mega-flyout">
                    <a href="/tools_archive.php?mode=tar" class="mega-item">TAR to ZIP</a>
                    <a href="/tools_archive.php?mode=targz" class="mega-item">TAR.GZ to ZIP</a>
                    <span class="mega-item disabled">RAR to ZIP <em>Soon</em></span>
                    <span class="mega-item disabled">7Z to ZIP <em>Soon</em></span>
                    <span class="mega-item disabled">TAR.BZ2 to ZIP <em>Soon</em></span>
                    <span class="mega-item disabled">TAR.XZ to ZIP <em>Soon</em></span>
                </div>
            </div>
            <div class="mega-category">
                <span class="mega-category-label">File Utilities</span>
                <div class="mega-flyout">
                    <a href="/tools_share.php" class="mega-item">Share Files</a>
                    <a href="/tools_shortener.php" class="mega-item">URL Shortener</a>
                    <span class="mega-item disabled">Merge Archives <em>Soon</em></span>
                    <span class="mega-item disabled">Split / Multipart ZIP <em>Soon</em></span>
                </div>
            </div>
            <div class="mega-category">
                <span class="mega-category-label">Email &amp; Contacts</span>
                <div class="mega-flyout">
                    <a href="/tools_qr.php" class="mega-item">QR Code Generator</a>
                    <a href="/tools_email_verify.php" class="mega-item">Email List Verifier</a>
                    <a href="/register.php" class="mega-item">Contact Cleanup <em>Sign in</em></a>
                    <a href="/register.php" class="mega-item">Email Campaigns <em>Sign in</em></a>
                </div>
            </div>
            <div class="mega-category">
                <span class="mega-category-label">Legal &amp; Docs</span>
                <div class="mega-flyout">
                    <a href="/tools_policy_generator.php" class="mega-item">Privacy Policy Generator</a>
                    <a href="/tools_policy_generator.php" class="mega-item">Terms of Service Generator</a>
                </div>
            </div>
            <div class="mega-category">
                <span class="mega-category-label">Disc &amp; System Images</span>
                <div class="mega-flyout">
                    <span class="mega-item disabled">Open ISO <em>Soon</em></span>
                    <span class="mega-item disabled">Open VMDK <em>Soon</em></span>
                    <span class="mega-item disabled">Open DMG <em>Soon</em></span>
                    <span class="mega-item disabled">Open VHD <em>Soon</em></span>
                </div>
            </div>
            <div class="mega-category">
                <span class="mega-category-label">Apps &amp; Packages</span>
                <div class="mega-flyout">
                    <span class="mega-item disabled">Open APK <em>Soon</em></span>
                    <span class="mega-item disabled">Open IPA <em>Soon</em></span>
                    <span class="mega-item disabled">Open EXE <em>Soon</em></span>
                    <span class="mega-item disabled">Open DEB <em>Soon</em></span>
                </div>
            </div>
        </div>
    </div>
</div>
