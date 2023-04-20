<!-- 
tabを2に設定すると見やすいです．
	AHPの結果を計算するプログラム
	当面は，変更する必要はない
	下のここから修正というところから修正
-->
<?php
	include("ahp.php");		//	一対比較や総合評価値の計算を行う関数（配布した物）を読み込む
	include("ahp_create_gchart.php");		//	Google Chart Tools でグラフを生成するプログラム

/* ----------------------------------------------------
基本的な設定
------------------------------------------------------ */

		//	全体の設定   基準の数 と 代替案の数を設定
	$n=3;			//	基準間の一対比較の項目数
	$m=4;			//	代替案の数

			//  入力したデータの取り出し
			//	最初の画面で入力した物
	$sel1 = $_POST['SEL1'];
	$sel2 = $_POST['SEL2'];
			//一対比較値をセットして，重要度を求める   基準の数が増えれば．増やしていく
			//PHPは，通常0から使うので，PC1の値は，$Pc_Value[0] に代入  
			//	$n=3 の場合の例
	$Pc_Value[0] = $_POST['PC0'];		//	PC0の値を取り出し，配列に代入
	$Pc_Value[1] = $_POST['PC1'];		//	PC1の値を取り出し，配列に代入
	$Pc_Value[2] = $_POST['PC2'];;	//	PC2の値を取り出し，配列に代入
			//	$n=4 の場合，  $Pc_Value[0] から $Pc_Value[5] までの6個

			//	基準名の設定
	$Evi_name = array("甘さ","食感","費用");
			//	array は，配列  $Evi_name[0] ="甘さ"; $Evi_name[1] ="食感"; $Evi_name[2] ="費用"; となる
			//	甘さと食感は，人により異なるので，その好みを括弧書きで追加．
	$Evi_name[0] = $Evi_name[0] . "(" . $sel1 . ")";
			//	. は文字列を連結
	$Evi_name[1] = $Evi_name[1] . "(" . $sel2 . ")";

			//	代替案名など

	$Alts_Name = array("プリン1","プリン2","プリン3","プリン4");	//代替案の名前
	$Alts_URLS = array("http://www.purin-ya.com/goods/pudding-p.html",
										"http://www.purin-ya.com/goods/pudding-s.html",
										"http://www.purin-ya.com/goods/milk.html",
										"http://www.purin-ya.com/sticking.html"); //代替案のURL

	$Alts_Comment = array (	"185円のデリシャスなプリン",
													"105円のお手軽プリン",
													"ミルクプリン",
													"こだわりのプリン");		//代替案のコメント



		// 重要度の計算、$n(基準数）と 一対比較値 $Pc_Value を使って計算
		//	戻り値$CI,と配列$bb[]に重要度が入る。
	$CI = AHP_Weight_Calc($n,$Pc_Value,$bb); 


		//	各代替案の評価値を配列 $kobetu_h に代入する．
		//			注意： 重みを変える前の値
		//	$kobetu_h[0] は，1番目の基準の各代替案の評価値．表計算で計算したものを使用
		//	$kobetu_h[1] は，2番目の基準の各代替案の評価値．表計算で計算したものを使用
		//	好みでことなる評価値の場合，if を使って，異なる評価値を与える

	if ($sel1 == "甘いもの" ){
			$kobetu_h[0] = array(0.3,0.4,0.25,0.05);	//基準1の各代替案の評価値
	}
	else{
			$kobetu_h[0] = array(0.1,0.1,0.3,0.5);//	基準1の各代替案の評価値
	}

	if ($sel2 == "さくさく" ){
		$kobetu_h[1] = array(0.7,0.15,0.05,0.1);	//基準2の各代替案の評価値
	}
	else{
		$kobetu_h[1] = array(0.1,0.65,0.2,0.05);	//基準2の各代替案の評価値
	}

	$kobetu_h[2] = array(0.1,0.3,0.5,0.1);	//基準3の各代替案の評価値




		// $n（基準の数）と$m(代替案の数) 配列$bb(各基準の重要度)，配列$kobetu_h(各基準の重要度)
		// を与え，配列$GV_value（総合評価値）, 配列$GV_Rank（順位），配列$cc (重要度をかけた後の個別評価値)を得る
	AHP_GV_Calc($n,$m,$bb,$kobetu_h,$GV_value, $GV_Rank, $cc);

		//	HTML 表示用  それぞれの代替案

?>


<!--
		ここから，HTML文を生成
-->
<html>
<head>
	<meta http-equiv=Content-Type content="text/html; charset=utf-8">
	<link rel="stylesheet" href="rcss.css" type="text/css" />
	<title>計算結果</title>
<?php
		create_Gcharts($Evi_name, $Alts_Name,$bb, $GV_value, $cc, $n , $m);			//	ここにGoogleChartToolsでグラフを作成する
?>
</head>
<body>
<h1>計算結果</h1>
<h2>グラフ</h2>
			<!-- 円グラフ    -->
<div id="PieG_div" style="width: 600px; height: 350px;"></div>
			<!-- 積み上げ棒グラフ    -->
<div id="BarG_div" style="width: 800px; height: 500px;"></div>
<h2>データの表</h2>
			<!-- 表    -->
<div id="table_div" style="width: 1000px; height: 150px;"></div>
<h2>代替案へのリンク</h2>
<dl>
<?php
	for ( $i = 0 ; $i < $m ; $i++ ){
?>
		<dt> <?php print $i+1; ?>位 ： 
				 <a href="<?php print $Alts_URLS[$GV_Rank[$i]]; ?>"> <?php print $Alts_Name[$GV_Rank[$i]]; ?></a>
		</dt>
		<dd>
			<?php print $Alts_Comment[$GV_Rank[$i]]; ?>
		</dd>
<?php
	}
?>
</dl>


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
