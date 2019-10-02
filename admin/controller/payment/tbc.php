<?php
class ControllerPaymentTbc extends Controller {
  
  private $error = array();

  public function index() {

    $this->language->load('payment/tbc');
    $this->document->setTitle('TBC Payment Method Configuration');
    $this->load->model('setting/setting');

    if ($this->request->server['REQUEST_METHOD'] == 'POST'){
        
        
       $data['tbc_order_status_id'] = (int) $this->request->post['tbc_order_status_id'];
       $data['tbc_status'] = $this->request->post['tbc_status'];
       $data['tbc_Cert_passphrase'] = $this->request->post['tbc_Cert_passphrase'];
       $data['tbc_cert_path'] = $this->request->post['tbc_cert_path'];
       $data['tbc_Merchant_url'] = 'https://securepay.ufc.ge:18443/ecomm2/MerchantHandler';
        
       $this->model_setting_setting->editSetting('tbc', $data);
       $this->session->data['success'] = 'Saved.';
       $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
}

if($this->request->server['REQUEST_METHOD'] == 'GET'){


    $this->is_tbc_table();

    $data['tbc_order_status_id'] = $this->config->get('tbc_order_status_id');
    $data['tbc_status'] = $this->config->get('tbc_status');

    $data['tbc_Cert_passphrase'] = $this->config->get('tbc_Cert_passphrase');
    $data['tbc_cert_path'] = $this->config->get('tbc_cert_path');
    $data['heading_title'] = $this->language->get('heading_title');
    $data['entry_closeday_text'] = $this->language->get('entry_closeday_text');
    $data['button_save'] = $this->language->get('text_button_save');
    $data['button_cancel'] = $this->language->get('text_button_cancel');
    $data['entry_order_status'] = $this->language->get('entry_order_status');
    $data['text_enabled'] = $this->language->get('text_enabled');
    $data['text_disabled'] = $this->language->get('text_disabled');
    $data['entry_status'] = $this->language->get('entry_status');

    $data['text_tbc_Cert_passphrase'] = $this->language->get('text_tbc_Cert_passphrase');
    $data['text_tbc_cert_path'] = $this->language->get('text_tbc_cert_path');
    $data['text_tbc_cert_path_exemple'] = $this->language->get('text_tbc_cert_path_exemple');
    $data['text_tbc_ok_url'] = $this->language->get('text_tbc_ok_url');
    $data['text_tbc_failed_url'] = $this->language->get('text_tbc_failed_url');

    $data['action'] = $this->url->link('payment/tbc', 'token=' . $this->session->data['token'], 'SSL');
    $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

    $this->load->model('localisation/order_status');
    $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
    $this->template = 'payment/tbc.tpl';

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('payment/tbc.tpl', $data));

    }

  } // end  index method

  public function is_tbc_table(){

      $tablename = DB_PREFIX."transactions";
      $dbname = DB_DATABASE;
      $sql = "
                CREATE TABLE IF NOT EXISTS `".DB_PREFIX."transactions` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `order_id` int(11) NOT NULL,
                  `transaction_id` varchar(255) NOT NULL,
                  `customer_id` int(11) NOT NULL,
                  `amount` decimal(10,2) NOT NULL,
                  `currency` varchar(10) NOT NULL,
                  `client_ip` varchar(200) NOT NULL,
                  `status` varchar(255) NOT NULL DEFAULT 'OPENED',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ";
      $result = $this->db->query("SHOW TABLES LIKE '".$tablename."'");

      if($result->num_rows){
          return false;
      }else{
        $this->db->query($sql);
          return;
      }

  }
    

public function return_transaction_amount(){

      if ($this->request->server['REQUEST_METHOD'] == 'POST'){

          $trans_id = $this->db->escape($this->request->post['trans_id']);
          $amount   = $this->db->escape($this->request->post['amount']);
          $order_id = (int)$this->db->escape($this->request->post['order_id']);
          $amount = 100 * sprintf("%0.2f",$amount);
          // die(  $trans_id.'<br>'.$amount.'<br>'.  $order_id);
          $post_fields = array(
    				'command'         => 'k', // identifies a request for transaction registration
    				'trans_id'        => $trans_id
    			);
$resultString = 'RESULT: OK RESULT_CODE: 345 REFUND_TRANS_ID: bAt6JLX52DUbibbzD9gDFl5P=322';

      //$resultString = $this->curl_sender( http_build_query( $post_fields ));
      $pushed = preg_split('[ ]', $resultString);
      $comment = 'TBC: unsuccessful transaction refund amount,  refund amount is FAILED';
      $order_status_id = 10;
      $succsess_order_status_id = 11;
      $notify = 0;
      $sucsess_refaund = 'TBC: transaction refaunded successfully';
      if (substr($resultString,0,5) == "error"){
            $pushed[1] = 'error';
      }
     $error =  'TBC: error';
     $tablename = DB_PREFIX."transactions";

      switch (trim($pushed[1])) {
        case 'OK':
        $this->db->query("UPDATE $tablename SET status = 'TRANSACTION AMOUNT REFUNDED' WHERE  order_id = $order_id");
        $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$succsess_order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($sucsess_refaund) . "', date_added = NOW()");
        $this->response->redirect($this->url->link('sale/order/info', 'token='.$this->session->data['token'].'&order_id='.$order_id, 'SSL'));
          break;
        case 'FAILED':
          $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
          $this->response->redirect($this->url->link('sale/order/info', 'token='.$this->session->data['token'].'&order_id='.$order_id, 'SSL'));
          break;
        case 'error':
          $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($error) . "', date_added = NOW()");
          $this->response->redirect($this->url->link('sale/order/info', 'token='.$this->session->data['token'].'&order_id='.$order_id, 'SSL'));
          break;
      }

    }else{
              if (!empty($_SERVER['HTTP_REFERER'])){
                  header("Location: ".$_SERVER['HTTP_REFERER']);
              }else{
                  header("Location: ".'http://'.$_SERVER['SERVER_NAME']);
              }
    }

}


public function reverse_transaction()
{

      if ($this->request->server['REQUEST_METHOD'] == 'POST'){

        $trans_id = $this->db->escape($this->request->post['trans_id']);
        $amount   = $this->db->escape($this->request->post['amount']);
        $order_id = (int)$this->db->escape($this->request->post['order_id']);
        $amount = 100 * sprintf("%0.2f",$amount);
          // die(  $trans_id.'<br>'.$amount.'<br>'.  $order_id);
      $post_fields = array(
        'command'         => 'r', // identifies a request for transaction registration
        'trans_id'        => $trans_id,
        'amount'          => $amount
      );

      $resultString = 'RESULT: OK RESULT_CODE: 345 REFUND_TRANS_ID: bAt6JLX52DUbibbzD9gDFl5P=322';
      // $resultString = 'error: OK RESULT_CODE: 345 REFUND_TRANS_ID: bAt6JLX52DUbibbzD9gDFl5P=322';

      //$resultString = $this->curl_sender( http_build_query( $post_fields ));
      $pushed = preg_split('[ ]', $resultString);
      $comment = 'TBC: reverse transaction is FAILED';
      $sucsess_comment = 'TBC: reverse transaction is successfully';
      $order_status_id = 10;
      $reversed_order_status_id = 12;
        if (substr($resultString,0,5) == "error"){
              $pushed[1] = 'error';
        }
      $error =  'TBC: error';
      $notify = 0;
      $reversed = 'TBC: The transaction has already been returned before';
      $sucsess_reversed = 'TBC: transaction returned successfully';
      $tablename = DB_PREFIX."transactions";

      switch (trim($pushed[1])) {
        case 'OK':
        $this->db->query("UPDATE $tablename SET status = 'RETURNED' WHERE  order_id = $order_id");
        $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$reversed_order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($sucsess_comment) . "', date_added = NOW()");
        $this->response->redirect($this->url->link('sale/order/info', 'token='.$this->session->data['token'].'&order_id='.$order_id, 'SSL'));
          break;
        case 'FAILED':
          $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
          $this->response->redirect($this->url->link('sale/order/info', 'token='.$this->session->data['token'].'&order_id='.$order_id, 'SSL'));
          break;
        case 'REVERSED':
        $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$reversed_order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($reversed) . "', date_added = NOW()");
        $this->response->redirect($this->url->link('sale/order/info', 'token='.$this->session->data['token'].'&order_id='.$order_id, 'SSL'));
          break;
        case 'error':
        $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($error) . "', date_added = NOW()");
        $this->response->redirect($this->url->link('sale/order/info', 'token='.$this->session->data['token'].'&order_id='.$order_id, 'SSL'));
          break;
      }

    }else{
              if (!empty($_SERVER['HTTP_REFERER'])){
                  header("Location: ".$_SERVER['HTTP_REFERER']);
              }else{
                  header("Location: ".'http://'.$_SERVER['SERVER_NAME']);
              }
    }

  }
    
    
public function curl_sender($query_string = ''){

    $this->load->model('setting/setting');
    // გამოვიტანოთ ადმინკიდან დაყენებული პარამეტრები
    $resultat = $this->model_setting_setting->getSetting('tbc');
    // $path = getcwd().'/cert/cert.pem';

    $curl = curl_init($resultat['tbc_Merchant_url']);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $query_string);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_VERBOSE,        1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//2
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);//1
    curl_setopt($curl, CURLOPT_CAINFO, $resultat['tbc_cert_path']); //this need because of Self-Signed certificate at payment server.
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSLCERT, $resultat['tbc_cert_path']);
    curl_setopt($curl, CURLOPT_SSLKEY,  $resultat['tbc_cert_path']);
    curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $resultat['tbc_Cert_passphrase']);

    $result = curl_exec($curl);
    if(curl_error($curl))
    {
        echo '<br>error:' . curl_error($curl).'<br><pre>';
        $info = curl_getinfo($curl);
        echo 'ERROR NUMBER = '.curl_errno( $curl ).'<br>';
        print_r($info);
        die;
    }
    return $result;

  }


  private function parse_result( $string )
  {
    $array1 = explode( PHP_EOL, trim( $string ) );
    $result = array();
    foreach( $array1 as $key => $value )
    {
      $array2 = explode( ':', $value  );
      $result[ $array2[0] ] = trim( $array2[1] );
    }

    return $result;
  }


} // end class
