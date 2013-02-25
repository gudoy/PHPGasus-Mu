<div class="body" id="body">
	{block name='aside'}{include file='blocks/aside/aside.html.tpl'}{/block}
	{block name='main'}
	<div class="main" id="main">
		<header class="header mainHeader" id="mainHeader">
			{block name='mainHeader'}{/block}
		</header>
		<div class="mainContent" id="mainContent">
			{block name='mainContent'}{/block}
		</div>
		<div class="mainFooter" id="mainFooter">{block name='mainFooter'}{/block}</div>
	</div>
	{/block}
</div>