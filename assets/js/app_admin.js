let spinner;
window.addEventListener('load', async function(){//HTML読み込み完了後に実行

  spinner = document.getElementById('loading');
  if(web3.currentProvider.selectedAddress !== null){

    await connect();
    if(user){
      getBalance();//売上の残高取得
      getUri();//ベースURI取得
      //getNftBalance();//NFTの所有数取得
    }
  }

});


/**
*「method」：インスタンスのメソッドを使う 
* 「get()」：インスタンス内のget()メソッドを指定
* 「call()」：インスタンスのviewを呼び出している
*/
//「method」インスタンスのメソッドを使う

document.addEventListener('click', async function(event){

    if(!event.target) return;

    switch (event.target.id) {
        
      case "connect"://connectをクリックされた場合

        await connect(event);
        break;

      case "get_address"://get_addressをクリックされた場合

        if(!user){
          alert(connect_message);
          break;
        }
        var elem = document.getElementById("token_creater_address");
        elem.value = user;
        break;
       
      case "get_balance"://ウォレット残高
        getBalance(event);
        break;
       
      case "get_uri"://metadata uriを取得
        getUri(event);
        break;
                
      case "get_token_data"://token dataの取得
        getTokenData(event);
        break;
                
      case "set_uri"://metadata uri set
        setUri(event);
        break;
             
      case "update_stock"://トークン在庫の残高
        var token_id = document.getElementById("token_id").value;
        if(token_id === null) break;
        updateStock(token_id);
        break;

      case "withdraw"://引き出し
        withdraw(event);
        break;

      case "burn"://焼却
        burn(event);
        break;

      case "transfer"://焼却
        transfer(event);
        break;              
    }

});

document.addEventListener('change', async function(event){

  //ブロックチェーンネットワークを変更した場合
  if (event.target && event.target.className.indexOf('blockchain_network') !== -1){

    var res = window.confirm(change_network_notice);
    if(res == false) return;

    blockchain_network = event.target.value;
    await ajaxChangeNetwork(blockchain_network);
    alert(network_change_message);
    //location.reload();
  }

  if(!event.target) return;

  switch (event.target.id) {
      
    case "token_id"://connectをクリックされた場合

      await getTokenData(event);
      break;
       
  }

});