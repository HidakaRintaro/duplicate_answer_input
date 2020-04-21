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
// ボタンを押されたとき
//--------------------

if (!empty($_POST)) {
  // 登録ボタンを押したとき
  if ($_POST['register'] == 'register') {
    $_SESSION['register'] = 'yes';
    header('location: ./register.php');
    exit;
  }
  // 削除ボタンを押したとき
  if ($_POST['delete'] == 'delete') {
    $_SESSION['delete'] = 'yes';
    header('location: ./delete.php');
    exit;
  }
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
    <h1>管理画面</h1>
    <form method="post">
      <button type="submit" name="register" value="register">問題登録</button>
      <button type="submit" name="delete" value="delete">問題削除</button>
    </form>
  </main>

</body>
</html>