<?php
/**---------------------------------------------------------
 * 初期値の設定
----------------------------------------------------------- */
if ( ! class_exists( 'snpmAdmin' ) ) :
	class snpmAdmin {

        public $views;
        public $query;
        public $general;
        public $defaultValue;

        public function __construct() {


            //管理画面の左側にメニューを追加
            add_action( 'admin_menu', [ $this, 'addAdminMenu' ] );

            //管理画面メイン処理
            add_action( 'admin_init', [ $this, 'snpmController' ]);//管理画面が初期化された後に実行される。管理画面内のすべてのページが対象

            //管理画面に独自のjs cssを読み込み
            add_action( 'admin_enqueue_scripts', [ $this, 'adminAddPluginAsset' ]);
         
            //投稿ページなどに、カスタママイズした入力エリアを追加
            add_action( 'add_meta_boxes', [ $this, 'addCustomMetaBox']);

            //管理画面でカスタムボックス内のデータを保存
            add_action( 'save_post', [ $this, 'saveCustomeMetaBox' ] );//投稿や固定ページを保存、更新した時に実行される

            $this->views = new snpmViews();
            $this->query = new snpmQuery();
            $this->general = new snpmGeneral();
            $this->defaultValue = new snpmDefaultValue();
        }


        /**
         * 管理画面にメニューを追加
         */
        function addAdminMenu() {

            /**
             * メインメニュー追加
             * 第1引数：メニューが選択されたとき、ページのタイトルタグに表示されるテキスト
             * 第2引数：メニューとして表示されるテキスト
             * 第3引数：メニューを表示するために必要な権限
             * 第4引数：メニューのスラッグ名
             * 第5引数：（任意）メニューページを表示する際に実行される関数
             * 第6引数：（任意）メニューのアイコンを示す URL
             * 第7引数：（任意）メニューが表示される位置
             */
            add_menu_page( 
                'SNPM',
                'SNPM',//'SNPM 設定',
                SNPM_ADMIN_CAPABILITY,
                SNPM_PLUGIN_PREFIX,
                [ $this, 'snpmAdminPage' ],
                'dashicons-id'
            );

            /**
             * サブメニュー追加
             * 第1引数：親メニューのスラッグ（ブランクにすると、メニューには表示されない）
             * 第2引数：サブメニューが選択されたとき、ページのタイトルタグに表示されるテキスト
             * 第3引数：サブメニューとして表示されるテキスト
             * 第4引数：サブメニューを表示するために必要な権限
             * 第5引数：サブメニューのスラッグ名
             * 第6引数：（任意）このページのコンテンツを出力するために呼び出される関数
             */

             add_submenu_page( 
                SNPM_PLUGIN_PREFIX,
                __('SNPM general setting', 'simple-nft-protection-manager'),//'NFT発行',
                __('SNPM general setting', 'simple-nft-protection-manager'),//'NFT発行',
                SNPM_ADMIN_CAPABILITY,
                'snpm_general_setting',
                [ $this, 'snpmAdminPage' ]
            );
            
            add_submenu_page( 
                SNPM_PLUGIN_PREFIX,
                __('Token new issue', 'simple-nft-protection-manager'),//'NFT発行',
                __('Token new issue', 'simple-nft-protection-manager'),//'NFT発行',
                SNPM_ADMIN_CAPABILITY,
                'nft_mint',
                [ $this, 'nftMintPage' ]
            );
            
            add_submenu_page( 
                SNPM_PLUGIN_PREFIX,
                __('Create token MetaData', 'simple-nft-protection-manager'),//'NFT MetaData作成',
                __('Create token MetaData', 'simple-nft-protection-manager'),//'NFT MetaData作成',
                SNPM_ADMIN_CAPABILITY,
                'nft_create_metadata',
                [ $this, 'nftCreateMetadataPage' ]
            );

            add_submenu_page( 
                SNPM_PLUGIN_PREFIX,
                __('Token list', 'simple-nft-protection-manager'),//'NFT一覧',
                __('Token list', 'simple-nft-protection-manager'),//'NFT一覧',
                SNPM_ADMIN_CAPABILITY,//権限：「edit_dashboard」は管理画面の編集権限が必要
                'nft_list',
                [ $this, 'nftListPage' ]
            );

            add_submenu_page( 
                SNPM_PLUGIN_PREFIX,
                __('Sales list', 'simple-nft-protection-manager'),//'売上一覧',
                __('Sales list', 'simple-nft-protection-manager'),//'売上一覧',
                SNPM_ADMIN_CAPABILITY,//権限：「edit_dashboard」は管理画面の編集権限が必要
                'nft_sales_list',
                [ $this, 'salesListPage' ]
            );

            add_submenu_page( 
                '',
                __('Change token MetaData', 'simple-nft-protection-manager'),//'NFT MetaData変更',
                __('Change token MetaData', 'simple-nft-protection-manager'),//'NFT MetaData変更',
                SNPM_ADMIN_CAPABILITY,//権限：「edit_dashboard」は管理画面の編集権限が必要
                'nft_edit',
                [ $this, 'nftEditPage' ]
            );

            global $submenu;
            unset($submenu[SNPM_PLUGIN_PREFIX][0]); // 重複するサブメニューの非表示
        }

        /**
         * メイン処理のコントローラー
         */
        function snpmController(){

            if (isset($_POST['action']) && $_POST['action'] == 'validation' ) return;

            //snpm一般設定のフォームの送信
            if (!empty($_POST) && wp_verify_nonce( $_POST['snpm_general_nonce_name'], 'snpm_general_nonce_action' ) ) {
                $this->snpmGeneralSubmit();
            }
            
            //nft発行フォームの送信
            if (!empty($_POST) && wp_verify_nonce( $_POST['nft_mint_nonce_name'], 'nft_mint_nonce_action' ) ) {
                $this->nftMintSubmit();
            }
            
            //nft MetaData作成フォームの送信
            if (!empty($_POST) && wp_verify_nonce( $_POST['nft_create_metadata_nonce_name'], 'nft_create_metadata_nonce_action' ) ) {
                $this->nftCreateMetadataSubmit();
            }

            //nft更新フォームの送信
            if (!empty($_POST) && wp_verify_nonce( $_POST['nft_edit_nonce_name'], 'nft_edit_nonce_action' ) ) {
                $this->nftEditSubmit();
            }

        }

        /**
         * Simple NFT Members 設定ページ表示処理
         */
        function snpmAdminPage(){

            //データの取得
            $request = get_option( 'snpm_general' );
            if($request === false){
                $request = array();
            }

            $this->views->viewSNMGeneral($request);

        }

        
        /**
         * DBのNFTの在庫を更新
         */
        function updateStock( ) {

            $request = $this->general->request('post');

            $fields = $this->defaultValue->update_stock_fields();
            $this->query->table_update($fields,$request);

        }
        
        /**
         * Optionのblockchain networkを更新
         */
        function changeNetwork() {

            $request = $this->general->request('post');

            //現在のデータの取得
            $snpm_general = get_option( 'snpm_general' );

            if(isset($request['blockchain_network']) && $request['blockchain_network']){
                $snpm_general['blockchain_network'] = $request['blockchain_network'];
            }

            //wp_optionsへ書き込み
            $fields = $this->defaultValue->snpm_general_fields();
            $this->query->wp_options_update($fields,$snpm_general);

        }

        /**
         * Simple NFT Members 設定更新
         */
        function snpmGeneralSubmit(){

            //サニタイズされたrequestデータを取得
            $request = $this->general->request('post');

            //入力チェック
            $error = $this->validation($request);

            if(!empty($error)) return;

            //wp_optionsへ書き込み
            $fields = $this->defaultValue->snpm_general_fields();
            $this->query->wp_options_update($fields,$request);

            //リダイレクト
            $referer = $this->general->getReferer();
            header("Location: ".$referer.'&message='.__('Update completed.', 'simple-nft-protection-manager')); exit;
        }

        /**
         * NFT新規発行ページ表示処理
         */
        function nftMintPage(){

            //creater addressのチェック
            $request = SNPM_GENERAL;
            if(!SNPM_CREATER_ADDRESS){
                $this->views->viewErrorMessage();
                return;
            }

            $this->views->viewNftMint($request);

        }

        /**
         * NFT新規発行送信処理
         */
        function nftMintSubmit(){

            //サニタイズされたrequestデータを取得
            $request = $this->general->request('post');

            //validation
            $error = $this->validation($request);
            if(!empty($error)) return;

            //フィールド情報を取得
            $fields = $this->defaultValue->nft_mint_fields();

            $dateTime = new \DateTime();
            $request['created_at'] = $dateTime->format("Y/m/d H:i:s");
            $request['token_stock'] = $request['token_issued'];
            $request['blockchain_network'] = SNPM_BLOCKCAHIN_NETWORK;

            //NFTテーブルに追加
            $this->query->table_insert($fields,$request);

            //metadata作成
            $this->createMetadata($request);

            //リダイレクト
            $referer = $this->general->getReferer();
            header("Location: ".$referer.'&message='.__('issued completed.', 'simple-nft-protection-manager')); exit;

        }

        /**
         * NFT新規発行ページ表示処理
         */
        function nftCreateMetadataPage(){

            //creater addressのチェック
            $request = SNPM_GENERAL;
            if(!SNPM_CREATER_ADDRESS){
                $this->views->viewErrorMessage();
                return;
            }

            $this->views->viewNftCreateMetadata($request);

        }

        /**
         * NFT Metadata作成送信処理
         */
        function nftCreateMetadataSubmit(){

            //サニタイズされたrequestデータを取得
            $request = $this->general->request('post');

            //validation
            $error = $this->validation($request);
            if(!empty($error)) return;

            //フィールド情報を取得
            $fields = $this->defaultValue->nft_create_metadata_fields();

            $dateTime = new \DateTime();
            $request['created_at'] = $dateTime->format("Y/m/d H:i:s");
            $request['blockchain_network'] = SNPM_BLOCKCAHIN_NETWORK;

            //NFTテーブルに追加
            $this->query->table_insert($fields,$request);

            //metadata作成
            $this->createMetadata($request);

            //リダイレクト
            $referer = $this->general->getReferer();
            header("Location: ".$referer.'&message='.__('Create Metadata completed.', 'simple-nft-protection-manager')); exit;

        }

        /**
         * NFT詳細ページ表示処理
         */
        function nftEditPage(){

            //creater addressのチェック
            $args = SNPM_GENERAL;
            if(!SNPM_CREATER_ADDRESS){
                $this->views->viewErrorMessage();
                return;
            }

            $fields = $this->defaultValue->nft_edit_fields();

            //データの取得
            $request = $this->general->request('get');
            if(isset($request['id']) && $request['id']){
                $results = $this->query->table_select($fields,$request);
            }
            if(isset($results[0])){
                $args = $results[0];
            }

            //viewの呼び出し
            $this->views->viewNftEdit($args);

        }

        /**
         * NFT新規発行送信処理
         */
        function nftEditSubmit(){

            //サニタイズされたrequestデータを取得
            $request = $this->general->request('post');

            //入力チェック
            $error = $this->validation($request);

            if(!empty($error)) return;

            $fields = $this->defaultValue->nft_edit_fields();
            $this->query->table_update($fields,$request);

            $this->createMetadata($request);

            //リダイレクト
            $referer = $this->general->getReferer();
            header("Location: ".$referer.'&message='.__('Update completed.', 'simple-nft-protection-manager')); exit;

        }

        /**
         * NFT一覧ページデータ件数の取得
         */
        function getNftCount(){

            $fields = $this->defaultValue->nft_mint_fields();

            $request = $this->general->request('get');
            $request['token_creater_address'] = SNPM_CREATER_ADDRESS;

            $count = $this->query->table_count($fields,$request);

            return $count;

        }

        /**
         * NFT一覧ページデータの取得
         */
        function getNftList($request = array()){

            $fields = $this->defaultValue->nft_list_fields();

            if(!is_array($request)) $request = array();
            $request['token_creater_address'] = SNPM_CREATER_ADDRESS;
            $request['blockchain_network'] = SNPM_BLOCKCAHIN_NETWORK;

            $list = $this->query->table_select($fields,$request);

            return $list;

        }

        function getExpirationDate($expiration){

            $dateTime = new \DateTime();
          
            if($expiration == 0 || $expiration == 9999999999999){
                $expiration = __('Indefinite period', 'simple-nft-protection-manager');
            }
            else if($expiration < $dateTime->getTimestamp()){
                $expiration = __('Expired', 'simple-nft-protection-manager');
            }
            else{
                $dateTime->setTimestamp($expiration);  
                $expiration = $dateTime->format('Y/m/d H:i:s');
            }
          
            return $expiration;
        }

        /**
         * NFT一覧ページ表示処理
         */
        function nftListPage(){

            //creater addressのチェック
            if(!SNPM_CREATER_ADDRESS){
                $this->views->viewErrorMessage();
                return;
            }

            $nftListTable= new snpmNftListTable();
            echo'<div class="wrap"><h2>'.__('Token list', 'simple-nft-protection-manager').'</h2>'; 
            echo'<input type="hidden" id="token_creater_address" name="token_creater_address" value="'.SNPM_CREATER_ADDRESS.'">'; 
            echo $this->views->connectMessage();
            $nftListTable->prepare_items(); 
            $nftListTable->display(); 
            
            echo'</div>'; 
        }

        /**
         * owned NFT一覧html取得処理
         */
        function ownedNftList(){

            $request = $this->general->request('post');
            
            $html = $this->views->ownedNftListFormat($request);

            return $html;
            
        }

        
        /**
         * NFT一覧ページデータ件数の取得
         */
        function getSalesCount(){

            $fields = $this->defaultValue->sales_list_fields();

            $request = $this->general->request('get');
            $request['token_creater_address'] = SNPM_CREATER_ADDRESS;

            $count = $this->query->table_count($fields,$request);

            return $count;

        }

        /**
         * NFT一覧ページデータの取得
         */
        function getSalesList($request = array()){

            $fields = $this->defaultValue->sales_list_fields();

            if(!is_array($request)) $request = array();
            $request['token_creater_address'] = SNPM_CREATER_ADDRESS;

            $list = $this->query->table_select($fields,$request);

            return $list;

        }

        /**
         * 売上一覧ページ表示処理
         */
        function salesListPage(){

            //creater addressのチェック
            if(!SNPM_CREATER_ADDRESS){
                $this->views->viewErrorMessage();
                return;
            }

            $request = $this->general->request('get');

            $salesListTable= new snpmSalesListTable();

            $this->views->viewSalesListPageHeader($request);//検索フォームとヘッダーエリア

            $salesListTable->prepare_items(); 
            $salesListTable->display(); 
            
            echo'</div>'; 
        }

        /**
         * DBのNFTの在庫を更新
         */
        function salesRegister( ) {

            $request = $this->general->request('post');

            $fields = $this->defaultValue->sales_register_fields();

            $dateTime = new \DateTime();
            $request['created_at'] = $dateTime->format("Y/m/d H:i:s");
            $request['blockchain_network'] = SNPM_BLOCKCAHIN_NETWORK;
            $request['token_creater_address'] = SNPM_CREATER_ADDRESS;

            $this->query->table_insert($fields,$request);

        }

        /**
         * validation処理
         */
        function validation($request = array()){

            $error = array();

            if(empty($request)){
                $request = $this->general->request('post');
            }

            if(!isset($request['ui_info_name']) && !$request['ui_info_name']) return $error;
            $ui_info_name = $request['ui_info_name'];
            if (method_exists($this->defaultValue,$ui_info_name)) {
                $fields = $this->defaultValue->$ui_info_name();
            }else{
                return $error;    
            }

            $validation = new snpmValidation();
            $error = $validation->checked($request['ui_info_name'],$fields,$request);

            return $error;

        }

        /**
         * metadata作成処理
         */
        function createMetadata($request){

            $content = '{
    "image":"'.$request['token_image_uri'].'",
    "tokenId":"'.$request['token_id'].'",
    "name":"'.$request['token_name'].'",
    "description":"'.$request['token_description'].'"
}';

            //wp_upload_dir() :uploadsディレクトリまでのパス
            $dir_path = SNPM_METADATA_DIR . SNPM_BLOCKCAHIN_NETWORK . '/';
/*
            //dir存在チェック
            if(!file_exists($dir_path)){
                mkdir($dir_path, 0755,true);//true ディレクトリを再帰的に作成
            }
*/
            //dir存在 or 作成出来た場合　true
            if ( wp_mkdir_p( $dir_path ) ) {
                // フォルダの成功または既に存在する場合の処理
                $file_path = $dir_path.$request['token_id'];

                //file存在チェック
                if(file_exists($file_path)){
                    unlink($file_path);
                }
    
                file_put_contents($file_path, $content);
    
            }

        }

        /**
         * 投稿ページにメタボックス（入力するエリア）を追加
         */
        public function addCustomMetaBox() {
            if (function_exists('add_meta_box')) {
                $post_types = get_post_types();
                foreach ($post_types as $post_type => $post_type) {
                    add_meta_box(
                        'snpm_sectionid',
                        __('Simple Token Protection', 'simple-nft-protection-manager'),//'Simple NFT Membersプロテクション',
                        array($this->views, 'viewProtectionBox'),
                        $post_type,
                        'advanced'
                    );
                }
            }
        }


        /**
         * カスタムメタボックスに入力した値を保存する
        */
        public function saveCustomeMetaBox( $post_id ) {

            //Check nonce
            $snpm_post_protection_box_nonce = filter_input(INPUT_POST, 'snpm_post_protection_box_nonce');
            if (!wp_verify_nonce($snpm_post_protection_box_nonce, 'snpm_post_protection_box_nonce_action')) {
                //Nonce check failed.
                return $post_id;
            }

            $set_meta_data = array();
            $fields = $this->defaultValue->nft_protection_fields();
            $request = $this->general->request('post');
            foreach($fields as $f_key => $f_value){
                if(!isset($f_value['input']) || !$f_value['input']) continue;
                if(!isset( $request[$f_key])) continue;
                $set_meta_data[$f_key] = $request[$f_key];
            }
            //post meta dataのセット
            update_post_meta( $post_id, "blockchain_network_".SNPM_BLOCKCAHIN_NETWORK, $set_meta_data );

        }

        
        /**
         * カスタムメタボックスの値を取得する
        */
        public function getProtectionData( $id ) {

            $meta_value = array();

            //chainnetwrok毎に取得
            if(!get_post_meta( $id, "blockchain_network_".SNPM_BLOCKCAHIN_NETWORK ,true )) return $meta_value;
            $post_meta = get_post_meta( $id, "blockchain_network_".SNPM_BLOCKCAHIN_NETWORK ,true );
    
            $fields = $this->defaultValue->nft_protection_fields();

            foreach($fields as $f_key => $f_value){
                if(!isset($f_value['data_type']) || !$f_value['data_type']) continue;
                //post meta data
                if(!$post_meta[$f_key]) continue;
                $meta_value[$f_key] = $post_meta[$f_key];
            }

            return $meta_value;
    
        }

        /**
         * pluginを有効化した時に実行する処理
        */
        public function activatePlugin(  ) {

            $nftTable = new snpmNftTable();
            $salesTable = new snpmSalesTable();

            //nft tableの作成
            $nftTable->nftTableInstall();

            //nft tableの作成
            $salesTable->salesTableInstall();

            //言語ファイルをlanguagesディレクトリにコピー
            $source = SNPM_PLUGIN_DIR . '/languages/'.'simple-nft-protection-manager'.'-ja.mo';
            $destination = WP_LANG_DIR . '/plugins/'.'simple-nft-protection-manager'.'-ja.mo';
            if ( file_exists( $source ) ) {
                copy( $source, $destination );
            }
        }

        /**
         * pluginを削除した時に実行する処理
         * この関数は使われていない代わりにuninstall.phpに記述
        */
        public function uninstallPlugin(  ) {

            $nftTable = new snpmNftTable();

            //tableの削除
            $nftTable->nftTableDelete();

        }

        /**
         *admin画面にプラグイン独自のCSS追加 
         */
        function adminAddPluginAsset(){

            wp_enqueue_script(
                SNPM_DIR_NAME . '_common',//スクリプトのハンドル名。ユニーク
                plugins_url('../assets/js/common.js', __FILE__),//.jsファイルの場所までのURL
                array(), //依存関係　該当のJavaScriptの読み込み前に読んでおくスクリプト$handleを配列形式で渡します。
                '', //ver情報
                false//trueにするとfooterに表示
            );

            global $hook_suffix;
            if(strpos($hook_suffix,'nft') === false && strpos($hook_suffix,'snpm') === false) return;//simple nft memberのページチェック
            if( !current_user_can( 'manage_options') ) return;//管理画面の権限チェック

            wp_enqueue_style( SNPM_DIR_NAME . '_bootstrap' , plugins_url('../assets/css/bootstrap.min.css', __FILE__));
            wp_enqueue_style( SNPM_DIR_NAME . '_common' , plugins_url('../assets/css/common.css', __FILE__));
            wp_enqueue_style( SNPM_DIR_NAME . '_component' , plugins_url('../assets/css/component.css', __FILE__));
            wp_enqueue_style( SNPM_DIR_NAME . '_fontawesome' , plugins_url('../assets/css/font-awesome.min.css', __FILE__));

            wp_enqueue_script(
                SNPM_DIR_NAME . '_web3',//スクリプトのハンドル名。ユニーク
                plugins_url('../assets/js/web3.min.js', __FILE__),//.jsファイルの場所までのURL
                array(), //依存関係　該当のJavaScriptの読み込み前に読んでおくスクリプト$handleを配列形式で渡します。
                '', //ver情報
                false//trueにするとfooterに表示
            );

                    
            wp_enqueue_script(
                SNPM_DIR_NAME . '_initial',//スクリプトのハンドル名。ユニーク
                plugins_url('../assets/js/initial.js', __FILE__),//.jsファイルの場所までのURL
                array(), //依存関係　該当のJavaScriptの読み込み前に読んでおくスクリプト$handleを配列形式で渡します。
                '', //ver情報
                false//trueにするとfooterに表示
            );

            wp_enqueue_script(
                SNPM_DIR_NAME . '_app_function',//スクリプトのハンドル名。ユニーク
                plugins_url('../assets/js/app_function.js', __FILE__),//.jsファイルの場所までのURL
                array(), //依存関係　該当のJavaScriptの読み込み前に読んでおくスクリプト$handleを配列形式で渡します。
                '', //ver情報
                true//trueにするとfooterに表示
            );

            wp_enqueue_script(
                SNPM_DIR_NAME . '_app_admin',//スクリプトのハンドル名。ユニーク
                plugins_url('../assets/js/app_admin.js', __FILE__),//.jsファイルの場所までのURL
                array(), //依存関係　該当のJavaScriptの読み込み前に読んでおくスクリプト$handleを配列形式で渡します。
                '', //ver情報
                true//trueにするとfooterに表示
            );

            $scripts = new snpmScripts();
            $scripts->jsAlertMessage();

        }

    }
endif;