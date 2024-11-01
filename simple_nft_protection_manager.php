<?php
/**
 * Plugin Name: Simple NFT Protection Manager
 * Plugin URI : https://webloco.webolha.com/simple_nft_members/
 * Description: This plugin publishes the original NFT. Selling NFTs.It is possible to make limited disclosure to users who have NFT.
 * Author:      Webloco by Mawsdesign
 * Author URI:  https://webloco.webolha.com/
 * Text Domain: simple-nft-protection-manager
 * Domain Path: /languages
 * Version:     1.0
 * License:     GPL2
 */
//---------------------------------------
//初期値の設定
//---------------------------------------
require_once __DIR__ . '/initial.php';

//---------------------------------------
//moduleの読み込み
//---------------------------------------
require_once __DIR__ . '/load.php';

$snpmShortcode = new snpmShortcode();

// do the nftTable
$nftTable = new snpmNftTable();

/**
 * 管理画面のインスタンス作成
 * 管理画面内ほとんどの処理は、このインスタンス内で処理される
 */
$snpmAdmin = new snpmAdmin();

//add_action( 'admin_notices', [ $snpmAdmin, 'admin_add_plugin_asset' ]);//WordPressの管理画面にメッセージを表示するために使用されるフック

/**
 * フロントエンド側のインスタンス作成
 * フロントエンド側のほとんどの処理は、このインスタンス内で処理される
 */
$snpmFront = new snpmFront();


//---------------------------------------
//ajaxの処理
//---------------------------------------
/**
 * ajax validation
 */
function validationByAjax() {

    global $snpmAdmin;

    //簡単なセキュリティのチェック 
    /**
     * wp_nonce_field( 'hogehoge_nonce_action', 'hogehoge_nonce_name' );
     * 以下のHTMLコードに変換される
     * <input type="hidden" id="hogehoge_nonce_name" name="hogehoge_nonce_name" value="40c892286b">
     * check_ajax_referer( 'hogehoge_nonce_action', 'hogehoge_nonce_name' );
     */
    check_ajax_referer( 'validation', 'validation' );

    /**
     * wp_nonce_field( 'hogehoge_nonce_action', 'hogehoge_nonce_name' );
     * wp_verify_nonce( $_REQUEST['hogehoge_nonce_name'], 'hogehoge_nonce_action' )
     */
    if ( wp_verify_nonce( $_POST['validation'], 'validation' ) ) {

        $error = $snpmAdmin->validation();
        $html = array('error'=>$error);
        echo json_encode($html); //jsonオブジェクト化。必須。配列でない場合は、敢えてjson化する必要はない

    }
    die();
}
//wp_ajax_[action名]・・・ログインしているユーザー用
//wp_ajax_nopriv_[action名]・・・ログインしていないユーザー用
add_action( 'wp_ajax_validation', 'validationByAjax' );
add_action( 'wp_ajax_nopriv_validation', 'validationByAjax' );

/**
 * ajax set user data
 */
function snpmSetUserDataAjax() {

    //簡単なセキュリティのチェック 
    check_ajax_referer( 'ajax_nonce', 'ajax_nonce' );

    if ( wp_verify_nonce( $_POST['ajax_nonce'], 'ajax_nonce' ) ) {

        global $snpmFront;

        $snpmFront->setUserData();
        echo json_encode(array());
        
    }
    die();

}
add_action( 'wp_ajax_set_user_data', 'snpmSetUserDataAjax' );
add_action( 'wp_ajax_nopriv_set_user_data', 'snpmSetUserDataAjax' );

/**
 * ajax update stock
 */
function snpmUpdateStockAjax() {

    //簡単なセキュリティのチェック 
    check_ajax_referer( 'ajax_nonce', 'ajax_nonce' );

    if ( wp_verify_nonce( $_POST['ajax_nonce'], 'ajax_nonce' ) ) {

        global $snpmAdmin;

        $snpmAdmin->updateStock();
        echo json_encode(array('flg'=>true));
        
    }
    die();

}
add_action( 'wp_ajax_update_stock', 'snpmUpdateStockAjax' );
add_action( 'wp_ajax_nopriv_update_stock', 'snpmUpdateStockAjax' );

/**
 * ajax update stock
 */
function snpmSalesRegisterAjax() {

    //簡単なセキュリティのチェック 
    check_ajax_referer( 'ajax_nonce', 'ajax_nonce' );

    if ( wp_verify_nonce( $_POST['ajax_nonce'], 'ajax_nonce' ) ) {

        global $snpmAdmin;

        $snpmAdmin->salesRegister();
        echo json_encode(array('flg'=>true));
        
    }
    die();

}
add_action( 'wp_ajax_sales_register', 'snpmSalesRegisterAjax' );
add_action( 'wp_ajax_nopriv_sales_register', 'snpmSalesRegisterAjax' );

/**
 * ajax update stock
 */
function snpmOwnedNftListAjax() {

    //簡単なセキュリティのチェック 
    check_ajax_referer( 'ajax_nonce', 'ajax_nonce' );

    if ( wp_verify_nonce( $_POST['ajax_nonce'], 'ajax_nonce' ) ) {

        global $snpmAdmin;

        $html = $snpmAdmin->ownedNftList();
        echo json_encode(array('html'=>$html));
        
    }
    die();

}
add_action( 'wp_ajax_owned_nft_list', 'snpmOwnedNftListAjax' );
add_action( 'wp_ajax_nopriv_owned_nft_list', 'snpmOwnedNftListAjax' );

/**
 * ajax change network
 */
function snpmChangeNetworkAjax() {

    //簡単なセキュリティのチェック 
    check_ajax_referer( 'ajax_nonce', 'ajax_nonce' );

    if ( wp_verify_nonce( $_POST['ajax_nonce'], 'ajax_nonce' ) ) {

        global $snpmAdmin;

        $snpmAdmin->changeNetwork();
        echo json_encode(array('flg'=>true));
        
    }
    die();

}
add_action( 'wp_ajax_change_network', 'snpmChangeNetworkAjax' );
add_action( 'wp_ajax_nopriv_change_network', 'snpmChangeNetworkAjax' );

//---------------------------------------
//plugin 有効化時の処理
//---------------------------------------
register_activation_hook( __FILE__, [$snpmAdmin,'activatePlugin'] );

//---------------------------------------
//plugin アンインストールした時の処理
//---------------------------------------
/**
 * WordPress 3.1以降、パラメータ$functionに指定できる関数にはクラスメソッドを使用できない。 
 * 以下の書き方はNG
 * register_uninstall_hook ( __FILE__, [$snpmAdmin,'uninstallPlugin'] );
 * 
 * uninstall.phpにアンインストールした時の処理を書く方法もある
 */
//register_uninstall_hook ( __FILE__, 'uninstallPlugin' );



/**
 * $hook_suffixを調べる
 */
/*
function current_pagehook(){
	global $hook_suffix;
	if( !current_user_can( 'manage_options') ) return;
	//echo '<div class="updated"><p>hook_suffix : ' . $hook_suffix . '</p></div>';
}
*/
//add_action('admin_notices', 'current_pagehook');
