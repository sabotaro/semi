<!--  一対比較表を入力  -->
<!--  
  前ページ選んだ SEL1 の値を取得．$_POST という配列に入っているので，$sel1 という変数に代入
	SEL2 も同様
	?php から ? の間  (カギ括弧は省略）は，phpのプログラム
	計算をしたり，計算結果を表示する部分
-->
<?php
	$sel1 = $_POST['SEL1'];
	$sel2 = $_POST['SEL2'];
	$Evi_Name = array("甘さ","食感","費用");
	$Evi_Name[0] = $Evi_Name[0] . "(". $sel1 . ")";
	$Evi_Name[1] = $Evi_Name[1] . "(". $sel2 . ")";

	include("ahp.php");		//	一対比較や総合評価値の計算を行う関数（配布した物）を読み込む
	$n=3;			//	基準間の一対比較の項目数



?>
<html>
	<head>
	<meta http-equiv=Content-Type content="text/html; charset=utf-8">
	<link rel="stylesheet" href="rcss.css" type="text/css" />
	<title>一対比較</title>
	</head>
	<body>
		<form method="POST" action="ahpex3.php">
<?php
			//	関数を呼び出して，一対比較のアンケート用紙を作成
		AHP_enqtable($n, $Evi_Name);
?>

<!-- 			hidden属性で，好みを次のページに送る  
					hidden属性は，画面には表示されないが，入力値として，valueの値を
					次のプログラムに送付する．
-->

	<input type="hidden" name="SEL1" value="<?php print $sel1; ?>" />
	<input type="hidden" name="SEL2" value="<?php print $sel2; ?>" />
	<input type="submit" value="送信">
	</form>

					<!--  以下デバッグ用のルーチン 完成後は削除 -->
<hr />
<h2>デバッグ</h2>
<pre>
<?php
	 print_r(get_defined_vars()); 
?>
</pre>


	</body>
</html>
