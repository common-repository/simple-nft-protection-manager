<?php
/**
 * NFTのデータを管理するCLASS
 * custom post typeを仕様
 */

class snpmSalesTable{

    public function __construct() {

        
        
    }

    /**
     * nftテーブル作成 
     */
    function salesTableInstall(){
        global $wpdb;
        
        $table = $wpdb->prefix.'sales_table';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("show tables like '".$table."'") != $table) {
            
            $sql = "CREATE TABLE  {$table} (
                id INT NOT NULL AUTO_INCREMENT, 
                created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                token_id INT NOT NULL,
                sales float NOT NULL,
                quantity INT NOT NULL,
                customer_address VARCHAR(255) NOT NULL,
                token_creater_address VARCHAR(255) NOT NULL,
                blockchain_network VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
                ) {$charset_collate};";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * nftテーブル削除
     */
    function nftTableDelete()
    {
            global $wpdb;
            $table = $wpdb->prefix . 'sales_table';

            if ($wpdb->get_var("show tables like '".$table."'") != $table) return;

            $sql = "DROP TABLE IF EXISTS {$table}";
            $wpdb->query($sql);
    }

}