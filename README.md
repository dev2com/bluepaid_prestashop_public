# Bluepaid multi payments

## About

Accept payments by credit card for your products via bluepaid.


### Requirements

Contributors **must** follow the following rules:

* **Make your Pull Request on the "dev" branch**, NOT the "master" branch.
* Do not update the module's version number.
* Follow [the coding standards][1].

### Process in details

Contributors wishing to edit a module's files should follow the following process:

1. Create your GitHub account, if you do not have one already.
2. Fork the bluepaidmulti project to your GitHub account.
3. Clone your fork to your local machine in the ```/modules``` directory of your PrestaShop installation.
4. Create a branch in your local clone of the module for your changes.
5. Change the files in your branch. Be sure to follow [the coding standards][1]!
6. Push your changed branch to your fork in your GitHub account.
7. Create a pull request for your changes **on the _'dev'_ branch** of the module's project. Be sure to follow [the commit message norm][2] in your pull request. If you need help to make a pull request, read the [Github help page about creating pull requests][3].
8. Wait for one of the core developers either to include your change in the codebase, or to comment on possible improvements you should make to your code.

That's it: you have contributed to this open-source project! Congratulations!

[1]: http://doc.prestashop.com/display/PS16/Coding+Standards
[2]: http://doc.prestashop.com/display/PS16/How+to+write+a+commit+message
[3]: https://help.github.com/articles/using-pull-requests



***INSTALLATION*****
Depuis votre interface Prestashop : 
Onglet Modules
=> Cliquer sur "Ajouter un nouveau module"
=> Cliquer sur "Parcourir", s�lectionner le module t�l�charg� (bluepaid.zip) sur votre ordinateur puis cliquer sur "Charger le module"
=> Section paiement
=> Cliquer sur le bouton "Installer" en face du module Bluepaid
=> Une fois install� (Module install�)
	=> Cliquer sur Configurer
	=> Identifiant de votre compte d'encaissement : Fourni par Bluepaid (diff�rent du num�ro de client, se trouve
													sur votre espace client Bluepaid apr�s avoir s�lectionn� 
													votre compte d'encaissement (Identifiant))
	=> Valider la mise � jour

*** PAIEMENTS RECURRENTS ****
	Cocher la case "Autoriser les paiements en X fois"
	Autoriser les paiements en X fois d�s ...   ## Indiquer ici le montant minimum � partir duquel vos clients seront autoris�s � payer en plusieurs fois ##
	Proposer le paiement en ... ## Indiquer ici le nombre de pr�l�vements � effectuer (3 pour 3 fois, 4 pour 4 fois, [...])
	Montant initial... ## Indiquer ici le montant qui sera pr�lev� lors de la premi�re transaction (en % du montant total de la commande ou en Euros) ##
	Nombre de pr�sentations si Ko ## Indiquer ici le nombre de re-pr�sentations � effectuer si l'un des pr�l�vements est refus� ##
	

	

************************************************
********** ESPACE CLIENT BLUEPAID **************
************************************************
MISE A JOUR DES URLS DE RETOUR APRES TRANSACTION
	URL REFERENTE => Indiquez l'Url de votre site Internet
	Url du logo => Indiquez l'adresse o� se trouve votre logo sur votre serveur. Ce logo sera affich� sur la page de paiement Bluepaid
	Url de confirmation => url_de_votre_site/modules/bluepaid/confirmOf.php
	
!! ATTENTION !! Pour des raisons de s�curit�, il est fortement conseill� de modifier le nom du fichier de confirmation (/modules/bluepaid/confirmOf.php) et de reporter le nouveau nom sur votre espace client Bluepaid. Par exemple, /modules/bluepaid/confirmOf.php deviendra /modules/bluepaid/fzeevgfre_monfichierdeconfirmation_fzse.php
	


	

