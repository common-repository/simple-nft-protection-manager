<?php
/**---------------------------------------------------------
 * 一般的によく使う機能をまとめたクラス
----------------------------------------------------------- */
class snpmGeneral {

    /**
     * request データをサニタイズ
     * サイバー攻撃につながるような文字を無効化
     */
    function request($request_type = 'post'){
        
        $request = array();

        if($request_type == 'get'){

            foreach($_GET as $d_key => $d_value){
                if(is_array($d_value)){
                    $request[$d_key] = $this->arraySanitize($d_value);
                }else{
                    $request[$d_key] = sanitize_textarea_field($d_value);
                }
            }

        }elseif($request_type == 'post'){
            foreach($_POST as $d_key => $d_value){
                if(is_array($d_value)){
                    $request[$d_key] = $this->arraySanitize($d_value);
                }else{
                    $request[$d_key] = sanitize_textarea_field($d_value);
                }
            }
        }elseif($request_type == 'session'){
            foreach($_SESSION as $d_key => $d_value){
                if(is_array($d_value)){
                    $request[$d_key] = $this->arraySanitize($d_value);
                }else{
                    $request[$d_key] = sanitize_textarea_field($d_value);
                }
            }
        }

        return $request;
    
    }

    /**
     * 配列のサニタイズ
     * */
    function arraySanitize($array = array()){

        if(empty($array)) {
            return $array;
        }

        foreach($array as $key => $value){
            if(is_array($value)){
                $array[$key] = $this->arraySanitize($value);
            }else{
                $array[$key] = sanitize_textarea_field($value);
            }
        }

        return $array;

    }

    /**
     * queryパラメータをstringで取得
     * 
     * 第1引数：排除したいパラメータ：【配列】
     */
    public function getQueryParam($except_param = array())
    {

        //クエリパラメータを生成
        //--------------------------------------
        $param = '';

        //GETの値の特殊文字を HTML エンティティに変換（無力化）して取得
        $get = $this->request('get');

        foreach($get as $key => $value){
            
            //除外したいパラメータが存在した場合
            if(in_array($key, $except_param) !== false) continue;

            //配列の場合
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    $param .= '&'.$key.'['.$key2.']='.$value2;
                }
            }
            //配列じゃない場合
            else{
                $param .= '&'.$key.'='.$value;
            }
            
        }

        return $param;

    }    
    
    /**
     * 文字列の比較演算子を判定して、booleanで返す
     * 例　destination = difference や field > 5 など
     * 
     * 第１引数：比較文字列：【文字列】
     * 第２引数：リクエスト：【配列】
     * 戻り値：真偽値：【boolean】
     */
    function compare($requirement,$param){

        $requirement_check = false;
        $requirement = str_replace(array(' ', '　'), ' ', $requirement); //区切りコードの統一
        $requirement = str_replace('  ', ' ', $requirement); //連続の区切りコードを１つにする

        $CONDITIONS = explode(" ", $requirement); //デリミタ文字で分割して配列へ

        $field = $CONDITIONS[0];//比較用のフィールド
        $operator = $CONDITIONS[1];//比較演算子
        $value = $CONDITIONS[2];//比較する値

        if(!isset($param[$field])) return $requirement_check;

        if($value == 'blank'){
            $value = '';
        }

        //比較対象がフィールドの値か？それとも絶対値か？
        if(isset($param[$value])){
            $value = $param[$value];
        }

        switch ($operator) { 
            case '=':
                if($param[$field] == $value){
                    $requirement_check = true;
                }
            break;

            case '!=':
                if($param[$field] != $value){
                    $requirement_check = true;
                }
            break;

            case '<':
                if($param[$field] < $value){
                    $requirement_check = true;
                }
            break;

            case '<=':
                if($param[$field] <= $value){
                    $requirement_check = true;
                }
            break;

            case '>':
                if($param[$field] > $value){
                    $requirement_check = true;
                }
            break;

            case '>=':
                if($param[$field] >= $value){
                    $requirement_check = true;
                }
            break;

            case 'like':

                $target_value = $param[$field];
                //ワイルドカード（曖昧検索）
                //「*」で囲まれている
                if(mb_substr($value,0,1) == '*' && mb_substr($value,-1) == '*'){
                    $value = str_replace('*','',$value);
                    if(strpos($target_value,$value) !== false){
                        $requirement_check = true;
                    }
                }
                //「*」から始まる 後方一致
                elseif(mb_substr($value,0,1) == '*'){
                    $value = str_replace('*','',$value);
                    $cnt = mb_strlen($value);//文字数取得
                    $target_value = $param[$field];
                    if(mb_substr($target_value,-1 * $cnt) === $value){
                        $requirement_check = true;
                    }
                }
                //「*」で終わる　前方一致
                elseif(mb_substr($value,-1) == '*'){
                    $value = str_replace('*','',$value);
                    $cnt = mb_strlen($value);//文字数取得
                    if(mb_substr($target_value,0,$cnt) === $value){
                        $requirement_check = true;
                    }
                }
                elseif(strpos($target_value,$value) !== false){
                    $requirement_check = true;
                }


            break;   

            case 'include'://比較対象がcheckboxの様に、複数の値を配列で持っているケース

                if(!is_array($param[$field])) break;
                $array = $param[$field];

                if(in_array($value, $array)) {
                    $requirement_check = true;
                }

            break;

        }

        return $requirement_check;

    }

        
    /**
     * 論理式のチェック（and or なども入っている。ただし「（）」は対応していない）
     * 第1引数：チェックする値：【文字列、数字、配列etc】
     * 第2引数：論理式：【文字列】
     * 戻り値：boolean：【boolean】
     */
    function logicalCheck($param,$logical_sign){

        $check = false;

        //条件に「or」が含まれている場合⇒「or」で分割
        if(strpos($logical_sign,' or ') !== false){
            $logical_sign_array = explode(' or ',$logical_sign);

            foreach($logical_sign_array as $lsa_value){

                if(!$lsa_value) continue;

                //or で分割された中に、「and」が入っている場合
                if(strpos($logical_sign,' and ') !== false){
                    $vr = explode(' and ',$logical_sign);
                    $check = true;
                    foreach($vr as $lsa_value){
                        $check = $this->compare($lsa_value,$param);
                        if($check === false) break;
                    }
                    return $check;
                }else{
                    $check = $this->compare($lsa_value,$param);
                    if($check === true) break;
                }

            }

            return $check;
        }

        //条件１つのみ
        $check = $this->compare($logical_sign,$param);
        return  $check;

    }

    
    /**
     * 日付の時間差を求める
     * 
     * 第1引数：日時from：【文字列】
     * 第2引数：日時to：【文字列】
     * 
     * 戻り値：時間差：【配列】
     */
    public function difTimeFromTo($from,$to,$from_time = null,$to_time = null)
    {

        $from = new \datetime($from);
        $to = new \datetime($to);

        $diff_sec = $to->getTimestamp() - $from->getTimestamp();//時間差（秒数）
        $diff_min = floor($diff_sec/(60));//時間差（分数）
        $diff_hours = floor($diff_sec/(60 * 60));//時間差（時間）
        $diff_days = floor($diff_sec/(60 * 60 * 24));//時間差（日数）

        $diff = array('sec'=>$diff_sec,'min'=>$diff_min,'hour'=>$diff_hours,'day'=>$diff_days);

        return $diff;

    }    

    
    /**
     * 日数差を求める
     * 
     * 第1引数：日時from：【文字列】
     * 第2引数：日時to：【文字列】
     * 
     * 戻り値：時間差：【配列】
     */
    public function difDayFromTo($from,$to)
    {

        $from = new \datetime($from);
        $to = new \datetime($to);

        $diff = $from->diff($to);
        $days = $diff->days;

        return $days;

    } 

    /**
     * ランダム文字列生成
     * 第1引数：文字列の長さ：【数字】
     * 戻り値：ランダム文字列：【文字列】
     */
    public function getRandomVar($length = 64)
    {

        $char_list = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_";
        $random_var = substr(str_shuffle($char_list), 0, $length);

        return $random_var;

    }     
    
    /**
     * 参照元URLを取得
     * 戻り値：参照元URL：【文字列】
     */
    function getReferer() {

        $referer = '';

        $referer = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . sanitize_textarea_field($_SERVER["HTTP_HOST"]) . sanitize_textarea_field($_SERVER["REQUEST_URI"]);

        return $referer;
    }

    /**
     * 任意のフィールドのセッションクリア
     * 
     * 第1引数：フィールド情報：【配列】
     */
    public function clearSession($params)
    {

        foreach($params as $key => $value){
            if(!isset($_SESSION[$key]) || !$_SESSION[$key]) continue;
            unset($_SESSION[$key]);
        }

    }    


    /**
     * 空白や「:」で区切った文字列を配列に格納
     * 
     * 第1引数：対象データ：【文字列】
     * 第2引数：データの区切り文字：【文字列】
     * 第3引数：keyと要素の区切り文字：【文字列】
     * 
     * 戻り値：成形したデータ：【配列】
     */
    public function strToArray($str,$data_separate,$key_separate = null)
    {

        $data = array();

        if(!$data_separate) return $data;

        //区切り文字が「改行コード」の場合、改行コードの統一
        if($data_separate == "\n") $str = str_replace(array("\r\n", "\r", "\n"), "\n", $str);

        $array = explode($data_separate,$str);

        foreach($array as $array_key => $array_value){

            if(!$array_value) continue;

            if($key_separate && strpos($array_value,$key_separate) !== false){
                $array = explode($key_separate,$array_value);
                $key = $array[0];
                $value = $array[1];
                if(isset($data[$key]) && $data[$key]){
                    $data[$key] .= ' ' . $value;
                }else{
                    $data[$key] = $value;
                }
            }
            else{
                $data[$array_value] = $array_value;
            }
        }

        return $data;

    }

}