<?php

session_start();
require('./db_conect_info.php');

//--------------------
// ログインしていないとき(直接URLを入力したとき)
//--------------------

if (!isset($_SESSION['student_num'])) {
  header('location: ./index.php');
  exit;
}

//--------------------
// 開催回($_GET)の値が受け取れなっかた時
//--------------------

if (!isset($_GET['problem_num'])) {
  header('location: ./problem_select.php');
  exit;
}

//--------------------
// 変数の初期化
//--------------------

$student_num = $_SESSION['student_num'];
$problem_num = $_GET['problem_num'];
$err_path = './error.php';
$before_flg = 0;
$after_flg = 0;

//--------------------
// 午前、午後問題の取得
//--------------------

// DBサーバーへ接続
$db_link = mysqli_connect( DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 接続エラーの確認
if( mysqli_connect_errno($db_link) ) {
  header('location:'.$err_path);
  exit;
}

// 文字コードを設定する
mysqli_set_charset( $db_link, 'utf8');

// 午前、午後の問題番号の取得
$sql = "SELECT before_problem_id, after_problem_id 
FROM all_problem_details 
WHERE problem_id = ".$problem_num;

$res = mysqli_query( $db_link, $sql);

// 取得結果を出力
if($res) {
  $problem_presence = $res->fetch_array();
}
// DBとの接続解除
mysqli_close($db_link);

//--------------------
// 午前、午後問題の有無の確認
//--------------------

// 午前問題の有無
if (!empty($problem_presence[0])) {
  $before_flg = 1;
}
// 午後問題の有無
if (!empty($problem_presence[1])) {
  $after_flg = 1;
}


?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>基本情報模擬試験解答入力</title>

  <link rel="stylesheet" href="./css/reset.css">
  <link rel="stylesheet" href="./css/select.css">

</head>
<body>

  <main>
    <h1 class="none">解答問題選択</h1>
    <p class="red">登録済みの場合は再度入力すると上書き登録されます。</p>
    <p class="heading">選択してください</p>
    <ul>
<?php if ($before_flg == 1) : ?>
      <li><a href=" ./input_before.php?before_num=<?php echo $problem_presence[0]; ?>">午前問題</a></li>
<?php endif; ?>
<?php if ($after_flg == 1) : ?>
      <li><a href="./input_after.php?after_num=<?php echo $problem_presence[1]; ?>">午後問題</a></li>
<?php endif; ?>
    </ul>
  </main>
  
  <hr>

  <footer>
    <p><small>&copy; 2020 HidakaRintaro</small></p>
  </footer>

</body>
</html>