<?php

session_start();

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

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>基本情報模擬試験解答入力</title>

  <link rel="stylesheet" href="./css/reset.css">
  <link rel="stylesheet" href="./css/entry.css">

</head>
<body>

  <main>
    <h1 class="none">登録完了画面</h1>
    <p class="heading">登録完了致しました!</p>
    <p class="link_btn"><a href="./problem_select.php">問題一覧に戻る</a></p>
  </main>

  <hr>

  <footer>
    <p><small>&copy; 2020 HIdakaRintaro</small></p>
  </footer>

</body>
</html>