# bluepaid_prestashop_public
Module de paiement bluepaid pour prestashop


***INSTALLATION*****
D�zipper le fichier bluepaid
Envoyer l'int�gralit� du dossier bluepaid par FTP sur votre serveur
dans le dossier prestashop/modules/

Depuis votre interface Prestashop : 
Onglet Modules
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
	Url du logo => Indiquez l'adresse o� se trouve votre logo sur votre serveur. Ce logo sera affich� sur la page de paiement Bleuapid
	Url de retour apr�s transaction => url_de_votre_site/modules/bluepaid/payment_return.php
	Url de retour si clic sur retour boutique sans validation de la page de paiement => url_de_votre_site/order.php
	Url de confirmation => url_de_votre_site/modules/bluepaid/confirmOf.php
	
!! ATTENTION !! Pour des raisons de s�curit�, il est fortement conseill� de modifier le nom du fichier de confirmation (/modules/bluepaid/confirmOf.php) et de reporter le nouveau nom sur votre espace client Bluepaid. Par exemple, /modules/bluepaid/confirmOf.php deviendra /modules/bluepaid/fzeevgfre_monfichierdeconfirmation_fzse.php
	
	
V2.8
- Ajout d'un champ de configuation pemrttant un acc�s restreint aux serveurs appelant le fhcier de confirmation de commande
- Acc�es restreint aux appels en m�thode POST pour l'url de confirmation.
	


