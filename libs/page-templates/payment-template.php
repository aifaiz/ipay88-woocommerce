<?php /* Template Name: ipay Payment */ ?>
<?php
global $woocomerce;
$gateway = new Aics_ipay_gateway();
aics_processResponse();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Processing</title>
        <?php wp_head(); ?>
    </head>
    <body>
        <div class="container text-center">
            <h1>Processing</h1><hr>
            <p>Please wait while we redirect you to the payment site.</p>
        </div>
        <?php if(isset($_GET['oid']) && !empty($_GET['oid'])): $gateway->preparePayment($_GET['oid']); endif; ?>
        <?php wp_footer(); ?>
        <?php do_action('ipay_template'); ?>
  </body>
</html>