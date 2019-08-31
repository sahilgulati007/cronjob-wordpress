<?php

/**
 * Plugin Name: Debit Credit Plugin
 * Description: To get list of Debit and Credit shortcode:[total_shortcode].
 * Version: 1.0
 * Author: Sahil Gulati
 */

/* Activate Hook */
function myplugin_activate()
{

    if (!wp_next_scheduled('pt_my_task_hook')) {
        //wp_schedule_event(time(), 'hourly', 'pt_my_task_hook');
        //wp_schedule_event(time(), 'every_three_minutes', 'pt_my_task_hook');
        wp_schedule_event(strtotime('22:00:00'), 'daily', 'pt_my_task_hook');
        wp_schedule_event(strtotime('22:01:00'), 'daily', 'pt_my_task_hook2');
    }
}

register_activation_hook(__FILE__, 'myplugin_activate');

/* Deactivate Hook */
register_deactivation_hook(__FILE__, 'my_deactivation');
function my_deactivation()
{
    wp_clear_scheduled_hook('pt_my_task_hook');
    wp_clear_scheduled_hook('pt_my_task_hook2');
}

add_action('pt_my_task_hook2', 'user_email_task_function');
function user_email_task_function()
{
    $usr_list = get_users();
    foreach ($usr_list as $u) {

        global $wpdb;
        $html = '';
        $tc = 0;
        $tcom = 0;
        $tpay = 0;

        $td = 0;
        $bal = 0;


        $tableName = $wpdb->prefix . 'woo_wallet_transactions';
        $tableNameMeta = $wpdb->prefix . 'woo_wallet_transaction_meta';

        $currentDateTime = date('Y-m-d H:i:s', strtotime('-1 days'));
        $invoiceDate = date('Y-m-d', strtotime('-1 days'));

        $res = $wpdb->get_results('select * from ' . $tableName . ' where date>="' . $currentDateTime . '" and user_id="' . $u->ID . '"');
        //$html.=$res;
        //$html.=$u->data->user_email;
        //$html.='select * from '.$tableName.' where date>="'.$currentDateTime.'" and user_id="'.$u->ID.'"';
        $html .= "<div style='width:50%;float: left;margin-top: 20px;'><img style='margin-right: auto;width:50%; display: block' src='http://gmasterpos.com/wp-content/uploads/2018/12/Screenshot_101.png'></div>";
        $html .= "<div style='width:50%;float: left;text-align: right;margin-left: -18px;'><h3 style='margin:0'>GMasterPos</h3>";
        $html .= "C2 GIDAN ADO KACHIA<br> CONSTITUTION ROAD KADUNA<br> KADUNA NORTH<br> Kaduna 810282<br> <b>Phone:</b> 08096488224 <br><b>Email:</b> gmasterpos@gmail.com</div>";
        $html .= "<h2 align='center' style='margin-top:10px'>WALLET POINT STATEMENT OF ACCOUNT </h2>";

        $m = get_user_meta($u->ID, 'billing_address_1');
        $html .= "<div style='width: 99%;float: left;border-top: 3px solid #96588a;border-bottom: 3px solid #96588a;border-left: 3px solid #96588a;border-right: 3px solid #96588a;margin: 5px 0px 5px 0px;padding: 10px 10px 10px 10px;background: #e2e1e1;'> <div style='width: 50%;float: left;'><b style='color:#96588a'>Statement To: &nbsp;</b>";
        $html .= $u->data->display_name . "<br>";
        $html .= $m[0] . "<br>";
        $html .= "<b>Email: &nbsp;</b>";
        $html .= $u->data->user_email . "<br></div>";
        $html .= "<div style='width: 30%;float: right;background: #96588a;color: white;padding: 5px 15px 5px 13px;    text-align: right;'> <b style='width:50px;float:left;'>Date:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $invoiceDate . "</div></div>";

        //$html.="Hello <b>".$u->data->display_name."</b>, <br> <p>Please Find your daily wallet transaction report.</p>";
        if (empty($res)) {
            $html .= "<h3>No Wallet Transaction Found For the Day</h3>";
        } else {
            $html .= '<table border="1" style="background:#F4F4F4 ;border:2px solid #333333; border-collapse: collapse;color:#333333; width:100%">';

            //$html.="<table border='1' cellpadding='10' class='widefat fixed' cellspacing='0'>";

            $html .= "<tr style='background:#96588a;color:white'><th style='padding:10px;'> TID </th><th style='padding:10px;'>Credit</th><th style='padding:10px;'>Debit</th><th style='padding:10px;'> Transaction Details </th><th style='padding:10px;'> Date </th></tr>";

            foreach ($res as $r) {
                $res2 = $wpdb->get_results('select count(*) as cnt from ' . $tableNameMeta . ' where meta_key="_admin_debit_commission" and transaction_id="' . $r->transaction_id . '"');


                // if($res2[0]->cnt){
                // 	continue;
                // }

                $res3 = $wpdb->get_results('select transaction_id from ' . $tableNameMeta . ' where meta_key="_admin_debit_commission_transaction_id" and meta_value="' . $r->transaction_id . '"');
                $res5 = $wpdb->get_results('select transaction_id from ' . $tableNameMeta . ' where meta_key="_admin_debit_commission_shop_manager_id" and meta_value="' . $r->ID . '"');
                // echo "<pre>";
                // print_r($res3);
                // echo "</pre>";
                $res4 = $wpdb->get_results('select * from ' . $tableName . ' where transaction_id="' . $res3[0]->transaction_id . '"');
                $res6 = $wpdb->get_results('select * from ' . $tableName . ' where transaction_id="' . $res5[0]->transaction_id . '"');
                // echo "<pre>";
                // print_r($res4);
                // echo "</pre>";

                $html .= "<tr>";

                if ($r->type == 'credit') {
                    $tc += $r->amount;
                    $html .= "<td style='padding:10px;'>" . $r->transaction_id . "</td><td style='padding:10px;color:green'>&#8358;" . number_format($r->amount, 2, '.', ',') . "</td><td style='padding:10px;'> --- </td><td style='padding:10px;'>" . $r->details . "</td><td style='padding:10px;'>" . $r->date . "</td>";
                } else {
                    $td += $r->amount;
                    $html .= "<td style='padding:10px;'>" . $r->transaction_id . "</td><td style='padding:10px;'> --- </td><td style='padding:10px;color:red'>&#8358;" . number_format($r->amount, 2, '.', ',') . "</td><td style='padding:10px;'>" . $r->details . "</td><td style='padding:10px;'>" . $r->date . "</td>";
                }

                if ($res4[0]->amount) {
                    // 	$html.="<td style='padding:10px;'>&#8358;".number_format($res4[0]->amount, 2, '.', ',')."</td>";
                    $tcom += $res4[0]->amount;
                }
                if ($res6[0]->amount) {
                    // 	$html.="<td style='padding:10px;'>&#8358;".number_format($res4[0]->amount, 2, '.', ',')."</td>";
                    $tpay += $res6[0]->amount;
                }
                // else{
                // 	$html.="<td style='padding:10px;'></td>";
                // }
                $bal = $r->balance;
                $html .= "</tr>";

            }
            //$html.="<tr><td style='padding:10px;'><b> Total Credit: &#8358;".number_format($tc, 2, '.', ',')." </b></td><td style='padding:10px;'><b> Total Debit:  &#8358;".number_format($td, 2, '.', ',')."</b></td><td style='padding:10px;'><b> Available Balance: &#8358;".number_format($bal, 2, '.', ',')."</b></td><td style='padding:10px;'><b> Total Points: ".number_format($bal, 0, '.', ',')."</b></td><td><b>Total Commission: &#8358;".number_format($tcom, 0, '.', ',')."</b></td></tr>";

            $html .= "</table>";
            $html .= "<div style='float:right;margin-top: 15px; width:40%'><div style='border-bottom: 2px solid #72777c;padding-bottom: 5px;margin-bottom: 5px;'><div style='width:49%;float:left;'><b>SubTotal</b></div> <div style='width:49%;float:right;'><b style='float:right;margin-right:0px'>&#8358;" . number_format($bal, 2, '.', ',') . "</b></div></div>";
            $html .= "<div style='border-bottom: 2px solid #72777c;padding-bottom: 5px;margin-bottom: 5px;'><div style='width:49%;float:left;'><b>Taxes</b></div> <div style='width:49%;float:right;'> <b>&#8358;" . number_format(0, 2, '.', ',') . "</b></div></div>";
            $html .= "<div style='border-bottom: 2px solid #72777c;padding-bottom: 5px;'><div style='width:49%;float:left;'><b>Point Balance</b></div> <div style='width:49%;float:right;'><b>" . number_format($bal, 2, '.', ',') . "</b></div></div></div>";
            if (in_array('administrator', (array)$u->roles) || in_array('shop_manager', (array)$u->roles)) {
                $html .= "<div><b>PayOut: &#8358;" . $tpay . "</b></div>";
                $html .= "<div><b>Total Commission: &#8358;" . $tcom . "</b></div>";
            }
            if (in_array('administrator', (array)$u->roles) || in_array('contributor', (array)$u->roles)) {
                $html .= "<div><b>Referrals: &#8358;" . get_user_meta($u->ID, '_woo_wallet_referring_earning', true) . "<b></div>";
            }
            $html .="<img style='margin-right: 0;margin-top: 30px; display: block; float:right;' src='http://gmasterpos.com/wp-content/uploads/2019/07/images1.jpg' width='150px' height='auto'>";
        }
        //$html.="<br>Thanks for your Patronage";
        //$html.="<br>Management GmasterPos";
        // $pdf=new PDF();
        //    $pdf->AddPage();
        //    $pdf->SetFont('Arial','B',16);
        //$pdf->generateTable(5);
        // $pdf->WriteHTML($html);
        require_once __DIR__ . '/vendor/autoload.php';

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($html);
        $dir = plugin_dir_path(__FILE__) . 'pdf/';
        $filename = $u->ID . "filename.pdf";
        $mpdf->Output($dir . $filename, \Mpdf\Output\Destination::FILE);
        // $pdf ->Output('F', $dir.$filename);
        //echo "Save PDF in folder";
        $attachments = array(plugin_dir_path(__FILE__) . 'pdf/' . $filename);

        $headers = array('Content-Type: text/html; charset=UTF-8', 'From: GmasterPos <admin@gmasterpos.com>');
        //$headers = array('Content-Type: text/html; charset=UTF-8');
        //wp_mail($u->data->user_email, 'GmasterPos Daily Report', $html,$headers);
        wp_mail('wondercrazy15@gmail.com', 'GmasterPos Daily Report', 'Hey, Find the PDF in Attachment', $headers, $attachments);
        wp_mail($u->data->user_email, 'GmasterPos Daily Report', 'Hey, Find the PDF in Attachment', $headers, $attachments);

    }
}


add_action('pt_my_task_hook', 'pt_my_task_function');
function pt_my_task_function()
{
    $usr_list = get_users();
    foreach ($usr_list as $u) {
        //if (in_array('administrator', (array)$u->roles) || in_array('shop_manager', (array)$u->roles) || in_array('contributor', (array)$u->roles)) {
            //echo $u->data->user_email;
            global $wpdb;
            $html = '';
            $tc = 0;
            $tcom = 0;

            $td = 0;
            $bal = 0;


            $tableName = $wpdb->prefix . 'woo_wallet_transactions';
            $tableNameMeta = $wpdb->prefix . 'woo_wallet_transaction_meta';

            $currentDateTime = date('Y-m-d H:i:s', strtotime('-1 days'));

            $res = $wpdb->get_results('select * from ' . $tableName . ' where date>="' . $currentDateTime . '" and user_id="' . $u->ID . '"');
            //$html.=$res;
            //$html.=$u->data->user_email;
            //$html.='select * from '.$tableName.' where date>="'.$currentDateTime.'" and user_id="'.$u->ID.'"';
            $html .= "<img style='margin-left: auto;margin-right: auto;width:50%; display: block' src='http://gmasterpos.com/wp-content/uploads/2018/12/Screenshot_101.png'>";
            $html .= "<h1 align='center'>Today's Wallet Transaction Report " . date('d-m-Y') . " </h1>";
            $html .= "Hello <b>" . $u->data->display_name . "</b>, <br> <p>Please Find your daily wallet point transaction report.</p>";
            if (empty($res)) {
                $html .= "<h3>No Wallet Point Transaction Found For Today</h3>";
            } else {
                foreach ($res as $r) {
                    $bal = $r->balance;
                }
                $html .= "<h2 align='right' style='color:green'>Total Points: " . number_format($bal, 0, '.', ',') . " </h2>";
                $html .= '<br> <table border="1" style="background:#F4F4F4 ;border:2px solid #333333; border-collapse: collapse;color:#333333; width:100%">';

                //$html.="<table border='1' cellpadding='10' class='widefat fixed' cellspacing='0'>";

                $html .= "<tr style='background:#96588a;color:white'><th style='padding:10px;'>Credit Value (&#8358;)</th><th style='padding:10px;'>Debit Value (&#8358;)</th><th style='padding:10px;'>Wallet Cash Value (&#8358;)</th><th>Wallet Point Value</th><th>Commission (&#8358;)</th></tr>";

                foreach ($res as $r) {
                    $res2 = $wpdb->get_results('select count(*) as cnt from ' . $tableNameMeta . ' where meta_key="_admin_debit_commission" and transaction_id="' . $r->transaction_id . '"');


                    if ($res2[0]->cnt) {
                        continue;
                    }

                    $res3 = $wpdb->get_results('select transaction_id from ' . $tableNameMeta . ' where meta_key="_admin_debit_commission_transaction_id" and meta_value="' . $r->transaction_id . '"');
                    // echo "<pre>";
                    // print_r($res3);
                    // echo "</pre>";
                    $res4 = $wpdb->get_results('select * from ' . $tableName . ' where transaction_id="' . $res3[0]->transaction_id . '"');
                    // echo "<pre>";
                    // print_r($res4);
                    // echo "</pre>";

                    $html .= "<tr>";

                    if ($r->type == 'credit') {
                        $tc += $r->amount;
                        $html .= "<td style='padding:10px;color:green'>&#8358;" . number_format($r->amount, 2, '.', ',') . "</td><td style='padding:10px;'> --- </td><td style='padding:10px;'>&#8358;" . number_format($r->balance, 2, '.', ',') . "</td><td style='padding:10px;'>" . number_format($r->balance, 0, '.', ',') . "</td>";
                    } else {
                        $td += $r->amount;
                        $html .= "<td style='padding:10px;'> --- </td><td style='padding:10px;color:red'>&#8358;" . number_format($r->amount, 2, '.', ',') . "</td><td style='padding:10px;'>&#8358;" . number_format($r->balance, 2, '.', ',') . "</td><td style='padding:10px;'>" . number_format($r->balance, 0, '.', ',') . "</td>";
                    }

                    if ($res4[0]->amount) {
                        $html .= "<td style='padding:10px;'>&#8358;" . number_format($res4[0]->amount, 2, '.', ',') . "</td>";
                        $tcom += $res4[0]->amount;
                    } else {
                        $html .= "<td style='padding:10px;'> --- </td>";
                    }
                    $bal = $r->balance;
                    $html .= "</tr>";

                }
                $html .= "<tr><td style='padding:10px;'><b> Total Credit: &#8358;" . number_format($tc, 2, '.', ',') . " </b></td><td style='padding:10px;'><b> Total Debit:  &#8358;" . number_format($td, 2, '.', ',') . "</b></td><td style='padding:10px;'><b> Available Balance: &#8358;" . number_format($bal, 2, '.', ',') . "</b></td><td style='padding:10px;'><b> Total Points: " . number_format($bal, 0, '.', ',') . "</b></td><td><b>Total Commission: &#8358;" . number_format($tcom, 0, '.', ',') . "</b></td></tr>";

                $html .= "</table>";
            }
            $html .= "<br>Thanks for your Patronage";
            $html .= "<br>Management GmasterPos";


            // $currentDateTime = date('Y-m-d H:i:s',strtotime('-1 days'));


            // $res = $wpdb->get_results('select * from '.$tableName.' where date>="'.$currentDateTime.'"');

            // $tc=0;

            // $td=0;

            // foreach ($res as $r) {

            // 	if($r->type=='credit'){

            // 		$tc+=$r->amount;

            // 	}

            // 	else{

            // 		$td+=$r->amount;

            // 	}

            // }

            // $html.="total credit=".$tc;

            // $html.="<br>total debit=".$td;
            $headers = array('Content-Type: text/html; charset=UTF-8', 'From: GmasterPos <admin@gmasterpos.com>');
            //$headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($u->data->user_email, 'GmasterPos Daily Wallet Point Report', $html, $headers);
            wp_mail('wondercrazy15@gmail.com', 'GmasterPos Daily Wallet Point Report', $html, $headers);
            // $recipient = 'wondercrazy15@gmail.com';
            // $subject = __("GmasterPos Daily Report new", 'theme_name');
            //$content = get_custom_email_html( $order, $subject, $mailer );
            // $headers = "Content-Type: text/html\r\n";

            //send the email through wordpress
            // $mailer->send( $recipient, $subject, $html, $headers );
        //}
    }
}


function isa_add_cron_recurrence_interval($schedules)
{
    $schedules['every_three_minutes'] = array(
        'interval' => 180,
        'display' => __('Every 3 Minutes', 'textdomain')
    );
    // $schedules['every_fifteen_minutes'] = array(
    //         'interval'  => 900,
    //         'display'   => __( 'Every 15 Minutes', 'textdomain' )
    // );
    return $schedules;
}

add_filter('cron_schedules', 'isa_add_cron_recurrence_interval');

add_action('admin_menu', 'debit_menu');

function debit_menu()

{

    //adding plugin in menu

    add_menu_page('Debit_admin', //page title

        'Debit Credit List', //menu title

        'manage_options', //capabilities

        'Debit_Admin', //menu slug

        'debit_credit_function' //function

    );

}


function debit_credit_function()
{

    //echo "in";

    //echo get_current_user_id();
    //pt_my_task_function();
    //user_email_task_function();

    global $wpdb;

    $tableName = $wpdb->prefix . 'woo_wallet_transactions';
    $tableNameMeta = $wpdb->prefix . 'woo_wallet_transaction_meta';

    $currentDateTime = date('Y-m-d H:i:s', strtotime('-2 days'));

    // $currentDateTime = date('Y-m-d H:i:s',strtotime('-10 days'));

    //$currentDateTime = new DateTime('2019-07-01');

    //date_sub($currentDateTime, date_interval_create_from_date_string('10 days'));

    //echo 'select * from '.$tableName.' where date>='.$currentDateTime;
    //$number = $wpdb->get_results('select meta_value from ' . $tableNameMeta . ' where meta_key="_admin_debit_commission_transaction_id"');
    //print_r($number);
    //$res = $wpdb->get_results('select * from '.$tableName.' where date>="'.$currentDateTime.'" and user_id="'.get_current_user_id().'" and transaction_id in (select meta_value from '.$tableNameMeta.' where meta_key="_admin_debit_commission_transaction_id") ');
    //echo 'select * from '.$tableName.' where date>="'.$currentDateTime.'" and user_id="'.get_current_user_id().'" and transaction_id in (select meta_value from '.$tableNameMeta.' where meta_key="_admin_debit_commission_transaction_id") ';
    //echo "<pre>";
    //print_r($res);
    //echo "</pre>";

    $res = $wpdb->get_results('select * from ' . $tableName . ' where date>="' . $currentDateTime . '" and user_id="' . get_current_user_id() . '"');

    // echo "<pre>";

    // print_r($res);

    // echo "</pre>";

    echo "<h1>Todays transaction </h1>";

    echo "<table border='1' cellpadding='10' class='widefat fixed' cellspacing='0'>";

    echo "<tr><th>Credit</th><th>Debit</th><th>Balance</th></tr>";

    foreach ($res as $r) {

        # code...

        // echo "<pre>";

        // print_r($r);

        // echo "</pre>";
        $res2 = $wpdb->get_results('select count(*) as cnt from ' . $tableNameMeta . ' where meta_key="_admin_debit_commission" and transaction_id="' . $r->transaction_id . '"');
        echo $r->transaction_id . " -";
        //echo $res2[0]->cnt."<br>";
        // echo "<pre>";
        // print_r($res2);
        // echo "</pre>";

        if ($res2[0]->cnt) {
            continue;
        }

        $res3 = $wpdb->get_results('select transaction_id from ' . $tableNameMeta . ' where meta_key="_admin_debit_commission_transaction_id" and meta_value="' . $r->transaction_id . '"');
        // echo "<pre>";
        // print_r($res3);
        // echo "</pre>";
        $res4 = $wpdb->get_results('select * from ' . $tableName . ' where transaction_id="' . $res3[0]->transaction_id . '"');
        // echo "<pre>";
        // print_r($res4);
        // echo "</pre>";


        echo "<tr>";
        //echo $r->transaction_id;
        //$res = $wpdb->get_results('select transaction_id from '.$tableNameMeta.' where meta_key="_admin_debit_commission_transaction_id" and meta_value="'.$r->transaction_id;.'"')

        if ($r->type == 'credit') {

            echo "<td style='color:green'>" . number_format($r->amount, 2, '.', ',') . "</td><td></td><td>" . $r->balance . "</td>";

        } else {

            echo "<td></td><td style='color:red'>" . number_format($r->amount, 2, '.', ',') . "</td><td>" . $r->balance . "</td>";

        }
        if ($res4[0]->amount) {
            echo "<td>" . $res4[0]->amount . "</td>";
        } else {
            echo "<td></td>";
        }

        // echo "<td>".$r->type."</td><td>".$r->amount."</td><td>".$r->balance."</td>";


        echo "</tr>";

    }

    echo "</table>";


    $currentDateTime = date('Y-m-d H:i:s', strtotime('-1 days'));

    //$currentDateTime = new DateTime('2019-07-01');

    //date_sub($currentDateTime, date_interval_create_from_date_string('10 days'));

    //echo 'select * from '.$tableName.' where date>='.$currentDateTime;

    $res = $wpdb->get_results('select * from ' . $tableName . ' where date>="' . $currentDateTime . '"');

    $tc = 0;

    $td = 0;

    foreach ($res as $r) {

        if ($r->type == 'credit') {

            $tc += $r->amount;

        } else {

            $td += $r->amount;

        }

    }

    echo "total credit=" . $tc;

    echo "<br>total debit=" . $td . "<br>";

    $user = wp_get_current_user();

    if (in_array('administrator', (array)$user->roles)) {

        //The user has the "author" role

        //echo 'in admin';

        //    echo "total credit=".$tc;

        // echo "<br>total debit=".$td;

    }

    if (is_user_logged_in()) {

        $user = wp_get_current_user();

        $roles = ( array )$user->roles;

        //print_r($roles); // This returns an array

        // Use this to return a single value

        // return $roles[0];

    } else {

        return array();

    }
    //start pdf

    //end pdf
    // echo "<br> <pre>";
    // print_r(get_users());
    // echo "</pre>";
    $usr_list = get_users();
    foreach ($usr_list as $u) {
        if (in_array('administrator', (array)$u->roles) || in_array('shop_manager', (array)$u->roles) || in_array('contributor', (array)$u->roles)) {
            //echo $u->data->user_email;
            global $wpdb;
            $html = '';
            $tc = 0;

            $td = 0;
            $bal = 0;
            $args = array(
                'user_id' => $u->ID,
                'where' => array(array('key' => 'type', 'value' => 'credit')),
                'where_meta' => array(array('key' => '_admin_debit_commission', 'value' => true), array('key' => '_admin_debit_commission', 'value' => true)),
                'nocache' => true
            );
            $transactions = get_wallet_transactions($args);
//echo "<pre>"; print_r($transactions);
            $html = $transactions;
            $total_commisstion = array_sum(wp_list_pluck($transactions, 'amount'));
        }
    }
    $usr_list = get_users();
    //echo "<pre>";
    //print_r($roles);
    //print_r($u);
    foreach ($usr_list as $u) {
        // print_r($u);
        // $m=get_user_meta( $u->ID,'billing_address_1' );
        // echo $m[0];
        // print_r($m);

        // echo "<br><br><br>";
        // echo $m[0];
        //echo $m['billing_address_1'][0];
        //echo $u->data->user_email;
        //echo $u->allcaps->shop_manager;
        //$user = wp_get_current_user(12);

        //$roles = ( array ) $user->roles;

        // 	$args = array(
        //     'user_id' => $u->ID,
        //     'where' => array(array('key' => 'type', 'value' => 'credit')),
        //     'where_meta' => array(array('key' => '_admin_debit_commission', 'value' => true)),
        //     'nocache' => true
        // );
        // $transactions = get_wallet_transactions($args);
        // $total_commisstion = array_sum(wp_list_pluck($transactions, 'amount'));
        // echo "in<pre>";
        // print_r($transactions);
        // print_r($total_commisstion);


        // echo "<pre>";
        // print_r($roles);
        // print_r($u);
        // echo "</pre>";
        //$user_meta = get_userdata($user_id);

        //print_r($user_meta->roles);
        break;
    }


}

function total_shortcode_function()
{

    ob_start();

    global $wpdb;

    $tableName = $wpdb->prefix . 'woo_wallet_transactions';

    $currentDateTime = date('Y-m-d H:i:s', strtotime('-1 days'));

    //$currentDateTime = new DateTime('2019-07-01');

    //date_sub($currentDateTime, date_interval_create_from_date_string('10 days'));

    //echo 'select * from '.$tableName.' where date>='.$currentDateTime;

    $res = $wpdb->get_results('select * from ' . $tableName . ' where date>="' . $currentDateTime . '"');

    $tc = 0;

    $td = 0;

    //print_r($res);

    foreach ($res as $r) {

        if ($r->type == 'credit') {

            $tc += $r->amount;

        } else {

            $td += $r->amount;

        }

    }

    //echo "total credit=".$tc;

    //echo "<br>total debit=".$td;

    $user = wp_get_current_user();

    if (in_array('administrator', (array)$user->roles) || in_array('super_admin', (array)$user->roles) || in_array('contributor', (array)$user->roles)) {

        //The user has the "author" role

        //echo 'in admin';

        return "Total Credit=" . $tc . "  Total Debit=" . $td;

    }

    if (is_user_logged_in()) {

        $user = wp_get_current_user();

        $roles = ( array )$user->roles;

        //print_r($roles); // This returns an array

        // Use this to return a single value

        // return $roles[0];

    } else {

        //return array();

    }

    $output = ob_get_contents();

    //return $output;

    ob_end_clean();

}

add_shortcode('total_shortcode', 'total_shortcode_function');
