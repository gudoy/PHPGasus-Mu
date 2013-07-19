<li  id="{$key}NavItem" class="item navItem lv{$level} {$key}NavItem">
	<a id="{$key}NavAction" class="action lv{$level}Action {$key}NavAction" href="{$item.url|default:"#{$key}NavGroup"}"><span class="label">{$item.label}</span></a>
	{if $item.children && $level < 2}
	<div id="itemsLv{$level+1}Container" class="itemsLv{$level+1}Container">
		<ul id="{$key}NavGroup" class="itemsLv{$level+1}">
		{$children = $item.children}
		{foreach $children as $key => $item}
		{include file='blocks/header/mainNavItem.html.tpl' level=$level+1}
		{/foreach}
		</ul>
	</div>
	{/if}
</li>