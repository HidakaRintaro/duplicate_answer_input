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

if (!isset($_GET['after_num'])) {
  header('location: ./select.php');
  exit;
}

//--------------------
// 変数の初期化
//--------------------

$student_num = $_SESSION['student_num'];
$user_id = $_SESSION['user_id'];
$after_num = $_GET['after_num'];
$err_path = './error.php';
$choice_signs = ['コ', 'ア', 'イ', 'ウ', 'エ', 'オ', 'カ', 'キ', 'ク', 'ケ'];
$problem_signs = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'];
$date = null;
$boolean = null;
$choice_list = [];
$suffix = '';
$success_cnt = 0;
$choice_cnt = 0;
$total_cnt = 0;

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
$sql = "SELECT answer_num, problem_sign, choices_num 
FROM after_problem_data 
WHERE after_problem_id = ".$after_num." 
ORDER BY answer_num, problem_sign";

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
$sql = "SELECT problem_num, answer_total 
FROM after_problem_details 
WHERE after_problem_id = ".$after_num;

$res = mysqli_query( $db_link, $sql);

// 取得結果を出力
if($res) {
  $problem_details = $res->fetch_array();
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
$sql = "SELECT input_after_id, answer_num, problem_sign, input_answer 
FROM input_after_data 
WHERE input_after_id = 
(
  SELECT input_after_id 
  FROM input_after_details 
  WHERE after_problem_id = ".$after_num." 
  AND users_id = ".$user_id."
)";

$res = mysqli_query( $db_link, $sql);

// 取得結果を出力
if($res) {
  $answer_list = $res->fetch_all();
  foreach ($answer_list as $key => $row) {
    if ($row[3] !== null) {
      $answer_list[$key][3] = intval($row[3]);
    }
  }
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
  foreach ($_POST['choice_item'] as $key => $val) {
    // $boolean = isset($val);
    if ($val === "") { // 解答選択されたかの判断
      // 未選択時に空文字の代入
      $choice_list[$key] = null;
      continue;
    }
    $choice_list[$key] = intval($val);
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
      "INSERT INTO input_after_details (
        users_id, after_problem_id, created_date
      ) VALUES (
        ?, ?, ?
      )"
    );

    mysqli_stmt_bind_param( $stmt, 'iis', $user_id, $after_num, $date);

  } else { // すでに入力してる時

    $stmt = mysqli_prepare(
      $db_link, 
      "UPDATE input_after_details SET 
        created_date = ? 
      WHERE input_after_id = ?"
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
    // 解答入力テーブル(input_after_details)のID(input_after_id)の取得
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
    $sql = "SELECT input_after_id 
    FROM input_after_details 
    WHERE users_id = ".$user_id." 
    AND after_problem_id = ".$after_num;

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
        // 添字から問題の番号、記号を配列で取得
        $suffix = explode('_', $key);

        $stmt = mysqli_prepare(
          $db_link, 
          "INSERT INTO input_after_data (
            input_after_id, answer_num, problem_sign, input_answer
          ) VALUES (
            ?, ?, ?, ?
          )"
        );
      
        mysqli_stmt_bind_param( $stmt, 'iisi', $input_id[0], intval($suffix[0]), $suffix[1], $val);

        $res = mysqli_stmt_execute($stmt);

        // 書込回数のカウント
        if ($res) {
          $success_cnt++;
        }

      }

    } else { // すでに入力してる時

      foreach ($choice_list as $key =>$val) {
        // 添字から問題の番号、記号を配列で取得
        $suffix = explode('_', $key);

        $stmt = mysqli_prepare(
          $db_link, 
          "UPDATE input_after_data SET 
            input_answer = ? 
          WHERE input_after_id = ? 
          AND answer_num = ? 
          AND problem_sign = ?"
        );
  
        mysqli_stmt_bind_param( $stmt, 'iiis', $val, $input_id[0], intval($suffix[0]), $suffix[1]);
  
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
    
    if($success_cnt == $problem_details[1]) { // 入力値の登録に成功した時
      // 登録終了
      header('location: ./entry.php');
      exit;
    } else { // 入力値の登録に失敗した時
      // TODO いくらか登録されているデータを削除(input_after_dataのDBの中のinput_after_idが一致するデータの削除)　可能であれば削除フラグを立てる
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
  <link rel="stylesheet" href="./css/after.css">

</head>
<body>

  <main>
    <h1>午後問題模擬試験</h1>
    <article id="problem_list">
      <h2 class="none">午後問題解答一覧</h2>
      <form method="post">
        <ul>
<?php for ($i = 0; $i < $problem_details[0]; $i++) : ?>
          <li class="problem_item">
            <p>問<?php echo $i + 1; ?></p>
<?php   foreach ($problem_list as $row) if ($row[0] == ($i + 1)) $choice_cnt++; ?>
<?php   for ($j = $total_cnt; $j < $choice_cnt; $j++) : ?>
            <div class="choice_list">
              <label for="choice_item_<?php echo ($i + 1).'_'.$problem_list[$j][1]; ?>"><?php echo $problem_list[$j][1]; ?></label>
              <select name="choice_item[<?php echo $i + 1; ?>_<?php echo $problem_list[$j][1]; ?>]" id="choice_item_<?php echo ($i + 1).'_'.$problem_list[$j][1]; ?>">
                <option value="" hidden></option>
<?php     for ($k = 0; $k < $problem_list[$j][2]; $k++) : ?>
                <option value="<?php echo ($k == 9) ? 0 : $k + 1; ?>" <?php if ( !empty($answer_list) && $answer_list[$j][3] === ($k == 9 ? 0 : $k + 1 ) ) echo 'selected'; ?>><?php echo ($k == 9) ? $choice_signs[0] : $choice_signs[$k + 1]; ?></option> 
<?php     endfor; ?>
              </select>
              <div class="select_btn"></div>
            </div><!-- /.choice_list -->
<?php   endfor; ?>
<?php   $total_cnt = $choice_cnt; ?>
          </li>
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