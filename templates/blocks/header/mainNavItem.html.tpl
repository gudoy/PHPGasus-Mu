<li class="item navItem lv{$level} {$key}NavItem" id="{$key}NavItem">
	<a class="action lv{$level}Action {$key}NavAction" id="{$key}NavAction" href="{$item.url|default:"#{$key}NavGroup"}"><span class="label">{$item.label}</span></a>
	{if $item.children && $level < 2}
	<div class="itemsLv{$level+1}Container" id="itemsLv{$level+1}Container">
		<ul class="itemsLv{$level+1}" id="{$key}NavGroup">
		{$children = $item.children}
		{foreach $children as $key => $item}
		{include file='blocks/header/mainNavItem.html.tpl' level=$level+1}
		{/foreach}
		</ul>
	</div>
	{/if}
</li>