<?php
/**---------------------------------------------------------
 * よく使うscriptsの登録
----------------------------------------------------------- */
class snpmScripts {


    /**
     * media uploaderのscript
     */
    function mediaUploader(){

        //メディアアップローダー用のscript
        echo "<script>
        jQuery(document).ready(function($){
            var mediaUploader;
        
            $('.upload_image_button').click(function(e) {
                e.preventDefault();
                var field = e.target.dataset.field;
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media.frames.file_frame = wp.media({
                    title: '画像をアップロード',
                    button: {
                        text: '画像を選択'
                    },
                    multiple: false
                });
        
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();

                    //メディアで選択した画像のURLを、valueにセットする
                    $('#'+field).val(attachment.url);
                    
                    //画像を表示
                    var img_html = '<img src=\"'+attachment.url+'\">';
                    $('#'+field+'_thumbnail').children().remove();
                    $('#'+field+'_thumbnail').append(img_html);

                    //強制的にchangeイベントを発火
                    const event = new Event('change'); // changeイベントを作成する
                    form_elem.dispatchEvent(event); // input要素にイベントを発火する
                });
        
                mediaUploader.open();
            });

            $('.remove_image_button').click(function(e) {
                e.preventDefault();
                var field = e.target.dataset.field;
                $('#'+field).val('');
                $('#'+field+'_thumbnail').children().remove();

                //強制的にchangeイベントを発火
                const event = new Event('change'); // changeイベントを作成する
                form_elem.dispatchEvent(event); // input要素にイベントを発火する
            });
        });

        // wp_enqueue_media()関数を使ってスクリプトを読み込む
        jQuery(document).ready(function($){
            if( typeof wp !== 'undefined' && wp.media && wp.media.editor ){
                ".wp_enqueue_media()."
            }
        });
        </script>";

    }


    /**
     * form submit のscript
     */
    function formSubmit($ui_info_name){

        //メディアアップローダー用のscript
        echo "<script>

            window.addEventListener('load', function(){//HTML読み込み完了後に実行

                //submit
                //---------------------------------------------
                var form_elem = document.getElementById('form');
                // フォームの submit イベントを乗っ取り
                form_submit(form_elem,'".esc_html($ui_info_name)."',callback_form_submit);
            
                //リアルタイムvalidation
                fields_change(form_elem,'".esc_html($ui_info_name)."')

            });

        
            /**
             * エラーが無い場合の処理
             * 第1引数：バリデーションチェック後、バックエンドから戻ってきたjsonデータ：【オブジェクト型】
             * 第2引数：いろいろな値 ：【配列】送信したフォームデータ（オブジェクト型、配列）、コールバック関数
             */
            async function callback_form_submit(data,param){

                //form 送信
                document.forms.form.submit();
            
            }
        
        </script>";

    }

    
    /**
     * NFTを発行してform submit のscript
     */
    function formSubmitBeforeMint($ui_info_name){

        //メディアアップローダー用のscript
        echo "<script>

            //submit
            //---------------------------------------------
            var form_elem = document.getElementById('form');
            // フォームの submit イベントを乗っ取り
            form_submit(form_elem,'".esc_html($ui_info_name)."',callback_form_submit);
            
            //リアルタイムvalidation
            fields_change(form_elem,'".esc_html($ui_info_name)."')
        
            /**
             * エラーが無い場合の処理
             * 第1引数：バリデーションチェック後、バックエンドから戻ってきたjsonデータ：【オブジェクト型】
             * 第2引数：いろいろな値 ：【配列】送信したフォームデータ（オブジェクト型、配列）、コールバック関数
             */
            async function callback_form_submit(data,param){

                
                if(param.data.action == 'nft_mint'){
                    var token_id = await mint(param.data);

                    if(!token_id) return;

                    var token_id_elem = document.getElementById('token_id');
                    token_id_elem.value = token_id;

                    //form 送信
                    document.forms.form.submit();
                }
            
            }
        
        </script>";

    }


    /**
     * javascript側で出力するalertメッセージを出力
     */
    function jsAlertMessage(){

        $creater_address = SNPM_CREATER_ADDRESS;
        $contract_address = SNPM_CONTRACT_ADDRESS;
        $current_network_id = SNPM_BLOCKCAHIN_NETWORK;
        $change_network_notice = __('Switch blockchain network?', 'simple-nft-protection-manager');//ブロックチェーンネットワークを切り替えますか？
        $denied_transaction_message = __('User denied transaction signature.', 'simple-nft-protection-manager');//ユーザーがトランザクションのリクエストを拒否しました。
        $transaction_pending_message = __('Transaction was not mined within 50 blocks, please make sure your transaction was properly sent. Be aware that it might still be mined!', 'simple-nft-protection-manager');//処理が保留中になっています。MetaMaskからガス代を追加してスピードアップするか、処理をキャンセルした後、再度（ガス代を積極的にして）購入処理をしてください。
        $current_metadata_message = __('The base URI for metadata is not registered on the blockchain.', 'simple-nft-protection-manager');//ブロックチェーン上に、ベースメタデータURIが登録されていません。
        $not_network_message = __('This site is currently network enabled for [chainid]. Your MetaMask network is not [chainid]. Please switch to [chainid].', 'simple-nft-protection-manager');//現在、このサイトは、[chainid]のネットワークが有効になっています。あなたのMetaMaskのネットワークが[chainid]ではありません。[chainid]に切り替えてください。
        $not_exist_token_message = __('A token with the specified token ID does not exist on the blockchain.', 'simple-nft-protection-manager');//指定のトークンIDのトークンがブロックチェーン上に存在しません。
        $not_blockchain_message = __('The blockchain network has not been configured/set up.', 'simple-nft-protection-manager');//ブロックチェーンネットワークが設定されていません。
        $possessions_message = __('Number of possessions', 'simple-nft-protection-manager');//所持数
        $connecting_message = __('Connected to MetaMask', 'simple-nft-protection-manager');
        $address_different_message = __('The registered creator address is different from the connected address.', 'simple-nft-protection-manager');//登録されているクリエイターアドレスと接続したアドレスが違います。
        $change_address_message = __('Please change your MetaMask address.', 'simple-nft-protection-manager');//MetaMaskのアドレスを変更してください。
        $connect_message = __('Please connect to MetaMask.', 'simple-nft-protection-manager');//MetaMaskに接続してください。
        $could_not_connect_message = __('Could not connect.', 'simple-nft-protection-manager');//接続できませんでした。
        $not_installed_mobile_message = __('MetaMask is not installed. Please install the MetaMask app and open it from the browser of that app.', 'simple-nft-protection-manager');//MetaMaskがインストールされていません。MetaMaskのアプリをインストールして、そのアプリのブラウザから開いてください。
        $not_installed_message = __('MetaMask is not installed.', 'simple-nft-protection-manager');//MetaMaskがインストールされていません。
        $connected_address_message = __('Connected address', 'simple-nft-protection-manager');//接続アドレス
        $balance_message = __('NFT sales balance', 'simple-nft-protection-manager');
        $no_balance_message = __('There is no withdrawable balance.', 'simple-nft-protection-manager');//引き出せる残高がありません。
        $withdrawal_message = __('The withdrawal has been completed.', 'simple-nft-protection-manager');//引き出しが完了しました。
        $quantity_exceeds_message = __('The quantity exceeds the owned stock.', 'simple-nft-protection-manager');//数量が保有在庫を超えています。
        $quantity_not_message = __('The quantity has not been entered.', 'simple-nft-protection-manager');//数量が未入力です。
        $quantity_incorrect_message = __('The quantity is incorrect.', 'simple-nft-protection-manager');//数量が正しくありません。
        $to_address_not_message = __('The to address has not been entered.', 'simple-nft-protection-manager');//宛先が未入力です。
        $update_stock_message = __('Inventory update has been completed.', 'simple-nft-protection-manager');//在庫の更新が完了しました。
        $expiration_date_label = __('Expiration date', 'simple-nft-protection-manager');
        $indefinite_period = __('Indefinite period', 'simple-nft-protection-manager');
        $set_uri_message = __('Please enter the base URI of the metadata.', 'simple-nft-protection-manager');
        $expired = __('Expired', 'simple-nft-protection-manager');
        $expiration_notice = __('Token with expiration dates cannot be listed or sold on marketplaces such as "Open Sea." Additionally, if you already own the same NFT, the quantity will not increase, but the expiration date will be extended.', 'simple-nft-protection-manager');//有効期限のあるNFTは、「Open Sea」など、マーケットプレイスで出品、販売は出来ません。また、既に同じNFTを所持している場合は、個数は増えず、有効期限が加算されます。
        $network_change_message = __('If you change the blockchain network, please do not forget to also change MetaMasks network to the corresponding network.', 'simple-nft-protection-manager');//ブロックチェーンネットワークを変更した場合、MetaMaskのネットワークも忘れずに、該当のネットワークに変更してください。
        $not_creater_address_message = __('The creator address is not registered. Please register the creator address on the SNM setting screen.', 'simple-nft-protection-manager');//ブロックチェーンネットワークを変更した場合、MetaMaskのネットワークも忘れずに、該当のネットワークに変更してください。
        $minting_request_message = __('Token issuance process request is in progress! Please wait until it is completed.', 'simple-nft-protection-manager');//トークン発行処理リクエスト中！完了するまでこのままお待ちください。
        $minting_process_message = __('Token issuance process is in progress! Please wait until it is completed.', 'simple-nft-protection-manager');//トークン発行処理実行中！完了するまでこのままお待ちください。
        $process_message = __('In process', 'simple-nft-protection-manager');//購入手続き中！
        $purchase_completed_message = __('Purchase successful!', 'simple-nft-protection-manager');//購入完了
        $price_label = __('Price', 'simple-nft-protection-manager');//価格
        $quantity_label = __('Quantity', 'simple-nft-protection-manager');//数量

        $ajax_nonce = wp_create_nonce( 'ajax_nonce' );

        echo "<script>
        const creater_address = '".esc_html($creater_address)."';
        const contract_address = '".esc_html($contract_address)."';
        let current_network_id = '".esc_html($current_network_id)."';
        let ajax_nonce = '".esc_html($ajax_nonce)."';
        let change_network_notice = '".esc_html($change_network_notice)."';
        let denied_transaction_message = '".esc_html($denied_transaction_message)."';
        let current_metadata_message = '".esc_html($current_metadata_message)."';
        let not_exist_token_message = '".esc_html($not_exist_token_message)."';
        let not_blockchain_message = '".esc_html($not_blockchain_message)."';
        let not_network_message = '".esc_html($not_network_message)."';
        let possessions_message = '".esc_html($possessions_message)."';
        let expiration_date_label = '".esc_html($expiration_date_label)."';
        let indefinite_period = '".esc_html($indefinite_period)."';
        let connecting_message = '".esc_html($connecting_message)."';
        let address_different_message = '".esc_html($address_different_message)."';
        let change_address_message = '".esc_html($change_address_message)."';
        let could_not_connect_message = '".esc_html($could_not_connect_message)."';
        let not_installed_mobile_message = '".esc_html($not_installed_mobile_message)."';
        let not_installed_message = '".esc_html($not_installed_message)."';
        let connect_message = '".esc_html($connect_message)."';
        let connected_address_message = '".esc_html($connected_address_message)."';
        let balance_message = '".esc_html($balance_message)."';
        let no_balance_message = '".esc_html($no_balance_message)."';
        let withdrawal_message = '".esc_html($withdrawal_message)."';
        let quantity_exceeds_message = '".esc_html($quantity_exceeds_message)."';
        let quantity_not_message = '".esc_html($quantity_not_message)."';
        let quantity_incorrect_message = '".esc_html($quantity_incorrect_message)."';
        let to_address_not_message = '".esc_html($to_address_not_message)."';
        let update_stock_message = '".esc_html($update_stock_message)."';
        let set_uri_message = '".esc_html($set_uri_message)."';
        let expired = '".esc_html($expired)."';
        let expiration_notice = '".esc_html($expiration_notice)."';
        let network_change_message = '".esc_html($network_change_message)."';
        let not_creater_address_message = '".esc_html($not_creater_address_message)."';
        let minting_request_message = '".esc_html($minting_request_message)."';
        let minting_process_message = '".esc_html($minting_process_message)."';
        let process_message = '".esc_html($process_message)."';
        let purchase_completed_message = '".esc_html($purchase_completed_message)."';
        let price_label = '".esc_html($price_label)."';
        let quantity_label = '".esc_html($quantity_label)."';

        </script>";

    }

}