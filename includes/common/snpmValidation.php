<?php
/**---------------------------------------------------------
 * validation
----------------------------------------------------------- */
class snpmValidation {

    /**
     * バリデーションチェックのメイン処理
     * 
     * 第1引数：フィールド基本設定名：【文字列】
     * 第2引数：フィールドの基本設定情報：【配列】
     * 第3引数：request情報：【配列】
     * 第4引数：メタ情報（補足情報）：【配列】
     * 戻り値：フィールド名=>errorメッセージ：【配列】
     */
    function checked($fields_info_name = null,$fields = array(),$params = array(),$meta = array()){

        $general = new snpmGeneral();
        $error = array();

        if(empty($fields) || empty($params)) return $error;

        foreach($fields as $fields_key => $fields_value){

            if(!isset($fields_value['validation']) || empty($fields_value['validation'])) continue;
            if(!isset($fields_value['input']) || !$fields_value['input']) continue;
            
            (isset($params[$fields_key]))? $value = $params[$fields_key]: $value = '';

            //labelフィールド
            (isset($fields_value['label']) && $fields_value['label']) ? $label = $fields_value['label'] : $label = 'この項目';

            //validation条件がある場合
            if(isset($fields_value['validation_requirement']) && $fields_value['validation_requirement']){
                $check = true;
                foreach($fields_value['validation_requirement'] as $validation_requirement){
                    $check = $general->logicalCheck($params,$validation_requirement);
                    //$check = $general->compare($validation_requirement,$params);
                    if($check === false){
                        break;
                    }
                }

                //validation条件にマッチしない場合、バリデーションチェックは行わない
                if($check === false){
                    continue;
                }
            }      

            //空白NGチェック submit_flgがONの時（submitが1回でも押されたとき）
            if(isset($params['submit_flg']) && $params['submit_flg'] == 'submit' &&
             in_array ('not_blank', $fields_value['validation']) && $value === ""){
                $error[$fields_key] = $label.'は空白NGです。';
                continue;
            }

            //必須入力チェック submit_flgがONの時（submitが1回でも押されたとき）
            $flg = $this->required($value,$fields_value['input']);
            if($flg == 'error'){
                if(isset($params['submit_flg']) && $params['submit_flg'] == 'submit' && in_array ('required', $fields_value['validation'])){
                    $error[$fields_key] = $label.'は必須です。';
                    continue;
                }
            }

            //-----------------------------------------------
            //フォーマットチェック
            //-----------------------------------------------

            //メールアドレスチェック
            if(in_array ('email', $fields_value['validation'] )){

                $flg = $this->email($value,$fields_value['input']);
                if($flg == 'error'){
                    $error[$fields_key] = $label.'はメールアドレスの形式ではありません。';
                    continue;
                }
            }            
            
            //半角数字チェック
            if(in_array ('half_width_numeric', $fields_value['validation'] )){

                $flg = $this->halfWidthNumeric($value,$fields_value['input']);
                if($flg == 'error'){
                    $error[$fields_key] = $label.'は半角数字ではありません。';
                    continue;
                }
            }   

            //半角英数字チェック
            if(in_array ('half_width_alphanumeric', $fields_value['validation'] )){

                $flg = $this->halfWidthAlphanumeric($value,$fields_value['input']);
                if($flg == 'error'){
                    $error[$fields_key] = $label.'は半角英数字ではありません。';
                    continue;
                }
            }   

            //パスワードチェック
            if(in_array ('password', $fields_value['validation'] )){

                $flg = $this->password($value,$fields_value['input']);
                if($flg == 'error'){
                    $error[$fields_key] = $label.'は半角英字数字記号をそれぞれ1文字以上含む8文字以上の文字列を入力してください。';
                    continue;
                }
            }
            
            //カスタムフィールドのフィールド名チェック
            if(in_array ('cf_check', $fields_value['validation'] )){

                $flg = $this->custom_field_column_name($value,$fields_value['input']);
                if($flg == 'error'){
                    $error[$fields_key] = $label.'は、「cf_」から始まる半角英数字「-」「_」で入力してください。';
                    continue;
                }
            }
            //-----------------------------------------------
            //その他
            //-----------------------------------------------
            //入力値の比較チェック
            if(in_array ('compare', $fields_value['validation'] )){

                if(isset($fields_value['compare']) && $fields_value['compare']){

                    //比較する内容が複数ある場合（配列の場合）
                    if(is_array($fields_value['compare'])){
                        foreach($fields_value['compare'] as $compare_key => $compare_value){
                            $check = $general->compare($compare_value,$params);
                            if($check == false){
                                break;
                            }
                        }
                    }
                    //1つのみの場合
                    else{
                        $check = $general->compare($fields_value['compare'],$params);
                    }

                    if($check == false){
                        $error[$fields_key] = $label.'の値が正しくありません。';
                        continue;
                    }
                }
            }  

            //重複チェック
            if(in_array ('double', $fields_value['validation'] )){

                if(isset($fields_value['table']) && $fields_value['table']){

                    //重複チェックするフィールド名の指定がある場合
                    if(isset($fields_value['field']) && $fields_value['field']){
                        $field = $fields_value['field'];
                    }else{
                        $field = $fields_key;
                    }
                    $flg = $this->double($value,$field,$fields_value['table'],$fields_value);
                    if($flg == 'error'){
                        $error[$fields_key] = $label.'の値が既に登録されています。';
                        continue;
                    }
                }
            }
            
        }         

        return $error;

    }

    /**
     * 必須入力チェック
     * 
     * 第1引数：チェックする値：【文字列、数字、配列etc】
     * 第2引数：値の形式：【文字列】
     * 戻り値：error（エラーの場合） or ブランク：【文字列】
     */
    function required($value,$type=null){

        $flg = '';

        if($type == 'variable' && $value){

            //配列の各要素が空の場合、その要素を削除
            $result=array_filter($value,"array_filter");

            if(empty($result)) $flg = 'error';
        }
        //checkbox用の空の配列チェック 
        /*checkboxは、全くチェックされない場合、nameの値すら送信されない為、それを防ぐため、フォーム側に、１つダミーで空の値を送信している。*/
        elseif(is_array($value)){
            $empty = 'yes';
            //配列のすべての値が空ならエラー
            foreach($value as $v){
                if($v){
                    $empty = 'no';
                    return;
                };
            }
            if($empty == 'yes') $flg = 'error';
        }
        elseif(empty($value)){
            $flg = 'error';
        } 

        return $flg;

    }


    /**
     * メールアドレスチェック
     * 
     * 第1引数：チェックする値：【文字列、数字、配列etc】
     * 第2引数：値の形式：【文字列】
     * 戻り値：error（エラーの場合） or ブランク：【文字列】
     */
    function email($value,$type=null){

        $flg = '';

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $flg = 'error';
        }

        return $flg;

    }

    /**
     * パスワードチェック
     * 
     * 第1引数：チェックする値：【文字列、数字、配列etc】
     * 第2引数：値の形式：【文字列】
     * 戻り値：error（エラーの場合） or ブランク：【文字列】
     */
    function password($value,$type=null){

        $flg = '';

        //$pettern = '/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}+\z/i';
        $pettern = '/\A(?=.*?[a-z])(?=.*?\d)(?=.*?[!-\/:-@[-`{-~])[!-~]{8,100}+\z/i';
        //半角英字と半角数字をそれぞれ1文字以上含む8文字以上の文字列
        if (preg_match($pettern,$value) == 0) {
            $flg = 'error';            
        }

        return $flg;

    }

    /**
     * カスタムフィールドカラム名チェック
     * 
     * 第1引数：チェックする値：【文字列、数字、配列etc】
     * 第2引数：値の形式：【文字列】
     * 戻り値：error（エラーの場合） or ブランク：【文字列】
     */
    function custom_field_column_name($value,$type=null){

        $flg = '';

        //$pettern = '/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}+\z/i';
        $pettern = substr($value,0,3);
        if ($pettern != 'cf_') {
            $flg = 'error';            
        }

        return $flg;

    }

    /**
     * 半角数字チェック
     * 
     * 第1引数：チェックする値：【文字列、数字、配列etc】
     * 第2引数：値の形式：【文字列】
     * 戻り値：error（エラーの場合） or ブランク：【文字列】
     */
    function halfWidthNumeric($value,$type=null){

        $flg = '';

        $pettern = '/^[0-9]+$/';
        if (preg_match($pettern,$value) == 0) {
            $flg = 'error';            
        }

        return $flg;

    }

    /**
     * 半角英数字チェック
     * 
     * 第1引数：チェックする値：【文字列、数字、配列etc】
     * 第2引数：値の形式：【文字列】
     * 戻り値：error（エラーの場合） or ブランク：【文字列】
     */
    function halfWidthAlphanumeric($value,$type=null){

        $flg = '';

        $pettern = '/^[a-zA-Z0-9_-]+$/';
        if (preg_match($pettern,$value) == 0) {
            $flg = 'error';            
        }

        return $flg;

    }

    /**
     * 重複チェック
     * 
     * 第1引数：チェックする値：【文字列】
     * 第2引数：チェックするフィールド名：【文字列】
     * 第3引数：table名：【文字列】
     * 戻り値：error（エラーの場合） or ブランク：【文字列】
     */
    function double($value,$field,$table,$fields){

        global $wpdb;
        $flg = '';

        //tableの存在チェック
        if ($wpdb->get_var("show tables like '".$table."'") != $table) return $flg;

        //追加のwhere条件
        (isset($fields['double_where']) && $fields['double_where']) ? $double_where = ' and ' . $fields['double_where'] : $double_where = '';

        if(is_numeric($value)){
            $place_holder = '%d';
        }else{
            $place_holder = '%s';
        }

        $sql = "SELECT id FROM ".$table." where " .$field."=".$place_holder.$double_where." ;";
        $wpdb->get_results($wpdb->prepare($sql,$value));
        $num = $wpdb->num_rows; //最後に実行したクエリ「$sql」の行数を取得

        if ($num) {
            $flg = 'error';            
        }

        return $flg;

    }
        
}