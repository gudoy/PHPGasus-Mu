<header id="header" class="header" role="banner">
	<header id="headerHeader" class="header headerHeader">{block name='headerHeader'}{include file='blocks/header/branding.html.tpl'}{/block}</header>
	<div id="headerContent" class="content headerContent">{block name='headerContent'}
		{include file='blocks/header/userNav.html.tpl'}
		{include file='blocks/header/mainNav.html.tpl'}
	{/block}</div>
	<footer id="headerFooter" class="footer headerFooter">{block name='headerFooter'}{/block}</footer>
</header>