<?php
/**---------------------------------------------------------
 * UIの設定
----------------------------------------------------------- */
class snpmViews {

    public $htmls;
    public $viewHtmls;
    public $scripts;
    public $general;
    public $defaultValue;

    public function __construct() {

        $this->htmls = new snpmHtmls();
        $this->viewHtmls = new snpmViewHtmls();
        $this->scripts = new snpmScripts();
        $this->general = new snpmGeneral();
        $this->defaultValue = new snpmDefaultValue();
    }

    /**
     * 処理完了メッセージ（HTML）取得
     */
    function displayMessage(){

        $html = '';

        $request = $this->general->request('get');

        if(isset($request['message']) && $request['message']){
            $html .= '<div class="updated notice notice-success is-dismissible">';
            $html .= '<p>'.$request['message']. '</p>' . "\n";
            $html .= '</div>';
        }

        return $html;

    }

    /**
     * エラー画面
     */
    function viewErrorMessage(){

        $html = '';

        $html .= '<div class="wrap">';
        $html .= '<div class="notice notice-warning is-dismissible">';
        $html .= '<p>'.__('The creator address is not registered. Please register the creator address on the SNPM setting screen.', 'simple-nft-protection-manager').'</p>' . "\n";//クリエイターアドレスが登録されていません。SNM設定画面でクリエイターアドレスを登録してください。
        $html .= '</div>';
        $html .= '</div>';

        echo wp_kses_post( $html );

    }

    /**
     * MetaMask接続メッセージ
     */
    function connectMessage(){

        $html = '';

        $html .= '<div id="connection_status" class="mt-2 notice notice-warning">';
        $html .= '<p>'.__('You are not connected to MetaMask. Please connect with MetaMask on the SNPM setting screen.', 'simple-nft-protection-manager').'</p>' . "\n";
        $html .= '</div>';

        return $html;

    }
    
    /**
     * SNM一般設定ページのUI（HTML）取得
     */
    function viewSNMGeneral($request = array()){

        $fields = $this->defaultValue->snpm_general_fields();
        //$label_html = $this->htmls->getLabel($fields);
        //$input_html = $this->htmls->getHtml($request,$fields);
        //$description_html = $this->htmls->getDescription($fields);
        //$other_html = array();
        //$layout_option = array();

        $form_name = 'snpm_general';

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline mb-3">'.__('SNPM general setting', 'simple-nft-protection-manager').'</h1>';
        echo $this->connectMessage();
        //完了メッセージの表示
        echo $this->displayMessage();

        echo '<form id="form" method="post" action="" class="">';

        echo '<button id="connect" type="button" class="btn btn-connect mb-2">
        '.__('Connect to MetaMask.', 'simple-nft-protection-manager').'</button>';

        //フォーム内のinputコードを指定のレイアウトに整形してHTMLで返す
        $this->viewHtmls->viewFormHtml($fields,$request);
        //echo $this->htmls->getFormHtml($fields,$label_html,$input_html,$description_html,$other_html,$layout_option);

        //WordPressのセキュリティ　CSRFトークンの様な物
        echo  wp_nonce_field( $form_name.'_nonce_action', $form_name.'_nonce_name' );
        echo  wp_nonce_field( 'validation', 'validation' );
     
        echo '<div id="submit-message" class=""></div>
        <button type="submit" name="'.esc_html($form_name).'_submit" class="button button-primary button-large" id="form-submit" onclick="return confirm(&quot;'.__('Do you really want to update?', 'simple-nft-protection-manager').'&quot;)">'.__('Update', 'simple-nft-protection-manager').'</button>' . "\n";

        echo '</form>';
        echo '</div>';

        //この画面で使用するscriptの追加
        $this->scripts->formSubmit('snpm_general_fields');

    }

    /**
     * NFT発行ページのUI（HTML）取得
     */
    function viewNftMint($request = array()){

        $fields = $this->defaultValue->nft_mint_fields();
        //$label_html = $this->htmls->getLabel($fields);
        //$input_html = $this->htmls->getHtml($request,$fields);
        //$description_html = $this->htmls->getDescription($fields);
        //$other_html = array();
        //$layout_option = array();

        $form_name = 'nft_mint';

        echo '<div id="loading">
        <div class="loader005"></div>
        </div>';

        echo '<div class="wrap">';

        echo '<h1 class="wp-heading-inline mb-3">'.__('Token new issue', 'simple-nft-protection-manager').'</h1>';

        echo $this->connectMessage();
        //完了メッセージの表示
        echo $this->displayMessage();        

        echo '<form id="form" method="post" action="" class="">';
        //$baseMetadataURIPrefix = plugins_url(SNPM_DIR_NAME.'/metadata/');
        //echo '<input type="hidden" name="baseMetadataURIPrefix" value="'.$baseMetadataURIPrefix.'">';
        echo '<input type="hidden" name="action" value="nft_mint">';

        //フォーム内のinputコードを指定のレイアウトに整形してHTMLで返す
        $this->viewHtmls->viewFormHtml($fields,$request);
        //echo $this->htmls->getFormHtml($fields,$label_html,$input_html,$description_html,$other_html,$layout_option);

        //WordPressのセキュリティ　CSRFトークンの様な物
        echo  wp_nonce_field( $form_name.'_nonce_action', $form_name.'_nonce_name' );
        echo  wp_nonce_field( 'validation', 'validation' );
     
        echo '<div id="submit-message" class=""></div>
        <button type="submit" name="'.esc_html($form_name).'_submit" class="button button-primary button-large" id="form-submit" onclick="return confirm(&quot;'.__('Do you really want to issue a new one?', 'simple-nft-protection-manager').'&quot;)">'.__('Token new issue', 'simple-nft-protection-manager').'</button>' . "\n";

        echo '</input>';
        echo '</div>';

        //この画面で使用するscriptの追加
        $this->scripts->formSubmitBeforeMint('nft_mint_fields');
        $this->scripts->mediaUploader();

    }


    /**
     * NFT発行ページのUI（HTML）取得
     */
    function viewNftCreateMetadata($request = array()){

        $fields = $this->defaultValue->nft_create_metadata_fields();
        //$label_html = $this->htmls->getLabel($fields);
        //$input_html = $this->htmls->getHtml($request,$fields);
        //$description_html = $this->htmls->getDescription($fields);
        //$other_html = array();
        //$layout_option = array();

        $form_name = 'nft_create_metadata';

        echo '<div id="loading">
        <div class="loader005"></div>
        </div>';

        echo '<div class="wrap">';

        echo '<h1 class="wp-heading-inline mb-3">'.__('Create token MetaData', 'simple-nft-protection-manager').'</h1>';

        echo $this->connectMessage();
        //完了メッセージの表示
        echo $this->displayMessage();        

        echo '<form id="form" method="post" action="" class="">';
        //$baseMetadataURIPrefix = plugins_url(SNPM_DIR_NAME.'/metadata/');
        //echo '<input type="hidden" name="baseMetadataURIPrefix" value="'.$baseMetadataURIPrefix.'">';
        echo '<input type="hidden" name="action" value="nft_create_metadata">';

        //フォーム内のinputコードを指定のレイアウトに整形してHTMLで返す
        $this->viewHtmls->viewFormHtml($fields,$request);
        //echo $this->htmls->getFormHtml($fields,$label_html,$input_html,$description_html,$other_html,$layout_option);

        //WordPressのセキュリティ　CSRFトークンの様な物
        echo  wp_nonce_field( $form_name.'_nonce_action', $form_name.'_nonce_name' );
        echo  wp_nonce_field( 'validation', 'validation' );
     
        echo '<div id="submit-message" class=""></div>
        <button type="submit" name="'.esc_html($form_name).'_submit" class="button button-primary button-large" id="form-submit" onclick="return confirm(&quot;'.__('Do you really want to create a token MetaData?', 'simple-nft-protection-manager').'&quot;)">'.__('Create Token MetaData', 'simple-nft-protection-manager').'</button>' . "\n";

        echo '</input>';
        echo '</div>';

        //この画面で使用するscriptの追加
        $this->scripts->formSubmit('nft_create_metadata_fields');
        $this->scripts->mediaUploader();

    }

        
    /**
     * NFT 詳細ページのUI（HTML）取得
     */
    function viewNftEdit($request = array()){

        $fields = $this->defaultValue->nft_edit_fields();
        //$label_html = $this->htmls->getLabel($fields);
        //$input_html = $this->htmls->getHtml($request,$fields);
        //$description_html = $this->htmls->getDescription($fields);
        //$other_html = array();
        //$layout_option = array();

        $form_name = 'nft_edit';

        echo '<div class="wrap">';

        echo '<h1 class="wp-heading-inline mb-3">' . __('Change token MetaData', 'simple-nft-protection-manager') . '</h1>';
        echo $this->connectMessage();
        //完了メッセージの表示
        echo $this->displayMessage();        

        echo '<form id="form" method="post" action="">';

        //フォーム内のinputコードを指定のレイアウトに整形してHTMLで返す
        $this->viewHtmls->viewFormHtml($fields,$request);
        //echo $this->htmls->getFormHtml($fields,$label_html,$input_html,$description_html,$other_html,$layout_option);

        echo  wp_nonce_field( $form_name.'_nonce_action', $form_name.'_nonce_name' );
        echo  wp_nonce_field( 'validation', 'validation' );
        
        echo '<div id="submit-message" class=""></div>
        <button type="submit" name="'.esc_html($form_name).'_submit" class="button button-primary button-large" id="form-submit" onclick="return confirm(&quot;'.__('Do you really want to update?', 'simple-nft-protection-manager').'&quot;)">'.__('Update', 'simple-nft-protection-manager').'</button>' . "\n";

        echo '</form>';
        echo '</div>';

        //この画面で使用するscriptの追加
        $this->scripts->formSubmit('nft_edit_fields');
        $this->scripts->mediaUploader();

    }
    
    /**
     * 売上一覧ページのヘッダーエリア
     */
    function viewSalesListPageHeader($request = array()) {

        echo '<div class="wrap"><h2>'.__('Sales list', 'simple-nft-protection-manager').'</h2>'; 
        echo '<input type="hidden" id="token_creater_address" name="token_creater_address" value="'.SNPM_CREATER_ADDRESS.'">'; 
        echo $this->connectMessage();
        $this->viewSearchForm($request);

    }

    /**
     * 一覧表示のsearch form
     */
    function viewSearchForm($request = array()) {

        $from_date = '';
        if(isset($request['from_date'])){$from_date = $request['from_date'];}
        
        $to_date = '';
        if(isset($request['to_date'])){$to_date = $request['to_date'];}

        $token_id = '';
        if(isset($request['token_id'])){$token_id = $request['token_id'];}
        
        echo '<form id="posts-filter" action="" method="get">
        <input type="hidden" name="page" value="nft_sales_list">
        <div class="search-box">
        日付:
        <input type="date" name="from_date" value="'.esc_html($from_date).'" />~
        <input type="date" name="to_date" value="'.esc_html($to_date).'" />
        token id:
        <input type="search" id="post-search-input" name="token_id" value="'.esc_html($token_id).'" />
        <input type="submit" id="search-submit" class="button" value="検索"  />
        </div>
        </form>';

    }

    /**
     * 入力するエリアのHTML
     */
    public function viewProtectionBox() {

        global $post, $wpdb, $snpmAdmin;
        $id = $post->ID;

        $fields = $this->defaultValue->nft_protection_fields();

        //カスタムメタボックスの値を取得する
        $meta_value = $snpmAdmin->getProtectionData( $id );

        //$label_html = $this->htmls->getLabel($fields);
        //$input_html = $this->htmls->getHtml($meta_value,$fields);
        //$description_html = $this->htmls->getDescription($fields);

        echo '<input type="hidden" name="snpm_post_protection_box_nonce" value="' . wp_create_nonce('snpm_post_protection_box_nonce_action') . '" />';
        //echo '<h4>'.__('Limited published this content?', 'simple-nft-protection-manager').'</h4>';
        $this->viewHtmls->viewFormHtml($fields,$meta_value);
        //echo $this->htmls->getFormHtml($fields,$label_html,$input_html,$description_html);

        //echo '<h4>'.__('Please select an NFT that can view this content.', 'simple-nft-protection-manager').'</h4>';

        echo "<script>
        let select_nft_elem;

        var display_control = {
            'nft_scope':{
                'ids':['row-3'],
                'display':'select'
            },
        };

        window.addEventListener('load', function(){//HTML読み込み完了後に実行

            value_check(display_control);

        });
        
        document.addEventListener(\"change\", function (event) {

            var name = event.target.name;
            var value = event.target.value;

            // 一つでもチェックを外すと「全て選択」のチェック外れる
            disChecked(event)

        	// 値によって、項目の表示・非表示を切り替え
            if(typeof display_control !== 'undefined' && display_control !== null && display_control[name] !== null && typeof display_control[name] !== 'undefined'){
                var ids = display_control[name]['ids'];
                var display = display_control[name]['display'];
                style_change(value,ids,display);//選択した値によって、フィールドの表示非表示を切り替え
            }

            // 選択した値によって、表示する値や選択肢を切り替え
            if(typeof option_value_control !== 'undefined' && option_value_control !== null){
                var name = event.target.name;
                option_value_change(value,name);//選択した値によって、フィールドの選択肢を変更
            }
        });
        </script>";

    }

    
    /**
     * 特定のページの閲覧に必要なNFTの一覧を表示
     */
    function scopeNftList() {

        global $snpmAdmin,$wpdb;
        $post_id = get_the_ID(); // 投稿IDを取得

        //chainnetwrok毎に取得
        $meta_value = $snpmAdmin->getProtectionData( $post_id );

        $nft_scope = $meta_value['nft_scope'];//NFTの範囲
        $select_nft = $meta_value['select_nft'];//選択されたNFTの一覧

        //全てのNFTを取得
        if($nft_scope == 'all'){
            $request = [
                'num'=>999,
                'paged'=>1,
            ];

            $nfts = $snpmAdmin->getNftList($request);

        }
        //選択されたNFTを取得
        else{

            $nftTable = new snpmNftTable();
            $nfts = $nftTable->getNftListFromId($select_nft);//id配列からtoken情報を取得

        }

        $html = '';
        //一覧のHTMLを取得
        $html .= $this->nftListFormat($nfts);//NFT一覧のフォーマットにデータを流し込む

        return $html;
        
    }

    
    /**
     * 投稿ページや固定ページのコンテンツを保護処理
     */
    function snpm_protection_content( $content ) {

        
        $custom_content = '';
        $custom_content .= '<h3>'. __('This content is protected.', 'simple-nft-protection-manager') .'</h3>';//このコンテンツは保護されています。

        $custom_content .= '<p>'. __('If you possess an token with viewing permission, please connect to MetaMask from the following button.', 'simple-nft-protection-manager') .'</p>';//閲覧権限のあるNFTを所持している場合は、以下のボタンより「MetaMask」に接続してください。
        $custom_content .= '<p><button id="connect" type="button" class="btn btn-connect btn-md mb-2">
        '.__('Connect to MetaMask.', 'simple-nft-protection-manager').'</button></p>';//MetaMaskに接続


        $custom_content .= '<h4>'. __('List of tokens that can access/view this content', 'simple-nft-protection-manager') .'</h4>';//このコンテンツを閲覧できるNFT一覧
        $custom_content .= '<p>'. __('If you do not possess an token with viewing permission, you can purchase it from the following after connecting to MetaMask.', 'simple-nft-protection-manager') .'</p>';//閲覧権限のあるNFTを所持していない場合は、「MetaMask」に接続後、以下よりご購入いただけます。

        //NFT一覧表示
        $custom_content .= $this->scopeNftList();

        return $custom_content;
    }

    /**
     * NFT一覧のフォーマット（定型書式）
     */
    function nftListFormat($nfts,$label = ''){

        $html = '';

        if(!$label){
            $label = __('Purchase this token', 'simple-nft-protection-manager');
        }

        $issued_label = __('Issued', 'simple-nft-protection-manager');
        $stock_label = __('Number of tokens in stock', 'simple-nft-protection-manager');
        $price_label = __('Selling price', 'simple-nft-protection-manager');
        $expiration_label = __('Expiration date', 'simple-nft-protection-manager');

        //NFT一覧表示
        //----------------------------------------
        $html .= '<section class="nft-list-wrap">';
        $html .= '<input type="hidden" id="creater_address" name="creater_address" value="'.SNPM_CREATER_ADDRESS.'">';
        $html .= '<ul class="nft-list">';

        foreach($nfts as $n_key => $n_value){

            if($n_value['token_expiration_date'] == 0){
                $expiration_date = __('Indefinite period', 'simple-nft-protection-manager');
            }else{
                $expiration_date = $n_value['token_expiration_date'] . __('days', 'simple-nft-protection-manager');
            }

            if($n_value['token_stock'] == 0){
                //$cta = '<div class="out_of_stock">'.__('Out of stock', 'simple-nft-protection-manager')."</div>";
                $cta = '<button type="button" class="buy_token inactive card-btn btn-md">'.__('Out of stock', 'simple-nft-protection-manager')."</button>";
            }else{
                $cta = '<button type="button" class="buy_token card-btn btn-md" data-tokenId="'.$n_value['token_id'].'" data-cost="'.$n_value['token_cost'].'" data-expiration="'.$n_value['token_expiration_date'].'">'.$label.'</button>';
            }
            $html .= '<li>
            <div class="card">
            <div class="card-img"><img src="'.$n_value['token_image_uri'].'" alt="'.$n_value['token_name'].'"></div>
            <div class="card-contents">
            <h3 class="card-title">'.$n_value['token_name'].'</h3>
            <div class="card-description">'.$n_value['token_description'].'</div>
            <div class="card-issued"><span class="card-label">'.$stock_label.'/'.$issued_label.'</span>'.$n_value['token_stock'].'/'.$n_value['token_issued'].'</div>
            <div class="card-expiration-date"><span class="card-label">'.$expiration_label.'</span>'.$expiration_date.'</div>
            <div class="card-price"><span class="card-label">'.$price_label.'</span><span class="card-price">'.$n_value['token_cost'].'</span>Matic</div>
            </div>
            <div class="card-footer">
            '.$cta.'
            </div>
            </div>
            </li>';

        }

        $html .= '</ul>';
        $html .= '</section>';

        return $html;

    }


    /**
     * ownedNFT一覧のフォーマット（定型書式）
     */
    function ownedNftListFormat($request){

        global $snpmAdmin;
        $nftTable= new snpmNftTable();
        $nfts = $nftTable->getNftListFromId($request['owned_nfts']);

        $html = '';
        
        if(empty($nfts)){
            return '<p>'.__('You do not have a token issued by the creator of this site.', 'simple-nft-protection-manager') .'</p>';//このサイトのクリエイターが発行したトークンを所持していません。
        }

        $expiration_label = __('Expiration date', 'simple-nft-protection-manager');
        $issued_label = __('Number of possessions', 'simple-nft-protection-manager');

        foreach($nfts as $n_key => $n_value){

            $token_id = $n_value['token_id'];
            $expiration_date = $snpmAdmin->getExpirationDate($request['expiration'][$token_id]);
            $owned = $request['owned'][$token_id];

            $html .= '<li>
            <div class="card">
            <div class="card-img"><img src="'.$n_value['token_image_uri'].'" alt="'.$n_value['token_name'].'"></div>
            <div class="card-contents">
            <h3 class="card-title">'.$n_value['token_name'].'['.$n_value['token_id'].']</h3>            
            <div class="card-description">'.$n_value['token_description'].'</div>
            <div class="card-expiration-date"><span class="card-label">'.$expiration_label.'</span>'.$expiration_date.'</div>
            <div class="card-owned"><span class="card-label">'.$issued_label.'</span>'.$owned.'</div>
            </div>
            </div>
            </li>';

        }

        return $html;

    }

}