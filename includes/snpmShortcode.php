<?php
/**---------------------------------------------------------
 * shortcodeの設定
----------------------------------------------------------- */
class snpmShortcode {

    public $htmls;
    public $views;
    public $scripts;
    public $general;
    public $defaultValue;

    public function __construct() {

        $this->htmls = new snpmHtmls();
        $this->views = new snpmViews();
        $this->scripts = new snpmScripts();
        $this->general = new snpmGeneral();
        $this->defaultValue = new snpmDefaultValue();

        add_shortcode('connect', [$this ,'shortcodeConnect']);
        add_shortcode('nft_list', [$this ,'shortcodeNftList']);
    }

    
    /**
     * NFT 詳細ページのUI（HTML）取得
     */
    function shortcodeConnect($attr){

        (isset($attr['title']))? $title = $attr['title']: $title = 'このサイトで購入したNFT一覧';

        $html = '';

        $html .= '<button id="connect" type="button" class="cta-btn btn-connect btn-md mb-sm">
        MetaMaskに接続する
        </button>
        <section id="my_page" style="display:none">
        <div id="loading">
        <div class="loader005"></div>
        </div>
        <h3>'.$title.'</h3>
        <button id="viewOwn" type="button" class="cta-btn btn-display btn-md mb-sm">
        NFTを表示する
        </button>
        <div id="owned-nft-list">
        </div>
        </section>';

        return $html;

    }
    
    /**
     * NFT 詳細ページのUI（HTML）取得
     */
    function shortcodeNftList($attr){

        $html = '';

        global $snpmAdmin;

        $request = $this->general->request();
        (isset($attr['num']))? $request['num'] = $attr['num']: $request['num'] = 12;
        
        //現在のページ番号取得
        $request['paged'] = 1;
        if(get_query_var( 'paged' )){
            $request['paged'] = get_query_var( 'paged' );//一覧ページのページ
        }elseif(get_query_var( 'page' )){
            $request['paged'] = get_query_var( 'page' );//固定ページのページ
        }
        $request['blockchain_network'] = SNPM_BLOCKCAHIN_NETWORK;
        $request['token_display_flg'] = 1;//表示フラグ
        $request['orderby'] = 'token_display_order';//表示順
        $request['order'] = 'asc';//昇順
        
        //NFTデータ取得
        $total_items = $snpmAdmin->getNftCount();
        $nfts = $snpmAdmin->getNftList($request);

        if(empty($nfts)) return '<p>'.__('There are no tokens to display.', 'simple-nft-protection-manager').'</p>';//表示するNFTはありません。

        //shortcodeのパラメータ
        (isset($attr['label']))?$label = $attr['label']: $label = __('Purchase this token', 'simple-nft-protection-manager');//このトークンを購入

        //NFT一覧表示
        //----------------------------------------
        $html .= $this->views->nftListFormat($nfts,$label);//NFT一覧のフォーマット

        //pagination取得
        $url = get_the_permalink();//現在のページ
        $html .= $this->htmls->pagination($total_items,$request['paged'],$request['num'],$url);

        return $html;

    }

}