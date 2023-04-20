<!-- 
tabを2に設定すると見やすいです．
	AHPの結果を計算するプログラム
	当面は，変更する必要はない
	下のここから修正というところから修正
-->
<?php
	include("ahp.php");		//	一対比較や総合評価値の計算を行う関数（配布した物）を読み込む
	include("fuzzyintegrals.php");
	include("ahp_create_gchart.php");		//	Google Chart Tools でグラフを生成するプログラム

/* ----------------------------------------------------
基本的な設定
------------------------------------------------------ */

		//	全体の設定   基準の数 と 代替案の数を設定
	$n=5;			//	基準間の一対比較の項目数
	$m=6;			//	代替案の数

			//  入力したデータの取り出し
			//	最初の画面で入力した物
	$sel3 = $_POST['SEL3'];
	
	  	//  入力したデータの取り出し
    $xi = $_POST['xiv'];

			//一対比較値をセットして，重要度を求める   基準の数が増えれば．増やしていく
			//PHPは，通常0から使うので，PC1の値は，$Pc_Value[0] に代入  
			//	$n=5 の場合の例 10回一対比較をする
	$Pc_Value[0] = $_POST['PC0'];		//	PC0の値を取り出し，配列に代入
	$Pc_Value[1] = $_POST['PC1'];		//	PC1の値を取り出し，配列に代入
	$Pc_Value[2] = $_POST['PC2'];	//	PC2の値を取り出し，配列に代入
	$Pc_Value[3] = $_POST['PC3'];		//	PC3の値を取り出し，配列に代入
	$Pc_Value[4] = $_POST['PC4'];		//	PC4の値を取り出し，配列に代入
	$Pc_Value[5] = $_POST['PC5'];	//	PC5の値を取り出し，配列に代入
	$Pc_Value[6] = $_POST['PC6'];		//	PC6の値を取り出し，配列に代入
	$Pc_Value[7] = $_POST['PC7'];		//	PC7の値を取り出し，配列に代入
	$Pc_Value[8] = $_POST['PC8'];	//	PC8の値を取り出し，配列に代入
	$Pc_Value[9] = $_POST['PC9'];	//	PC9の値を取り出し，配列に代入
			//	$n=4 の場合，  $Pc_Value[0] から $Pc_Value[5] までの6個

			//	基準名の設定
	$Evi_name = array("楽しさ","激しさ","運動量","気軽さ","費用");
			//	array は，配列  $Evi_name[0] ="甘さ"; $Evi_name[1] ="食感"; $Evi_name[2] ="費用"; となる
			//	甘さと食感は，人により異なるので，その好みを括弧書きで追加．
	$Evi_name[2] = $Evi_name[2] . "(" . $sel3 . ")";
			//	. は文字列を連結
			//	代替案名など

	$Alts_Name = array("アルティメット","インディアカ","オージーフットボール","カバディ","セパタクロー",
"モルック");	//代替案の名前
	$Alts_URLS = array("https://www.jfda.or.jp/introduction/ultimate/",
										"https://japan-indiaca.com/aboutindiaca/",
										"https://www.ssf.or.jp/ssf_eyes/dictionary/australiannfootball.html",
										"https://www.jaka.jp/","http://jstaf.jp/sepa/sepa.html","https://www.molkky-japan.com/howto"); //代替案のURL

	$Alts_Comment = array (	"ルール","ルール","ルール","ルール","ルール","ルール"
													);		//代替案のコメント


		// 重要度の計算、$n(基準数）と 一対比較値 $Pc_Value を使って計算
		//	戻り値$CI,と配列$bb[]に重要度が入る。
	$CI = AHP_Weight_Calc($n,$Pc_Value,$bb); 


		//	各代替案の評価値を配列 $kobetu_h に代入する．
		//			注意： 重みを変える前の値
		//	$kobetu_h[0] は，1番目の基準の各代替案の評価値．表計算で計算したものを使用
		//	$kobetu_h[1] は，2番目の基準の各代替案の評価値．表計算で計算したものを使用
		//	好みでことなる評価値の場合，if を使って，異なる評価値を与える

	$kobetu_h[0] = array(0.2123, 0.0401, 0.2343, 0.1387, 0.1783, 0.1964);
	
	$kobetu_h[1] = array(0.1113, 0.0733, 0.5143,0.1371,0.1425, 0.0215);
	
	if ($sel3 == "運動量" ){
			$kobetu_h[2] = array(0.1620, 0.0466, 0.4103, 0.2577, 0.0940, 0.0294 );	//基準1の各代替案の評価値
	}
	else{
			$kobetu_h[2] = array(0.1270, 0.3092, 0.0311, 0.0481, 0.0694, 0.4151 );//	基準1の各代替案の評価値
	}
    $kobetu_h[3] = array(0.2016, 0.1104, 0.0551, 0.2173, 0.2803, 0.1354);
	$kobetu_h[4] = array(0.2413, 0.0539, 0.0702, 0.2901, 0.1518, 0.1928);	//基準3の各代替案の評価値




		// $n（基準の数）と$m(代替案の数) 配列$bb(各基準の重要度)，配列$kobetu_h(各基準の重要度)
		// を与え，配列$GV_value（総合評価値）, 配列$GV_Rank（順位），配列$cc (重要度をかけた後の個別評価値)を得る
	fuzzym_inpnum($n , $xi , $g , $bb );
fmci_GV_Calc($n,$m,$bb, $g,$kobetu_h, $GV_value,  $GV_Rank, $cc);
 // AHP_GV_Calc($n,$m,$bb,$g, $kobetu_h,$GV_value, $GV_Rank, $cc);


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
		create_Gcharts($Evi_name, $Alts_Name,$bb, $GV_value, $cc, $n , $m,$kobetu_h ,1);			//	ここにGoogleChartToolsでグラフを作成する
?>
</head>
<body>
<h1>計算結果</h1>
<h2>グラフ</h2>
			<!-- 円グラフ    -->
<div id="PieG_div" style="width: 600px; height: 350px;"></div>
			<!-- 積み上げ棒グラフ    -->
<div id="BarG_div" style="width: 800px; height: 500px;"></div>
<!--  折れ線グラフ -->
<div id="lineC_div" style="width: 1000px; height: 500px"></div>
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
<h1>&xi; の変更</h1>
   	 <form method="POST" action="fmciex3.php">
&xi; : <input type="text" name="xiv" value="<?php print $xi; ?>"> (0以上1未満) <br />
0に近い値：悪い点がない代替案に高い総合評価値 <br />
1に近い値：良い点がある代替案に高い総合評価値 <br />

<?php
   	 //    xiv を除き　送られてきた値を　次のプルログラムに送る．
    foreach ($_POST as $vname => $vvale){
   	 if ( $vname != 'xiv') {
?>
   		 <input type="hidden" name="<?php print $vname;?>" value="<?php print $vvale;?>" />
<?php
   	 }
    }
?>
    <input type="submit" value="送信">
    </form>


					<!--  以下デバッグ用のルーチン 完成後は削除 
<hr />
<h2>デバッグ</h2>
<pre>
<?php
	 print_r(get_defined_vars()); 
?>
</pre>
-->
	</body>
</html>
