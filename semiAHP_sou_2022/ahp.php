<!-- 
tabを2に設定すると見やすいです．
AHPの重要度を計算する関数
総合評価値，順位を計算する関数
-->
<?php

/* ---------------------------------------------------------
番号は，すべて0から始める．

AHP_WC
	一対比較行列から，重要度，整合度を求める．
入力：
	$n			:	一対比較の項目数
	$a[][]	:	一対比較行列，$n*$n，すべての要素に値が入力されていることが必要 （対角要素1，対象の要素は逆数)
	$b[]		: 重要度が計算されセットされる．(合計が1に正規化されている)．
						呼び出すときは，&$b のようにする．
戻り値：
	整合度(C.I.)
---------------------------------------------------------- */
function AHP_WC($n, $a, &$bb) {

    $EPS = 0.00001;		//	最大の誤差
    $MAX_COUNT = 10 * $n;	//最大の計算回数
    
         // 初期値は 1/n
    for($i = 0; $i < $n; $i++){
        $bb[$i] = 1.0 / $n;
    }
  

    //計算
 
    $i = 0;
    While (1){
      $i = $i + 1;
      for ( $j = 0; $j < $n ; $j++ ){
        $b[$j] = $bb[$j];
      }

          //行列のかけ算
      for ( $j = 0; $j< $n ; $j++ ){
        $bb[$j] = 0.0;
        for ($k = 0 ; $k < $n ; $k++ ){
          $bb[$j] = $bb[$j] + $a[$j][$k] * $b[$k];
        }
      }
      $lambda = $bb[0] / $b[0];
       
           //合計が1になるように計算
      $tt = 0;
      for($j=0; $j < $n ; $j++){
        $tt = $tt + $bb[$j];
      }
      for($j=0; $j < $n ; $j++){
        $bb[$j] = $bb[$j] / $tt;
      }
           // すべての差がEPS以下もしくはループ回数がMAX_COUNT以上になったら終了

      $ff = 0;
      for($j=0; $j < $n ; $j++){
        if ((Abs($bb[$j] - $b[$j]) / $b[$j]) > $EPS){
          $ff = 1;
				}
      }
      if (($ff == 0) || ($i > $MAX_COUNT)) {
				break;
			}
    }

    return(($lambda - $n) / ($n - 1));
}

/* ---------------------------------------------------------
AHP_Weight_Calc
	一対比較行列から，重要度，整合度を求める．
	$Pc_Value[]を，一対比較行列に変換して，関数AHP_WCを呼び出す．
入力：
	$n:	一対比較の項目数
	$Pc_Value[]	:	一対比較値  一対比較の順番にしたがって，順番に値が入っている．
								1/a  は，-a で表現する
						$Pc_Value[0]    <- 1番目の項目 ： 2番目の項目 
                    :           :                :
						$Pc_Value[$n-1] <- 1番目の項目 ： n番目の項目 
                    :           :                :
						$Pc_Value[($n*$n-1)2] <- n-1番目の項目 ： n番目の項目 
	$b[]		: 重要度が計算されセットされる(合計が1に正規化されている)．
						呼び出すときは，&$b のようにする．
戻り値：
	整合度(C.I.)
---------------------------------------------------------- */
function AHP_Weight_Calc($n,$Pc_Value,&$bb){

	$point = 0;
	for ($i=0; $i < $n ; $i++){
		for ($j=0; $j < $n ; $j++){
			if ( $j > $i ){
				$t = floatval($Pc_Value[$point]);
				if ($t < 0 ){
					$a[$i][$j] = 1 / (-1 * $t);
				} 
				else {
					$a[$i][$j] = $t;
				}
				$point++;
			}
			if ($j == $i ){
				$a[$i][$j] = 1;
			}
			if ($j < $i ){
				$a[$i][$j] = 1 / $a[$j][$i];
			}
		}
	}

	return( AHP_WC($n, $a, $bb));
}

/* ---------------------------------------------------------
AHP_GV_Calc
	総合評価値を計算する関数

入力：
	$n:	評価項目の数
	$m:	代替案の数
	$kobetu_h[][]:	個別評価値  
									$kobetu_h[$i][$j] : $i-1 番目の基準についての$j-1番目の代替案の個別評価値
									例：$kobetu_h[1][0] : 2番目の基準の1番目の代替案の個別評価値
  $GV_value[] : 総合評価値
	$GV_Rank[]	:  $GV_Rank[$i] :$i-1 位の代替案の番号
									$GV_Rank[0] -> 2：1位の代替案の番号は，2で（3番目の）代替案
	$cc[][] : 重要度をかけた後の個別評価値
戻り値：
	なし
---------------------------------------------------------- */

			//	総合評価値を計算

function AHP_GV_Calc($n,$m,$bb,$kobetu_h,&$GV_value, &$GV_Rank, &$cc){

	for ($i = 0 ; $i < $m ; $i++ ){		//	代替案分繰り返す
		$GV_value[$i] = 0;
		for($j = 0 ; $j < $n ; $j++ ){
			$cc[$j][$i] = $bb[$j] * $kobetu_h[$j][$i];
			$GV_value[$i] = $GV_value[$i] + $cc[$j][$i];
		}
	}

		//	ソートして順位を求める
	for ($i = 0 ; $i < $m ; $i++ ){		//	代替案分繰り返す
		$GV_Rank[$i] = $i;
		$rr_GV[$i] = $GV_value[$i];
	}
	$ff=1;
	while ($ff==1){
		$ff=0;
		for($i = 0 ; $i < ($m-1) ; $i++ ){
			if ( $rr_GV[$i] < $rr_GV[$i+1] ){
				$ff=1;
				$t =$rr_GV[$i];
				$rr_GV[$i] = $rr_GV[$i+1];
				$rr_GV[$i+1] = $t;
				$t =$GV_Rank[$i];
				$GV_Rank[$i] = $GV_Rank[$i+1];
				$GV_Rank[$i+1] = $t;
			}
		}
	}
}
/* ---------------------------------------------------- 
AHPのアンケート用紙を作成する．
$nn	:	一対比較の項目数 ($nn * ($nn -1) /2) 回一対比較が行われる
$Evi_Name[]	: 項目名 配列．1から$nn

結果を入れる名前，PC1 ～ PC($nn * ($nn -1) /2)

----------------------------------- */

function AHP_enqtable($nn, $Evi_Name){

$mm = ($nn * ($nn -1) /2); //	一対比較の回数

?>
		<table border="1">
				<!--  <tr>...</tr>が1行  ここは,表頭 -->
			<tr>
				<td>　</td>
				<td align="justify" valign="justify">左<br />の<br />項<br />目<br />が<br />圧<br />倒<br />的<br />に<br />重<br />要</td>
				<td align="justify" valign="justify">　</td>
				<td align="justify" valign="justify">左<br />の<br />項<br />目<br />が<br />う<br />ん<br />と<br />重<br />要<br /></td>
				<td align="justify" valign="justify">　</td>
				<td align="justify" valign="justify">左<br />の<br />項<br />目<br />が<br />か<br />な<br />り<br />重<br />要<br /></td>
				<td align="justify" valign="justify">　</td>

				<td align="justify" valign="justify">左<br />の<br />項<br />目<br />が<br />少<br />し<br />重<br />要<br /></td>
				<td align="justify" valign="justify">　</td>

				<td align="justify" valign="justify">左<br />右<br />同<br />じ<br />く<br />ら<br />い<br />重<br />要<br /></td>
				<td align="justify" valign="justify">　</td>
				<td align="justify" valign="justify">右<br />の<br />項<br />目<br />が<br />少<br />し<br />重<br />要<br /></td>
				<td align="justify" valign="justify">　</td>
				<td align="justify" valign="justify">右<br />の<br />項<br />目<br />が<br />か<br />な<br />り<br />重<br />要<br /></td>
				<td align="justify" valign="justify">　</td>
				<td align="justify" valign="justify">右<br />の<br />項<br />目<br />が<br />う<br />ん<br />と<br />重<br />要<br /></td>
				<td align="justify" valign="justify">　</td>
				<td align="justify" valign="justify">右<br />の<br />項<br />目<br />が<br />圧<br />倒<br />的<br />に<br />重<br />要</td>
				<td>　</td>
			</tr>

<?php

	$il = 0 ;	//	左側一対比較場所
	$ir = 1 ;	//	右側一対比較場所

	for ( $ii = 0 ; $ii < $mm ; $ii++ ){

		$pcn = "PC" . strval($ii);

?>

		

				<!--  <tr>...</tr>が1行  ここは,1つめの一対比較 
							1個目の一対比較なので，結果は「PC1」という変数に入れる
							valueは，そこを選択したときの一対比較値．
							1/3は，-3という値にしている．次のプログラムで，-3は1/3に変換する．
							左右の基準名を表示するエリアは，決めてあるときはそのまま，
							選択の場合は，phpのprint文で表示
				-->
			<tr>
						<!-- print $sel1; は，$sel1の内容を表示，ここでは，「甘いもの」か「からいもの」の文字列  -->
				<td><?php print $Evi_Name[$il]; ?></td>
<?php
				for ($ik= 9; $ik >= 1; $ik-- ){
?>
					<td><input type=radio name="<?php print $pcn; ?>" value="<?php print $ik; ?>" ></td>
<?php

				}
				for ($ik= -2; $ik >= -9; $ik-- ){
?>
					<td><input type=radio name="<?php print $pcn; ?>" value="<?php print $ik; ?>" ></td>
<?php

				}
?>
				<td><?php print $Evi_Name[$ir]; ?></td>
			</tr>
<?php
			$ir++;
			if ($ir >= $nn ){
				$il++;
				$ir = $il+1;
			}
	}
?>
		<table border="1">
<?php

}
