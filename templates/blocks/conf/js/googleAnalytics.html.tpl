{if $smarty.const._APP_CONTEXT === 'prod'}
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '{$smarty.const._GOOGLE_ANALYTICS_UA}']);
_gaq.push(['_setDomainName', '{$smarty.const._GOOGLE_ANALYTICS_DOMAIN}']);
_gaq.push(['_trackPageview']);

(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
{/if}
