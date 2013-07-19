<div class="body" id="body">
	{block name='aside'}{include file='blocks/aside/aside.html.tpl'}{/block}
	{block name='main'}
	<div id="main" class="main"role="main">
		<header id="mainHeader" class="header mainHeader">{block name='mainHeader'}{/block}</header>
		<div id="mainContent" class="content mainContent">{block name='mainContent'}{/block}</div>
		<footer id="mainFooter" class="footer mainFooter">{block name='mainFooter'}{/block}</footer>
	</div>
	{/block}
</div>