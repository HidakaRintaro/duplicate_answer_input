<?php

session_start();
require('./db_conect_info.php');

//--------------------
// 変数の初期化
//--------------------

$err_path = './error.php';
$login_flg = 0;
$msg = '';


//--------------------
// ログイン処理
//--------------------

if (!empty($_POST['submit']) && $_POST['submit'] == 'submit') {
  
  // 入力値の受取
  $student_num = $_POST['student_num'];
  $birthday = $_POST['birthday'];

  // DBサーバーへ接続
  $db_link = mysqli_connect( DB_HOST, DB_USER, DB_PASS, DB_NAME);

  // 接続エラーの確認
  if( mysqli_connect_errno($db_link) ) {
    header('location:'.$err_path);
    exit;
  }

  // 文字コードを設定する
  mysqli_set_charset( $db_link, 'utf8');

  // 学籍番号と生年月日が一致するデータの取得
  $sql = 
  "SELECT id, student_num 
  FROM users 
  WHERE student_num = ".$student_num." 
  AND birthday = ".$birthday;

  $res = mysqli_query( $db_link, $sql);

  // 取得結果を出力
  if($res) {
    $user_data = $res->fetch_all();
  }
  // DBとの接続解除
  mysqli_close($db_link);

  // 一致する値があるときログイン
  if (!empty($user_data)) {
    $_SESSION['student_num'] = $user_data[0][1];
    $_SESSION['user_id'] = $user_data[0][0];
    header('location: ./problem_select.php');
    exit;
  }

  // 管理者画面への遷移
  if ($student_num == 00000 && $birthday == 11111111) {
    $_SESSION['admin'] = 'yes';
    header('location: ./admin/index.php');
    exit;
  }

  // 間違っていたとき
  $msg = '学籍番号または生年月日が間違っています';

}

//--------------------
// 入力値の引継処理
//--------------------

// 引き継ぎ値の初期表示用の空文字
$input_list = [
  'student_num' => '',
  'birthday' => ''
];

// 入力された値があるとき代入
foreach ($input_list as $key => $val) {
  if (isset($_POST[$key])) $input_list[$key] = $_POST[$key];
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>基本情報模擬試験解答入力</title>

  <link rel="stylesheet" href="./css/reset.css">
  <link rel="stylesheet" href="./css/style.css">

</head>
<body>

  <main>
    <h1>ログイン画面</h1>
    <p>ログインしてください</p>
    <p class="err red"><?php echo $msg; ?></p>
    <form method="post">
      <ul>
        <li>
          <label for="student_num">
            <input type="number" name="student_num" id="student_num" placeholder="学籍番号 (99999)" maxlength="5" min="00000" max="99999" value="<?php echo $input_list['student_num']; ?>">
          </label>
        </li>
        <li>
          <label for="birthday">
            <input type="number" name="birthday" id="birthday" placeholder="生年月日 (19700101)" maxlength="8" min="00000000" max="<?php echo date('Ymd'); ?>" value="<?php echo $input_list['birthday']; ?>">
          </label>
        </li>
      </ul>
      <button class="red" type="submit" name="submit" value="submit">ログイン</button>
    </form>
  </main>

  <hr>
  
  <footer>
    <p><small>&copy; 2020 HidakaRintaro</small></p>
  </footer>

</body>
</html>