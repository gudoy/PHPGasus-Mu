{$menuItems = [
	'group1' => [
		'label' 	=> 'Group 1',
		'url' 		=> '#',
		'children' 	=> [
			'group1item1' => [
			'label' 	=> 'item 1.1',
			'url' 		=> '#'
			]
		]
	],
	'group2' => [
		'label' 	=> 'Group 2',
		'url' 		=> '#'
	]
]}
<nav id="mainNav" class="main mainNav" role="navigation">
	{$level = 0}
	<ul class="itemsLv{$level+1}">
		{foreach $menuItems as $key => $item}
		{include file='blocks/header/mainNavItem.html.tpl' level=$level+1}
		{/foreach}
	</ul>
</nav>