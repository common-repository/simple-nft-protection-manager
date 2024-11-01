<?php
/**
 * NFT一覧を表示するクラス
 * データを一覧表示するWPのコアファイルのCLASSを継承
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class snpmNftListTable extends WP_List_Table{


    /**
     * 表で使用されるカラム情報を返す
     * この関数で、カラム毎に、表示をカスタママイズ出来る
     * @return value
     */
    function column_default($item, $column_name){
        return $item[$column_name];
    }

    /**
     * token_nameにマウスホバーすると、「edit」「delete」のリンクを表示させる設定
     * この関数で、カラム毎に、表示をカスタママイズ出来る
     * ※関数名を「column_カラム名」にする※
     * @return value
     */
    function column_token_name($item){
    
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&id=%s">'.__('edit', 'simple-nft-protection-manager').'</a>','nft_edit','edit',$item['id']),
            //'delete'    => sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
        );
        
        //Return the token_name contents
        return sprintf('%1$s <span style="color:silver"></span>%2$s',
            /*$1%s*/ $item['token_name'],
            /*$2%s*/ $this->row_actions($actions)
        );
    }

    //token_image_uriの出力
    function column_token_image_uri($item){
    
        if(!isset($item['token_image_uri']) || !$item['token_image_uri']) return;

        $return = '<img src="'.$item['token_image_uri'].'" style="width:150px;">';

        return $return;

    }

    //blockchain_networkの出力
    function column_blockchain_network($item){
    
        $htmls = new snpmHtmls();
        $defaultValue = new snpmDefaultValue();

        $param_value = [
            'data_type'=>'choice',
            'choices'=>$defaultValue->blockchain_network(),
        ];
        $return = $htmls->getDisplayValue('blockchain_network',$param_value,$item);

        return $return;

    }

    /**
     * label毎の並び替えの機能の表示設定
     * @return value
     */
    function get_sortable_columns() {

        $sortable_columns = array(
            'created_at'     => array('created_at',true),//true means it's already sorted
            'token_id'    => array('token_id',true),
            'token_name'  => array('token_name',true),
            'token_display_order'  => array('token_display_order',true)
        );
        return $sortable_columns;

    }


    /**
     * 表で使用されるカラムlabelを返す
     * @return array
     */
        function get_columns(){

        //カラムキー=>カラムlabel
        $columns= array(
            'token_image_uri' => __('Token image', 'simple-nft-protection-manager'),//'NFT画像',
            'token_id' => __('Token ID', 'simple-nft-protection-manager'),//'Token ID',
            'blockchain_network' => __('Blockchain network', 'simple-nft-protection-manager'),//
            'token_name'    => __('Token name', 'simple-nft-protection-manager'),//'NFT名',
            'token_description'    => __('Token description', 'simple-nft-protection-manager'),//'NFTの説明',
            'token_cost'      => __('Selling price', 'simple-nft-protection-manager'),//'販売価格',
            'token_issued'      => __('Issued', 'simple-nft-protection-manager'),//'発行数',
            'token_stock'      => __('Number of tokens in stock', 'simple-nft-protection-manager'),//'在庫数',
            'token_expiration_date'      => __('Expiration date', 'simple-nft-protection-manager'),//'有効期限',
            'token_display_flg'        => __('Display flg', 'simple-nft-protection-manager'),//'表示フラグ',
            'token_display_order'        => __('Display order', 'simple-nft-protection-manager'),//'表示順',
            'created_at'        => __('date of issue', 'simple-nft-protection-manager'),//'発行日',
        );
        return $columns;
        }

    /**
     * 表示するデータを準備する
     */
    function prepare_items() {

        $general = new snpmGeneral();

        //1ページあたりの件数
        $per_page = 10;

        //現在のページ数
        $current_page = $this->get_pagenum();
        
        $request = $general->request('get');

        if(!isset($request['paged']) || !$request['paged']) $request['paged'] = $current_page;
        if(!isset($request['num']) || !$request['num']) $request['num'] = $per_page;

        //表のヘッダー設定
        //----------------------------------------------
        //表で使用されるカラムlabel
        $columns = $this->get_columns();

        $hidden = array();

        //表のヘッダーlabelの並び替え機能
        $sortable = $this->get_sortable_columns();

        //表のヘッダー設定
        $this->_column_headers = array($columns, $hidden, $sortable);


        //表のコンテンツ表示設定
        //----------------------------------------------
        $snpmAdmin = new snpmAdmin();

        $total_items = $snpmAdmin->getNftCount();

        $this->items = $snpmAdmin->getNftList($request);

        //ページネーションの表示
        //----------------------------------------------
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'total_pages' => ceil($total_items/$per_page),
            'per_page' => $per_page,
        ) );

    }

        
}