<?php
require '../YiiepApi.php';
$payLink = '#';
$failMessage = '';

// 1 - Créer la facture
$billId = 'FACT0000001';
$billValue = 200;
$billCurrency = 'XOF';
/*Supported Currencies
XAF	=> CFA CEMAC
XOF	=> CFA UEMOA
NGN	=> Nigerian Naira
GHS	=> Ghana Cedis
*/

if (!empty($_POST)){
  $billId = $_POST['billid'];
  $billValue = $_POST['billval'];
  $billCurrency = $_POST['billcrcy'];
}

// 2 - Sauvegarder la facture dans la base locale
/* 
	Do database stuffs here 
*/

// 3 - Créer l'objet YiiepApi
$testMode = true; //Mettre a false pour passer en production
$YiiepApi = new \Yiiep\YiiepApi($testMode);
$apiId = $YiiepApi->getId();

// 4 - Initier le payement
if($YiiepApi->presetBill($billId, $billValue, $billCurrency)){
	
	// 5 - Récupérer les infos de payement
	$payInfo = $YiiepApi->data();
	
	// 6 - Récupérer l'ID de payement Yiiep de votre facture
	$billHash = $payInfo['billhash'];
	
	// 6 - Créer le lien de payement
	$payLink = $YiiepApi->payLink($billHash, 'btn btn-lg btn-primary');
	
	// 7 - Créer le QR code de payement
  $payQR = $YiiepApi->payQR($billHash, 'img-thumbnail');
  
  // 8 - Vérifier l'état d'une facture
  $billStateError = '';
  if($YiiepApi->checkBill($billHash)){
    $billState = $YiiepApi->data();
  }else{
    $billStateError = $YiiepApi->message();
  }
	
}else{
	// 5 - 
	$failMessage = $YiiepApi->message();
};

?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="../../favicon.ico">

        <title>Yiiep Simple Exemple</title>

        <!-- Bootstrap core CSS -->
        <link href="assets/bootstrap.min.css" rel="stylesheet">

        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <link href="assets/ie10-viewport-bug-workaround.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link href="assets/navbar.css" rel="stylesheet">

        <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
        <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
        <script src="assets/ie-emulation-modes-warning.js"></script>

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    </head>

    <body>

        <div class="container">

            <!-- Static navbar -->
            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="#">Yiiep Exemple</a>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav">
                            <li class="https://yiiep.com" target="_blank"><a href="#">Home</a></li>
                            <li><a href="#">About</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                            <li><a href="https://yiiep.com/webapi/" target="_blank">Obtenir un identifiant d'API</a></li>
                            <li><a href="https://yiiep.com/webapi/download" target="_blank">Téléchargez l'API Yiiep</a></li>
                        </ul>
                    </div>
                    <!--/.nav-collapse -->
                </div>
                <!--/.container-fluid -->
            </nav>


            <div class="alert alert-info">
                <h3>Identifian d'API </h3>
                <small>Editez le fichier config pour définir l'id et la clé d'API</small>
                <p>
                    <b><?php echo $apiId ?></b>
                </p>

                <h3>Facture Test</h3>
                <form method="post" class="form">
                    <div class="form-group form-group-sm">
                        <label for="billid">ID Facture</label>
                        <input type="text" class="form-control" id="billid" name="billid" placeholder="ID Facture" value="<?php echo $billId ?>">
                    </div>
                    <div class="form-group form-group-sm">
                        <label for="billval">Montant</label>
                        <input type="text" class="form-control" id="billval" name="billval" placeholder="Montant" value="<?php echo $billValue ?>">
                    </div>
                    <div class="form-group form-group-sm">
                        <label for="billcrcy">Devise (XOF | XAF | NGN | GHS)</label>
                        <input type="text" class="form-control" id="billcrcy" name="billcrcy" placeholder="Devise" value="<?php echo $billCurrency ?>">
                    </div>
                    <button type="submit" class="btn btn-sm btn-success">Tester</button>
                </form>
            </div>

            <?php if($failMessage != '' ){ ?>
                <div class="alert alert-danger">
                    <h3>Erreur =  [ <?php echo $failMessage ?> ]</h3>
                    <p></p>
                    <p></p>
                    <h4>Description des erreurs</h4>
                    <ul>
                        <li><b>Invalid Node </b> : Veuillez utiliser un identifiant et une clé d'API valide</li>
                        <li><b>Bill Exists </b> : Facture déjá enregistré pour paiement</li>
                        <li><b>Yiiep Server Error </b> : Erreur interne de la plateforme Yiiep</li>
                        <li><b>Invalid Source IP </b> : Votre Adresse IP ne correspond pas a votre identifiant d'API</li>
                        <li><b>Invalid Source Hostname </b> : Votre URL ne correspond pas a votre identifiant d'API</li>
                        <li><b>Request Timed Out </b> : Votre Adresse IP ne correspond pas a votre identifiant d'API</li>
                    </ul>
                </div>

                <?php }else{ ?>

                    <div class="alert alert-success">
                        <b>Succès !</b>
                        <h3>Id Paiement Yiiep : <b><?php echo $billHash ?></b></h3>
                        <h3>Lien de paiement</h3>
                        <p>
                            <?php echo $payLink ?>
                        </p>
                        <h3>QRCode de paiement </h3>
                        <p>
                            <?php echo $payQR ?>
                        </p>
                        <h3>Etat de la facture </h3>
                        <p>
                            <?php if($billStateError == '' ){ ?>
                                <?php echo print_r($billState); ?>
                                    <?php }else{ ?>
                                        <?php echo $billStateError; ?>
                                            <?php } ?>
                        </p>

                    </div>

                    <?php } ?>

        </div>
        <!-- /container -->


        <!-- Bootstrap core JavaScript
    ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="assets/jquery.min.js"></script>
        <script>
            window.jQuery || document.write('<script src="assets/jquery.min.js"><\/script>')
        </script>
        <script src="assets/bootstrap.min.js"></script>
        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <script src="assets/ie10-viewport-bug-workaround.js"></script>
    </body>

    </html>