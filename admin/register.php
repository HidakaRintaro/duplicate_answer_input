<?php

session_start();

//--------------------
// ログインしていないとき(直接URLを入力したとき)
//--------------------

if (!isset($_SESSION['admin'])) {
  header('location: ../index.php');
  exit;
}

//--------------------
// 登録、削除の選択をしていないとき
//--------------------

if (!isset($_SESSION['register'])) {
  header('location: ./index.php');
  exit;
}




?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>管理画面</title>

  <link rel="stylesheet" href="./css/.css">

</head>
<body>
  
  <main>
    <h1>登録画面</h1>

  </main>

</body>
</html>