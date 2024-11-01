<?php
/**---------------------------------------------------------
 * 初期値の設定
 * 定数や変数の設定
----------------------------------------------------------- */
//定数の設定
//---------------------------------------------------------
define( 'SNPM_DIR_NAME', 'simple-nft-protection-manager' );//このプラグインのディレクトリ名

define( 'SNPM_PLUGIN_PREFIX', 'snpm' );//このプラグインの識別名

define( 'SNPM_PLUGIN', __FILE__ );//このファイルのフルパス

define( 'SNPM_PLUGIN_DIR', untrailingslashit( dirname( SNPM_PLUGIN ) ) );//このディレクトリまでのフルパス

$upload_dir = wp_get_upload_dir();
define( 'SNPM_METADATA_DIR', $upload_dir['basedir'] . '/simple_nft_protection_manager/metadata/' );//metadataを書き込むディレクトリまでのフルパス
define( 'SNPM_METADATA_URL', $upload_dir['baseurl'] . '/simple_nft_protection_manager/metadata/' );//metadataを書き込むディレクトリまでのフルパス

//管理画面の権限
define( 'SNPM_ADMIN_CAPABILITY', 'edit_dashboard' );

//custom post typeの権限
if ( ! defined( 'SNPM_ADMIN_READ_CAPABILITY' ) ) {
	define( 'SNPM_ADMIN_READ_CAPABILITY', 'edit_posts' );
}

//custom post typeの権限
if ( ! defined( 'SNPM_ADMIN_READ_WRITE_CAPABILITY' ) ) {
	define( 'SNPM_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );
}

//wp optionの取得
$snpm_general = get_option( 'snpm_general' );
define( 'SNPM_GENERAL', $snpm_general );

//creater address
$creater_address = '';
if(isset($snpm_general['token_creater_address']) && $snpm_general['token_creater_address']){
	$creater_address = $snpm_general['token_creater_address'];
}
define( 'SNPM_CREATER_ADDRESS', $creater_address );

//contract address
$contract_address = '';
if(isset($snpm_general['blockchain_network']) && $snpm_general['blockchain_network'] == '137'){
	$contract_address = "0x41fA458F768083972E8230Ae3fCD3E710c15240f";//live chain
}else{
	$contract_address = "0x5199C3F821a39BC53D8BD3BeD99101d4246Ad1A6";//test chain
}
define( 'SNPM_CONTRACT_ADDRESS', $contract_address );

//blockchain_network
$blockchain_network = '80001';//Polygon testnet(mumbai)
if(isset($snpm_general['blockchain_network']) && $snpm_general['blockchain_network']){
	$blockchain_network = $snpm_general['blockchain_network'];//live chain
}
define( 'SNPM_BLOCKCAHIN_NETWORK', $blockchain_network );

//---------------------------------------------------------
//一般設定
//---------------------------------------------------------
//セッションの開始
session_start();

//timizone
date_default_timezone_set('Asia/Tokyo');

//翻訳言語ファイルの設定
load_plugin_textdomain( 
	'simple-nft-protection-manager',
	false,
	SNPM_PLUGIN_DIR . '/languages/'
);
