<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class tinklit {
    var $code, $title, $description, $enabled;

    function tinklit() {
      global $order;

      $this->code = 'tinklit';
      $this->title = MODULE_PAYMENT_TINKLIT_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_TINKLIT_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_TINKLIT_SORT_ORDER') ? MODULE_PAYMENT_TINKLIT_SORT_ORDER : 0;
      $this->enabled = defined('MODULE_PAYMENT_TINKLIT_STATUS') && (MODULE_PAYMENT_TINKLIT_STATUS == 'True') ? true : false;
      $this->order_status = defined('MODULE_PAYMENT_TINKLIT_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_TINKLIT_ORDER_STATUS_ID > 0) ? (int)MODULE_PAYMENT_TINKLIT_ORDER_STATUS_ID : DEFAULT_ORDERS_STATUS_ID;

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }
    }

    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_TINKLIT_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_TINKLIT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }

// disable the module if the order only contains virtual products
      if ($this->enabled == true) {
        if ($order->content_type == 'virtual') {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      return false;
    }

    function after_process() {
        
        global $insert_id, $order, $currencies;

        require_once 'tinklit/tinklit_lib.php';          
            
        // change order status to value selected by merchant
        tep_db_query("update ". TABLE_ORDERS. " set orders_status = " . $this->order_status . " where orders_id = ". intval($insert_id));
            
        
        $options = array(
            //'price' => $currencies->rateAdjusted($order->info['total']), //$order->info['total'],
            'price' => $currencies->currencies['EUR']['value'] * $order->info['total'],
            'currency' => 'EUR', 
            'order_id' => $insert_id,
            'notification_url' => HTTP_SERVER.DIR_WS_CATALOG.'tinklit_callback.php',
            'redirect_url' => tep_href_link(FILENAME_ACCOUNT, 'tinklit=update_status'));
        //echo '<pre>'; print_r($options); echo '</pre>'; exit();
        $post = json_encode($options);
    	
        //  create invoice
    	//$invoice = tinklitCurl('https://api-staging.tinkl.it/v1/invoices', MODULE_PAYMENT_TINKLIT_CLIENTID, MODULE_PAYMENT_TINKLIT_TOKEN, $post);
        $invoice = tinklitCurl('https://api.tinkl.it/v1/invoices', MODULE_PAYMENT_TINKLIT_CLIENTID, MODULE_PAYMENT_TINKLIT_TOKEN, $post);
          //echo '<pre>'; print_r($invoice); echo '</pre>'; exit();
        if (!is_array($invoice) or array_key_exists('error', $invoice)) {
          $this->log('createInvoice error '.var_export($invoice['error'], true));
          zen_remove_order($insert_id, $restock = true);
          // unfortunately, there's not a good way of telling the customer that it's hosed.  Their cart is still full so they can try again w/ a different payment option.
        } else {
            
            $insert_sql = "INSERT INTO ".TABLE_TINKLIT."
                            (order_id, guid, status, btc_price, invoice_time, payment_confidence, time_created)
                            VALUES ('".$invoice['order_id']."', '".$invoice['guid']."', '".$invoice['status']."', '".$invoice['btc_price']."', '".$invoice['invoice_time']."', '".$invoice['payment_confidence']."', now())";
            tep_db_query($insert_sql);
            
          $_SESSION['cart']->reset(true);
          tep_redirect($invoice['url']);
        }
    
        return false;
      
    }

    function get_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_TINKLIT_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
        
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Client ID', 'MODULE_PAYMENT_TINKLIT_CLIENTID', 'CLIENTID', '', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Token', 'MODULE_PAYMENT_TINKLIT_TOKEN', 'TOKEN', '', '6', '0', now())");
        
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Cash On Delivery Module', 'MODULE_PAYMENT_TINKLIT_STATUS', 'True', 'Do you want to accept Cash On Delevery payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_TINKLIT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_TINKLIT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_TINKLIT_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      
      tep_db_query("CREATE TABLE IF NOT EXISTS `tinklit` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `order_id` int(11) NOT NULL,
                      `guid` varchar(64) NOT NULL,
                      `status` varchar(64) NOT NULL,
                      `btc_price` float NOT NULL,
                      `invoice_time` varchar(64) NOT NULL,
                      `payment_confidence` varchar(64) NOT NULL,
                      `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
      
   }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_TINKLIT_STATUS', 'MODULE_PAYMENT_TINKLIT_ZONE', 'MODULE_PAYMENT_TINKLIT_ORDER_STATUS_ID', 'MODULE_PAYMENT_TINKLIT_SORT_ORDER', 'MODULE_PAYMENT_TINKLIT_CLIENTID', 'MODULE_PAYMENT_TINKLIT_TOKEN');

    }
  }
?>
