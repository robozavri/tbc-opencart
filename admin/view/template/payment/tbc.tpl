<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="content">
  <div class="box">

    <div class="content">
	<div class="container-fluid">
	<div class="row">
  <div class="col-md-8">

    <div class="heading">
      <h1><img src="view/image/payment/tbc.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
	  <a onclick="$('#form').submit();" class="btn btn-primary"><?php echo $button_save; ?></a>
	  <a href="<?php echo $cancel; ?>" class="btn btn-danger"><?php echo $button_cancel; ?></a>
	  </div>
    </div>
	<br><br>
      <form class="form-inline" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form table">
          <tr>
            <td><?php echo $entry_order_status; ?></td>
            <td><select name="tbc_order_status_id" class="form-control">
                <?php foreach ($order_statuses as $one_order_status) { ?>
                  <?php if ($one_order_status['order_status_id'] == $tbc_order_status_id) { ?>
                  <option value="<?php echo $one_order_status['order_status_id']; ?>" selected="selected"><?php echo $one_order_status['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $one_order_status['order_status_id']; ?>"><?php echo $one_order_status['name']; ?></option>
                  <?php } ?>
                <?php } //end foreach ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><select class="form-control" name="tbc_status">
                <?php if ($tbc_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $text_tbc_Cert_passphrase; ?></td>
            <td><input type="text" class="form-control" name="tbc_Cert_passphrase" value="<?php echo $tbc_Cert_passphrase;?>" placeholder="tbc certificate passphrase"></td>
          </tr>
          <tr>
            <td><?php echo $text_tbc_cert_path; ?></td>
            <td><input type="text" class="form-control" name="tbc_cert_path" value="<?php echo $tbc_cert_path;?>" placeholder="certificate full path"> <?php echo $text_tbc_cert_path_exemple; ?></td>
          </tr>
          <tr>
            <td><?php echo $text_tbc_ok_url; ?></td>
            <td><?php  echo 'http://'.$_SERVER['SERVER_NAME'].'/index.php?route=payment/tbc/successful';?></td>
          </tr>
          <tr>
            <td><?php echo $text_tbc_failed_url; ?></td>
            <td><?php  echo 'http://'.$_SERVER['SERVER_NAME'].'/index.php?route=payment/tbc/failed';?></td>
          </tr>
        </table>
      </form>
	  </div>
   </div>
</div>

    </div>
  </div>
</div>
<?php echo $footer; ?>
