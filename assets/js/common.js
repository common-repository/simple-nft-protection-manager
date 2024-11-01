/**
 * common.js
 */  
window.addEventListener('load', function(){//HTML読み込み完了後に実行



});

/**
 * フォームのsubmitがクリックされたときの処理
 * 第１引数：フォーム要素：【オブジェクト】
 * 第２引数：フィールド基本情報名：【文字列】
 * 第３引数：コールバック関数：【オブジェクト型】
 * ※バリデーションチェック後、エラーが無ければしたい処理
 */
function form_submit(form_elem,ui_info_name,callback = null,message_id = null,submit_id = null){
  if(form_elem != null){

    // フォームの submit イベントを乗っ取り
    form_elem.addEventListener("submit", function (e) {

      e.preventDefault();
    
      //submitフラグをONにする
      if(form_elem.submit_flg.value != null){
        form_elem.submit_flg.value = 'submit';
      }
      
      //form情報を一式取得(formData)
      form_data = get_form_data(form_elem);

      //form情報を一式取得(配列)
      form_value = get_form_value(form_elem);

      ajax_validation(form_data,form_value,ui_info_name,form_elem,callback,message_id,submit_id);

    });
  }
}


/**
 * フォーム内のフィールドの値が変わった時の処理
 * 第１引数：フォーム要素：【オブジェクト】
 * 第２引数：フィールド基本情報名：【文字列】
 */
function fields_change(form_elem,ui_info_name,callback = null,message_id = null,submit_id = null){
  if(form_elem != null){

    form_elem.addEventListener("change", function (e) {

      // 一つでもチェックを外すと「全て選択」のチェック外れる
      disChecked(e)

      //form情報を一式取得(formData)
      form_data = get_form_data(form_elem);

      //form情報を一式取得(配列)
      form_value = get_form_value(form_elem);

      //通常のフィールドのバリデーション
      ajax_validation(form_data,form_value,ui_info_name,form_elem,callback,message_id,submit_id);

    });
  }
}

/**
 * フォーム内のファイル削除クリック後の処理
 */
function callback_file_delete(data,param){

  if(data['id'] && data['html']){
    //一端要素の中を削除
    var file_elem = document.getElementById(data['id']);
    while(file_elem.lastChild){
      file_elem.removeChild(file_elem.lastChild);
    }
    //要素を追加
    file_elem.insertAdjacentHTML('beforeend',data['html']);

  }
  
}

/**
 * validationメイン処理
 * 【引数】
 * form_data:【formData型】
 * form_value:【配列】
 * 【流れ】
 * ajax（バックエンド側）でバリデーションチェックを行う
 * チェック後の結果をjsonなどで取得
 * 「callback_validation」関数を呼び出し
*/
function ajax_validation(form_data,form_value,ui_info_name,form_elem,callback=null,message_id=null,submit_id=null){

  form_data.append('action','validation');
  form_data.append('ui_info_name',ui_info_name);

  let param = {'submit_flg':form_value['submit_flg']};
  param['data'] = form_value;
  param['message-id'] = message_id;//submitボタンの上にメッセージを表示させる要素のID
  param['submit-id'] = submit_id;//submitボタンのID
  param['callback'] = callback;
  param['form_elem'] = form_elem;

  //url,postするパラメータ,callback関数,callback関数用のパラメータ
  ajax_fetch(ajaxurl, form_data, callback_validation, param); 

}


//フォーム内の値を取得してformDataオブジェクトで返す
//---------------------------------------------
function get_form_data(form_elem){

const post_data = new FormData; // フォーム方式で送る場合

for(elem in form_elem) {

  // name未設定のものからは取得しない
  if(form_elem[elem]?.name === undefined || form_elem[elem].name == ""){
    continue;
  }

  // チェックボックス、ラジオボタンはチェックが入ってないものは取得しない
  if(form_elem[elem].type == 'checkbox' || form_elem[elem].type == 'radio'){
      if(!form_elem[elem].checked){
          continue;
      }
  }  

  // valueが無いものは取得しない
  if(form_elem[elem]?.value === undefined){
    continue;
  }

  var name = form_elem[elem].name;

  //nameの中に「[」or「]」が含まれる場合 variableの処理
  if ( name.indexOf('[') != -1 || name.indexOf(']') != -1) {

    //『[]』で囲まれた値を取得
    var regexp = /\[.+?\]/g;
    const myArray = name.match(regexp);

    var cnt = 0;

    //keyを格納しておく配列
    var key_array = {};
    for (const elem of myArray) {

      cnt++;

      //『[]』以降の値を削除
      var name  = name.replace(elem, '');

      //抜き出した文字から『[]』を削除 : 純粋な「key」を取得
      var key_value = elem.replace(/\[/g, '').replace(/\]/g, '');

      key_array[cnt] = key_value;

    }

    //console.log(key_array);

    if(cnt >= 1) var key1 = key_array[1];
    if(cnt >= 2) var key2 = key_array[2];
    if(cnt >= 3) var key3 = key_array[3];

    if(form_elem[elem].type == 'file'){
      var set_value = form_elem[elem].files[0];
    }else{
      var set_value = form_elem[elem].value;
    }

    if(cnt == 1){
      post_data.append(name+'['+key1+']', set_value);
    }
    else if(cnt == 2){
      post_data.append(name+'['+key1+']'+'['+key2+']', set_value);
    }
    else if(cnt == 3){
      post_data.append(name+'['+key1+']'+'['+key2+']'+'['+key3+']', set_value);
    }

    //console.log(data);

  }
  //アップロードファイルの場合file
  else if(form_elem[elem].type == 'file'){
    // valueが無いものは取得しない
    if(form_elem[elem]?.files[0] === undefined){
      continue;
    }
    post_data.append(name, form_elem[elem].files[0]);
  }  
  else{
    post_data.append(name, form_elem[elem].value);
  }
  
}

return post_data;

}

//フォーム内の値を取得して配列で返す場合
//---------------------------------------------
function get_form_value(form_elem){
  var data = {};
  for(elem in form_elem) {

    // name未設定のものからは取得しない
    if(form_elem[elem]?.name === undefined || form_elem[elem].name == ""){
      continue;
    }

    // チェックボックス、ラジオボタンはチェックが入ってないものは取得しない
    if(form_elem[elem].type == 'checkbox' || form_elem[elem].type == 'radio'){
        if(!form_elem[elem].checked){
            continue;
        }
    }  

    // valueが無いものは取得しない
    if(form_elem[elem]?.value === undefined){
      continue;
    }

    var name = form_elem[elem].name;

    //nameの中に「[」or「]」が含まれる場合 variableの処理
    if ( name.indexOf('[') != -1 || name.indexOf(']') != -1) {

      //『[]』で囲まれた値を取得
      var regexp = /\[.+?\]/g;
      const myArray = name.match(regexp);

      var cnt = 0;

      //keyを格納しておく配列
      var key_array = {};
      for (const elem of myArray) {

        cnt++;

        //『[]』以降の値を削除
        var name  = name.replace(elem, '');

        //抜き出した文字から『[]』を削除 : 純粋な「key」を取得
        var key_value = elem.replace(/\[/g, '').replace(/\]/g, '');

        key_array[cnt] = key_value;

      }

      //console.log(key_array);

      if(!data[name]) data[name] = {};

      if(cnt >= 1){
        var key1 = key_array[1];
        if(!data[name][key1]) data[name][key1] = {};
      }
      if(cnt >= 2){
        var key2 = key_array[2];
        if(!data[name][key1][key2]) data[name][key1][key2] = {};
      }
      if(cnt >= 3){
        var key3 = key_array[3];
        if(!data[name][key1][key2][key3]) data[name][key1][key2][key3] = {};
      }

      if(cnt == 1){
        data[name][key1] = form_elem[elem].value;
      }
      else if(cnt == 2){
        data[name][key1][key2] = form_elem[elem].value;
      }
      else if(cnt == 3){
        data[name][key1][key2][key3] = form_elem[elem].value;
      }

      //console.log(data);

    }
    //アップロードファイルの場合file
    else if(form_elem[elem].type == 'file'){
      // valueが無いものは取得しない
      if(form_elem[elem]?.files[0] === undefined){
        continue;
      }
      data[name] = form_elem[elem].files[0].name;
    }
    else{
      data[name] = form_elem[elem].value;
    }
    
  }

  return data;

}


/**
 * validation処理
 * 第１引数：ajax処理後、戻ってきたデータ：【配列】
 * 第２引数：コールバック関数に渡すパラメータ：【配列】
 *【流れ】
 * jsonで取得したデータを元に、エラーメッセージを表示
 * 「callback」関数を呼び出し（指定されていれば）
 */
function callback_validation(data, param){

  new Promise((resolve) => {

    //追加メッセージを一端全て削除
    var elems1 = document.getElementsByClassName('entry-message');
    //console.log(elems1);
    if(elems1.length > 0){
      for (var i = 0; i < elems1.length; i++) {
        var e = elems1[i];
        while(e.lastChild){
          e.removeChild(e.lastChild);
        }
      }
    }

    //タブに表示される「notice」を全て削除
    var elems4 = document.getElementsByClassName('error-caution');
    if(elems4.length > 0){
      for (var i = 0; i < elems4.length; i++) {
        var e = elems4[i];
        while(e.lastChild){
          e.removeChild(e.lastChild);
        }
      }
    }

    //全てのエラースタイルを解除
    let elems2 = document.getElementsByClassName('entry-field');
    Array.prototype.forEach.call(elems2, function (elem) {
      elem.classList.remove('input-error');
      elem.parentNode.classList.remove('input-error');
    });

    let elems3 = document.getElementsByClassName('input-error');
    Array.prototype.forEach.call(elems3, function (elem) {
      elem.classList.remove('input-error');
      elem.parentNode.classList.remove('input-error');
    });

    resolve();

  }).then(() => {

    //console.log(data['error']);
    //エラーを付与
    if(data['error'].length !== null && data['error'].length !== 0){

      var tab = [];
      
      for (var name in data['error']) {
        // 追加する要素を作成します
        var newContent = '<div class="error-message add-message">'+data['error'][name]+'</div>';

        //エラーメッセージを表示させる
        var elem = document.getElementById(name+'-message');
        if(elem){
          elem.insertAdjacentHTML('beforeend',newContent);

          //タブ要素を取得　タブがある場合
          var tab_elem = elem.closest(".tab-pane");

          //タブidを配列に格納
          if(tab_elem){
            var tab_id = tab_elem.getAttribute("id");
            tab.push(tab_id);
          }
        }

        //入力項目の背景を赤くする
        var targetElements = form.getElementsByClassName(name);
        if(targetElements.length !== 0){// name属性が存在する場合
          [].forEach.call(targetElements, function(elems) {
            if(elems.type == 'checkbox' || elems.type == 'radio'){
              elems.parentNode.classList.add("input-error");
            }else{
              elems.classList.add("input-error");
            }
          })
        }
        else{// name属性が存在しない場合（variableフィールド）
          var elems = document.getElementById(name);
          if(elems){
            elems.classList.add("input-error");
          }
        }

      }

      //タブ上にエラーがある事を伝えるアイコンを表示
      if(tab.length){
        let set = new Set(tab);//tab_idの重複排除
        let tab_deduplication = Array.from(set);//setから配列へ変換
        for(const tab_id of tab_deduplication){
          var nav_item_elem = document.getElementById("nav-item-"+tab_id);
          nav_item_elem.insertAdjacentHTML('beforeend','<div class="error-caution '+tab_id+'" data-bs-toggle="tooltip" data-bs-placement="top" title="入力エラーがあります。" ><i class="fas fa-exclamation-circle"></i></div>');
        }
      }

      /*submitボタンの処理*/
      var newContent = '<div class="error-message add-message">必要な入力が完了していません。</div>';

      //エラーメッセージを表示させる
      if(param['message-id']){
        var submit_message = document.getElementById(param['message-id']);
      }else{
        var submit_message = document.getElementById('submit-message');
      }
      //console.log(submit_message);
      if(submit_message && !submit_message.hasChildNodes()){
        submit_message.insertAdjacentHTML('beforeend',newContent);
      }

      //ボタンを無効化する
      if(param['submit-id']){
        var submit_elem = document.getElementById(param['submit-id']);
      }else{
        var submit_elem = document.getElementById('form-submit');
      }
      submit_elem.classList.add("btn-disabled");

    }

  }).then(() => {


    /*エラースタイルの処理*/
    let elems = document.getElementsByClassName('input-error');

    //エラーメッセージがない場合
    if(elems.length === 0){
      //console.log(param['message-id']);
      //エラーメッセージを消す
      if(param['message-id']){
        var submit_message = document.getElementById(param['message-id']);
      }else{
        var submit_message = document.getElementById('submit-message');
      }
      if(submit_message != null && submit_message.hasChildNodes()){
        submit_message.removeChild(submit_message.lastChild);
      }

      //console.log(param['submit-id']);
      //ボタンを有効化する
      if(param['submit-id']){
        var submit_elem = document.getElementById(param['submit-id']);
      }else{
        var submit_elem = document.getElementById('form-submit');
      }
      submit_elem.classList.remove("btn-disabled");

    }

    //console.log(param['callback']);
    //エラーがない場合-->callback関数の処理
    if(data['error'].length === null || data['error'].length === 0){
      if(param['callback']) {
        param['callback'](data,param);
      }
    }

    //loading非表示
    setTimeout(function() {
      const spinner = document.getElementById('loading');
      if(spinner){
        spinner.classList.remove('active');
      }
    }, 400)    

  });

}

/**
 * ajax処理（JSON形式でPOST）
 * 第１引数：POSTするURL：【文字列】
 * 第２引数：POSTするデータ：【配列】
 * 第３引数：コールバック関数：【オブジェクト型】
 * ※ajax送信成功後に実施したい処理
 * 第４引数：コールバック関数に渡すパラメータ：【配列】
 */
const ajax = function(url, request, callback = null, param = null){
  fetch(url,{
      method : "POST",
      body : JSON.stringify(request),
      headers: {"Content-Type": "application/json; charset=utf-8"},
  })
  .then(function(res){
      return res.json(); 
  })
  .then(function(data){
      // 返されたデータ(json)
      if(callback) callback(data,param);
  })
  .catch(function(err){
      // エラー処理
      console.log(err);
  });
}

/**
 * ajax処理(fetch)
 */
const ajax_fetch = function(url, request, callback = null, param = null){

  fetch(url,{
      method : "POST",
      body : request,
  })
  .then(function(res){
      return res.json(); 
  })
  .then(function(data){
      // 返されたデータ(json)
      if(callback) callback(data,param);
  })
  .catch(function(err){
      // エラー処理
      console.log(err);
  });
}

//ajax(jsonp)の共通処理
var ajax_jsonp = function(url, callback, params = null){

  fetchJsonp(url,{
      timeout: 5000, //タイムアウト時間
  })
  .then(function(res){
      return res.json(); 
  })
  .then(function(data){
      // 返されたデータ(json) 
      if(callback) callback(data,params);
  })
  .catch(function(err){
      // エラー処理
      console.error(err);
  });

}

// 「全て選択」チェックで全てにチェック付く
//---------------------------------------------
function allChecked(e){
  var field = e.target.dataset.field;
  var checks = document.querySelectorAll("input[name^='"+field+"[']");
[].forEach.call(checks, function(elems) {
  if(e.target.checked === true){
    elems.checked = true;
  }else{
    elems.checked = false;
  }
})
}

// 一つでもチェックを外すと「全て選択」のチェック外れる
function disChecked(e){
  if(e.target.type != 'checkbox') return;
  var field = e.target.dataset.field;
  var checks = document.querySelectorAll("input[name^='"+field+"[']");
  var all = document.getElementById(field+'_all');
  if(!all) return;
  var checksCount = 0;
  for (var i=0; i<checks.length; i++){
    if(checks[i].checked == false){
      all.checked = false;
    }else{
      checksCount += 1;
      if(checksCount == checks.length){
        all.checked = true;
      }
    }
  }
}

// 「全て選択」チェックで全てにチェック付く
function ListAllChecked(){
  var all = document.getElementById('all');
  var checks = document.getElementsByName('id[]');
  [].forEach.call(checks, function(elems) {
    if(all.checked === true){
    elems.checked = true;
    }else{
    elems.checked = false;
    }
  })
}
 
// 一つでもチェックを外すと「全て選択」のチェック外れる
function ListDisChecked(){
  var checks = document.getElementsByName('id[]');
  var checksCount = 0;
  for (var i=0; i<checks.length; i++){
    if(checks[i].checked == false){
      document.form2.all.checked = false;
    }else{
      checksCount += 1;
      if(checksCount == checks.length){
        document.form2.all.checked = true;
      }
    }
  }
}

//現在の値を反映
function value_check(data){
  for (let key in data) {
    //一端非表示
    style_change('',data[key]['ids'],data[key]['display']);
    var elems = document.getElementsByName(key);

    //要素がない場合　checkboxの可能性あり
    if(elems.length === 0){
      var elems = document.getElementsByClassName(key);	
    }

    for (var i=0; i < elems.length; i++) {
      //radioやcheckboxの場合は、checkされている場合
      if (elems[i].checked && (elems[i].type == 'radio' || elems[i].type == 'checkbox') ) {
        style_change(elems[i].value,data[key]['ids'],data[key]['display']);//選択した値によって、フィールドの表示非表示を切り替え
        break ;
      }
      //上記以外の場合は、取得した値
      else if(elems[i].value && elems[i].type != 'radio' && elems[i].type != 'checkbox'){
        style_change(elems[i].value,data[key]['ids'],data[key]['display']);//選択した値によって、フィールドの表示非表示を切り替え
        break ;
      }
    }
  }
}
	
//値によって、指定した要素の表示、非表示を切り替え
//---------------------------------------------
function style_change(value,ids,display){
	for(var i=0; i < ids.length; i++){
		var elem = document.getElementById(ids[i]);

		//配列かどうかのチェック
		if(Array.isArray(display)){
			if(display.includes(value)){
				elem.style.display = "flex";
			}else{
				elem.style.display = "none";
			}
		}
		//オブジェクト型
		else if(display !== null && typeof display === 'object'){
			if(display[value] == ids[i]){
				elem.style.display = "flex";
			}else{
				elem.style.display = "none";
			}
		}
		//配列でない場合
		else{
			if(value == display){
				elem.style.display = "flex";
			}else{
				elem.style.display = "none";
			}

		}
	}
}

//timestampから年月日を出力する
//---------------------------------------------
function getYmdHis(timestamp){

  const date = new Date(timestamp);

  const year = date.getFullYear(); // 年を取得する
  const month = date.getMonth() + 1; // 月を取得する（0が1月なので1を足す）
  const day = date.getDate(); // 日を取得する
  const hour = date.getHours(); // 時を取得する
  const minute = date.getMinutes(); // 分を取得する
  const second = date.getSeconds(); // 秒を取得する

  return `${year}/${month}/${day} ${hour}:${minute}:${second}`;

}

//ユーザーエージェントからスマホかPCか判断する
//---------------------------------------------
function getDeviceType(){

  var userAgent = navigator.userAgent;
  if(
    userAgent.indexOf('iPhone') > 0 ||
    userAgent.indexOf('iPod') > 0 ||
    userAgent.indexOf('Android') > 0
  ){
    return 'mobile';
  }else if(
    userAgent.indexOf('iPad') > 0 ||
    userAgent.indexOf('Android') > 0
  ){
    return 'tablet';
  }else{
    return 'desktop';
  }

}