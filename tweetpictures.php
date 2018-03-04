<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Refresh" content="100">
<!-- ↑ 1度に100件しかツイートを取得できないので100秒ごとにツイートを自動取得させる -->
<link rel="stylesheet" type="text/css" href="stylesheet.css">

<title>bot test</title>
</head>
<body>

<?php
session_start();

set_time_limit(315000); //タイムアウトを防ぐ
require "twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

require_once 'key/consumer.php';




if (isset($_POST['isFirst'])) {
  //index.htmlから来ていたら諸値を初期化する。
    $no = 0;
  $_SESSION['count'] ='999999999999999999';
  $_SESSION['directory'] =$_POST['directory'];
  $_SESSION['query'] =$_POST['query'];
  $_SESSION['fav_num'] =$_POST['fav_num'];
}

//index.htmlから取得される保存先のパス
$directory=$_SESSION['directory'];
//index.htmlから取得されるクエリ
$word=$_SESSION['query'];
//index.htmlから取得される最低fav数
$fav_num=$_SESSION['fav_num'];

$max_id=$_SESSION['count'];//検索を開始するツイートのID。これを利用して取得できるツイート数への制限に対応
if(!file_exists('./'.$directory)){ //指定した名前のディレクトリが無かったら作る
    mkdir('./'.$directory);
  }

//認証情報
$consumerKey = $ck;
$consumerSecret = $cs;
$accessToken = $at;
$accessTokenSecret = $ats;

$query=$word.' -RT filter:twimg -#RTした人全員フォローする';
//クエリを少し編集(ここは各自の好きに編集して下さい)
// -RT → RTを含まない
// filter:twimg →画像を含むツイートのみを取得
// -#RTした人全員フォローする → タグ #RTした人全員フォローする を含むツイートを除外する
//これらは空白区切りで組み合わせる事ができる



$repeat=11;
$connection = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
//接続




$tweets_params = ['q' =>$query,'count' => '100','max_id' =>$max_id]; //クエリ
// q → 検索クエリ
// count → 検索件数。上限は100
// max_id → 検索を開始するツイートのid
$search_result=$connection->get('search/tweets', $tweets_params);
// $tweets_paramsの条件でツイートを検索する
$tweets =$search_result-> statuses;
// $search_result-> statuses → 検索結果のうちツイートの情報
//$tweetsの各要素 →ツイートの本文、画像情報、ファボ数などのツイートの情報を持つ  $tweetsはたくさんのツイートの情報の配列

if(count($tweets)==1){//ツイートが遡れなくなったら
header( "Location:index.html" ) ;//index画面に戻る
}


echo '初期値'.$max_id.'<br>';
//var_dump($tweets);
for($count=0;$count<$repeat;$count++){
  foreach ($tweets as $tweet) {
    echo 'ファボ数:'.$tweet->favorite_count.'  <br>';
    echo 'ユーザー名:'.$tweet->user->name.' '.'ユーザーID:@'.$tweet->user->screen_name.'  <br>';
    echo $tweet->text.'<br>';

        if($tweet->favorite_count> $fav_num){ //ファボ数で保存するかを選択。
        foreach ($tweet->extended_entities->media as $pic){
//$tweet->extended_entities->media → 画像の情報が詰まった配列
            echo '<strong>保存しました<br></strong>';
            $url =$pic->media_url ;  // $pic->media_url → 画像のURL
            $data = file_get_contents($url); //画像データを取得して
            //file_put_contents('./'.$directory.'/'.$pic->id.'-'.$no.'.jpg',$data); //指定したフォルダに保存する。
            file_put_contents('./'.$directory.'/'.$tweet->user->name.$pic->id.'-'.$no.'.jpg',$data); //指定したフォルダに保存する。
            $no+=1; //画像の上書きを防ぐために noを付けて保存する
        }
        $no=1;
      }
      echo '<br>';
      echo $tweet->id;
      echo '<br>';
      echo '<br>';

     $_SESSION['count']=$tweet->id; //一度に取得できるツイート数が限られているため続きのツイートから始めるためにIDを保存
  }
  $max_id=$_SESSION['count'];
  var_dump($max_id);
  echo '<br>before↑<br>after↓<br>';
//  $max_id=$search_result->search_metadata;
  //    var_dump($search_result->search_metadata);
  //新しいパラメーターでツイートを取得
  $tweets_params = ['q' =>$query ,'count' => '100','max_id' =>$max_id];
  $tweets = $connection->get('search/tweets', $tweets_params)->statuses;
 // echo '<br><strong>'.$max_id=$tweet->id_str.'</strong>';
   echo '<br><br><br><br>--------------------------一区切り-----------------------------------<br><br><br><br>';


}


?>


</body>
</html>

