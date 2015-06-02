<?php
/**
 * Bluepaid payment
 *
 * Accept payment by CB with Bluepaid.
 *
 * @class 		Bluepaid
 * @version		2.1
 * @category	Payment
 * @author 		Bluepaid - Julien L.
 */
global $_MODULE;
$_MODULE = array();
$_MODULE['<{bluepaid}prestashop>bluepaid_0981c28797684ede5114ae410997ffc5'] = 'Bluepaid';
$_MODULE['<{bluepaid}prestashop>bluepaid_0c70853cdce55048ea5a9d3ace56b5dd'] = 'Accepte les paiements par \"Bluepaid\"';
$_MODULE['<{bluepaid}prestashop>bluepaid_41ef095800141e52c62fa58b93a16640'] = 'ID marchant, la clé de cryptage et la catégorie doivent être configurer pour que le module fonctionne correctement.';
$_MODULE['<{bluepaid}prestashop>bluepaid_47e13d684c88d911f4cee0f589fdd349'] = 'L\'ID de compte d\'encaissement Bluepaid est requis';
$_MODULE['<{bluepaid}prestashop>bluepaid_d2633fbd326a2dde82a0556be3a78529'] = 'L\'ID de compte d\'encaissmeent Bluepaid doit être positif';
$_MODULE['<{bluepaid}prestashop>bluepaid_7ecf63c7d5bcc0efdfdc53bf96518be1'] = 'La clée de cryptage bluepaid est requise ';
$_MODULE['<{bluepaid}prestashop>bluepaid_25e21a75f506069d0ac0bac80a657c05'] = 'La catégorie bluepaid est requise';
$_MODULE['<{bluepaid}prestashop>bluepaid_9ae3d6d5a5a9761b552ba1d228a83fa9'] = 'bluepaid \"le nombre de jours de livraison\" est nécessaire et doit être un nombre.';
$_MODULE['<{bluepaid}prestashop>bluepaid_740e53a0ed1b643f69c4aaa8ad4ae8d2'] = 'Code transporteur invalide';
$_MODULE['<{bluepaid}prestashop>bluepaid_5031699c0d8a6f79406ec389b8ecc899'] = 'Code catégorie invalide';
$_MODULE['<{bluepaid}prestashop>bluepaid_39e9cff403e3f1cf395371bcb5f4a853'] = 'Vous avez sélectionné environnement de test : bluepaid n\'est pas en relation avec votre banque. Les clients sont en mesure de payer, mais vous ne pourrez pas recevoir l\'argent.';
$_MODULE['<{bluepaid}prestashop>bluepaid_3a6c8dc6c8f292d5206d2e59b1ccdd99'] = 'Gérez vos paiements dans votre interface d\'administration bluepaid';
$_MODULE['<{bluepaid}prestashop>bluepaid_a0af5fc4859cbe09a6221762142f2bac'] = 'Votre interface d\'administration';
$_MODULE['<{bluepaid}prestashop>bluepaid_993f23bce4541f562142ced7dbabd827'] = 'L\'interface d\'administration bluepaid vous permet de gérer vos paiements: le suivi, l\'annulation, le remboursement';
$_MODULE['<{bluepaid}prestashop>bluepaid_080d3ce779d939f18af2ca1e499c2d4d'] = 'URL de retour';
$_MODULE['<{bluepaid}prestashop>bluepaid_dce1bc5479ecf6e0309b1a5b96f61412'] = 'Ce module a été developpé par BLUEPAID INVEST ';
$_MODULE['<{bluepaid}prestashop>bluepaid_6df4dad510fb08e2e6df44b53cb2ce29'] = 'Reportez tous vos bugs à';
$_MODULE['<{bluepaid}prestashop>bluepaid_d575acff7e1035a4212d2a53d5a8c115'] = 'ou utiliser notre';
$_MODULE['<{bluepaid}prestashop>bluepaid_23372c0d3713719764670087006fc1b6'] = 'formulaire de contact';
$_MODULE['<{bluepaid}prestashop>bluepaid_f4d1ea475eaa85102e2b4e6d95da84bd'] = 'Confirmation';
$_MODULE['<{bluepaid}prestashop>bluepaid_c888438d14855d7d96a2724ee9c306bd'] = 'mettre a jour la configuration';
$_MODULE['<{bluepaid}prestashop>bluepaid_6357d3551190ec7e79371a8570121d3a'] = 'Il y a';
$_MODULE['<{bluepaid}prestashop>bluepaid_4ce81305b7edb043d0a7a5c75cab17d0'] = 'Il y a';
$_MODULE['<{bluepaid}prestashop>bluepaid_07213a0161f52846ab198be103b5ab43'] = 'erreurs';
$_MODULE['<{bluepaid}prestashop>bluepaid_cb5e100e5a9a3e7f6d1fd97512215282'] = 'erreur';
$_MODULE['<{bluepaid}prestashop>bluepaid_3879149292f9af4469cec013785d6dfd'] = 'avertissements';
$_MODULE['<{bluepaid}prestashop>bluepaid_7b83d3f08fa392b79e3f553b585971cd'] = 'avertissement';
$_MODULE['<{bluepaid}prestashop>bluepaid_f114313d45e351341a5dcaffdb529231'] = 'Paramètres \"bluepaid\"';
$_MODULE['<{bluepaid}prestashop>bluepaid_02e4e63c6051721bf5a5268071cdd749'] = 'Les paramètres suivants vous ont été fournis par bluepaid';
$_MODULE['<{bluepaid}prestashop>bluepaid_af7c2efe81330e4c9089f2c90781282e'] = 'Si vous n\'êtes pas encore inscrit, cliquez';
$_MODULE['<{bluepaid}prestashop>bluepaid_6c92285fa6d3e827b198d120ea3ac674'] = 'ici';
$_MODULE['<{bluepaid}prestashop>bluepaid_756d97bb256b8580d4d71ee0c547804e'] = 'Production';
$_MODULE['<{bluepaid}prestashop>bluepaid_83fa8195169a729ffba378061c26ba32'] = '(Dans ce mode, vous recevrez des paiements réels)';
$_MODULE['<{bluepaid}prestashop>bluepaid_0cbc6611f5540bd0809a388dc95a615b'] = 'Test';
$_MODULE['<{bluepaid}prestashop>bluepaid_a22b3c8b0dd28c62e9f2ed241be3222a'] = '(ce mode vous permet de tester si ce module bluepaid fonctionne bien, mais vous ne pourrez pas recevoir de paiements)';
$_MODULE['<{bluepaid}prestashop>bluepaid_236b8cb0e15350adccdf224ae12e5a29'] = 'ID marchand';
$_MODULE['<{bluepaid}prestashop>bluepaid_8402cc7e72a5bc3b4f59206add9b1cfa'] = 'Clé de cryptage';
$_MODULE['<{bluepaid}prestashop>bluepaid_6d4e3994ce06bdb293f1c82d744e7f13'] = 'Type de paiement acceptés';
$_MODULE['<{bluepaid}prestashop>bluepaid_582aa4ffe30fca188bd3a77453084e97'] = 'Comptant';
$_MODULE['<{bluepaid}prestashop>bluepaid_6fdcab538bf433ea39be984abfae60f4'] = 'Crédit';
$_MODULE['<{bluepaid}prestashop>bluepaid_eb6d8ae6f20283755b339c0dc273988b'] = 'Standard';
$_MODULE['<{bluepaid}prestashop>bluepaid_6e47536a9324c3ecaff918a434be3deb'] = 'Delais de livraison';
$_MODULE['<{bluepaid}prestashop>bluepaid_44fdec47036f482b68b748f9d786801b'] = 'jours';
$_MODULE['<{bluepaid}prestashop>bluepaid_b02b464fbe0b40f0c481f17894f66747'] = 'Compte test';
$_MODULE['<{bluepaid}prestashop>bluepaid_2704815788ff7e7fb8be8abe242ce493'] = 'Informations sur les produits vendus sur votre boutique';
$_MODULE['<{bluepaid}prestashop>bluepaid_1eb6855c3061c998c6cf8b25a4a04b03'] = 'Pour mieux vous accompagner, bluepaid a besoin de savoir quels sont les principaux types de produits que vous vendez';
$_MODULE['<{bluepaid}prestashop>bluepaid_7791022694bf889f0b72d52bdac04285'] = 'Type de produit principal';
$_MODULE['<{bluepaid}prestashop>bluepaid_93390930550e0b8fa85206312ba938ce'] = 'Choisissez un type ...';
$_MODULE['<{bluepaid}prestashop>bluepaid_5f001485711e47385cb84b10c888bbfd'] = 'Configuration du transporteur';
$_MODULE['<{bluepaid}prestashop>bluepaid_f29f3f689a8a652a58669f60ab68aa50'] = 'Merci de sélectionner un type de chacun des transporteurs utilisés sur votre boutique';
$_MODULE['<{bluepaid}prestashop>bluepaid_84a4ee79ed85180a743340dceb2f98d1'] = 'Détail du transporteur';
$_MODULE['<{bluepaid}prestashop>bluepaid_914419aa32f04011357d3b604a86d7eb'] = 'Transporteur';
$_MODULE['<{bluepaid}prestashop>bluepaid_fecb07f7ab46e23e9b520a9a9af7b97b'] = 'Type de transporteur';
$_MODULE['<{bluepaid}prestashop>bluepaid_b04ad61ad875f6e7149fdd51c2e60ce2'] = 'Selectionner un type de transporteur';
$_MODULE['<{bluepaid}prestashop>bluepaid_b17f3f4dcf653a5776792498a9b44d6a'] = 'Mise à jour configuration';
$_MODULE['<{bluepaid}prestashop>bluepaid_aefb81b982d000f15054504940959c12'] = 'bluepaid en 1 fois par CB. C’est simple, pratique et sécurisé.';
$_MODULE['<{bluepaid}prestashop>bluepaid_d59048f21fd887ad520398ce677be586'] = 'En savoir plus ...';
$_MODULE['<{bluepaid}prestashop>bluepaid_137da31377cde78c4995c2078e885f7e'] = 'bluepaid en 1 fois par CB, service Paiement après réception inclus.';
$_MODULE['<{bluepaid}prestashop>bluepaid_3d1de2c4abeeaaefe2ca1e9c2e3ff5d9'] = 'bluepaid en plusieurs fois, service Paiement après réception inclus.';
$_MODULE['<{bluepaid}prestashop>payment_return_ef03a4272cd62d20212923051465d3ad'] = 'paiement annulé';
$_MODULE['<{bluepaid}prestashop>payment_return_e3c2c6d512df5a014b3af6369803d174'] = 'le contrôle de hachage n\'est pas spécifiée';
