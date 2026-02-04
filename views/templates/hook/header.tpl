{**
 * BestAuto Meta Conversions API Header Template
 *}

{if isset($bestauto_event_id)}
<script type="text/javascript">
    // Deduplication logic for Facebook Pixel
    // This variable should be used by the already installed Pixel module
    var fb_event_id = '{$bestauto_event_id|escape:'javascript':'UTF-8'}';
    
    // If the site uses a custom JS event to trigger Pixel, we can hook into it
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof fbq !== 'undefined') {
            // Example: fbq('track', 'ViewContent', { ... }, {eventID: fb_event_id});
            // Since Pixel is already installed, we hope it picks up the global variable or we can try to re-trigger it if needed.
            // However, usually, we just need to provide the eventID to the existing call.
        }
    });
</script>
{/if}
