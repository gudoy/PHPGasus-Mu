PHPGasus-Mu
===========

Lightweight version of PHPGasus with only the following modules:

PHPGasus\Core
PHPGasus\Request
PHPGasus\Response


INSTALLATION
-------------
1) Add the following rules to your vhost or in a .htaccess file in the root of your project folder 
```
# Active rewrite rules engine
RewriteEngine on

# (un)comment this to force redirect all requests from http to https  
#RewriteCond %{HTTPS} off
#RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI} [NC,R,L] # no case, force redirect, last rule 

# Redirect every request to index.php (request params in arguments)
RewriteRule ^((?!index|public|favicon\.ico).*) index.php/$1/%{QUERY_STRING}	[L]
```

2) Extract the files in the root of your project folder


USAGE
-----------------





LICENSE
-----------------
MIT license