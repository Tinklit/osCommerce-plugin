<?php

function tinklit_update_status() { 

    global $db;
    
    require_once 'tinklit/tinklit_lib.php';

    $tinkl_info_sql = "SELECT configuration_value
                            FROM ".TABLE_CONFIGURATION."
                            WHERE configuration_key = 'MODULE_PAYMENT_TINKLIT_CLIENTID'";
    $tinkl_info_result = tep_db_query($tinkl_info_sql);
    $tinkl_info = tep_db_fetch_array($tinkl_info_result);
    $clientid = $tinkl_info['configuration_value'];
        
    $tinkl_info_sql = "SELECT configuration_value
                            FROM ".TABLE_CONFIGURATION."
                            WHERE configuration_key = 'MODULE_PAYMENT_TINKLIT_TOKEN'";
    $tinkl_info_result = tep_db_query($tinkl_info_sql);
    $tinkl_info = tep_db_fetch_array($tinkl_info_result);
    $tokenid = $tinkl_info['configuration_value'];
        
    $tinkl_orders_sql = "SELECT guid, order_id FROM ".TABLE_TINKLIT."
                            WHERE status = 'pending'
                            AND time_created > '".date('Y-m-d h:i:s', (date('U')-16*60))."'";
    //echo $tinkl_orders_sql; exit();
    $tinkl_orders_result = tep_db_query($tinkl_orders_sql);
    
    while ($tinkl_orders = tep_db_fetch_array($tinkl_orders_result)) {
        
        $guid = $tinkl_orders['guid'];
        
        $options = array('guid' => $tinkl_orders['guid']);
    
        $post = json_encode($options);
        
        //  create invoice
    	//$invoice = tinklitCurl('https://api-staging.tinkl.it/v1/invoices', MODULE_PAYMENT_TINKLIT_CLIENTID, MODULE_PAYMENT_TINKLIT_TOKEN, $post);
        $invoice = tinklitCurl('https://api.tinkl.it/invoices/'.$guid, $clientid, $tokenid);
        
        //echo $guid;
        //echo '<pre>'; print_r($invoice); echo '</pre>'; //exit();
        
        if ($invoice['status'] == 'payed') {
            
            $get_status_sql = "SELECT orders_status FROM ".TABLE_ORDERS."
                                WHERE orders_id = '".$tinkl_orders['order_id']."'";
            $get_status_result = tep_db_query($get_status_sql);
            $get_status = tep_db_fetch_array($get_status_result);
            
            if ($get_status['orders_status'] != '2') {
            
                tep_db_query("update " . TABLE_ORDERS . "
                                set orders_status = '2', last_modified = now()
                                where orders_id = '" . $tinkl_orders['order_id'] . "'");
                                
                tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . "
                              (orders_id, orders_status_id, date_added, customer_notified, comments)
                              values ('" . $tinkl_orders['order_id'] . "',
                              '2',
                              now(),
                              '0',
                              'Order successfully payed')");
                              
                tep_db_query("update " . TABLE_TINKLIT . "
                                set status = 'payed'
                                where order_id = '" . $tinkl_orders['order_id'] . "'");
            }
        }
        
     }
}

?>
