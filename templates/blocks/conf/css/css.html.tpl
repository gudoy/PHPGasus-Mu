{block name='css-app'}
<link rel="stylesheet" href="/public/css/app.css" />
<link rel="stylesheet" href="/public/css/{$smarty.const._APP_NAME}.css" />
<link rel="stylesheet" href="/public/css/app-320up.css" media="screen and (min-width:320px)" />
<link rel="stylesheet" href="/public/css/app-480up.css" media="screen and (min-width:480px)" />
<link rel="stylesheet" href="/public/css/app-960up.css" media="screen and (min-width:960px)" />
<link rel="stylesheet" href="/public/css/app-1280up.css" media="screen and (min-width:1280px)" />
{/block}{block name='css-specific'}{if $view.name}<link rel="stylesheet" href="/public/css/pages/{$view.name}.css" media="screen" />{/if}{/block}
