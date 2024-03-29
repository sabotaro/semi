<?php
function create_Gcharts($Evi_name, $Alts_Name, $bb,$GV_value, $cc, $n , $m,$kh=0,$fzlc=0){
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
  google.setOnLoadCallback(drawChart);

  google.load('visualization', '1', {packages:['table']});
  google.setOnLoadCallback(drawTable);

function drawChart() {
			//	円グラフ

	var options_PieG = { 
    title: '重要度'

//				色変更するとき
//		colors: ['#FF0000', '#00FF00', '#AFAF00']
	};


 var data_PieG = google.visualization.arrayToDataTable([
        [       '',  ''],
<?php
			for ($i=0; $i < $n ; $i++ ){
?>
        [   '<?php print $Evi_name[$i]; ?>',  <?php printf ("%6.4f", $bb[$i]); ?> ]
<?php
				if ( $i < ($n-1) ) print ",";
?>
<?php
			}
?>
        
    ]);

	var chart_PieG = new google.visualization.PieChart(document.getElementById('PieG_div'));
	chart_PieG.draw(data_PieG, options_PieG);


				//	棒グラフ

	var options_BarG = { 
    title: 'おすすめのマイナースポーツ',
    hAxis: {title: '評価値'},
    isStacked: true						// 積み上げ形式
//				色変更するとき
//		colors: ['#FF0000', '#00FF00', '#AFAF00']

	};

	var data_BarG= google.visualization.arrayToDataTable([
		[' ', 
<?php
				for ($i = 0 ; $i < $n ; $i++ ){
					print "'".$Evi_name[$i]."'";
					if ( $i < ($n-1) ) print ",";
				}
 ?>
		],
<?php
			for ($j = 0 ; $j < $m ; $j++ ){
				print "['".$Alts_Name[$j]."',";
				for ($i = 0 ; $i < $n ; $i++ ){
					printf("%6.4f ", $cc[$i][$j] );
					if ( $i < ($n-1) ) print ",";
				}
				print "]";
				if ( $j < ($m-1) ) print ",";
				print "\n";
			}
?>
	]);
	
	var chart_BarG = new google.visualization.BarChart(document.getElementById('BarG_div'));
	chart_BarG.draw(data_BarG, options_BarG );

//	折れ線グラフ　
<?php
	if ($fzlc != 0 ){
?>
    var data = google.visualization.arrayToDataTable([
<?php
		print ("['ξ'");
		for ($j = 0 ; $j < $m ; $j++ ){
			printf(", '%s'", $Alts_Name[$j]);
		}
		print ("],\n");
		for ( $k = 0 ; $k < 21 ; $k++ ){		// xi を 0 から　0.05ずつ増やして1まで
			$gxi = $k / 20.00;			// xiを設定
			$gtotal = 0;						// 総合評価値の合計を求める
			for ($j = 0 ; $j < $m ; $j++ ){			//	各代替案の総合評価値を求める $j 代替案
				for ($i = 0 ; $i < $n ; $i++ ){		// 各基準の評価値を取り出し，$cc2 へ
					$kh2[$i] = $kh[$i][$j];
				}
				$g_gvv[$j] = c_int($gxi, $n, $kh2, $bb );
				$gtotal += $g_gvv[$j];
			}
			for ($j = 0 ; $j < $m ; $j++ ){
				$g_gvv[$j] = $g_gvv[$j] / $gtotal;
			}
			printf ("[ %f", $gxi);
			for ($j = 0 ; $j < $m ; $j++ ){
				printf (", %f" , $g_gvv[$j]);
			}
			if ( $k < 20 ){
				print( "], \n");
			}
			else {
				print( "] \n");
			}
		}
?>
    ]);
 
    // オプションの設定
    var options = {
      title: '感度分析（各xi で各代替案の総合評価値を1にする正規化を適用）'
     };     
             
    var chart = new google.visualization.LineChart(document.getElementById('lineC_div'));
      
    // グラフの描画
    chart.draw(data, options);
<?php
	}
?>

}


function drawTable() {

  var data_Table = new google.visualization.DataTable();
			//	表
	data_Table.addColumn('string', '代替案');
<?php 
	for ($i=0; $i < $n ; $i++){
?>
		data_Table.addColumn('number', '<?php print $Evi_name[$i]; ?>');
<?php 
	}
?>
	data_Table.addColumn('number', '総合評価値');
  data_Table.addRows([
<?php
			for ($j = 0 ; $j < $m ; $j++ ){
				print "['".$Alts_Name[$j]."',";
				for ($i = 0 ; $i < $n ; $i++ ){
					printf("%6.4f,", $cc[$i][$j] );
				}
				printf("%6.4f", $GV_value[$j]);
				print "]";
				if ( $j < ($m-1) ) print ",";
				print "\n";
			}
?>

	]);
  var table = new google.visualization.Table(document.getElementById('table_div'));
  table.draw(data_Table);
}
</script>
</head>
</pre>
<?php
}
?>

