<?php
/**
 * plugin uninstall 時に自動実行
 */

 if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

function snpmUninstallPlugin(  ) {

    global $wpdb;

    //nft_tableを削除
    $table = $wpdb->prefix . 'nft_table';

    if ($wpdb->get_var("show tables like '".$table."'") == $table) {
        $sql = "DROP TABLE IF EXISTS {$table}";
        $wpdb->query($sql);
    }


    //sales_tableを削除
    $table = $wpdb->prefix . 'sales_table';

    if ($wpdb->get_var("show tables like '".$table."'") == $table) {
        $sql = "DROP TABLE IF EXISTS {$table}";
        $wpdb->query($sql);
    }

    //option データ削除
    delete_option( 'snpm_general' );

}

snpmUninstallPlugin( );