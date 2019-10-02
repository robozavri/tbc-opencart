<link rel="stylesheet" href="catalog/view/theme/OPC080192_1/stylesheet/megnor/bootstrap.min.css">
 <div class="container-fluid">
  <h1></h1>
      <div class="row">
         <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="panel panel-danger">
                  <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $text_warning;?></h3>
                  </div>
                  <div class="panel-body">
                   <div class="well well-lg"><h1> <?php echo $text_failpayment; ?></h1></div>
                   <p><?php echo $text_redirect_alert;?></p>
                   <a href="<?php echo $redirectUrl?>" class="btn btn-success"><?php echo $text_back_to_accaunt;?></a>
                  </div>
                </div>
            </div>
        <div class="col-md-3"></div>
      </div>
</div>
<meta http-equiv="refresh" content="20;url=<?php echo $redirectUrl?>" />
