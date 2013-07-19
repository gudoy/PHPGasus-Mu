<!DOCTYPE html>
<html id="{$view.id|default:$view.name}Page" class="no-js {$view.name}Page loading" role="application">
<head>
<meta charset="utf-8" />
{if $view.title}<title>{$view.title|default:$smarty.const._APP_NAME}</title>{/if}

{include file='blocks/conf/metas/infos.html.tpl'}

{block name='meta-compatibility'}{include file='blocks/conf/metas/compatibility.html.tpl'}{/block}

{block name='stylesheets'}{include file='blocks/conf/css/css.html.tpl'}{/block}

{block name='meta-icons'}{include file='blocks/conf/metas/icons.html.tpl'}{/block}

{block name='head-js'}{/block}
</head>
<body>
{block name='page'}
	<div id="{$view.name}" class="layout page {$view.id} current" role="window">
		{block name='header'}{include file='blocks/header/header.html.tpl'}{/block}
		{block name='body'}{include file='blocks/body/body.html.tpl'}{/block}
		{block name='footer'}{include file='blocks/footer/footer.html.tpl'}{/block}
	</div>
{/block}
{block name='javascripts'}{include file='blocks/conf/js/js.html.tpl'}{/block}
</body>
</html>