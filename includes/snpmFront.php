<?php
/**---------------------------------------------------------
 * frontページ用のclass
----------------------------------------------------------- */
if ( ! class_exists( 'snpmFront' ) ) :
	class snpmFront {

        public $views;
        public $query;
        public $general;
        public $defaultValue;

        public function __construct() {

            //フロント側に独自のjs cssを読み込み
            add_action( 'wp_enqueue_scripts', [ $this, 'addPluginAsset' ]);

            add_action( 'wp', [$this ,'snpmProtectionPostContent'] );//WordPressのページが呼び出されたときに発動するフック

            add_action( 'wp_footer', [$this ,'addScriptFooter'] );//footerエリアにコードを差し込むフック

            $this->views = new snpmViews();
            $this->query = new snpmQuery();
            $this->general = new snpmGeneral();
            $this->defaultValue = new snpmDefaultValue();
        }


        /**
         * 投稿ページや固定ページの保護を判別する処理
         */
        function snpmProtectionPostContent() {
            if( is_singular() ) { // 投稿ページや固定ページの場合に実行
                $post_id = get_the_ID(); // 投稿IDを取得

                //プロテクションデータの取得
                if(!get_post_meta( $post_id, "blockchain_network_".SNPM_BLOCKCAHIN_NETWORK ,true )){
                    return;
                }else{
                    $protection_data = get_post_meta( $post_id, "blockchain_network_".SNPM_BLOCKCAHIN_NETWORK ,true );
                }
                
                // nft_protectionが「未設定」or「1：保護しない」の場合
                if(!isset($protection_data['nft_protection']) || !$protection_data['nft_protection'] || $protection_data['nft_protection'] == 1 )  return;

                // nft_protectionが「2：保護する」の場合
                //------------------------------------------
                $session = $this->general->request('session');
                //ユーザーのNFT情報を確認
                if(isset($session['snpm_user']) && $session['snpm_user']){
                    $snpm_user = $session['snpm_user'];//ユーザーのNFT情報

                    //NFTの範囲が「全て」となっており、ユーザーが１つでも所持している場合
                    if(isset($protection_data['nft_scope']) && $protection_data['nft_scope'] == 'all' && !empty($snpm_user['owned_nfts'])) return;

                    // NFTの範囲が「選択」の場合
                    //------------------------------------------
                    if(isset($protection_data['select_nft']) && $protection_data['select_nft']){
                        $select_nft = $protection_data['select_nft'];//選択されたNFTの一覧
                    }else{
                        $select_nft = array();
                    }

                    //NFTを何も選択されておらず、ユーザーが１つでも所持している場合
                    if(empty($select_nft) && !empty($snpm_user['owned_nfts'])) return;

                    //NFTの範囲が「選択」となっている場合で、ユーザーが対象のNFTを１つでも所持している場合
                    $owned_nfts = $snpm_user['owned_nfts'];
                    $resutls = array();
                    if(is_array($owned_nfts)){
                        $resutls = array_intersect($owned_nfts,$select_nft);//値を基準に配列の共通項を取得
                    }

                    //$resutls = array_intersect_key($owned_nfts,$select_nft);//キーを基準に配列の共通項を取得

                    //重複したデータがある場合は、所持しているという事
                    if(!empty($resutls)) return;
                }

                //投稿ページや固定ページのコンテンツを保護処理
                add_filter( 'the_content', [$this->views ,'snpm_protection_content']);

            }
        }


        /**
         * footerエリアに差し込むscript
         */
        function addScriptFooter( ) {

            echo "<script>
             var ajaxurl = '". admin_url('admin-ajax.php') . "';
             </script>\n";

            $scripts = new snpmScripts();
            $scripts->jsAlertMessage();

        }
        
        /**
         * user dataをセッションに格納
         */
        function setUserData( ) {

            $request = $this->general->request('post');

            $userData = array();

            $userData['snpm_user_address'] = $request['snpm_user_address'];

            if(isset($request['owned_nfts'])){
                $userData['owned_nfts'] = $request['owned_nfts'];
            }else{
                $userData['owned_nfts'] = array();
            }

            $_SESSION['snpm_user'] = $userData;

        }


        /**
         *フロントにプラグイン独自のCSS追加 
         */
        function addPluginAsset(){

            global $hook_suffix;
            
            wp_enqueue_style( SNPM_DIR_NAME . '_style' , plugins_url('../assets/css/style.css', __FILE__));
            wp_enqueue_style( SNPM_DIR_NAME . '_fontawesome' , plugins_url('../assets/css/font-awesome.min.css', __FILE__));
            
            wp_enqueue_script(
                SNPM_DIR_NAME . '_common',//スクリプトのハンドル名。ユニーク
                plugins_url('../assets/js/common.js', __FILE__),//.jsファイルの場所までのURL
                array(), //依存関係　該当のJavaScriptの読み込み前に読んでおくスクリプト$handleを配列形式で渡します。
                '', //ver情報
                false//trueにするとfooterに表示
            );

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
                SNPM_DIR_NAME . '_app_front',//スクリプトのハンドル名。ユニーク
                plugins_url('../assets/js/app_front.js', __FILE__),//.jsファイルの場所までのURL
                array(), //依存関係　該当のJavaScriptの読み込み前に読んでおくスクリプト$handleを配列形式で渡します。
                '', //ver情報
                true//trueにするとfooterに表示
            );

            wp_enqueue_script(
                SNPM_DIR_NAME . '_app_function',//スクリプトのハンドル名。ユニーク
                plugins_url('../assets/js/app_function.js', __FILE__),//.jsファイルの場所までのURL
                array(), //依存関係　該当のJavaScriptの読み込み前に読んでおくスクリプト$handleを配列形式で渡します。
                '', //ver情報
                true//trueにするとfooterに表示
            );

        }

    }
endif;