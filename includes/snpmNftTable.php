<?php
/**
 * NFTのデータを管理するCLASS
 * custom post typeを仕様
 */

class snpmNftTable{

    public function __construct() {

        
        
    }

    /**
     * nftテーブル作成 
     */
    function nftTableInstall(){
        global $wpdb;
        
        $table = $wpdb->prefix.'nft_table';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("show tables like '".$table."'") != $table) {
            
            $sql = "CREATE TABLE  {$table} (
                id INT NOT NULL AUTO_INCREMENT, 
                created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                token_id INT NOT NULL,
                token_name VARCHAR(255) NOT NULL,
                token_description TEXT NOT NULL,
                token_cost float NOT NULL,
                token_issued INT NOT NULL,
                token_stock INT NOT NULL,
                token_expiration_date INT NOT NULL,
                token_image_uri VARCHAR(255) NOT NULL,
                token_note TEXT NOT NULL,
                token_creater_address VARCHAR(255) NOT NULL,
                token_display_flg INT NOT NULL,
                token_display_order INT NOT NULL,
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
            $table = $wpdb->prefix . 'nft_table';

            if ($wpdb->get_var("show tables like '".$table."'") != $table) return;

            $sql = "DROP TABLE IF EXISTS {$table}";
            $wpdb->query($sql);
    }

    /**
     * token idから、token情報を取得
     * @param token_id array
     */
    function getNftListFromId($token_ids)
    {
        global $wpdb;

        $ids = array();
        $placeholder = '';
        foreach($token_ids as $s_key => $s_value){
            if(!$s_key && $s_value === "") continue;
            $ids[] = (int)$s_value;
            $placeholder .= '%s,';
        }
        $placeholder = substr( $placeholder, 0, -1 ); 

        $table_name = $wpdb->prefix . 'nft_table';
        $sql = "SELECT * FROM {$table_name} WHERE token_id IN ( ".$placeholder." ) and blockchain_network = '".SNPM_BLOCKCAHIN_NETWORK."';";
        $query = $wpdb->prepare($sql,$ids); 
        //$query = call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $ids));
        $nfts = $wpdb->get_results( $query, ARRAY_A );//配列で取得  

        return $nfts;
    }    

}