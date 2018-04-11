# yiiepapi-php
Yiiep payment plateform API for PHP

Yiiep est une plateforme de payement en ligne basée sur le mobile money. Cet API vous permet d'intégrer Yiiep dans votre site web comme solution de payement. L'api génére un qrcode que votre client peut scanner avec [l'appication mobile Yiiep](https://play.google.com/store/apps/details?id=com.numerumservices.yiiep) pour initier le payement.

Visitez [www.yiiep.com](https://www.yiiep.com/) pour en savoir plus.

## Installation
Cloner / Télécharger et déconpresser le répertoire yiiepapi-php dans votre projet.

## Obtenir un ID d'api pour votre site ou application
1. [Créer un compte](https://www.yiiep.com/login)
2. Enregistrez un site marchand

## Utilisation
Ci dessous un exemple d'utilisation de l'API. Une version fonctionnelle de cet exemple est disponible dans le dossier  [example](../../example).  Pour plus d'information veuillez consulter la documentation.

```php
include 'YiiepApi.php';

// 0 - Obtenir et définir un ID/KEY dans le fichier config.php

// 1 - Créer la facture
$billId = 'FACT0000001';
$billValue = 200;
/*Supported Currencies
XAF	=> CFA CEMAC
XOF	=> CFA UEMOA
NGN	=> Nigerian Naira
GHS	=> Ghana Cedis
*/

// 2 - Sauvegarder la facture dans la base locale
/* 
	Do database stuffs here 
*/

// 3 - Créer l'objet YiiepApi
$testMode = true; //Mettre a false pour passer en production
$YiiepApi = new \Yiiep\YiiepApi($testMode);

// Récupérer l'identifiant d'application
$apiId = $YiiepApi->getId();

// 4 - Initier le payement
if($YiiepApi->presetBill($billId, $billValue, $billCurrency)){
	
	// 5 - Récupérer les infos de payement
	$payInfo = $YiiepApi->data();
	
	// 6 - Récupérer l'ID de payement Yiiep de votre facture
	$billHash = $payInfo['billhash'];
	
	// 7' - Créer le lien de payement
	$payLink = $YiiepApi->payLink($billHash, 'btn btn-lg btn-primary');
	
	// 7'' - Créer le QR code de payement
	$payQR = $YiiepApi->payQR($billHash, 'img-thumbnail');
	
}else{
	// 5 - 
	$failMessage = $YiiepApi->message();
};

/*
Do html stuffs below
*/

```
## Credits
[Requests for PHP](http://requests.ryanmccue.info/)
