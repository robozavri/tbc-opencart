<?php
class ModelPaymentTbc extends Model {

  public function getMethod($address, $total) {
    $this->load->language('payment/tbc');

    $method_data = array(
      'code'     => 'tbc',
      'title'    => $this->language->get('text_title'),
      'terms'    => $this->language->get('terms'),
      'sort_order' => $this->config->get('tbc_sort_order')
    );

    return $method_data;
  }



}
