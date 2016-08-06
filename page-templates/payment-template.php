<?php /* Template Name: Payment */ ?>
<?php
//echo'<pre>'.print_r($_REQUEST, true).'</pre>';
//exit;
global $woocomerce;
$gateway = new Faiz_ipay_gateway();
processResponse();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Processing</title>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
        <!--[if lt IE 9]>
        <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <?php
        // for debugging
        /* if(isset($responsed) && $responsed == 'ok'): ?>
            <pre><?php print_r($response); ?></pre>
        <?php endif; */
        ?>
        <div class="container text-center">
            <h1>Processing</h1><hr>
            <p>Please wait while we redirect you to the payment site.</p>
        </div>
        <?php if(isset($_GET['oid']) && !empty($_GET['oid'])): $gateway->preparePayment($_GET['oid']); endif; ?>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <script>
        $(document).ready(function(){
            //$('#ipaysubmitForm').submit();
        });
        </script>
  </body>
</html>