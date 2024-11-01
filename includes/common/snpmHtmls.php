<?php
/**---------------------------------------------------------
 * よく使うHTMLの設定
----------------------------------------------------------- */
class snpmHtmls {

    
    /**
     * フォーム入力用HTMLコードの取得
     * 第1引数：request（getやpost）：【配列】
     * 第2引数：入力タイプ type：【文字列】
     * 第3引数：フィールド名 name：【文字列】
     * 第4引数：フィールド設定情報 value：【配列】
     * 第5引数：mode：【文字列・配列】
     * 
     * 戻り値：フィールド別のinput用HTMLコード：【配列】
     */
    public function getInputHtml($request,$type,$field,$value,$mode = null)
    {

        $html='';

        switch ($type) {

            case 'select':

                //selectの入力用のHTMLを取得
                $html = $this->inputSelect($field,$value,$request,$mode);
                break;

            case 'radio':

                //radioの入力用のHTMLを取得
                $html = $this->inputRadio($field,$value,$request);
                break;

            case 'checkbox':

                //checkboxの入力用のHTMLを取得
                $html = $this->inputCheckbox($field,$value,$request);
                break;

            case 'textarea':

                //textareaの入力用のHTMLを取得
                $html = $this->inputTextarea($field,$value,$request);
                break;

            case 'texteditor':

                //texteditorの入力用のHTMLを取得
                $html = $this->inputTextarea($field,$value,$request,'editor');
                break;

            case 'datetime-local':

                //texteditorの入力用のHTMLを取得
                $html = $this->inputDateTime($field,$value,$request);
                break;
                
            case 'variable':

                //可変フィールドの入力用のHTMLを取得
                $html = $this->inputVariable($field,$value,$request);
                break;

            case 'media':

                //mediaフィールドの入力用のHTMLを取得
                $html = $this->inputMedia($field,$value,$request,$mode);
                break;

            case 'file':

                //可変フィールドの入力用のHTMLを取得
                $html = $this->inputFile($field,$value,$request,$mode);
                break;

            case 'password':

                //情報をクリア
                if(isset($request[$field]) && $request[$field]) $request[$field] = '';
                //可変フィールドの入力用のHTMLを取得
                $html = $this->inputOther($field,$value,$request);
                break;
  
            default:
            
                //その他の入力用のHTMLを取得
                $html = $this->inputOther($field,$value,$request);
                break;
                            
        }

        return $html;

    }

    /**
     * フォーム用ラベルHTMLの取得
     * 第1引数：フィールド基本情報：【配列】
     * 
     * 戻り値：フィールド別のinput用HTMLコード：【配列】
     */
    public function getLabel($params)
    {
        $label_html = array();

        foreach($params as $param_key => $param_value){

            if(!isset($param_value['label']) || !$param_value['label']) continue;

            $html='<label for="'.$param_key.'" class="form-label">'.$param_value['label'].'</label> ';

            if(isset($param_value['validation']) && in_array ('required', $param_value['validation'] )){
                $html.='<span class="badge bg-danger" style="font-size:0.5rem">必須</span> ';
            }
            
            if($html) $label_html[$param_key] = $html;

        }

        return  $label_html;
    }

    /**
     * フォーム用説明文HTMLの取得
     * 第1引数：フィールド基本情報：【配列】
     * 
     * 戻り値：フィールド別のinput用HTMLコード：【配列】
     */
    public function getDescription($params)
    {

        $description_html = array();

        foreach($params as $param_key => $param_value){

            if(!isset($param_value['description']) || !$param_value['description']) continue;

            $html='<div class="form-text">'.$param_value['description'].'</div> ';
           
            if($html) $description_html[$param_key] = $html;

        }

        return  $description_html;
    }

    /**
     * 選択肢の取得
     * 第1引数：field フィールド名：【文字列】
     * 第2引数：request（getやpost）：【配列】
     * 第3引数：params フィールド基本情報：【配列】
     * 第4引数：mode：【文字列・配列】
     * 
     * 戻り値：選択肢：【配列】
     */
    public function getChoices($field,$request,$params,$mode = null)
    {

        (isset($params['choices'])) ? $choices = $params['choices'] : $choices = '';

        //選択肢がフレキシブルオプションの場合
        if(isset($choices['flexible_option_value']['field']) && $choices['flexible_option_value']['field']){

            $key = $choices['flexible_option_value']['field'];

            $label = '';
            if(isset($choices['flexible_option_value']['label']) && $choices['flexible_option_value']['label']){
                $label = $choices['flexible_option_value']['label'];
            }
            $choices = array(''=>$label.'を選択してください。');    
            $function_name = '';

            //variable変数の場合
            if($mode == 'variable'){
                foreach($request as $r_key => $r_value){
                    if(strpos($r_key,'['.$key.']') !== false){
                        $function_name = $r_value;
                        break;
                    }
                }
            }elseif(isset($request[$key]) && $request[$key]) $function_name = $request[$key];

            if(!$function_name) return $choices;

            //選択肢を取得してセット
            $defaultValue = new snpmDefaultValue();
            if (method_exists($defaultValue, $function_name) === false) return $choices;
            $choices = $defaultValue->$function_name();

        }

        return $choices;

    }
 

    /**
     * プルダウン用のHTMLコードを取得
     * 第1引数：フィールド名（nameやidで使われる）：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：request：【配列】
     * 第4引数：mode：【文字列・配列】
     * 
     * 戻り値：テキスト入力用のHTMLコード：【文字列】
     */
    public function inputSelect($field,$params,$request,$mode = null)
    {

        $html = '';

        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $choices = $this->getChoices($field,$request,$params,$mode);
        //(isset($params['choices'])) ? $choices = $params['choices'] : $choices = '';
        $value = $this->valueJudgment($field,$params,$request);

        $html .= '<div id="'.$field.'-message" class="entry-message">';
        $html .= '</div>';
        if ($choices) {
            $html .= '<select name="'.$field.'" id="'.$field.'" class="entry-field '.$field." ".$class.'" '.$option.'>'."\n";
            foreach ((array) $choices as $choices_key => $choices_value) {
                if (strcmp($value, $choices_key) == 0) {
                    $html .= '<option value="'.$choices_key.'" selected>'.$choices_value.'</option>'."\n";
                } else {
                    $html .= '<option value="'.$choices_key.'">'.$choices_value.'</option>'."\n";
                }
            }
            $html .= '</select>';
        }

        return  $html;

    } 

    /**
     * ラジオボタン用のHTMLコードを取得
     * 第1引数：フィールド名（nameやidで使われる）：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：request：【配列】
     * 
     * 戻り値：テキスト入力用のHTMLコード：【文字列】
     */
    public function inputRadio($field,$params,$request)
    {

        $html = '';

        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        (isset($params['choices'])) ? $choices = $params['choices'] : $choices = '';
        $value = $this->valueJudgment($field,$params,$request);

        (isset($params['before_wrap']))? $before_wrap = $params['before_wrap'] : $before_wrap = '';
        (isset($params['after_wrap']))? $after_wrap = $params['after_wrap'] : $after_wrap = '';

        $html .= '<div class="entry-wrap" id="'.$field.'">';
        $html .= '<div id="'.$field.'-message" class="entry-message">';
        $html .= '</div>';
        $html .= '<input type="hidden" name="'.$field.'" value="">'."\n";//何もチェックしていない時、SESSIONの値が入ってしまう不具合を防ぐ
        $html .= '<ul class="checkbox-list">'."\n";
        if ($choices) {
            $cnt = 1;
            foreach ((array) $choices as $choices_key => $choices_value) {
                if(!$choices_key) continue;//radioの場合、valueがブランクは排除
                if (strcmp($value, $choices_key) == 0) {
                    $html .= $before_wrap.'<li><label class="active"><input type="radio" name="'.$field.'" value="'.$choices_key.'" checked="checked" class="entry-field '.$field." ".$class.'" '.$option.'><span class="list-item-label">'.$choices_value.'</span></label></li>'.$after_wrap."\n";
                } else {
                    $html .= $before_wrap.'<li><label ><input type="radio" name="'.$field.'" value="'.$choices_key.'" class="entry-field '.$field." ".$class.'" '.$option.'><span class="form-check-label list-item-label">'.$choices_value.'</span></label></li>'.$after_wrap."\n";
                }
                $cnt++;
            }
        }   
        $html .= '</ul>';     
        $html .= '</div>';

        return  $html;

    } 

    /**
     * チェックボックス用のHTMLコードを取得
     * 第1引数：フィールド名（nameやidで使われる）：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：request：【配列】
     * 
     * 戻り値：テキスト入力用のHTMLコード：【文字列】
     */
    public function inputCheckbox($field,$params,$request)
    {

        $html = '';

        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        (isset($params['choices'])) ? $choices = $params['choices'] : $choices = '';
        $value = $this->valueJudgment($field,$params,$request);

        (isset($params['before_wrap']))? $before_wrap = $params['before_wrap'] : $before_wrap = '';
        (isset($params['after_wrap']))? $after_wrap = $params['after_wrap'] : $after_wrap = '';


        if ($value) {
            if (!is_array($value)) {                 
                $VALUE = unserialize($value); //checkboxは、配列（シリアライズデータ）の状態で値を持っているので、アンシリアライズして、配列に戻す
            } else {
                $VALUE = $value;
            }
        } else {
            $VALUE = array();
        }

        //ajaxでデータを送信する場合は、nameはそのまま
        /*
        if(isset($params['method']) && $params['method'] == 'get'){
            $name = $field . '[]';
        }
        //ajaxじゃない場合は、name[]と言った形にする
        else{
            $name = $field;
        }
        */

        $cnt = 0;

        $html .= '<div class="entry-wrap" id="'.$field.'">';
        $html .= '<div id="'.$field.'-message" class="entry-message">';
        $html .= '</div>';

        //選択肢が3つ以上で「全選択」を表示
        if(count($choices) >= 3){
            $html .= '<div class="bold w-100">全選択：<input type="checkbox" id="'.$field.'_all" name="'.$field.'_all" value="1" data-field="'.$field.'" onClick="allChecked(event);"></div>';
        }

        //checkboxは、チェックがされないとnameすら送信されないので、チェックされていない場合、空の値を送る為のコード
        $html .= '<input type="hidden" name="'.$field.'['.$cnt.']" value="" checked="checked">'."\n";
        $html .= '<ul class="checkbox-list">'."\n";
        if ($choices) {
            foreach ((array) $choices as $choices_key => $choices_value) {
                if(!$choices_key && !$choices_value) continue;//checkboxの場合、valueがブランクは排除
                $cnt++;
                if (in_array($choices_key, (array) $VALUE)) {
                    $html .= '<li><label class="active"><input type="checkbox" name="'.$field.'['.$cnt.']" value="'.$choices_key.'" data-field="'.$field.'" class="entry-field '.$field." ".$class.'" checked="checked" '.$option.'><span class="list-item-label">'.$choices_value.'</span></label></li>'."\n";
                } else {
                    $html .= '<li><label><input type="checkbox" name="'.$field.'['.$cnt.']" value="'.$choices_key.'" data-field="'.$field.'" class="entry-field '.$field." ".$class.'" '.$option.'><span class="list-item-label">'.$choices_value.'</span></label></li>'."\n";
                }
            }

        }
        
        if($cnt == 0){
            $html .= $before_wrap.'<li>選択できる'.$params['label'].'はありません。</li>'.$after_wrap."\n";
        }

        $html .= '</ul>';
        $html .= '</div>';

        return  $html;

    } 


    /**
     * テキストエリア用のHTMLコードを取得
     * 第1引数：フィールド名（nameやidで使われる）：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：request：【配列】
     * 
     * 戻り値：テキスト入力用のHTMLコード：【文字列】
     */
    public function inputTextarea($field,$params,$request,$mode = null)
    {

        $html = '';

        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $value = $this->valueJudgment($field,$params,$request);

        $html .= '<div id="'.$field.'-message" class="entry-message">';
        $html .= '</div>';

        if($mode == 'editor'){
            $html .= '<textarea rows="20" name="'.$field.'" '.'" class="entry-field '.$field." ".$class.'" id = "texteditor" '.$option.'>'.$value.'</textarea>';
        }else{
            $html .= '<textarea rows="10" name="'.$field.'" '.'" class="entry-field '.$field." ".$class.'" id = "'.$field.'" '.$option.'>'.$value.'</textarea>';
        }

        return  $html;

    } 

    /**
     * 可変フィールド用のHTMLコードを取得
     * 第1引数：フィールド名（nameやidで使われる）：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：request：【配列】
     * 第4引数：mode：【文字列】
     * 
     * 戻り値：テキスト入力用のHTMLコード：【文字列】
     */
    function inputVariable($field,$params,$request,$mode = null){

        $html = '';
        return  $html;

    }

    /**
     * メディアアップローダー付のHTMLコードを取得
     * 第1引数：フィールド名（nameやidで使われる）：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：request：【配列】
     * 第4引数：mode：【文字列】
     * 
     * 戻り値：テキスト入力用のHTMLコード：【文字列】
     */
    function inputMedia($field,$params,$request,$mode = null){

        $html = '';

        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $value = $this->valueJudgment($field,$params,$request);        

        if($params['input'] != 'hidden'){
            $html .= '<div id="'.$field.'-message" class="entry-message">';
            $html .= '</div>';
        }
        
        if($value){
            $html .= '<div id="'.$field.'_thumbnail" class="mb-1 form-control"><img src="'.$value.'"></div>';
            $html .= '<div class="mb-1"><input id="'.$field.'" type="text" name="'.$field.'" value="'.$value.'" class="entry-field '.$field." ".$class.'" '.$option.' /></div>';
        }else{
            $html .= '<div id="'.$field.'_thumbnail" class="mb-1"></div>';
            $html .= '<div class="mb-1"><input id="'.$field.'" type="text" name="'.$field.'" value="" class="entry-field '.$field." ".$class.'" '.$option.'/></div>';
        }
        $html .= '<input class="upload_image_button button button-secondary button-large" type="button" value="画像をアップロード" data-field="'.$field.'" /> ';
        $html .= '<input class="remove_image_button button button-secondary button-large" type="button" value="画像を削除" data-field="'.$field.'"  />';


        return  $html;

    }

    /**
     * その他入力用のHTMLコードを取得
     * 第1引数：フィールド名（nameやidで使われる）：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：request：【配列】
     * 第4引数：mode：【文字列】
     * 
     * 戻り値：テキスト入力用のHTMLコード：【文字列】
     */
    public function inputFile($field,$params,$request,$mode = null)
    {

        $html = '';

        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $value = $this->valueJudgment($field,$params,$request);

        $html .= '<div id="'.$field.'-message" class="entry-message">';
        $html .= '</div>';
        $html .= '<div id="'.$field.'">';
        if($value){
            if(isset($params['data_type']) && $params['data_type'] == 'img'){
                $html .= '<img src="'.ROOT_URL.UPLOAD_DIR.'/'.MANAGEMENT_ID.'/'.$value.'" class="img-fluid">';
            }else{
                $html .= '<div>'.$value.'</div>';
            }
            //可変フィールドの場合は、「削除ボタン」を表示させない
            if($mode != 'variable'){
                $html .= '<div class="btn btn-secondary file_delete" onclick="return confirm(&quot;本当に削除しますか？&quot;)" data-field="'.$field.'">削除</div>';
            }
            $html .= '<input type="hidden" name="'.$field.'" value="'.$value.'">';
        }else{

            //accept 拡張子
            $accept = '';
            if(isset($params['file_extension']) && $params['file_extension']){
                foreach($params['file_extension'] as $p_value){
                    if(!$p_value) continue;
                    $accept .= $p_value.',';
                }
                $accept = substr($accept,0,-1);
                $accept = str_replace(',',',.',$accept);
                $accept = '.'.$accept;
            }
             $accept = 'image/*';
            $html .= '<input type="'.$params['input'].'" name="'.$field.'" class="entry-field '.$field." ".$class.'" '.$option.' accept="'.$accept.'">';
        }
        $html .= '</div>';

        return  $html;

    } 

    /**
     * 日時入力用のHTMLコードを取得
     * 第1引数：フィールド名（nameやidで使われる）：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：request：【配列】
     * 
     * 戻り値：テキスト入力用のHTMLコード：【文字列】
     */
    public function inputDateTime($field,$params,$request)
    {

        $html = '';

        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $value = $this->valueJudgment($field,$params,$request);
        $date_time = new \DateTime($value);

        $html .= '<div id="'.$field.'-message" class="entry-message">';
        $html .= '</div>';
        $html .= '<input type="'.$params['input'].'" name="'.$field.'" id="'.$field.'" value="'.$date_time->format('Y-m-d\TH:i').'" class="entry-field '.$field." ".$class.'" '.$option.'>';

        return  $html;

    } 


    /**
     * その他入力用のHTMLコードを取得
     * 第1引数：フィールド名（nameやidで使われる）：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：request：【配列】
     * 
     * 戻り値：テキスト入力用のHTMLコード：【文字列】
     */
    public function inputOther($field,$params,$request)
    {

        $html = '';

        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $value = $this->valueJudgment($field,$params,$request);

        if($params['input'] != 'hidden'){
            $html .= '<div id="'.$field.'-message" class="entry-message">';
            $html .= '</div>';
        }

        //値が配列の場合
        if($value && is_array($value) === true){
            $html .= '<input type="'.$params['input'].'" name="'.$field.'" id="'.$field.'" value="" class="entry-field '.$field." ".$class.'" '.$option.'>';
        }else{
            $html .= '<input type="'.$params['input'].'" name="'.$field.'" id="'.$field.'" value="'.htmlspecialchars($value, ENT_QUOTES).'" class="entry-field '.$field." ".$class.'" '.$option.'>';
        }

        return  $html;

    } 

    /**
     * UI設定情報を元にscriptコードを取得
     * 戻り値：scriptコード：【文字列】
     */
    public function getAddScript($fields)
    {

        $script = "<script>";

        foreach($fields as $fields_key => $fields_value){

            //variable_optionに「sortable」が入っている場合
            if(isset($fields_value['variable_option']) && $fields_value['variable_option']){

                if(in_array('sortable',$fields_value['variable_option'],true)){

                    $script .= "
                    var ".$fields_key." = document.getElementById('".$fields_key."');
                    new Sortable(".$fields_key.", {
                        animation: 150,
                        ghostClass: 'background-color'
                    });
                    ";
                }
    
            }

        }        

        $script .= "</script>";

        return $script;

    }

    
    /**
     * value値の判断（デフォルト値か入力値のどちらの値を使うかの判断）
     * 第1引数：フィールド名（nameやidで使われる）：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：request：【配列】
     * 
     * 戻り値：value値：【配列or文字列】
     */
    public function valueJudgment($field,$params,$request)
    {

        $value = '';

        if(isset($params['input']) && $params['input'] == 'number'){
            $value = 0;
        }

        if(isset($request[$field])){
            $value = $request[$field];
        }
        elseif(isset($params['default'])){
            $value = $params['default'];
        }

        return $value;

    }


     /**
     * 検索フォーム用HTMLコードの取得
     * 第1引数：Requestデータ：【配列】
     * 第2引数：フィールド基本情報：【配列】
     * 
     * 戻り値：HTMLコード：【配列】
     */
    public function getSearch($request,$params)
    {

        $search_html = array();

        //必須のデフォルトフィールドの登録
        $general = new snpmGeneral();
        $session = $general->request('session');
        (isset($session['token']))? $token = $session['token']: $token = '';
        $search_html['token'] = '<input type="hidden" name="token" value="'.$token.'">';//csrf_token

        (isset($request['ui_info_name']))? $ui_info_name = $request['ui_info_name']: $ui_info_name = '';
        $search_html['ui_info_name'] = '<input type="hidden" name="ui_info_name" value="'.$ui_info_name.'">';//csrf_token

        if(!isset($params) || empty($params)) return $search_html; 

        if(!isset($params['general_info']['search']) || empty($params['general_info']['search'])) return $search_html; 
        
        foreach($params['general_info']['search'] as $search_key =>$search_value){

            $html ='';

            if(!isset($search_value['input']) || !$search_value['input']) continue;

            $html = $this->getInputHtml($request,$search_value['input'],$search_key,$search_value);

            //ラベルが設定されている場合
            if(isset($search_value['label']) && $search_value['label']){
                $html = '<div class="input-group">
                <span class="input-group-text">'.$search_value['label'].'</span>'.$html.'</div>';
            }

            if($html) $search_html[$search_key] = $html;

        }

        return  $search_html;
    }


    /**
     * 表示タイプ別のHTMLコードの取得
     * 第1引数：name：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：値：【いろいろ】
     * 第4引数：使用モード：【文字列】form or mail
     * 
     * 戻り値：HTMLコード：【配列】
     */
    public function getDisplayHtml($param_key,$param_value,$list_value,$mode = 'form')
    {

        $html = '';

        if(!isset($param_value['data_type']) || !$param_value['data_type']) return $html;

        //request valueが無くて、デフォルト値がある場合は、デフォルト値をセット
        if(!isset($list_value[$param_key]) || !$list_value[$param_key]){
            if(isset($param_value['default']) && $param_value['default']){
                $list_value[$param_key] = $param_value['default'];
            }else{
                $list_value[$param_key] = '';
            }
        } 

        //データタイプ別の表示用の値を取得
        $display_value = $this->getDisplayValue($param_key,$param_value,$list_value);

        if($mode == 'form'){
            (isset($param_value['option'])) ? $option = $param_value['option'] : $option = '';
            $html = '<div class="form-display" '.$option.'>'.$display_value.'</div>';
        }else{
            $html = $display_value;
        }


        return $html;

    }

    /**
     * データタイプ別の値を取得
     * 第1引数：name：【文字列】
     * 第2引数：フィールド基本情報：【配列】
     * 第3引数：値：【いろいろ】
     * 
     * 戻り値：表示用の値：【いろいろ】
     */
    public function getDisplayValue($param_key,$param_value,$list_value){

        $return_value = '';

        switch ($param_value['data_type']) {

            case 'checkbox':

                //delete_flg = 9 (削除不可)の場合
                $disabled = '';
                if(isset($list_value['delete_flg']) && $list_value['delete_flg'] == 9){
                    $disabled = 'disabled="disabled"';
                }
                $return_value = '<input type="checkbox" name="'.$param_key.'[]" value="'.$list_value[$param_key].'" '.$disabled.' title="'.$list_value[$param_key].'">';
                break;

            case 'choice'://selectやradioボタンで登録したデータ

                if(!isset($param_value['choices']) || !$param_value['choices']) break;

                $dummy_key = $list_value[$param_key]; //配列のkeyから値を取得する為、KEYを取得

                $choices = $this->getChoices($param_key,$list_value,$param_value,null);
                if (isset($choices[$dummy_key])) {
                    $return_value = $choices[$dummy_key];
                }
                break;

            case 'choices'://checkboxで複数データがある場合

                if(!isset($param_value['choices']) || !$param_value['choices']) break;

                if(!isset($list_value[$param_key]) || !$list_value[$param_key]){
                    $array = array();
                }elseif(is_array($list_value[$param_key])){
                    $array = $list_value[$param_key];
                }else{
                    $array = unserialize($list_value[$param_key]); //配列のkeyから値を取得する為、KEYを取得
                }

                foreach($array as $array_key => $array_value){
                    if (isset($param_value['choices'][$array_value]) && $array_value) {
                        $return_value .= $param_value['choices'][$array_value].',';
                    }
                }
                $return_value = substr($return_value,0,-1);//最後の1文字削除
                break;
            
            case 'Y/m/d':

                $return_value = date('Y/m/d', strtotime($list_value[$param_key]));
                break;
        
            case 'datetime':

                $return_value = date('Y/m/d H:i:s', strtotime($list_value[$param_key]));
                break;

            case 'date':

                $return_value = date('Y年m月d日', strtotime($list_value[$param_key]));
                break;

            case 'number':

                if(is_numeric($list_value[$param_key])){
                    $return_value = number_format($list_value[$param_key]);
                }else{
                    $return_value = $list_value[$param_key];
                }
                
                break;
    
            case 'img':

                (isset($param_value['option'])) ? $option = $param_value['option'] : $option = '';
                (isset($param_value['class'])) ? $class = $param_value['class'] : $class = '';

                $image_path = '';

                $return_value = '<img src="'.$image_path.'?'.time().'" class="'.$class.'" '.$option.'/>';//「?time())」は、キャッシュの対策
                break;    
    
            case 'html':

                (isset($param_value['option'])) ? $option = $param_value['option'] : $option = '';
                (isset($param_value['class'])) ? $class = $param_value['class'] : $class = '';

                $return_value = htmlspecialchars_decode($list_value[$param_key]);
                break;    

            case 'variable':

                if(!isset($param_value['variable']) || !$param_value['variable']) break;
                $value = $list_value[$param_key];
                if(is_array($value)){

                    //配列の各要素が空の場合、その要素を削除
                    $result=array_filter($value,"array_filter");
                    //空の場合は、ダミーの配列を挿入
                    if(empty($result)){
                        $variable_value = array(array('dummy'));
                    }else{
                        $variable_value = $result;
                    }                                
                }
                elseif($value){
                    $variable_value = unserialize($value);
                }
                else{
                    $variable_value = array(array('dummy'));
                }

                foreach($variable_value as $vv_key => $vv_value){
                    foreach($vv_value as $vvv_key => $vvv_value){

                        if($vvv_key == 'variable_name') continue;
                        //variable_multiかどうかの判定
                        //------------------------------------------
                        if(isset($vv_value['variable_name']) && $vv_value['variable_name']){
                            $variable_name = $vv_value['variable_name'];
                            $fields = $param_value['variable']['variable_name'][$variable_name]['fields'];
                        }else{
                            if(!isset($param_value['variable'][$vvv_key])) continue;
                            $fields = $param_value['variable'][$vvv_key];
                        }                    

                        $value = $this->getDisplayHtml($vvv_key,$fields,$vv_value,'mail');
                        //$value = str_replace(array('<div class="form-display">','</div>'),'',$value);
                        if(isset($fields['label'])){
                            $return_value .= '['.$fields['label'].':'.$value.']';
                        }elseif($value){
                            $return_value .= '['.$value.']';
                        }
                        
                    }
                    $return_value .= '<br>';
                }
                
                break;    

            default:
            
                //その他の入力用のHTMLを取得
                $return_value = $list_value[$param_key];
                break;
                            
        }

        return $return_value;

    }

    
    /**
     * フォーム入力用HTMLコードの取得
     * 第1引数：request（getやpost）：【配列】
     * 第2引数：フィールド基本情報：【配列】
     * 
     * 戻り値：フィールド別のinput用HTMLコード：【配列】
     */
    public function getHtml($request,$params)
    {

        $input_html = array();

        //必須のデフォルトフィールドの登録
        (isset($request['submit_flg']))? $submit_flg = $request['submit_flg']: $submit_flg = '';
        $input_html['submit_flg'] = '<input type="hidden" name="submit_flg" value="'.$submit_flg.'">';//submitボタンをクリックしたかの判定
                
        if(!isset($params) || empty($params)) return $input_html; 

        foreach($params as $param_key => $param_value){
/*
            if(!isset($param_value['input']) || !$param_value['input']) continue;

            $html = $this->getInputHtml($request,$param_value['input'],$param_key,$param_value);
*/
            if(isset($param_value['input']) && $param_value['input']){
                $html = $this->getInputHtml($request,$param_value['input'],$param_key,$param_value);
            }
            elseif(isset($param_value['data_type']) && $param_value['data_type']){
                $html = $this->getDisplayHtml($param_key,$param_value,$request);
            }
            elseif(isset($param_value['html']) && $param_value['html']){
                $html = $param_value['html'];
            }
            else{
                continue;
            }

            if($html) $input_html[$param_key] = $html;

        }

        return  $input_html;

    }

    
    /**
     * ラベルHTML、入力HTML、説明文HTMLをレイアウト情報を元に、フォームHTMLを生成する
     * 第1引数：フィールド基本情報：【配列】
     * 第2引数：ラベルHTML：【配列】
     * 第3引数：インプットHTML：【配列】
     * 第4引数：説明文HTML：【配列】
     * 第5引数：その他のHTML：【配列】
     * 第6引数：レイアウトオプション：【配列】
     * 
     * 戻り値：HTMLコード：【文字列】
     */
    public function getFormHtml($fields,$label_html,$input_html,$description_html,$other_html = array(),$layout_option = array())
    {

        $hidden = array();//非表示のフィールド
        $layout = array();//表示させるフィールド
        $tab = array();//表示させるフィールド
        $row = 1;
        $col = 1;
        
        //各フィールドをレイアウト情報を元に並べ替え
        foreach($fields as $fields_key => $fields_value){
            
            //tabが存在する場合
            if(strpos($fields_key,'tab-') !== false){
                $col = 1;
                $layout[$fields_key] = $fields_value['label'];
                $tab[$fields_key] = $fields_value['label'];
                continue;
            }

            if(strpos($fields_key,'row-') !== false){
                $col = 1;
                $row = str_replace('row-','',$fields_key);
                continue;
            }
            
            if(!isset($fields_value['layout']) || !$fields_value['layout']) continue;

            if($fields_value['layout'] == 'hidden'){
                $hidden[] = $fields_key;
                continue;
            }  
            //if(!isset($fields_value['label']) || !$fields_value['label']) continue;
            
            //layoutのclassを取得
            (isset($fields_value['layout']) && $fields_value['layout'])? $class = $fields_value['layout'] : $class = 'col-md-6';

            //そのkeyの配列が存在していない場合
            if(!isset($layout[$row])) $layout[$row] = array();
            if(!isset($layout[$row][$col])) $layout[$row][$col] = array();

            //レイアウト情報をset
            $layout[$row][$col][$fields_key] = $class;
            $col++;

        }

        //レイアウト情報を元にFORMのHTMLを生成
        //-----------------------------------------
        $html = '';
        $cnt = 1;
        if(isset($layout_option['layout_name']) && $layout_option['layout_name']){
            $layout_name = $layout_option['layout_name'].'-';
        }else{
            $layout_name = '';
        }
        if(isset($layout_option['layout_class']) && $layout_option['layout_class']){
            $layout_class = $layout_option['layout_class'];
        }else{
            $layout_class = '';
        }


        //タブリストのHTMLを生成
        //-----------------------------------------
        if($tab){
            $html .= '<ul class="nav nav-tabs mb-4">'."\n";
            $tab_class = 'active';
            foreach($tab as $t_key => $t_value){
                $html .= '<li class="nav-item">
                    <a href="#'.$t_key.'" class="nav-link '.$tab_class.'" id="nav-item-'.$t_key.'" data-bs-toggle="tab">'.$t_value.'</a>
                </li>'."\n";
                $tab_class = '';
            }
            $html .= '</ul>'."\n";
            $html .= '<div class="tab-content">'."\n";
        }


        //rowの生成
        //------------------------------------------
        //tab フラグ
        $tab_flg = false;
        $tab_class = 'active';
        if(empty($tab)){//タグがない場合に、並び替え
            ksort($layout);
        }
        foreach($layout as $row => $columns){

            //tabが存在する場合　全体を要素で囲む
            if(strpos($row,'tab-') !== false){
                if($tab_flg){$html .= '</div><!--'.$tab_flg.' END -->'."\n";}
                $html .= '<div id="'.$row.'" class="tab-wrap tab-pane '.$tab_class.'"><!--'.$row.' START -->'."\n";
                $tab_flg = $row;
                $tab_class = '';
                continue;
            }

            //2カラム以降を囲む要素
            if($cnt == 2 && $layout_name){
                $html .= '<div id="'.$layout_name.'wrap" class="'.$layout_class.'">'."\n";
            }

            $html .= '<div id="'.$layout_name.'row-'.$row.'" class="row">'."\n";
            ksort($columns);
            //colの生成
            foreach($columns as $col){
                foreach($col as $name => $value){

                    $html .= '<div class="'.$value.' mb-4">'."\n";     
                    
                    //ラベル出力
                    if(isset($label_html[$name]) && $label_html[$name]){
                        $html .= $label_html[$name]."\n";
                    }

                    //入力フィールド出力
                    if(isset($input_html[$name]) && $input_html[$name]){
                        $html .= $input_html[$name]."\n";
                    }

                    //説明文出力
                    if(isset($description_html[$name]) && $description_html[$name]){
                        $html .= $description_html[$name]."\n";
                    }
                
                    //その他のHTML出力
                    if(isset($other_html[$name]) && $other_html[$name]){
                        $html .= $other_html[$name]."\n";
                    }

                    $html .= '</div>'."\n";

                }
            }

            $html .= '</div>'."\n";

            $cnt++;

        }

        //2カラム以降を囲む要素
        if($cnt > 2 && $layout_name){
            $html .= '</div>'."\n";    
        }

        $html .= "\n";

        //tabを囲む要素
        if($tab_flg){
            $html .= '</div><!--'.$tab_flg.' END -->'."\n";
            $html .= '</div><!-- tab-content END -->'."\n";
        }

        //hiddenフィールドの表示
        foreach($hidden as $h_key => $h_value){
            //入力フィールド出力
            if(isset($input_html[$h_value]) && $input_html[$h_value]){
                $html .= $input_html[$h_value]."\n";
            }
        }


        return $html;

    }

    
    /**
     * その他のHTMLの取得
     * 第1引数：フィールド基本情報：【配列】
     * 
     * 戻り値：フィールド別のinput用HTMLコード：【配列】
     */
    public function getOtherHtml($params)
    {

        $other_html = array();


        foreach($params as $param_key => $param_value){

            if(!isset($param_value['component']) || !$param_value['component']) continue;

            switch ($param_value['component']) {

                case 'button':
    
                    //selectの入力用のHTMLを取得
                    $html = $this->getButtonHtml($param_value);
                    break;
            }
    
            
            if($html) $other_html[$param_key] = $html;

        }

        return  $other_html;
    }

    /**
     * buttonのHTMLコードの取得
     * 第1引数：データベースから取得した表示用データ：【配列】
     * 第2引数：フィールド基本情報：【配列】
     * 
     * 戻り値：HTMLコード：【配列】
     */
    public function getButtonHtml($param_value)
    {

        $html = '';

        if(!isset($param_value['button_type']) || !$param_value['button_type']) return $html;

        if(!isset($param_value['button_label']) || !$param_value['button_label']) return $html;

        (isset($param_value['class']) && $param_value['class']) ? $class = $param_value['class'] : $class = '';
        (isset($param_value['button_layout']) && $param_value['button_layout']) ? $button_layout = $param_value['button_layout'] : $button_layout = 'left';
        
        $html .= '<div class="mt-3 '.$button_layout.'">';

        if($param_value['button_type'] == 'submit'){
            $html .= '<div id="submit-message" class=""></div>
            <button class="btn btn-primary '.$class.'" type="submit" name="action" value="create_submit" id="form-submit" onclick="return confirm(&quot;本当に'.$param_value['button_label'].'しますか？&quot;)">'.$param_value['button_label'].'</button>';
        }

        $html .= '</div>';

        return $html;

    }

    /** 
     * paginationを出力する
     *
     * 第1引数：トータル件数：【数字】
     * 第2引数：現在のページ：【数字】
     * 第3引数：表示件数/page：【数字】
     * 第4引数：URL：【文字列】
     * 第5引数：表示範囲（最初、前ページ、現在ページ、次ページ、最後）【数字】
     */
    public function pagination($count,$currentPage,$num = 10,$url = '', $range = 3)
    {

        if(!isset($range) || !$range) $range = 1;

        $html = '';

        //複数ページにならない場合 トータル件数 <= 表示件数
        if($count <= $num) return $html;

        //pageの計算
        $last_page = floor($count / $num) + 1;//last page

        //クエリパラメータを文字列で生成
        $general = new snpmGeneral();
        $param = $general->getQueryParam(array('page'));
        //$param = substr($param,0,-1);

        $html .='<ul class="pagination">';


        //現在のページが1ページ目でない場合
        if($currentPage != 1){

            //【最初ページボタン表示】
            $html .='<li class="page-item"><a class="page-link" href="'.$url.$param.'">1</i></a></li>';

            //ページャーの最小ページと最初ページにギャップがある場合
            if($currentPage - $range > $range + 1){
                $html .='<li class="page-item">…</li>';
            }            
        }

        /**
         * ページャーの表示 rangeの数分表示
         * rangeが2の場合は、現在のページを含めないで２ページ分
         * 「１」「２」「現在のページ」
         */

        //カレントページがラストの場合、ページャーを１つ余分に表示させる
        ($currentPage == $last_page)? $range_dummy = $range+1:$range_dummy = $range;

        for($i = $currentPage - $range_dummy;$i < $currentPage;$i++){
            if($i <= 1 ) continue;
            $html .='<li class="page-item"><a class="page-link" href="'.$url.'page/'.$i.'/'.$param.'">'.$i.'</a></li>';
        }

        //現在のページ
        $html .='<li class="page-item active"><span class="page-link">'.$currentPage.'</span></li>';

        /**
         * ページャーの表示 rangeの数分表示
         * rangeが2の場合は、現在のページを含めないで２ページ分
         * 「現在のページ」「４」「５」
         */
        //カレントページが最初の場合、ページャーを１つ余分に表示させる
        ($currentPage == 1)? $range_dummy = $range+1:$range_dummy = $range;

        for($i = $currentPage + 1;$i <= $currentPage+$range_dummy;$i++){
            if($i >= $last_page ) break;
            $html .='<li><a class="page-link" href="'.$url.'page/'.$i.'/'.$param.'">'.$i.'</a></li>';
        }

        //現在のページが最後のページでない場合
        if($currentPage != $last_page){

            //ページャーの最大ページとラストページにギャップがある場合
            if($i < $last_page){
                $html .='<li class="page-item">…</li>';
            }
            
            //【最後ページボタン表示】
            $html .='<li class="page-item"><a class="page-link" href="'.$url.'page/'.$last_page.'/'.$param.'">'.$last_page.'</a></li>';

        }


        $html .='</ul>';

        return $html;

    }

}