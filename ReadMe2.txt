Ceci est un Read me pour expliquer quelques points car l'intégration wordpress <-> wamp <-> angular c'est bien mais il faut modifier certains fichiers en local (en ligne tout est fait pour que ça fonctionne)

1 - Les nouveaux scripts :
	a - install_angular.cdm : Installation d'angular sert au tout debut
	
	b - generate_angular.cmd : Va générer le contenue du dossier "ui" qui affiche le paramétrage,
	
	c - launch_angular.cmd : Lance le client angular pour developper,
	
	d - generate_resa.cmd : Génère sur le bureau la version courrante de resa à installer,
	
2 - Problème et fichier a changer
	a - En local - avec launch_angular.cdm : (adresse angular http://localhost:4200) 
		Modifier angular/src/services/user.service.ts => variable "serverRESA"
		
	b - En local avec generate_angular.cmd : 
		À cause de wamp et de son host : http://localhost/wordpress/ la variable host prend que http://localhost/ ce qui marche pas.
		Pour faire marcher il faut utiliser les virtuals host pour que ça donne http://wordpress/
		
	c - En ligne - obligation de passer par "ui" et tout fonction
	