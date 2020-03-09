SET directory=%~dp0

cd ui
for %%f in (*.*) do (
	call ..\replace.bat "%%f" "assets" "../wp-content/plugins/resa-online/ui/assets"
)
rename "index.html" "index.php"
echo ^<?php if(!defined('ABSPATH')){ exit; /* Exit if accessed directly */ } ?^> > index.php.new
type index.php >> index.php.new
type index.php.new > index.php
del index.php.new
cd ..
