<?php

session_start();
unset($_SESSION['problem_num']);
require('./db_conect_info.php');

//--------------------
// ログインしていないとき(直接URLを入力したとき)
//--------------------

if (!isset($_SESSION['student_num'])) {
  header('location: ./index.php');
  exit;
}

//--------------------
// 変数の初期化
//--------------------

$student_num = $_SESSION['student_num'];
$err_path = './error.php';
$problem_flg = 0;

//--------------------
// 保存問題を取得(開催回ごと)
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

// 全開催回の取得
$sql = "SELECT problem_id FROM all_problem_details";
$res = mysqli_query( $db_link, $sql);

// 取得結果を出力
if($res) {
  $all_problem = $res->fetch_all();
}
// DBとの接続解除
mysqli_close($db_link);

// 問題数ゼロ件の時
if (empty($all_problem)) {
  $problem_flg = 1;
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
    <h1>模擬試験一覧</h1>
<?php if ($problem_flg == 0) : ?>
    <ul>
<?php   foreach ($all_problem as $num) : ?>
      <li><a href="./select.php?problem_num=<?php echo $num[0]; ?>">第<?php echo $num[0]; ?>回 模擬試験</a></li>
<?php   endforeach; ?>
    </ul>
<?php elseif ($problem_flg == 1) : ?>
    <p class="red">問題が登録されていません。</p>
<?php endif; ?>
  </main>

  <hr>

  <footer>
    <p><small>&copy; 2020 HidakaRintaro</small></p>
  </footer>

</body>
</html>