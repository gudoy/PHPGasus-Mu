{block name='js-libs'}<script src="public/js/libs/modernizr/modernizr.custom.js"></script>
<script>document.write('<script src=public/js/libs/frameworks/' + ('__proto__' in {} ? 'zepto' : 'jquery' + ( navigator.userAgent.match(/MSIE [678]/) ? '-1.X' : '' )) + '.min.js><\/script>')</script>
<script>if ( typeof Zepto === 'undefined' && typeof jQuery !== 'undefined' ){ var Zepto = jQuery; }</script>
<script>document.write('<script src=public/js/libs/polyfills/history/' + ('__proto__' in {} ? 'zepto' : 'jquery') + '.history.js><\/script>')</script>
<script src="public/js/libs/frameworks/zepto.ghostclick.min.js"></script>
{/block}{block name='js-app'}<script src="public/js/app.js"></script>
<script src="public/js/{$smarty.const._APP_NAME}.js"></script>
{if $view.id}<script src="public/js/pages/{$view.id}.js"></script>{/if}
{/block}
{block name='js-init'}<script>$(document).ready(function(){ app.init(); })</script>{/block}

{block name='js-ga'}{include file='blocks/conf/js/googleAnalytics.html.tpl'}{/block}