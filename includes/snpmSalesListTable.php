<?php
/**
 * NFT一覧を表示するクラス
 * データを一覧表示するWPのコアファイルのCLASSを継承
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class snpmSalesListTable extends WP_List_Table{

    public $token_list;

    function __construct() {
        parent::__construct();
        $defaultValue = new snpmDefaultValue();
        $this->token_list = $defaultValue->nftArrayTokenIdKey('name');
    }

    /**
     * 表で使用されるカラム情報を返す
     * この関数で、カラム毎に、表示をカスタママイズ出来る
     * @return value
     */
    function column_default($item, $column_name){
        return $item[$column_name];
    }


    //token_idの出力 トークン名を付与
    function column_token_id($item){
    
        $token_list = $this->token_list;
        $token_id = $item['token_id'];
        $return = $token_list[$token_id] . '('.$token_id.')';
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
            'created_at'        => __('date of sales', 'simple-nft-protection-manager'),//'発行日',
            'token_id' => __('Token name', 'simple-nft-protection-manager') . '(token id)',//'Token ID',
            'blockchain_network' => __('Blockchain network', 'simple-nft-protection-manager'),//
            //'token_name'    => __('Token name', 'simple-nft-protection-manager'),//'NFT名',
            'sales'    => __('Sales', 'simple-nft-protection-manager'),//'NFTの説明',
            'quantity'      => __('Quantity', 'simple-nft-protection-manager'),//'販売価格',
            'customer_address'      => __('Customer address', 'simple-nft-protection-manager'),//'販売価格',
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

        $total_items = $snpmAdmin->getSalesCount();

        $this->items = $snpmAdmin->getSalesList($request);

        //ページネーションの表示
        //----------------------------------------------
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'total_pages' => ceil($total_items/$per_page),
            'per_page' => $per_page,
        ) );

    }

        
}