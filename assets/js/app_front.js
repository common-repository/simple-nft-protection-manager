let spinner;
window.addEventListener('load', async function(){//HTML読み込み完了後に実行

  spinner = document.getElementById('loading');
  if(web3.currentProvider.selectedAddress !== null){

    if (typeof window.ethereum === 'undefined'){
      return;
    } 
    frontConnect();
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

    if (typeof window.ethereum === 'undefined'){
      if( getDeviceType() == 'mobile' || getDeviceType() == 'tablet'){
        alert(not_installed_mobile_message);
      }else{
        alert(not_installed_message);
      }
      return;
    } 
  

    switch (event.target.id) {
        
      case "connect"://connectをクリックされた場合
        //loading animation　表示
        if(spinner){spinner.classList.add('active');}
        await frontConnect();
        location.reload();
        break;
        
      case "viewOwn"://ownをクリックされた場合
        viewNftList();
        break;

    }

    /* NFT TOKENの購入 */
    if(event.target.className.indexOf('buy_token') !== -1){
      await buyToken(event);
    }

});
