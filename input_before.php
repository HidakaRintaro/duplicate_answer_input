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
// 問題番号の選択値が受け取れなっかた時
//--------------------

if (!isset($_GET['before_num'])) {
  header('location: ./select.php');
  exit;
}

//--------------------
// 変数の初期化
//--------------------

$student_num = $_SESSION['student_num'];
$user_id = $_SESSION['user_id'];
$before_num = $_GET['before_num'];
$err_path = './error.php';
$choice_signs = ['コ', 'ア', 'イ', 'ウ', 'エ', 'オ', 'カ', 'キ', 'ク', 'ケ'];
$date = null;
$choice_items = '';
$boolean = null;
$choice_list = [];
$success_cnt = 0;

//--------------------
// 表示問題のデータの取得
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

// 入力済解答取得
$sql = "SELECT answer_num, choices_num 
FROM before_problem_data 
WHERE before_problem_id = ".$before_num." 
ORDER BY answer_num ASC";

$res = mysqli_query( $db_link, $sql);

// 取得結果を出力
if($res) {
  $problem_list = $res->fetch_all();
}
// DBとの接続解除
mysqli_close($db_link);

//--------------------
// 全問題数の取得
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

// 入力済解答取得
$sql = "SELECT answer_total 
FROM before_problem_details 
WHERE before_problem_id = ".$before_num;

$res = mysqli_query( $db_link, $sql);

// 取得結果を出力
if($res) {
  $answer_total = $res->fetch_array();
}
// DBとの接続解除
mysqli_close($db_link);

//--------------------
// 入力済の値の取出
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

// 入力済解答取得
$sql = "SELECT input_before_id, answer_num, input_answer 
FROM input_before_data 
WHERE input_before_id = 
(
  SELECT input_before_id 
  FROM input_before_details 
  WHERE before_problem_id = ".$before_num." 
  AND users_id = ".$user_id."
)";

$res = mysqli_query( $db_link, $sql);

// 取得結果を出力
if($res) {
  $answer_list = $res->fetch_all();
}
// DBとの接続解除
mysqli_close($db_link);

//--------------------
// 登録処理
//--------------------

if (!empty($_POST['register_btn']) && $_POST['register_btn'] == 'register_btn') { // 登録ボタンを押した時

  // 日時の取得
  $date = date('Y-m-d H:i:s');

  // 入力値の受取
  $choice_items = $_POST['choice_item'];
  for ($i = 0; $i < $answer_total[0]; $i++) {
    if (!isset($choice_items[$i + 1])) { // 解答選択されたかの判断
      // 未選択時に空文字の代入
      $choice_list[$i + 1] = null;
      continue;
    }
    $choice_list[$i + 1] = intval($choice_items[$i + 1]);
  }

  //
  // 入力値の詳細の登録処理
  //------------------
  
  // DBサーバーへ接続
  $db_link = mysqli_connect( DB_HOST, DB_USER, DB_PASS, DB_NAME);

  // 接続エラーの確認
  if( mysqli_connect_errno($db_link) ) {
    header('location:'.$err_path);
    exit;
  }

  // 文字コードを設定する
  mysqli_set_charset( $db_link, 'utf8');

  if (empty($answer_list)) { // 新規登録の時
    
    $stmt = mysqli_prepare(
      $db_link, 
      "INSERT INTO input_before_details (
        users_id, before_problem_id, created_date
      ) VALUES (
        ?, ?, ?
      )"
    );

    mysqli_stmt_bind_param( $stmt, 'iis', $user_id, $before_num, $date);

  } else { // すでに入力してる時

    $stmt = mysqli_prepare(
      $db_link, 
      "UPDATE input_before_details SET 
        created_date = ? 
      WHERE input_before_id = ?"
    );

    mysqli_stmt_bind_param( $stmt, 'si', $date, $answer_list[0][0]);
    
  }
  
  $res = mysqli_stmt_execute($stmt);

  // stmtクラスを閉じる
  mysqli_stmt_close($stmt);

  // DBとの接続解除
  mysqli_close($db_link);

  if($res) { // 詳細の登録に成功した時

    //
    // 解答入力テーブル(input_before_details)のID(input_before_id)の取得
    //----------------

    // DBサーバーへ接続
    $db_link = mysqli_connect( DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // 接続エラーの確認
    if( mysqli_connect_errno($db_link) ) {
      header('location:'.$err_path);
      exit;
    }

    // 文字コードを設定する
    mysqli_set_charset( $db_link, 'utf8');

    // IDの取得
    $sql = "SELECT input_before_id 
    FROM input_before_details 
    WHERE users_id = ".$user_id." 
    AND before_problem_id = ".$before_num;

    $res = mysqli_query( $db_link, $sql);

    // 取得結果を出力
    if($res) {
      $input_id = $res->fetch_array();
    }
    // DBとの接続解除
    mysqli_close($db_link);

    //
    // 入力値の登録
    //----------------

    // DBサーバーへ接続
    $db_link = mysqli_connect( DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // 接続エラーの確認
    if( mysqli_connect_errno($db_link) ) {
      header('location:'.$err_path);
      exit;
    }

    // 文字コードを設定する
    mysqli_set_charset( $db_link, 'utf8');

    if (empty($answer_list)) { // 新規登録の時

      foreach ($choice_list as $key =>$val) {

        $stmt = mysqli_prepare(
          $db_link, 
          "INSERT INTO input_before_data (
            input_before_id, answer_num, input_answer
          ) VALUES (
            ?, ?, ?
          )"
        );
      
        mysqli_stmt_bind_param( $stmt, 'iii', $input_id[0], $key, $val);

        $res = mysqli_stmt_execute($stmt);

        // 書込回数のカウント
        if ($res) {
          $success_cnt++;
        }

      }

    } else { // すでに入力してる時

      foreach ($choice_list as $key =>$val) {

        $stmt = mysqli_prepare(
          $db_link, 
          "UPDATE input_before_data SET 
            input_answer = ? 
          WHERE input_before_id = ? 
          AND answer_num = ?"
        );
  
        mysqli_stmt_bind_param( $stmt, 'iii', $val, $input_id[0], $key);
  
        $res = mysqli_stmt_execute($stmt);
  
        // 書込回数のカウント
        if ($res) {
          $success_cnt++;
        }

      }
      
    }

    // stmtクラスを閉じる
    mysqli_stmt_close($stmt);
    
    // DBとの接続解除
    mysqli_close($db_link);
    
    if($success_cnt == $answer_total[0]) { // 入力値の登録に成功した時
      // 登録終了
      header('location: ./entry.php');
      exit;
    } else { // 入力値の登録に失敗した時
      // TODO いくらか登録されているデータを削除(input_before_dataのDBの中のinput_before_idが一致するデータの削除)　可能であれば削除フラグを立てる
    }

  } else { // 詳細の登録に失敗した時
    // TODO 上記のTODOと同様なことをする
  }

}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>基本情報模擬試験解答入力</title>

  <link rel="stylesheet" href="./css/reset.css">
  <link rel="stylesheet" href="./css/before.css">

</head>
<body>

  <main>
    <h1>午前問題模擬試験</h1>
    <article id="problem_list">
      <h2 class="none">午前問題解答一覧</h2>
      <form method="post">
        <ul>
<?php for ($i = 0; $i < $answer_total[0]; $i++) : ?>
          <li class="problem_item">
            <p>問<?php echo $i + 1; ?></p>
<?php   for ($j = 0; $j < $problem_list[$i][1]; $j++) : ?>
            <div class="choice_list">
              <input type="radio" name="choice_item[<?php echo ($i + 1); ?>]" id="choice_item<?php echo ($i + 1).'-'.($j == 9 ? 0 : $j + 1); ?>" value="<?php echo $j == 9 ? 0 : $j + 1; ?>" <?php if ( !empty($answer_list) && $answer_list[$i][2] == ($j == 9 ? 0 : $j + 1 ) ) echo 'checked'; ?>>
              <label for="choice_item<?php echo ($i + 1).'-'.($j == 9 ? 0 : $j + 1); ?>"><?php echo $j == 9 ? $choice_signs[0] : $choice_signs[$j + 1]; ?></label>
            </div><!-- /.choice_list -->
<?php   endfor; ?>
          </li><!-- /.problem_item -->
<?php endfor; ?>
        </ul>
        <button class="red" type="submit" name="register_btn" value="register_btn">登録</button>
      </form>
    </article><!-- /#problem_list -->
  </main>

  <hr>

  <footer>
    <p><small>&copy; 2020 HidakaRintaro</small></p>
  </footer>

</body>
</html>