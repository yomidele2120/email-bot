<?php
// includes/ads.php
// Manual ad-unit placeholder. With just the Auto Ads script in <head> (no
// slot ID configured), Google places ads automatically and this renders
// nothing. Set ADSENSE_SLOT_TOOL once you create a manual ad unit in
// AdSense if you want a guaranteed slot at this exact spot instead.
if (\App\Ads::enabledForCurrentUser() && \App\Ads::toolSlotId()):
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
