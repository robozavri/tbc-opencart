<?php
class ControllerPaymentTbc extends Controller {

  public function index() {

    $this->language->load('payment/tbc');
    $data['button_confirm'] = $this->language->get('button_confirm');

    $data['action'] = HTTP_SERVER.'index.php?route=payment/tbc/checkout';

    $this->load->model('checkout/order');
    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

 
    if ($order_info){

      $data['orderid'] = date('His') . $this->session->data['order_id'];
      $data['callbackurl'] = $this->url->link('payment/custom/callback');
      $data['orderdate'] = date('YmdHis');
      $data['currency'] = $order_info['currency_code'];
      $data['orderamount'] = $this->currency->format($order_info['total'], $data['currency'] , false, false);
      $data['billemail'] = $order_info['email'];
      $data['billphone'] = html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
      $data['billaddress'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
      $data['billcountry'] = html_entity_decode($order_info['payment_iso_code_2'], ENT_QUOTES, 'UTF-8');
      $data['billprovince'] = html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8');;
      $data['billcity'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
      $data['billpost'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
      $data['deliveryname'] = html_entity_decode($order_info['shipping_firstname'] . $order_info['shipping_lastname'], ENT_QUOTES, 'UTF-8');
      $data['deliveryaddress'] = html_entity_decode($order_info['shipping_address_1'], ENT_QUOTES, 'UTF-8');
      $data['deliverycity'] = html_entity_decode($order_info['shipping_city'], ENT_QUOTES, 'UTF-8');
      $data['deliverycountry'] = html_entity_decode($order_info['shipping_iso_code_2'], ENT_QUOTES, 'UTF-8');
      $data['deliveryprovince'] = html_entity_decode($order_info['shipping_zone'], ENT_QUOTES, 'UTF-8');
      $data['deliveryemail'] = $order_info['email'];
      $data['deliveryphone'] = html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
      $data['deliverypost'] = html_entity_decode($order_info['shipping_postcode'], ENT_QUOTES, 'UTF-8');
      $data['client_ip_addr'] = html_entity_decode($order_info['ip'], ENT_QUOTES, 'UTF-8');

      echo $this->load->view('payment/tbc.tpl', $data);
    }
  }

	private function parse_result( $string )
	{
		$array1 = explode( PHP_EOL, trim( $string ) );
//      print_r($array1[0]); die;
		$result = array();
		foreach( $array1 as $key => $value )
		{
			$array2 = explode( ':', $value  );
          //print_r($array2); die;
			$result[ $array2[0] ] = trim( $array2[1] );
		}

		return $result;
	}

        
  public function checkout(){

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

          $this->load->model('setting/setting');
          // გამოვიტანოთ ადმინკიდან დაყენებული პარამეტრები
          $resultat = $this->model_setting_setting->getSetting('tbc');
          // print_r($resultat); die;

          // შემოწმებები მოხდება სესიიდან აღებული ორდერის id ის მიხედვით ან
          // ამ პოსტიდან მიღებული orderid ის მიხედვით
      //  $data['orderid'] = (int) $this->request->post['orderid'];
//       $order_id =  trim( substr( ((int)$this->request->post['orderid']), 6) );
       $order_id =  substr((trim($this->request->post['orderid'])), 6);
//       $order_id = trim(substr($this->request->post['orderid'] , 6));
       $order_id = ((int)$order_id);
//       echo  '<br>'.$order_id;
       if(empty($order_id)){
//            die('order id carielia');
           $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
           return;
       }
//       die('<br>'.$order_id);
        // გამოვიტანოთ ორდერი და შევამოთმოთ სუყველაფერ და მერე გავუშვატ ტრანზაქცია
      $this->load->model('checkout/order');
      $order_info = $this->model_checkout_order->getOrder($order_id);
		//შემოწმდეს არსებობს თუ არა ასეთი ორდერი, ნუ ტუ შედეგი დააბრუნა ესეიგი არსებობს
        if(empty($order_info)){
//          die('ორდერი ან არ მოსულა ან ორდერის ცვლადი ცარიელია');
          //თუ ასეთი ორდერი არ არსებობს გადავამისამართოთ თავისი ექაუნთში
            $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
            return;
        }

        if($_SERVER["REMOTE_ADDR"] != $order_info['ip']){
          $this->response->redirect($this->url->link('account/account', '', 'SSL'));
          return;
        }
            //გამოვყოთ ორდერის სტატუსის იდენთიფიკატორი
            $tbc_order_status_id = $resultat['tbc_order_status_id'];
            $currency = $order_info['currency_code'];
            $client_ip_addr = $order_info['ip'];
            //ბაზაში განვაახლოთ ორდერის სტატუსის იდენტიფიკატორი რათა ადინკაში ორდერი გამოაჩინოს
            //(იმისთვის რომ გამოაჩინოს order_status_id 0 - 16 შორის რომელიმე უნდა უყოს მაგრამ არა 0)
          //  $this->db->query("UPDATE tb_order SET order_status_id = $tbc_order_status_id WHERE  order_id = $order_id");
            $this->db->query("UPDATE ".DB_PREFIX."order SET order_status_id = $tbc_order_status_id WHERE  order_id = $order_id");

          //   echo 100 * sprintf("%0.2f",$order_info['total']) .'<hr>';
          //   echo floatval(sprintf("%0.2f",$order_info['total'])).'<hr>';
          //  echo   $currency.' = '.$order_info['currency_code'] .'  - this i  currency<hr>';
          //  echo $amount.' = '.$order_info['total'].' this<hr>';
          //  echo sprintf("%0.2f",$amount).' = '.sprintf("%0.2f",$order_info['total']);
          //  die('<br>aq vart ?');
  /*sprintf("%0.2f",$amount) == sprintf("%0.2f",$order_info['total']) &&*/

              switch ($currency) {
                case 'GEL':  $currency_code = 981;
                  break;
                case 'USD':  $currency_code = 840;
                  break;
                  default:
                   $currency_code = 981;
                    break;
              }

              // echo $amount = 100 * sprintf("%0.2f",$order_info['total']);
              $amount = 100 * sprintf("%0.2f",$order_info['total']);
              
              $post_fields = array(
                'command'        => 'v', // identifies a request for transaction registration
                'amount'         => $amount,
                'currency'       => $currency_code,
                'client_ip_addr' => $client_ip_addr,
                'description'    => '',
                'language'       => ''
              );
            
               $resp = $this->sendTransData( http_build_query( $post_fields ) );
//              $resp = 'TRANSACTION_ID: bAt6JLX52DUbibbzD9gDFl5P=983';

              //Check the answer
      if (substr($resp,0,14) == "TRANSACTION_ID"){

      //  $trans_id = urlencode(substr($resp,16,28));
        $trans_id = substr($resp,16,28);
        $customer_id = $order_info['customer_id'];
        $stans_status = 'opened';
        $amount = sprintf("%0.2f",$order_info['total']);
        //$trans_id = strip_tags($trans_id, '<br>');
        $tablename = DB_PREFIX."transactions";
        $sql = "
        INSERT INTO `$tablename`
        (`order_id`,`transaction_id`,`customer_id`,`amount`, `currency`, `client_ip`)
        values('$order_id','$trans_id','$customer_id','$amount','$currency','$client_ip_addr')";

        $this->db->query($sql);
        $this->session->data['trans_id'] = $trans_id;
        $this->model_checkout_order->addOrderHistory($order_id, 2,'TBC: transaction opened successful');
          
            $dataText['trans_id'] = $trans_id;
            echo $this->load->view('payment/tbcRedirect.tpl',$dataText);
          
          
      }else{
                 $this->write_in_log('open transaction error');
        $this->db->query("UPDATE ".DB_PREFIX."order SET order_status_id = 10 WHERE  order_id = $order_id");
          $this->model_checkout_order->addOrderHistory($order_id, 10, 'TBC: transaction open error');
         $this->failed();

      }
       
      return;
    } 
      
         if (!empty($_SERVER['HTTP_REFERER'])){
                  header("Location: ".$_SERVER['HTTP_REFERER']);
              }else{
                  header("Location: ".'http://'.$_SERVER['SERVER_NAME']);
              }
  }


public function sendTransData($query_string){

  $this->load->model('setting/setting');
  // გამოვიტანოთ ადმინკიდან დაყენებული პარამეტრები
  $resultat = $this->model_setting_setting->getSetting('tbc');
    if(empty($resultat)){
        return;
    }
//    die;
//  echo $resultat['tbc_cert_path'].' - cert path<br>';
//  echo $resultat['tbc_Cert_passphrase'].' - tbc_Cert_passphrase<br>';
//  echo $resultat['tbc_Merchant_url'].' - tbc_Merchant_url<br>';
//    print_r($resultat); die;
//    $myfile = fopen($resultat['tbc_cert_path'], "r") or die("Unable to open file!");
//  	$respons = fread($myfile,filesize($resultat['tbc_cert_path']));
//  	fclose($myfile);
//     print_r($respons);
//  	 die;

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
          $this->write_in_log('function-sendTransData|CURL_ERROR:|'.curl_error($curl).'|ERROR_NUMBER:|'.curl_errno( $curl ));

       }
       return $result;

}
    
public function getTransResult($query_string){

  $this->load->model('setting/setting');
  // გამოვიტანოთ ადმინკიდან დაყენებული პარამეტრები
  $resultat = $this->model_setting_setting->getSetting('tbc');
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
       $this->write_in_log('function-getTransResult|CURL_ERROR:|'.curl_error($curl).'|ERROR_NUMBER:|'.curl_errno( $curl ));

    }
    return $result;
}

    
    
  public function successful(){
    
    $this->language->load('payment/tbc');
    $dataText['text_trans_was_succsessful'] = $this->language->get('text_trans_was_succsessful');
      
   if($this->request->server['REQUEST_METHOD'] != 'POST'){
       $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));  
//       echo $this->load->view('payment/successful.tpl',$dataText);
         return;
   }
      
     if(empty($this->session->data['trans_id'])){
          $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));  
//         echo $this->load->view('payment/successful.tpl',$dataText);
         return;
     }

  	 $trans_id = $this->session->data['trans_id'];
       $post_fields = array(
       'command'        => 'c', // identifies a request for transaction registration
       'trans_id'       => $trans_id,
       'client_ip_addr' => $_SERVER["REMOTE_ADDR"],
       );

//      $resultString = 'RESULT: OK RESULT_CODE: 000 3DSECURE: ATTEMPTED RRN: 622611211657 APPROVAL_CODE: 149517 CARD_NUMBER: 5***********62873 = ATTEMPTED RRN:';

      $resultString = $this->getTransResult(http_build_query($post_fields));

      $pushed = preg_split('[ ]', $resultString);

        if($pushed[1] == 'OK'){
              $status = 'FINISHED';
        }else{
           $status = $pushed[1].' - '.$pushed[3];
        }
//        echo  $status;
//      echo '<br>1 = '.$pushed[1].'<br>';
//        echo '3 = '.$pushed[3].'<br>';
//        echo '<pre>';
//        print_r($pushed);
//        echo '</pre>';
//      echo '<br>check_trans_result_code() = '.$this->check_trans_result_code($pushed[1]).'<br>';
//      die;
          $tablename = DB_PREFIX."transactions";
          $sql = "
          UPDATE `$tablename` SET `status`= '$status'
          WHERE `transaction_id` = '$trans_id'
          ";
          $this->db->query($sql);

          ;
          if(!empty($this->session->data['order_id'])){
              $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 5, 'TBC: '.$status);
          }

       $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));  
//   echo $this->load->view('payment/successful.tpl',$dataText);
     $this->cart->clear();
    //$this->response->redirect($this->url->link('account/order', '', 'SSL'));
}

public function failed($message = ''){

    $this->language->load('payment/tbc');
    $dataText['text_trans_was_succsessful'] = $this->language->get('text_trans_was_succsessful');
    $dataText['text_failpayment'] = $this->language->get('text_failpayment');
    $dataText['text_warning'] = $this->language->get('text_warning');
    $dataText['text_redirect_alert'] = $this->language->get('text_redirect_alert');
    $dataText['text_back_to_accaunt'] = $this->language->get('text_back_to_accaunt');
    
   if($this->request->server['REQUEST_METHOD'] != 'POST'){
      $dataText['redirectUrl'] = $this->url->link('account/account');
       echo $this->load->view('payment/failed.tpl',$dataText);
         return;
   }
      
     if(empty($this->session->data['trans_id'])){
         $dataText['redirectUrl'] = $this->url->link('account/account');
         echo $this->load->view('payment/failed.tpl',$dataText);
         return;
     }

  $trans_id = $this->session->data['trans_id'];
    
  $post_fields = array(
    'command'        => 'c', // identifies a request for transaction registration
    'trans_id'       => $trans_id,
    'client_ip_addr' => $_SERVER["REMOTE_ADDR"]
    );
    
//   $resultString = 'RESULT: OK RESULT_CODE: 000 3DSECURE: ATTEMPTED RRN: 622611211657 APPROVAL_CODE: 149517 CARD_NUMBER: 5***********62873 = ATTEMPTED RRN:';
  $resultString = $this->getTransResult(http_build_query($post_fields));
  $pushed = preg_split('[ ]', $resultString);

      $status = $pushed[1].' - '.$pushed[3];

     $this->write_in_log('function-filed|'.$resultString);
    
    $tablename = DB_PREFIX."transactions";
    $sql = "
      UPDATE `$tablename` SET `status`= '$status'
      WHERE `transaction_id` = '$trans_id'
    ";
    $this->db->query($sql);
    if(!empty($this->session->data['order_id'])){
        
        $this->load->model('checkout/order');
        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 10, 'TBC: '.$status);
        
    }
      $dataText['redirectUrl'] = $this->url->link('account/account');
       echo $this->load->view('payment/failed.tpl',$dataText);
}

    
  public function close_day()
  {
    $post_fields = array(
      'command'         => 'b'
    );

     $result = $this->sendTransData( $post_fields );
     $this->write_in_log('function-close_day|'.$result);
     return;
  }
  
    public function write_in_log($message = ''){

        $file = 'catalog/tbc.log';
        $log = 'data-time['.date("d F Y H:i:s").']['.$message.']ip=['.$_SERVER["REMOTE_ADDR"].']'."\r\n";
        file_put_contents($file, $log,FILE_APPEND);

    }

  public function check_trans_result_code($result = ''){

      $this->language->load('payment/tbc');

            switch ($result) {
              case 'OK':
                return 'ტრანზაქცია წარმატებით დასრულდა';
                break;
              case 'FAILED':
                return 'ტრანზაქცია ვერ შედგა';
                break;
              case 'error':
                return 'დაფიქსირდა შეცდომა';
                break;
              case 'CREATED':
                return 'ტრანზაქცია დარეგისტრირდა სისტემაში';
                break;
              case 'PENDING':
                return 'ტრანზაქციის შესრულება გრძელდება';
                break;
              case 'DECLINED':
                return 'ტრანზაქცია უარყოფილია სისტემის მიერ';
                break;
              case 'REVERSED':
                return 'ტრანზაქცია დაბრუნებულია';
                break;
              case 'AUTOREVERSED':
                return 'ტრანზაქცია დაბრუნებულია ავტორევერსალის მიერ';
                break;
              case 'TIMEOUT':
                return 'ტრანზაქცია ვადაგასულია';
                break;
              case 'FINISHED':
                return 'გადახდა წარმატებით დასრულდა';
                break;
              case 'CANCELLED':
                return 'გადახდა უარყოფილია';
                break;
              case 'RETURNED':
                return 'გადახდა დაბრუნებულია';
                break;
              case 'ACTIVE':
                return 'გადახდა რეგისტრირებულია მაგრამ არ არის დასრულებული';
                break;

        }

  }

    
/*
public function sendTransData($query_string){

  $path = getcwd().'/cert/cert.pem';

  $curl = curl_init('https://securepay.ufc.ge:18443/ecomm2/MerchantHandler');
  //echo $path ;die;
  curl_setopt($curl, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($curl, CURLOPT_POST, TRUE);
  curl_setopt($curl, CURLOPT_VERBOSE,        1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//2
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);//1
  curl_setopt($curl, CURLOPT_CAINFO, $path); //this need because of Self-Signed certificate at payment server.
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_SSLCERT, $path);
  curl_setopt($curl, CURLOPT_SSLKEY,  $path);
  curl_setopt($curl, CURLOPT_SSLKEYPASSWD, 'KHGdti67e5jrysk');

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
*/


}
?>
