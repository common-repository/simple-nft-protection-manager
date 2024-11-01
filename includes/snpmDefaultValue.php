<?php
/**---------------------------------------------------------
 * defaultValueの設定
----------------------------------------------------------- */
class snpmDefaultValue {

    function require_fields(){

        $fields = [
            'submit_flg' =>
            [
                'input'=>'hidden',
                'layout'=>'hidden',
            ] 
        ];

        return $fields;

    }

    function blockchain_network(){
        $fields = [
            '137' => __('Polygon Mainnet', 'simple-nft-protection-manager'),//137は、Polygon Mainnetのchain id(network id)
            '80001' => __('Polygon Testnet(Mumbai)', 'simple-nft-protection-manager')//80001は、Mumbaiのchain id(network id)
        ];

        return $fields;
    }
    
    function token_name_list(){


        $values = array();

        

        return $values;
    }
    
    public function snpm_general_fields(){

        global $wpdb;

        $metadata_place_message = __('This plugin creates metadata in the "DIR"', 'simple-nft-protection-manager');
        $metadata_place_message = str_replace('DIR','<span style="display:inline-block;padding:0px 5px;background:#ccc">'.SNPM_METADATA_URL.SNPM_BLOCKCAHIN_NETWORK.'/'.'</span>',$metadata_place_message);

        $fields = [
            'general_info' => 
            [
                'table_name'=>'snpm_general',
                'ui_name'=>__('SNM general setting', 'simple-nft-protection-manager'),//'SNM一般設定',
                'script'=>'',
            ],
            'row-1-1' => 
            [

            ],
            'terms_of_service' => 
            [
                'label'=>__('Terms of service', 'simple-nft-protection-manager'),//
                'input'=>'checkbox',
                'data_type'=>'choices',
                'choices' =>['1'=>__('I agree to the <a href="https://webloco.webolha.com/snpm-terms-of-service-en" target="_blank">terms of use.</a>', 'simple-nft-protection-manager')],
                'value'=>'',
                'validation'=>['required'],
                'layout'=>'col-md-6',
                'class'=>'form-control',
                //'description'=>__('<a href="https://webloco.webolha.com/" _target="blank">Simple NFT Protection Manager Terms of Use</a>.', 'simple-nft-protection-manager'),//Simple NFT Protection Managerの利用規約。
            ],
            'row-1-2' => 
            [

            ],
            'blockchain_network' => 
            [
                'label'=>__('Block Chain network', 'simple-nft-protection-manager'),//
                'input'=>'radio',
                'data_type'=>'text',
                'choices' =>$this->blockchain_network(),
                'value'=>'',
                'validation'=>['required'],
                'default'=>'80001',
                'layout'=>'col-md-6',
                'class'=>'form-control',
                'description'=>__('The network of the blockchain to connect to.', 'simple-nft-protection-manager'),//'接続するブロックチェーンのネットワーク',
            ],
            'row-1-3' => 
            [

            ],
            'token_creater_address' => 
            [
                'label'=>__('creator address', 'simple-nft-protection-manager'),//'クリエイターアドレス',
                'input'=>'text',
                'data_type'=>'text',
                'value'=>'',
                'validation'=>['required'],
                'default'=>'',
                'layout'=>'col-md-6',
                'class'=>'form-control',
                'description'=>__('Address to issue Token. And the token creator address displayed on this site.', 'simple-nft-protection-manager'),//'NFTを発行するアドレス。且つ、このサイトで表示するNFTのクリエイターアドレス',
            ],
            'row-2' => 
            [

            ],
            'get_creater_address_button' => 
            [
                'label'=>'',
                'html'=>'<button type="button" id="get_address" class="button button-secondary button-large">' . __('To get your address from MetaMask', 'simple-nft-protection-manager') . '</button>',//MetaMaskからアドレスを取得
                'layout'=>'col-md-3',
            ],
            'row-3-1' => 
            [

            ],
            'current_base_metadata_uri' => 
            [
                'label'=>__('Current base metadata URI', 'simple-nft-protection-manager'),//'Meta data uri',
                'html'=>'<div id="current_base_metadata_uri"></div>',
                'layout'=>'col-md-6',
                'description'=>__('This is the location where the metadata (the file containing information such as NFT image, name, and attributes) is stored.<br>When reflecting information such as NFT images and names on marketplaces such as OpenSea, it is necessary to register the location where the metadata is stored on the blockchain.', 'simple-nft-protection-manager'),//'メタデータ（NFTの画像や名前、属性などの情報が格納されているファイル）が保管されている場所です。通常はデフォルトのままで大丈夫です。Open Seaなどのマーケットプレイスに、NFTの画像や名前などの情報を反映させた場合、ブロックチェーン上に、メタデータが保管されている場所を登録する必要があります。',
            ],
            'row-3' => 
            [

            ],
            'base_metadata_uri' => 
            [
                'label'=>__('Base URI for the metadata to set', 'simple-nft-protection-manager'),//'Meta data uri',
                'input'=>'text',
                'data_type'=>'text',
                //'value'=>'',
                //'validation'=>['required'],
                //'default'=>plugins_url(SNPM_DIR_NAME.'/metadata/'.SNPM_BLOCKCAHIN_NETWORK.'/'),
                'layout'=>'col-md-6',
                'class'=>'form-control',
                'description'=>$metadata_place_message,
            ],
            'row-4' => 
            [

            ],
            'set_metadata_uri_button' => 
            [
                'html'=>'<div><button type="button" id="set_uri" class="button button-secondary button-large">' . __('Set the base URI', 'simple-nft-protection-manager') . '</button></div>',
                'layout'=>'col-md-6',
                'description'=>__('If you want to set the base URI of metadata on the blockchain, please click the "Set URI" button.', 'simple-nft-protection-manager'),//'ブロックチェーン上に、メタデータのベースURIを設定する場合、「URIのセット」ボタンをクリックしてください。',
            ],
            'row-5' => 
            [

            ],
            'get_balance_button' => 
            [
                'label'=>__('Token sales balance', 'simple-nft-protection-manager'),//'NFT売上残高',
                'html'=>'<div><button type="button" id="get_balance" class="button button-secondary button-large">' . __('Update the sales balance of the token.', 'simple-nft-protection-manager') . '</button></div>',//NFT売上残高を更新
                'layout'=>'col-md-6',
            ],
            'row-6' => 
            [

            ],
            'balance' => 
            [
                'html'=>'<div id="balance"></div>',
                'layout'=>'col-md-6',
                'description'=>__('The amount obtained by subtracting a 3% royalty fee from the token sales will be added to this balance.', 'simple-nft-protection-manager'),//トークンの売上から3％のロイヤリティを引いた金額が、この残高に加算されます。
            ],
            'row-7' => 
            [

            ],
            'withdraw_balance_button' => 
            [
                //'label'=>__('Withdraw from token sales balance', 'simple-nft-protection-manager'),//'NFT売上残高から引き出し',
                'html'=>'<div><button type="button" id="withdraw" class="button button-secondary button-large">残高から引き出し</button></div>',
                'layout'=>'col-md-3',
            ],
        ];

        $fields += $this->require_fields();

        return $fields;   
    }

    /**
     * NFTの発行ページのフィールド情報
     * 
     * 戻り値：設定情報：【配列】
     */
    public function nft_mint_fields(){

        global $wpdb;

        $fields = [
            'general_info' => 
            [
                'table_name'=>$wpdb->prefix.'nft_table',
                'ui_name'=>__('Token new issue', 'simple-nft-protection-manager'),//'NFT新規発行',
                'script'=>'',
            ],
            'created_at' => 
            [
                'input'=>'datetime',
                'data_type'=>'datetime',
                'value'=>'',
                'default'=>'',
            ], 
            'token_id' => 
            [
                'input'=>'hidden',
                'data_type'=>'number',
                'value'=>'',
                'layout'=>'hidden',
            ], 
            'blockchain_network' => 
            [
                'label'=>__('Blockchain network', 'simple-nft-protection-manager'),//
                'choices' =>$this->blockchain_network(),
                'data_type'=>'text',
                'value'=>'',
                'description'=>__('The blockchain network that issued this token.', 'simple-nft-protection-manager'),//'このNFTを発行したブロックチェーンネットワーク。',
            ],
            'row-2' => 
            [

            ], 
            'token_creater_address' => 
            [
                'label'=>__('creator address', 'simple-nft-protection-manager'),//'クリエイターアドレス',
                'input'=>'text',
                'data_type'=>'text',
                'class'=>'form-control',
                'option' =>'readonly',
                'value'=>'',
                'layout'=>'col-md-6',
                'description'=>__('The address that issued (did) this token.', 'simple-nft-protection-manager'),//'このNFTを発行する（した）アドレス。',
            ],
            'row-2-1' => 
            [

            ], 
            'token_display_flg' => 
            [
                'label'=>__('Display flg', 'simple-nft-protection-manager'),//
                'input'=>'radio',
                'data_type'=>'number',
                'class'=>'form-control',
                'choices' =>[
                    1 => __('Display', 'simple-nft-protection-manager'),
                    9 => __('Do not display', 'simple-nft-protection-manager')
                ],
                'default'=>1,
                'option' =>'',
                'value'=>'',
                'validation'=>['required'],
                'layout'=>'col-md-3',
                'description'=>__('This is the setting to display or not display to the users of this site.', 'simple-nft-protection-manager'),//'このサイトのユーザーに表示する、しないの設定です。',
            ], 
            'token_display_order' => 
            [
                'label'=>__('Display order', 'simple-nft-protection-manager'),//
                'input'=>'number',
                'data_type'=>'number',
                'class'=>'form-control',
                'option' =>'',
                'value'=>'',
                'layout'=>'col-md-3',
                'description'=>__('The smaller the number, the earlier it will be displayed/shown first.', 'simple-nft-protection-manager'),//'数字が小さいほど、最初に表示されます。',
            ], 
            'row-2-2' => 
            [

            ], 
            'token_name' => 
            [
                'label'=>__('Token name', 'simple-nft-protection-manager'),//'NFT名',
                'input'=>'text',
                'data_type'=>'text',
                'class'=>'form-control',
                'option' =>'',
                'value'=>'',
                'validation'=>['required'],
                'layout'=>'col-md-3',
            ], 
            'token_description' => 
            [
                'label'=>__('description', 'simple-nft-protection-manager'),//'概要',
                'input'=>'text',
                'data_type'=>'text',
                'class'=>'form-control',
                'option' =>'',
                'value'=>'',
                'layout'=>'col-md-6',
            ],   
            'row-3' => 
            [

            ],  
            'token_cost' => 
            [
                'label'=>__('Selling price', 'simple-nft-protection-manager'),//'販売価格',
                'input'=>'number',
                'data_type'=>'float',
                'class'=>'form-control',
                'option' =>'placeholder="0.1" step="0.001"',
                'value'=>'',
                'validation'=>['compare','required'],
                'compare'=>['token_cost > 0'],
                'layout'=>'col-md-3',
                'description'=>__('Unit: matic', 'simple-nft-protection-manager'),//'単位：matic',
            ], 
            'token_issued' => 
            [
                'label'=>__('Issued', 'simple-nft-protection-manager'),//'発行数',
                'input'=>'number',
                'data_type'=>'number',
                'class'=>'form-control',
                'option' =>'placeholder="例) 1000"',
                'value'=>'',
                'validation'=>['compare','required'],
                'compare'=>['token_issued > 0'],
                'layout'=>'col-md-3',
                'description'=>__('The total issuance of this token.', 'simple-nft-protection-manager'),//'このNFTの総発行数。',
            ], 
            'token_expiration_date' => 
            [
                'label'=>__('Expiration date', 'simple-nft-protection-manager'),//'有効期限',
                'input'=>'number',
                'data_type'=>'number',
                'class'=>'form-control',
                'option' =>'placeholder="例) 30"',
                'value'=>'',
                'layout'=>'col-md-3',
                'description'=>__('You can set the expiration date for this token. The unit is "days." If you set it to "0", it will be valid indefinitely.', 'simple-nft-protection-manager'),//'このトークンの有効期限を設定できます。単位は「日」です。「0」を設定した場合は、無期限となります。',
            ], 
            'row-4' => 
            [

            ],  
            'token_stock' => 
            [
                'label'=>__('Number of tokens in stock', 'simple-nft-protection-manager'),//'クリエイターの所有数',
                'description'=>__('It is the number of tokens in stock and also the number owned by the creator.', 'simple-nft-protection-manager'),//'トークンの在庫数であり、クリエイターが所有している数でもある。',
                'input'=>'number',
                'value'=>'',
            ], 
            'update_token_stock' => //DBにはない
            [
                'label'=>__('Update stock', 'simple-nft-protection-manager'),//'在庫数を更新',
                'description'=>__('If tokens are moved outside of this site, you need to click on "Update inventory count" to adjust the inventory to the correct number.', 'simple-nft-protection-manager'),//このサイト以外で、トークンの移動をした場合、「在庫数を更新」をクリックして、正しい在庫数に修正する必要があります。
            ],
            'token_burn_number' => //DBにはない
            [
                'label'=>__('Incinerate token', 'simple-nft-protection-manager'),//'NFTを焼却する',
            ], 
            'row-4-2' => 
            [

            ], 
            'token_transfer' => //DBにはない
            [
                'label'=>__('Transfer the token', 'simple-nft-protection-manager'),
                'description'=>__('A token with an expiration date will be limited to one per address. Therefore, even if multiple tokens are transferred to the same address, only one will be transferred. Additionally, if the recipient already owns the token, the transfer will not be executed. Instead, the expiration date of the token will be extended by the number of tokens transferred.', 'simple-nft-protection-manager'),//有効期限のあるトークンは、同一アドレスにつき1つとなります。その為、同一アドレスに複数個転送した場合でも、1つしか転送されません。また、既に所有している場合は、トークンの転送は行われません。その代わり、転送個数分の有効期限がトークンに加算されます。
            ], 
            'row-5' => 
            [

            ],    
            'token_image_uri' => 
            [
                'label'=>__('Token image', 'simple-nft-protection-manager'),//'NFT画像',
                'input'=>'media',
                'data_type'=>'text',
                'class'=>'form-control',
                //'option' =>'placeholder="httsp://www.sample.com/wp-content/uploads/2022/01/sample.jpg"',
                'value'=>'',
                'validation'=>['required'],
                'layout'=>'col-md-6',
            ],
            'row-6' => 
            [

            ],           
            'token_note' => 
            [
                'label'=>__('Note', 'simple-nft-protection-manager'),//'備考欄',
                'input'=>'textarea',
                'data_type'=>'text',
                'class'=>'form-control',
                'option' =>'',
                'value'=>'',
                'layout'=>'col-md-6',
            ],
        ];

        $fields += $this->require_fields();

        return $fields;
    }

    
    /**
     * NFTのMetaData作成ページのフィールド情報
     * 
     * 戻り値：設定情報：【配列】
     */
    public function nft_create_metadata_fields(){

        global $wpdb;
        $fields = $this->nft_mint_fields();

        //hiddenとして、inputフィールドは残しておく
        $fields['token_cost']['value'] = '';
        $fields['token_issued']['value'] = '';
        $fields['token_expiration_date']['value'] = '';
        $fields['token_stock']['value'] = '';
        $fields['token_cost']['input'] = 'hidden';
        $fields['token_issued']['input'] = 'hidden';
        $fields['token_expiration_date']['input'] = 'hidden';
        $fields['token_stock']['input'] = 'hidden';
        $fields['token_cost']['layout'] = 'hidden';
        $fields['token_issued']['layout'] = 'hidden';
        $fields['token_expiration_date']['layout'] = 'hidden';
        $fields['token_stock']['layout'] = 'hidden';

        $fields['token_id']['label'] = 'Token ID';
        $fields['token_id']['input'] = 'number';
        $fields['token_id']['default'] = '';
        $fields['token_id']['validation'] = ['compare','not_blank','double'];
        $fields['token_id']['compare'] = ['token_id >= 0'];
        $fields['token_id']['double_where'] = 'blockchain_network = "'.SNPM_BLOCKCAHIN_NETWORK . '"';
        $fields['token_id']['table'] = $wpdb->prefix.'nft_table';
        $fields['token_id']['layout'] = 'col-md-3';

        $fields['blockchain_network']['data_type'] = 'choice';
        $fields['blockchain_network']['layout'] = 'col-md-3';

        return $fields;

    }

    /**
     * NFTの変更ページのフィールド情報
     * 
     * 戻り値：設定情報：【配列】
     */
    public function nft_edit_fields(){

        $fields = $this->nft_mint_fields();

        unset($fields['created_at']['value']);
        unset($fields['token_id']['value']);
        unset($fields['blockchain_network']['value']);
        unset($fields['token_cost']['value']);
        unset($fields['token_issued']['value']);

        $fields['token_id']['label'] = 'Token ID';
        $fields['token_id']['input'] = 'text';
        $fields['token_id']['option'] = 'readonly';
        $fields['token_id']['layout'] = 'col-md-3';

        $fields['blockchain_network']['data_type'] = 'choice';
        $fields['blockchain_network']['layout'] = 'col-md-3';

        $fields['token_cost']['option'] = 'readonly';
        $fields['token_cost']['validation'] = array();
        $fields['token_issued']['option'] = 'readonly';
        $fields['token_issued']['validation'] = array();
        $fields['token_expiration_date']['option'] = 'readonly';

        $fields['id'] = [
            'input'=>'hidden',
            'data_type'=>'number',
            'layout'=>'hidden',
        ];

        $fields['general_info']['where'] = [
            ['id','=','id'],
        ];

        //クリエイターの所有数（在庫数）
        $fields['token_stock']['layout'] = 'col-md-3';
        $fields['token_stock']['input'] = 'number';
        $fields['token_stock']['data_type'] = 'number';
        $fields['token_stock']['class'] = 'form-control';
        $fields['token_stock']['option'] = 'readonly';
        //$fields['token_stock']['html'] = '<input type="number" id="token_owned_number" name="token_owned_number" value="" class="form-control" readonly>';

        $fields['update_token_stock']['layout'] = 'col-md-3';
        $fields['update_token_stock']['html'] = '<div><button type="button" id="update_stock" class="button button-secondary" onclick="return confirm(&quot;'.__('Are you sure you want to update stock?', 'simple-nft-protection-manager').'&quot;)">'.__('Update stock', 'simple-nft-protection-manager').'</button></div>';
        

        $fields['token_burn_number']['layout'] = 'col-md-3';
        $fields['token_burn_number']['html'] = '<div>'.__('Quantity', 'simple-nft-protection-manager').'：<input type="number" id="token_burn_number" name="token_burn_number" value="" class="form-control mb-1 w-5em d-inline">
        <button type="button" id="burn" class="button button-danger button-large" onclick="return confirm(&quot;'.__('Are you sure you want to incinerate?', 'simple-nft-protection-manager').'&quot;)">'.__('Incinerate', 'simple-nft-protection-manager').'</button></div>';

        $fields['token_transfer']['layout'] = 'col-md-12';
        $fields['token_transfer']['html'] = '<div>'.__('Quantity', 'simple-nft-protection-manager').'：<input type="number" id="token_transfer_number" name="token_transfer_number" value="" class="form-control mb-1 w-5em d-inline">
        '.__('To address', 'simple-nft-protection-manager').'：<input type="text" id="to_address" name="to_address" value="" class="form-control mb-1 w-40 d-inline">
        <button type="button" id="transfer" class="button button-primary button-large" onclick="return confirm(&quot;'.__('Are you sure you want to transfer?', 'simple-nft-protection-manager').'&quot;)">'.__('Transfer token', 'simple-nft-protection-manager').'</button></div>';

        return $fields;

    }

    /**
     * NFT一覧ページのフィールド情報
     * 
     * 戻り値：設定情報：【配列】
     */
    public function nft_list_fields(){

        $fields = $this->nft_edit_fields();

        $fields['general_info']['where'] = [
            ['token_creater_address','=','token_creater_address'],
            ['token_display_flg','=','token_display_flg'],
            ['blockchain_network','=','blockchain_network'],
        ];

        return $fields;

    }

    
    /**
     * NFTの在庫更新のフィールド情報
     * 
     * 戻り値：設定情報：【配列】
     */
    public function update_stock_fields(){

        global $wpdb;

        $fields = [
            'general_info' => 
            [
                'table_name'=>$wpdb->prefix.'nft_table',
                'ui_name'=>__('Token new issue', 'simple-nft-protection-manager'),//'NFT新規発行',
                'script'=>'',
                'where'=>[
                    ['token_id','=','token_id']
                ],
            ],
            'token_stock' => 
            [
                'label'=>'token stock',
                'data_type'=>'number',
                'input'=>'number',//'クリエイターの所有数',
                'value'=>'',//'トークンの在庫数であり、クリエイターが所有している数でもある。',
            ]
        ];


        return $fields;
    }

    /**
     * NFTの変更ページのフィールド情報
     * 
     * 戻り値：設定情報：【配列】
     */
    public function nft_protection_fields(){

        $fields = [
            'row-1' => 
            [

            ],
            'nft_protection' => 
            [
                'label'=>__('Limited published this content?', 'simple-nft-protection-manager'),
                'input'=>'radio',
                'data_type'=>'text',
                'choices' =>[
                    1 => __('No, Do not protect this content.', 'simple-nft-protection-manager'),
                    2 => __('Yes, Protect this content.', 'simple-nft-protection-manager')
                ],
                'default'=>'1',
                'value'=>'',
                //'validation'=>['required'],
                'default'=>'1',
                'layout'=>'col-md-12',
                'class'=>'form-control',
            ],
            'row-2' => 
            [

            ],
            'view_nfts_title' => 
            [
                'html'=>'<h4>'.__('Please select an token that can view this content.', 'simple-nft-protection-manager').'</h4>',
                'layout'=>'col-md-12',
            ],
            'nft_scope' => 
            [
                'label'=>__('Token scope', 'simple-nft-protection-manager'),
                'input'=>'radio',
                'data_type'=>'text',
                'choices' =>[
                    'all' => __('All Token', 'simple-nft-protection-manager'),
                    'select' => __('Select Token', 'simple-nft-protection-manager')
                ],
                'value'=>'',
                //'validation'=>['required'],
                'default'=>'all',
                'layout'=>'col-md-12',
                'class'=>'form-control',
            ],
            'row-3' => 
            [

            ],
            'select_nft' => 
            [
                'label'=>__('Token list', 'simple-nft-protection-manager'),
                'input'=>'checkbox',
                'data_type'=>'choices',
                'choices' =>$this->nftArrayTokenIdKey(),
                'value'=>'',
                //'validation'=>['required'],
                //'default'=>'1',
                'layout'=>'col-md-12',
                'class'=>'form-control',
            ],
        ];

        return $fields;

    }

    /**
     * NFT一覧情報を、Token IDをキーにして、NFT名と説明の情報が値の連想配列を返す
     */
    function nftArrayTokenIdKey($mode = 'description'){

        global $snpmAdmin;    
        $nft_list = $snpmAdmin->getNftList();

        $array = array();
        foreach($nft_list as $n_key => $n_value){
            $token_id = $n_value['token_id'];

            //descriptionが存在しており、modeがdescriptionの場合
            if($mode != 'name' && $n_value['token_description']){
                $array[$token_id] = $n_value['token_name'] . '(' . $n_value['token_description'] . ')';
            }else{
                $array[$token_id] = $n_value['token_name'];
            }

        }

        return $array;

    }

    
    /**
     * 売上記録のフィールド情報
     * 
     * 戻り値：設定情報：【配列】
     */
    public function sales_register_fields(){

        global $wpdb;

        $fields = [
            'general_info' => 
            [
                'table_name'=>$wpdb->prefix.'sales_table',
                'ui_name'=>__('Sales list', 'simple-nft-protection-manager'),
                'script'=>'',
            ],
            'created_at' => 
            [
                'input'=>'datetime',
                'data_type'=>'datetime',
                'value'=>'',
            ], 
            'token_id' => 
            [
                'label'=>'Token id',//
                'input'=>'hidden',
                'data_type'=>'number',
                'value'=>'',
            ], 
            'blockchain_network' => 
            [
                'label'=>__('Blockchain network', 'simple-nft-protection-manager'),//
                'input'=>'radio',
                'choices' =>$this->blockchain_network(),
                'data_type'=>'text',
                'value'=>'',
            ],
            'token_creater_address' => 
            [
                'label'=>__('creator address', 'simple-nft-protection-manager'),//'クリエイターアドレス',
                'input'=>'text',
                'data_type'=>'text',
                'value'=>'',
            ],
            'token_name' => 
            [
                'label'=>__('Token name', 'simple-nft-protection-manager'),//'NFT名',
                'input'=>'text',
                //'data_type'=>'text',
                //'value'=>'',
            ], 
            'sales' => 
            [
                'label'=>__('Sales', 'simple-nft-protection-manager'),//'販売価格',
                'input'=>'number',
                'data_type'=>'float',
                'value'=>'',
            ], 
            'quantity' => 
            [
                'label'=>__('Quantity', 'simple-nft-protection-manager'),//'発行数',
                'input'=>'number',
                'data_type'=>'number',
                'value'=>'',
            ],  
            'customer_address' => 
            [
                'label'=>__('Customer address', 'simple-nft-protection-manager'),//'クリエイターの所有数',
                'input'=>'text',
                'data_type'=>'text',
                'value'=>'',
            ], 
        ];

        $fields += $this->require_fields();

        return $fields;
    }


    /**
     * 売上一覧ページのフィールド情報
     * 
     * 戻り値：設定情報：【配列】
     */
    public function sales_list_fields(){

        $fields = $this->sales_register_fields();

        $fields['general_info']['where'] = [
            ['created_at','>=','from_date'],
            ['created_at','<=','to_date'],
            ['token_creater_address','=','token_creater_address'],
            ['token_id','=','token_id'],
        ];

        return $fields;

    }

}