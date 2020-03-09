SET directory="%~dp0"
SET distDir=%UserProfile%\Desktop\resa-online\

cd angular && ng build --prod --output-hashing=none --build-optimizer --deploy-url /wp-content/plugins/resa-online/ui/ && cd .. && call rename.cmd

pause;
