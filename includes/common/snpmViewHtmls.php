<?php
/**---------------------------------------------------------
 * よく使うHTMLの設定
----------------------------------------------------------- */
class snpmViewHtmls {


    /**
     * フォーム入力用HTMLコードの表示
     * 第1引数：request（getやpost）：【配列】
     * 第2引数：入力タイプ type：【文字列】
     * 第3引数：フィールド名 name：【文字列】
     * 第4引数：フィールド設定情報 value：【配列】
     * 第5引数：mode：【文字列・配列】
     * 
     * 戻り値：フィールド別のinput用HTMLコード：【配列】
     */
    public function viewInputHtml($request,$type,$field,$value,$mode = null)
    {

        switch ($type) {

            case 'select':

                //selectの入力用のHTMLを取得
                $this->inputSelect($field,$value,$request,$mode);
                break;

            case 'radio':

                //radioの入力用のHTMLを取得
                $this->inputRadio($field,$value,$request);
                break;

            case 'checkbox':

                //checkboxの入力用のHTMLを取得
                $this->inputCheckbox($field,$value,$request);
                break;

            case 'textarea':

                //textareaの入力用のHTMLを取得
                $this->inputTextarea($field,$value,$request);
                break;

            case 'texteditor':

                //texteditorの入力用のHTMLを取得
                $this->inputTextarea($field,$value,$request,'editor');
                break;

            case 'datetime-local':

                //texteditorの入力用のHTMLを取得
                $this->inputDateTime($field,$value,$request);
                break;
                
            case 'variable':

                //可変フィールドの入力用のHTMLを取得
                $this->inputVariable($field,$value,$request);
                break;

            case 'media':

                //mediaフィールドの入力用のHTMLを取得
                $this->inputMedia($field,$value,$request,$mode);
                break;

            case 'file':

                //fileフィールドの入力用のHTMLを取得
                $this->inputFile($field,$value,$request,$mode);
                break;

            case 'password':

                //情報をクリア
                if(isset($request[$field]) && $request[$field]) $request[$field] = '';
                //可変フィールドの入力用のHTMLを取得
                $this->inputOther($field,$value,$request);
                break;
  
            default:
            
                //その他の入力用のHTMLを取得
                $this->inputOther($field,$value,$request);
                break;
                            
        }

    }

    
    /**
     * フォーム用ラベルHTMLの表示
     * 第1引数：フィールド基本情報：【配列】
     * 第2引数：フィールド名：【文字列】
     * 
     * 戻り値：フィールド別のinput用HTMLコード：【配列】
     */
    public function viewLabel($params,$fields)
    {

        if(!isset($params[$fields]['label']) || !$params[$fields]['label']) return;

        $html='<label for="'.$fields.'" class="form-label">'.$params[$fields]['label'].'</label> ';

        if(isset($params[$fields]['validation']) && in_array ('required', $params[$fields]['validation'] )){
            $html.='<span class="badge bg-danger" style="font-size:0.5rem">必須</span> ';
        }

        echo wp_kses_post($html);

    }

      
    /**
     * フォーム用ラベルHTMLの表示
     * 第1引数：フィールド基本情報：【配列】
     * 第2引数：フィールド名：【文字列】
     * 
     * 戻り値：フィールド別のinput用HTMLコード：【配列】
     */
    public function viewDescription($params,$fields)
    {

        if(!isset($params[$fields]['description']) || !$params[$fields]['description']) return;

        $html='<div class="form-text">'.$params[$fields]['description'].'</div> ';
        
        echo wp_kses_post($html);

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

        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $choices = $this->getChoices($field,$request,$params,$mode);
        //(isset($params['choices'])) ? $choices = $params['choices'] : $choices = '';
        $value = $this->valueJudgment($field,$params,$request);

        echo '<div id="'.esc_html($field).'-message" class="entry-message">';
        echo '</div>';
        if ($choices) {
            echo '<select name="'.esc_html($field).'" id="'.esc_html($field).'" class="entry-field '.esc_html($field)." ".esc_html($class).'" '.wp_kses_post($option).'>'."\n";
            foreach ((array) $choices as $choices_key => $choices_value) {
                if (strcmp($value, $choices_key) == 0) {
                    echo '<option value="'.esc_html($choices_key).'" selected>'.wp_kses_post($choices_value).'</option>'."\n";
                } else {
                    echo '<option value="'.esc_html($choices_key).'">'.wp_kses_post($choices_value).'</option>'."\n";
                }
            }
            echo '</select>';
        }

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

        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        (isset($params['choices'])) ? $choices = $params['choices'] : $choices = '';
        $value = $this->valueJudgment($field,$params,$request);

        (isset($params['before_wrap']))? $before_wrap = $params['before_wrap'] : $before_wrap = '';
        (isset($params['after_wrap']))? $after_wrap = $params['after_wrap'] : $after_wrap = '';

        echo '<div class="entry-wrap" id="'.esc_html($field).'">';
        echo '<div id="'.esc_html($field).'-message" class="entry-message">';
        echo '</div>';
        echo '<input type="hidden" name="'.esc_html($field).'" value="">'."\n";//何もチェックしていない時、SESSIONの値が入ってしまう不具合を防ぐ
        echo '<ul class="checkbox-list">'."\n";
        if ($choices) {
            $cnt = 1;
            foreach ((array) $choices as $choices_key => $choices_value) {
                if(!$choices_key) continue;//radioの場合、valueがブランクは排除
                if (strcmp($value, $choices_key) == 0) {
                    echo wp_kses_post($before_wrap).'<li><label class="active"><input type="radio" name="'.esc_html($field).'" value="'.esc_html($choices_key).'" checked="checked" class="entry-field '.esc_html($field)." ".esc_html($class).'" '.wp_kses_post($option).'><span class="list-item-label">'.wp_kses_post($choices_value).'</span></label></li>'.wp_kses_post($after_wrap)."\n";
                } else {
                    echo wp_kses_post($before_wrap).'<li><label ><input type="radio" name="'.esc_html($field).'" value="'.esc_html($choices_key).'" class="entry-field '.esc_html($field)." ".esc_html($class).'" '.wp_kses_post($option).'><span class="form-check-label list-item-label">'.wp_kses_post($choices_value).'</span></label></li>'.wp_kses_post($after_wrap)."\n";
                }
                $cnt++;
            }
        }   
        echo '</ul>';     
        echo '</div>';


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

        $cnt = 0;

        echo '<div class="entry-wrap" id="'.esc_html($field).'">';
        echo '<div id="'.esc_html($field).'-message" class="entry-message">';
        echo '</div>';

        //選択肢が3つ以上で「全選択」を表示
        if(count($choices) >= 3){
            echo '<div class="bold w-100">全選択：<input type="checkbox" id="'.esc_html($field).'_all" name="'.esc_html($field).'_all" value="1" data-field="'.esc_html($field).'" onClick="allChecked(event);"></div>';
        }

        //checkboxは、チェックがされないとnameすら送信されないので、チェックされていない場合、空の値を送る為のコード
        echo '<input type="hidden" name="'.esc_html($field).'['.esc_html($cnt).']" value="" checked="checked">'."\n";
        echo '<ul class="checkbox-list">'."\n";
        if ($choices) {
            foreach ((array) $choices as $choices_key => $choices_value) {
                if(!$choices_key && !$choices_value) continue;//checkboxの場合、valueがブランクは排除
                $cnt++;
                if (in_array($choices_key, (array) $VALUE)) {
                    echo '<li><label class="active"><input type="checkbox" name="'.esc_html($field).'['.esc_html($cnt).']" value="'.esc_html($choices_key).'" data-field="'.esc_html($field).'" class="entry-field '.esc_html($field)." ".esc_html($class).'" checked="checked" '.wp_kses_post($option).'><span class="list-item-label">'.wp_kses_post($choices_value).'</span></label></li>'."\n";
                } else {
                    echo '<li><label><input type="checkbox" name="'.esc_html($field).'['.esc_html($cnt).']" value="'.esc_html($choices_key).'" data-field="'.esc_html($field).'" class="entry-field '.esc_html($field)." ".esc_html($class).'" '.wp_kses_post($option).'><span class="list-item-label">'.wp_kses_post($choices_value).'</span></label></li>'."\n";
                }
            }

        }
        
        if($cnt == 0){
            echo wp_kses_post($before_wrap).'<li>選択できる'.esc_html($params['label']).'はありません。</li>'.wp_kses_post($after_wrap)."\n";
        }

        echo '</ul>';
        echo '</div>';


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

        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $value = $this->valueJudgment($field,$params,$request);

        echo '<div id="'.esc_html($field).'-message" class="entry-message">';
        echo '</div>';

        if($mode == 'editor'){
            echo '<textarea rows="20" name="'.esc_html($field).'" '.'" class="entry-field '.esc_html($field)." ".esc_html($class).'" id = "texteditor" '.wp_kses_post($option).'>'.esc_html($value).'</textarea>';
        }else{
            echo '<textarea rows="10" name="'.esc_html($field).'" '.'" class="entry-field '.esc_html($field)." ".esc_html($class).'" id = "'.esc_html($field).'" '.wp_kses_post($option).'>'.esc_html($value).'</textarea>';
        }

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


        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $value = $this->valueJudgment($field,$params,$request);        

        if($params['input'] != 'hidden'){
            echo '<div id="'.esc_html($field).'-message" class="entry-message">';
            echo '</div>';
        }
        
        if($value){
            echo '<div id="'.esc_html($field).'_thumbnail" class="mb-1 form-control"><img src="'.esc_html($value).'"></div>';
            echo '<div class="mb-1"><input id="'.esc_html($field).'" type="text" name="'.esc_html($field).'" value="'.esc_html($value).'" class="entry-field '.esc_html($field)." ".esc_html($class).'" '.wp_kses_post($option).' /></div>';
        }else{
            echo '<div id="'.esc_html($field).'_thumbnail" class="mb-1"></div>';
            echo '<div class="mb-1"><input id="'.esc_html($field).'" type="text" name="'.esc_html($field).'" value="" class="entry-field '.esc_html($field)." ".esc_html($class).'" '.wp_kses_post($option).'/></div>';
        }
        echo '<input class="upload_image_button button button-secondary button-large" type="button" value="画像をアップロード" data-field="'.esc_html($field).'" /> ';
        echo '<input class="remove_image_button button button-secondary button-large" type="button" value="画像を削除" data-field="'.esc_html($field).'"  />';


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


        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $value = $this->valueJudgment($field,$params,$request);

        echo '<div id="'.esc_html($field).'-message" class="entry-message">';
        echo '</div>';
        echo '<div id="'.esc_html($field).'">';
        if($value){
            if(isset($params['data_type']) && $params['data_type'] == 'img'){
                echo '<img src="'.ROOT_URL.UPLOAD_DIR.'/'.MANAGEMENT_ID.'/'.esc_html($value).'" class="img-fluid">';
            }else{
                echo '<div>'.esc_html($value).'</div>';
            }
            //可変フィールドの場合は、「削除ボタン」を表示させない
            if($mode != 'variable'){
                echo '<div class="btn btn-secondary file_delete" onclick="return confirm(&quot;本当に削除しますか？&quot;)" data-field="'.esc_html($field).'">削除</div>';
            }
            echo '<input type="hidden" name="'.esc_html($field).'" value="'.esc_html($value).'">';
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
            echo '<input type="'.esc_html($params['input']).'" name="'.esc_html($field).'" class="entry-field '.esc_html($field)." ".esc_html($class).'" '.wp_kses_post($option).' accept="'.$accept.'">';
        }
        echo '</div>';


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


        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $value = $this->valueJudgment($field,$params,$request);
        $date_time = new \DateTime($value);

        echo '<div id="'.esc_html($field).'-message" class="entry-message">';
        echo '</div>';
        echo '<input type="'.esc_html($params['input']).'" name="'.esc_html($field).'" id="'.esc_html($field).'" value="'.esc_html($date_time->format('Y-m-d\TH:i')).'" class="entry-field '.esc_html($field)." ".esc_html($class).'" '.wp_kses_post($option).'>';

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


        (isset($params['class'])) ? $class = $params['class'] : $class = '';
        (isset($params['option'])) ? $option = $params['option'] : $option = '';
        $value = $this->valueJudgment($field,$params,$request);

        if($params['input'] != 'hidden'){
            echo '<div id="'.esc_html($field).'-message" class="entry-message">';
            echo '</div>';
        }

        //値が配列の場合
        if($value && is_array($value) === true){
            echo '<input type="'.esc_html($params['input']).'" name="'.esc_html($field).'" id="'.esc_html($field).'" value="" class="entry-field '.esc_html($field)." ".esc_html($class).'" '.wp_kses_post($option).'>';
        }else{
            echo '<input type="'.esc_html($params['input']).'" name="'.esc_html($field).'" id="'.esc_html($field).'" value="'.htmlspecialchars($value, ENT_QUOTES).'" class="entry-field '.esc_html($field)." ".esc_html($class).'" '.wp_kses_post($option).'>';
        }

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
            $html = '<div class="form-display" '.wp_kses_post($option).'>'.$display_value.'</div>';
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

                $return_value = '<img src="'.$image_path.'?'.time().'" class="'.esc_html($class).'" '.wp_kses_post($option).'/>';//「?time())」は、キャッシュの対策
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
                            $return_value .= '['.$fields['label'].':'.esc_html($value).']';
                        }elseif($value){
                            $return_value .= '['.esc_html($value).']';
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
     * 第3引数：フィールド名：【文字列】
     * 
     * 戻り値：フィールド別のinput用HTMLコード：【配列】
     */
    public function viewHtml($request,$params,$field = null)
    {
               
        if(!isset($params) || empty($params)) return; 

        if(isset($params[$field]['input']) && $params[$field]['input']){
            $this->viewInputHtml($request,$params[$field]['input'],$field,$params[$field]);
        }
        elseif(isset($params[$field]['data_type']) && $params[$field]['data_type']){
            $html = $this->getDisplayHtml($field,$params[$field],$request);
            echo wp_kses_post($html);
        }
        elseif(isset($params[$field]['html']) && $params[$field]['html']){
            $allowed_html = wp_kses_allowed_html('post');
            $allowed_html['input'] = [
                'type'=>true,
                'id'=>true,
                'name'=>true,
                'value'=>true,
                'class'=>true,
            ];
            echo wp_kses($params[$field]['html'], $allowed_html);
        }

    }

    
    /**
     * ラベルHTML、入力HTML、説明文HTMLをレイアウト情報を元に、フォームHTMLを生成する
     * 第1引数：フィールド基本情報：【配列】
     * 第2引数：request：【配列】
     * 第5引数：その他のHTML：【配列】
     * 第6引数：レイアウトオプション：【配列】
     * 
     * 戻り値：HTMLコード：【文字列】
     */
    public function viewFormHtml($fields,$request,$other_html = array(),$layout_option = array())
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
            echo '<ul class="nav nav-tabs mb-4">'."\n";
            $tab_class = 'active';
            foreach($tab as $t_key => $t_value){
                echo '<li class="nav-item">
                    <a href="#'.esc_html($t_key).'" class="nav-link '.esc_html($tab_class).'" id="nav-item-'.esc_html($t_key).'" data-bs-toggle="tab">'.esc_html($t_value).'</a>
                </li>'."\n";
                $tab_class = '';
            }
            echo '</ul>'."\n";
            echo '<div class="tab-content">'."\n";
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
                if($tab_flg){echo '</div><!--'.esc_html($tab_flg).' END -->'."\n";}
                echo '<div id="'.esc_html($row).'" class="tab-wrap tab-pane '.esc_html().'"><!--'.esc_html($row).' START -->'."\n";
                $tab_flg = $row;
                $tab_class = '';
                continue;
            }

            //2カラム以降を囲む要素
            if($cnt == 2 && $layout_name){
                echo '<div id="'.esc_html($layout_name).'wrap" class="'.esc_html($layout_class).'">'."\n";
            }

            echo '<div id="'.esc_html($layout_name).'row-'.esc_html($row).'" class="row">'."\n";
            ksort($columns);
            //colの生成
            foreach($columns as $col){
                foreach($col as $name => $value){

                    echo '<div class="'.esc_html($value).' mb-4">'."\n";     
                    
                    //ラベル出力
                    $this->viewLabel($fields,$name);

                    //入力フィールド出力
                    $this->viewHtml($request,$fields,$name);

                    //説明文出力
                    $this->viewDescription($fields,$name);
                
                    //その他のHTML出力
                    if(isset($other_html[$name]) && $other_html[$name]){
                        echo wp_kses_post($other_html[$name])."\n";
                    }

                    echo '</div>'."\n";

                }
            }

            echo '</div>'."\n";

            $cnt++;

        }

        //2カラム以降を囲む要素
        if($cnt > 2 && $layout_name){
            echo '</div>'."\n";    
        }

        echo "\n";

        //tabを囲む要素
        if($tab_flg){
            echo '</div><!--'.esc_html($tab_flg).' END -->'."\n";
            echo '</div><!-- tab-content END -->'."\n";
        }

        //hiddenフィールドの表示
        foreach($hidden as $h_key => $h_value){
            //入力フィールド出力
            $this->viewHtml($request,$fields,$h_value);
        }

        //必須のデフォルトフィールドの登録
        (isset($request['submit_flg']))? $submit_flg = $request['submit_flg']: $submit_flg = '';
        echo '<input type="hidden" name="submit_flg" value="'.esc_html($submit_flg).'">';//submitボタンをクリックしたかの判定


    }


}