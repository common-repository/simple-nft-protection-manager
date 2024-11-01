<?php
/**---------------------------------------------------------
 * wordpressのデータベースを操作するclass
----------------------------------------------------------- */
class snpmQuery {

    //where文生成関数
    function sql_where($fields,$request){

        //whereのセット
        $where = '';
        $params = array();
        if(!isset($fields['general_info']['where']) || !$fields['general_info']['where']) return array();
            
        foreach($fields['general_info']['where'] as $where_value){

            $field1 = $where_value[0];
            $operator = $where_value[1];
            $field2 = $where_value[2];

            if(isset($request[$field2]) && $request[$field2] !== null && $request[$field2] != ''){
                if($where){
                    $where .= ' and ';
                }

                //検索フィールドが複数の場合
                if(strpos($field1,',') !== false){
                    $where .= 'concat('.$field1.') '.$operator.' %s ';
                }
                //検索フィールドが単数の場合
                else{
                    $where .= $field1." ".$operator.' %s ';
                }

                if($field2 == 'from_date'){
                    $request[$field2] = $request[$field2] . ' 00:00:00';
                }
                elseif($field2 == 'to_date'){
                    $request[$field2] = $request[$field2] . ' 23:59:59';
                }

                //比較演算子が「like」の場合
                if($operator == 'like'){
                    $params[] = '%'.$request[$field2].'%';
                }else{
                    $params[] = $request[$field2];
                }
                
            }
        }
        if($where){
            $where = ' where '.$where;
        }

        return array('where'=>$where,'param'=>$params);

    }

    /**
     * パラメータを基に、テーブルの件数を取得
     * 
     * $fieldsには、テーブル名やwhere条件、抽出したいカラム名が可能されている
     * $requestには、$_GETなどで取得した抽出条件の値が入っている
     */
    function table_count($fields,$request)
    {
        if(!isset($fields['general_info']['table_name'])) return;
        $table = $fields['general_info']['table_name'];
        

        //whereのセット
        $results = $this->sql_where($fields,$request);
        $where = $results['where'];
        $params = $results['param'];

        global $wpdb;

        $sql = "SELECT id FROM ".$table.$where.";";

        $query = $wpdb->prepare($sql,$params); 
        $wpdb->get_results( $query);
        $num = $wpdb->num_rows; //最後に実行したクエリ「$sql」の行数を取得

        return $num;

    }

    /**
     * パラメータを基に、データベースをselect
     * 
     * $TABLEには、テーブル名やwhere条件、抽出したいカラム名が可能されている
     * $VALUEには、$_GETなどで取得した抽出条件の値が入っている
     */
    function table_select($fields,$request)
    {
        if(!isset($fields['general_info']['table_name'])) return;
        $table = $fields['general_info']['table_name'];
        
        //wp_optionsのテーブルから値を取得する場合
        if($fields['general_info']['table_name'] == 'wp_options'){
            $results = $this->wp_options_select($fields,$request);
            return $results;
        } 

        //取得カラムのセット
        $columns = '';
        foreach($fields as $key => $f_value){

            //[data_type]の値が無い場合は処理しない
            if(!isset($f_value['data_type']) || !$f_value['data_type']) continue;
            $columns .= $key.',';

        }
        $columns = rtrim($columns, ',');//最後のカンマを取り除く

        //whereのセット
        $sql_where = $this->sql_where($fields,$request);
        $where = $sql_where['where'];
        $params = $sql_where['param'];

        //orderby
        $orderby = "";
        $order = "";
        if(isset($request['orderby']) && $request['orderby']){
            if(isset($request['order']) && $request['order']){
                $order = $request['order'];
            }
            $orderby .= " ORDER BY ".$request['orderby']." ".$order." ";
        }

        //limit
        $limit = "";
        if(isset($request['num']) && isset($request['paged'])){
            $num = $request['num'];
            $paged = $request['paged'];
            ($paged == 1) ? $limit_start = 0 : $limit_start = $paged * $num - $num;
            $limit_end = $num;
            $limit .= " LIMIT " . $limit_start . ", ".$limit_end." ";    
        }

        global $wpdb;

        $sql = "SELECT ".$columns." FROM ".$table.$where.$orderby.$limit.";";
        $query = $wpdb->prepare($sql,$params); 
        $results = $wpdb->get_results( $query, ARRAY_A );//配列で取得
        return $results;

    }
    /**
     * wp_optionsのテーブルからデータを取得
     */
    function wp_options_select($fields,$request)
    {

        //値とフォーマットのセット
        foreach($fields as $key => $f_value){

            //[value]が無い場合は処理しない
            if(!isset($f_value['value'])) continue;
            if(!isset($request[$key])) continue;

            $value = $request[$key];

            //wp_optionsテーブルに、指定のキーで、値を登録する
            add_option(SNPM_PLUGIN_PREFIX.'_'.$key, $value);

        }
    }

    /**
     * 任意のテーブルにデータを追加
     */
    //---------------------------------------
    function table_insert($fields,$request)
    {

        if(!isset($fields['general_info']['table_name'])) return;
        if(empty($request)) return;

        //テーブル名のセット
        $table = $fields['general_info']['table_name'];

        $values = array();
        $value_format = array();

        //値とフォーマットのセット
        foreach($fields as $key => $f_value){

            //[input]の値が無い場合は処理しない
            if(!isset($f_value['value'])) continue;
            if(!isset($request[$key])) continue;

            $values[$key] = $request[$key];

            if(isset($f_value['data_type']) && $f_value['data_type'] == 'number'){
                $value_format[] = '%d';
            }elseif(isset($f_value['data_type']) && $f_value['data_type'] == 'float'){
                    $value_format[] = '%f';
            }else{
                $value_format[] = '%s';
            }
        }

        global $wpdb;
        $result = $wpdb->insert( 
            $table, 
            $values, 
            $value_format 
        );

    }

    /**
     * 任意のテーブルのデータを更新
     */
    function table_update($fields,$request)
    {

        if(!isset($fields['general_info']['table_name'])) return;
        if(empty($request)) return;

        //テーブル名のセット
        $table = $fields['general_info']['table_name'];

        //whereのセット
        $where_data = $this->sql_where($fields,$request);
        $where = $where_data['where'];
        $where_param = $where_data['param'];


        $set_values = array();
        $set_value = '';

        //値とフォーマットのセット
        foreach($fields as $key => $f_value){

            //[value]が無い場合は処理しない
            if(!isset($f_value['value'])) continue;
            if(!isset($request[$key])) continue;

            $set_values[$key] = $request[$key];

            if(isset($f_value['data_type']) && $f_value['data_type'] == 'number'){
                $set_value .= $key. '= %d,';
            }elseif(isset($f_value['data_type']) && $f_value['data_type'] == 'float'){
                $set_value .= $key. '= %f,';
            }else{
                $set_value .= $key. '= %s,';
            }
        }
        $set_value = substr($set_value,0,-1);

        global $wpdb;

        $sql = "UPDATE ".$table." SET ".$set_value." ".$where.";";
        $params = $set_values + $where_param;
        $query = $wpdb->prepare( $sql, $params );
        //$query = call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql),$set_values,$where_param));
        $wpdb->get_results( $query);

    }

    /**
     * wp_optionsのテーブルのデータを更新
     */
    function wp_options_update($fields,$request)
    {

        if(!isset($fields['general_info']['table_name'])) return;
        if(empty($request)) return;

        //テーブル名のセット
        $table = $fields['general_info']['table_name'];

        $update_option = array();

        //値とフォーマットのセット
        foreach($fields as $key => $f_value){

            //[value]が無い場合は処理しない
            if(!isset($f_value['value'])) continue;
            if(!isset($request[$key])) continue;

            $update_option[$key] = $request[$key];

        }

        //wp_optionsテーブルに、指定のキーで、値を登録する
        update_option($table, $update_option);

    }

}