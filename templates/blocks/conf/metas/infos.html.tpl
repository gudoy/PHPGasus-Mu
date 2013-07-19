<meta name="description" content="{$view.description|default:$smarty.const._APP_DESCRIPTION}" />
<meta name="author" content="{$view.author|default:$smarty.const._APP_AUTHOR}" />
<meta name="keywords" content="{$view.keywords|default:$smarty.const._APP_KEYWORDS}" />
{if $smarty.const._APP_CONTEXT !== 'prod'}
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
<meta name="robots" content="noindex,nofollow,noarchive,noimageindex" />
{/if}