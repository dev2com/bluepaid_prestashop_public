# bluepaid_prestashop_public
Module de paiement bluepaid pour prestashop


***INSTALLATION*****
Dézipper le fichier bluepaid
Envoyer l'intégralité du dossier bluepaid par FTP sur votre serveur
dans le dossier prestashop/modules/

Depuis votre interface Prestashop : 
Onglet Modules
=> Section paiement
=> Cliquer sur le bouton "Installer" en face du module Bluepaid
=> Une fois installé (Module installé)
	=> Cliquer sur Configurer
	=> Identifiant de votre compte d'encaissement : Fourni par Bluepaid (différent du numéro de client, se trouve
													sur votre espace client Bluepaid après avoir sélectionné 
													votre compte d'encaissement (Identifiant))
	=> Valider la mise à jour

*** PAIEMENTS RECURRENTS ****
	Cocher la case "Autoriser les paiements en X fois"
	Autoriser les paiements en X fois dès ...   ## Indiquer ici le montant minimum à partir duquel vos clients seront autorisés à payer en plusieurs fois ##
	Proposer le paiement en ... ## Indiquer ici le nombre de prélèvements à effectuer (3 pour 3 fois, 4 pour 4 fois, [...])
	Montant initial... ## Indiquer ici le montant qui sera prélevé lors de la première transaction (en % du montant total de la commande ou en Euros) ##
	Nombre de présentations si Ko ## Indiquer ici le nombre de re-présentations à effectuer si l'un des prélèvements est refusé ##
	

	

************************************************
********** ESPACE CLIENT BLUEPAID **************
************************************************
MISE A JOUR DES URLS DE RETOUR APRES TRANSACTION
	URL REFERENTE => Indiquez l'Url de votre site Internet
	Url du logo => Indiquez l'adresse où se trouve votre logo sur votre serveur. Ce logo sera affiché sur la page de paiement Bleuapid
	Url de retour après transaction => url_de_votre_site/modules/bluepaid/payment_return.php
	Url de retour si clic sur retour boutique sans validation de la page de paiement => url_de_votre_site/order.php
	Url de confirmation => url_de_votre_site/modules/bluepaid/confirmOf.php
	
!! ATTENTION !! Pour des raisons de sécurité, il est fortement conseillé de modifier le nom du fichier de confirmation (/modules/bluepaid/confirmOf.php) et de reporter le nouveau nom sur votre espace client Bluepaid. Par exemple, /modules/bluepaid/confirmOf.php deviendra /modules/bluepaid/fzeevgfre_monfichierdeconfirmation_fzse.php
	
	
V2.8
- Ajout d'un champ de configuation pemrttant un accès restreint aux serveurs appelant le fhcier de confirmation de commande
- Accèes restreint aux appels en méthode POST pour l'url de confirmation.
	


