<?php
	$sel3 = $_POST['SEL3'];
	$Evi_Name = array("楽しさ","激しさ","運動量","気軽さ","費用");
	$Evi_Name[2] = $Evi_Name[2] . "(". $sel3 . ")";

	include("ahp.php");
	$n=5;			



?>
<html>
	<head>
	<meta http-equiv=Content-Type content="text/html; charset=utf-8">
	<link rel="stylesheet" href="rcss.css" type="text/css" />
	<title>一対比較</title>
	</head>
	<body>
		<form method="POST" action="fmciex3.php">

<?php
		AHP_enqtable($n, $Evi_Name);
?>


	<input type="hidden" name="SEL3" value="<?php print $sel3; ?>" />
	<input type="hidden" name="xiv" value="0.5" />
	<input type="submit" value="送信">
	</form>

<?php
	 print_r(get_defined_vars()); 
?>
</pre>-->
	</body>
</html>
