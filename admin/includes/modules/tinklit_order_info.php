<?php

//if (isset($_GET['tinklit']) && $_GET['tinklit'] == 'update_status') {
    
    $oID = $HTTP_GET_VARS['oID'];
    
    require_once '../tinklit/tinklit_lib.php';
    
    $tinkl_info_sql = "SELECT guid FROM ".TABLE_TINKLIT."
                        WHERE order_id = '".(int)$oID."'";
    $tinkl_info_result = tep_db_query($tinkl_info_sql);
    $tinkl_info = tep_db_fetch_array($tinkl_info_result);  
    //echo '<pre>'; print_r($tinkl_info); echo '</pre>'; exit();
    $guid = $tinkl_info['guid'];
    
    $options = array('guid' => $tinkl_info->fields['guid']);

    $post = json_encode($options);
    
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

    //  create invoice
	//$invoice = tinklitCurl('https://api-staging.tinkl.it/v1/invoices', MODULE_PAYMENT_TINKLIT_CLIENTID, MODULE_PAYMENT_TINKLIT_TOKEN, $post);
    $invoice = tinklitCurl('https://api.tinkl.it/invoices/'.$guid, $clientid, $tokenid);
    
    //echo '!!<pre>'; print_r($invoice); echo '</pre>'; //exit();
    
    if ($invoice['status'] == 'payed') {
        
        $get_status_sql = "SELECT orders_status FROM ".TABLE_ORDERS."
                            WHERE orders_id = '".(int)$oID."'";
        $get_status_result = tep_db_query($get_status_sql);
        $get_status = tep_db_fetch_array($get_status_result);
        
        if ($get_status['orders_status'] != '2') {
        
            tep_db_query("update " . TABLE_ORDERS . "
                            set orders_status = '2', last_modified = now()
                            where orders_id = '" . (int)$oID . "'");
                            
            tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . "
                          (orders_id, orders_status_id, date_added, customer_notified, comments)
                          values ('" . (int)$oID . "',
                          '2',
                          now(),
                          '0',
                          'Order successfully payed')");
        }
    } 
    
//}

$tinkl_info_sql = "SELECT * FROM ".TABLE_TINKLIT."
                   WHERE order_id = '".(int)$oID."'";

$tinkl_info_result = tep_db_query($tinkl_info_sql);

if (tep_db_num_rows($tinkl_info_result)) {
    
    $tinkl_info = tep_db_fetch_array($tinkl_info_result);
?>

<tr>
    <td class="main" valign="top"><strong>Tinkl.it order info:</strong></td>
    <td class="main">
        <table>
            <tr>
                <td><strong>guid:</strong></td>
                <td><?php echo $tinkl_info['guid']; ?></td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td><?php echo $invoice['status']; ?></td>
            </tr>
            <tr>
                <td><strong>Bitcoin price:</strong></td>
                <td><?php echo $tinkl_info['btc_price']; ?></td>
            </tr>
            <tr>
                <td><strong>Invoice time:</strong></td>
                <td><?php echo $tinkl_info['invoice_time']; ?></td>
            </tr>
            <tr>
                <td><strong>Payment confidence:</strong></td>
                <td><?php echo $tinkl_info['payment_confidence']; ?></td>
            </tr>
            <?php /*
            <tr>
                <td></td>
                <td>
                    <a href="<?php echo zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('tinklit')).'tinklit=update_status'); ?>">Update invoice status</a>
                </td>
            </tr>
            */ ?>
        </table>
    </td>
</tr>

<?php } ?>