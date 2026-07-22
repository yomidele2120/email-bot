<?php
// includes/ads.php
if (\App\Ads::enabledForCurrentUser()):
?>
<div class="ad-slot">
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="<?= htmlspecialchars(\App\Ads::clientId()) ?>"
         data-ad-slot="<?= htmlspecialchars(\App\Ads::toolSlotId()) ?>"
         data-ad-format="auto"
         data-full-width-responsive="true"></ins>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>
<?php endif; ?>