<!DOCTYPE html>
<html class="no-js {$view.name}Page loading" id="{$view.name}Page">
<head>
<meta charset="utf-8" />
{if $view.title}<title>{$view.title}</title>{/if}
	
    
{include file='blocks/conf/meta/infos.html.tpl'}

<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
<meta name="HandheldFriendly" content="True" />
<meta name="MobileOptimized" content="320" />
<meta http-equiv="cleartype" content="on" />
    
{block name='meta-apps'}{include file='blocks/conf/meta/apps.html.tpl'}{/block}
	
{block name='stylesheets'}{include file='blocks/conf/css/css.html.tpl'}{/block}

{block name='meta-icons'}{include file='blocks/conf/meta/icons.html.tpl'}{/block}

{block name='head-js'}{include file='blocks/conf/css/css.html.tpl'}{/block}

</head>
<body>
{block name='page'}
	<div class="layout page {$view.name} current" id="{$view.name}">
	
		{block name='header'}{include file='blocks/header/header.html.tpl'}{/block}
		{block name='body'}{include file='blocks/body/body.html.tpl'}{/block}
		{block name='footer'}{include file='blocks/footer/footer.html.tpl'}{/block}

	</div>
{/block}

{block name='javascripts'}{include file='blocks/conf/js/js.html.tpl'}{/block}
  	
</body>
</html>