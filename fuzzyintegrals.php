<?php

/* ------------------------------------------------------------
lambda to xi
------------------------------------------------------------- */
function lambda_to_xi( $lambda ){

	return( 1 / ( 1 + pow( $lambda + 1, 0.5 )));
}
/* ------------------------------------------------------------
xi to lambda
------------------------------------------------------------- */
function xi_to_lambda( $xi ){

	if ( ($xi > 0 ) && ( $xi <= 1 )){
		return(( ((1-$xi)*(1-$xi)) / ( $xi * $xi ))-1);
	}
	else{
		return( -1000 );		//	error
	}
}



/* -----------------------------------------------------------
	Check the Monotonisity of a fuzzy measure
		input	c_p:check point ( set number)
					t_fuzzy_m:	fuzzy measure
		output	: -1 : OK
							other values:	set number which do not satisfy the monotonicity
-----------------------------------------------------------	*/
function chk_mono_fm( $c_p ,	$t_fuzzy_m ){

	for( $i = 0 ; $i < $c_p ; $i++ ){
		if (( ( $c_p & $i ) == $i ) &&	($t_fuzzy_m[$c_p] < $t_fuzzy_m[$i] )){
			return( $i );
		}
	}
	return( -1 );
}

/* -----------------------------------------------------------
	Factorial calculation
-----------------------------------------------------------	*/
function fact($nn ){
	$v = 1;
	for( $i=1 ; $i <=$nn ; $i++ ) $v = $v * $i;
	return( $v );
}
/* -----------------------------------------------------------
	Shapley Value Calculation
		input nn: size of evaluation items
					mu[]:	fuzzy measure
		output	sh[]:	Shaplay Value
	
-----------------------------------------------------------	*/
function Shapley_value( $nn , $mu , &$sh ){

	$max_n = 1 << $nn;
	for ( $i = 0 ; $i < $nn ; $i++ ){
		$sh[$i]=0;
		$II = 1 << $i; 
		$MaskI = ( $max_n - 1 ) - $II;
		for( $S=0 ; $S < $max_n ; $S++){
			if (( $S & $II ) > 0 ){
				$nS = 0; 
				for( $j=0 ; $j<$nn ; $j++ ) 
					if (( $S & ( 1 << $j )) != 0 ) $nS++;
				$sh[$i] += ( fact( $nn - $nS ) * fact( $nS - 1 ) / fact($nn))*($mu[$S] - $mu[$S - $II]);
			}
		}
	}
}
/* -----------------------------------------------------------
	Mobius Transformation Calculation
		input nn: size of evaluation items
					mu[]:	fuzzy measure
		output	Mob[]: Mobius Transformation
-----------------------------------------------------------	*/


function Mobius($nn, $fuzzym, &$Mob){

	$max_fm = 1 << $nn ;
	for ( $I = 0 ; $I < $max_fm ; $I++ ){	
		$t = 0;													
		$Mask = (( 1 << $nn ) - 1) - $I ;		/* Complement of I	*/
		$nI = 0 ;												/*	nI: # of elemnts of I	*/
		for( $j=0 ; $j < $nn ; $j++ ){
			if ( ( $I & ( 1 << $j ) ) != 0 ){
				 $nI++;
			}
		}
		for ( $K = 0 ; $K < $max_fm ; $K++ ){
			if ( ( $K & $Mask ) == 0 ){
				$nK = 0 ;				/*	nK: # of elements of K	*/
 				for( $j=0 ; $j < $nn ; $j++ ){
 					if ( ( $K & ( 1 << $j ) ) != 0 ){
 						$nK++;
					}
				}
				if (( ( $nI - $nK ) % 2 ) == 0 ) {
					$t += $fuzzym[$K]; 
				}
				else	{
					$t -= $fuzzym[$K];
				}
	 		}
		}
		$Mob[$I] = $t;
	}
}

/* ------------------------------------------------------------
 phi xi transformation
 		Calculate \mu(A)
		Input	xi:	degree of interaction
								0-0.5:	Complementary	
								0.5:	Additive
								0.5-1:	Substitute
					u:	Sum of weights values ( \sum_{i in A} Weight_i where \sum Weight_i = 1)
		Output	:	fuzzy measure value of A ( \mu(A) )
------------------------------------------------------------- */
function phixitrans( $xi , $u ){

	if ( $xi == 1 ) {
			if ( $u != 0 ) 
				return(1.0 ); 
			else 
				return( 0.0 ) ;
	}
	if ( $xi == 0 ) {
		if ( $u != 1 ) 
				return( 0.0 ); 
		else 
			return( 1.0 ) ;
	}
	if ( $xi == 0.5 ) {
		return( $u );
	}
			// other cases 
	$s =	( (1.0 / $xi)-1.0 ) * (( 1.0 / $xi)-1.0 );
	return( ( pow( $s , $u) - 1 ) / ($s - 1) );
}
/* ------------------------------------------------------------
 Fuzzy Measure Identificatin by Input Numbers Standard
 
 	input	nn	:	# of evaluation items
 				xi	:	degree of interaction
 				weight[]	:		weight, weight[i] >= 0 , \forall i
 	output	mu[]:	Identified Fuzzy measure
 ------------------------------------------------------------- */
function fuzzym_inpnum($nn , $xi , &$mu , $weight ){
	
			// normalization( sum of weights = 1 )
	$t = 0 ; 
	for ($i = 0 ; $i < $nn ; $i++ ) 
		$t += $weight[$i];
	for ($i = 0 ; $i < $nn ; $i++ ) 
		$normal_weight[$i]= $weight[$i] / $t;
		

			// A: set	( \forall A \in 2^X )

	for( $A = 0 ; $A < ( 1 << $nn ) ; $A++ ){
		$u = 0;
		for ( $i = 0 ; $i < $nn ; $i++ ){
			if (( $A & ( 1 << $i )) != 0 ) 	//	if i \in A then 
				$u += $normal_weight[$i];
		}
		$mu[$A] = phixitrans( $xi , $u );
	}
	$mu[(1 << $nn )-1] = 1;		//	mu(X) = 1
}


/* ------------------------------------------------------------
Fuzzy Measure Identificatin by Singleton Fuzzy measure value ratio Standard

 	input	nn	:	# of evaluation items
 				r	:		lambda, degree of interaction
 				weight[]	:		weight, weight[i] >= 0 , \forall i
 	output	mu[]:	Identified Fuzzy measure
---------------------------------------- */
function fuzzym_singleton( $nn , $r , &$mu , $weight){
	$EPS = 0.00001;
	$MAX_CALC = 1000000;


			// normalization( max of weights = 1 )
	$max_weight = $weight[0];
	for( $i=1 ; $i < $nn ; $i++ ){
		if ($max_weight < $weight[$i]) {
			$max_weight = $weight[$i];
		}
	}
	for( $i=0 ; $i < $nn ; $i++ ) {
		$normal_weight[$i] = $weight[$i] / $max_weight;
	}


		//	Find lambda where mu(X)=1

	$pH = 1.0; $pL = 0.0; $counter = 0;
	do {
		$overf = 0;	// if mu(A) > 1 then next 
		$p = ( $pH + $pL ) / 2; 
		$q = $normal_weight[0] * $p;
		for( $i=1 ; $i < $nn ; $i++ ){
			if ( $q > 1 ){
					$overf = 1;	
					break;
			}
			$qq = $normal_weight[$i] * $p;
			$q = $q + $qq + $r * $q * $qq;
		}
		
		if ( $q > 1 ) 
			$pH = $p; 
		else 
			$pL = $p;
		if ( ++$counter > $MAX_CALC )
			break;
	} while ( ($overf==-1) || $q < ( 1 - $EPS ) || $q > ( 1 + $EPS ));
	
		//	Caculate the all fuzzy measure values from the identified lamda and weights
		
	$max_n = 1 << $nn;
	for( $ii = 0 ; $ii < $max_n ; $ii++ ){
		$ff = 0; 
		$q = 0;
		for ( $j = 0 ; $j < $nn ; $j++ ){
		 	if ( ( $ii & ( 1 << $j )) != 0 ){
			 	if ( $ff == 0 ){
					$q = $normal_weight[$j] * $p;
					$ff = -1;
				}
				else {
				 	$qq = $normal_weight[$j] * $p;
					$q = $q + $qq + $r * $q * $qq;
				}
			}
		}
		$mu[$ii] = $q;
	}
}


/* -----------------------------------------------------------
Fuzzy Measure Identificatin by Shapley Value Standard

 	input	nn	:	# of evaluation items
 				r	:		lambda, degree of interaction
 				weight[]	:		weight, weight[i] >= 0 , \forall i
 	output	mu[]:	Identified fuzzy measure
-----------------------------------------------------------	*/

function fuzzym_shapleyv($nn , $r , &$mu, $weight ){

		$EPS2 = 0.001;
		$MAX_CALC = 1000000;

			// normalization( sum of weights = 1 )
		$t = 0; 
		for ( $i= 0 ; $i<$nn ; $i++ ) 
			$t += $weight[$i];
		for ( $i=0; $i < $nn ; $i++ ){
			$normal_weight[$i] = $weight[$i] / $t;
			if ( $normal_weight[$i] != 0 ) 
				$iweight[$i] = 1; 
			else 
				$iweight[$i]=0;
		}


		$count = 0;
		do {
			$it = 0;	
			for( $i=0 ; $i < $nn ; $i++ ) 
				$it += $iweight[$i];
			for ( $i = 0 ; $i < $nn ; $i++ )
				$fweight[$i] = ( double ) $iweight[$i] / (double) $it;
			
			fuzzym_singleton( $nn , $r , $mu , $fweight );
			
			Shapley_value( $nn , $mu ,	$shv );

			$maxdef = $normal_weight[0] - $shv[0];
			$maxdef_p = 0;
			for( $i=1 ; $i < $nn ; $i++ ){
		 		if ( ($normal_weight[$i] - $shv[$i]) > $maxdef ){
					$maxdef = $normal_weight[$i] - $shv[$i]; $maxdef_p = $i;
				}
		 	}
			
			$iweight[$maxdef_p]++; 
			$maxdef = abs($normal_weight[0] - $shv[0]);
			
			for( $i=1 ; $i < $nn ; $i++ ){
				if ( abs($normal_weight[$i] - $shv[$i]) > $maxdef )
					$maxdef = abs($normal_weight[$i] - $shv[$i]);
			}
		} while ( ($maxdef > $EPS2 ) && (++$count < $MAX_CALC ));
}














/* -----------------------------------------------------------
	Choquet Integral
		Input	nn: # of evaluation items
					mu:	Fuzzy measure
					d:	input values
		Output : Choquet Integrated Value
-----------------------------------------------------------	*/

function Choquet_Integral($nn, $mu , $d ){

		$v_all = (1 << $nn)	- 1; /* Universal Set */

		for( $i=0 ; $i < $nn ; $i++ )	{
				$dd[$i] = $d[$i];	
				$order[$i] = $i;
		}
		$dd[$nn] = 0; 
		$order[$nn]=-1;
				// Sorting
		$ff = 1;
		while ($ff == 1) {
			$ff = 0;
			for($i = 0; $i < $nn ; $i++ ){
				if ($dd[$i] < $dd[$i + 1]){
					$ff = 1;
					$tt = $dd[$i];		$dd[$i] = $dd[$i + 1]; 				$dd[$i + 1] = $tt;
					$it = $order[$i];	$order[$i] = $order[$i + 1];	$order[$i + 1] = $it;
				}
			}
		}
				//	Calculation
		
		$v = 0; //	cumulative set ( initial value -> empty set )
		$z=0;	//	output value
		for($i = 0; $i < $nn ; $i++ ){
				if ( $order[$i] != -1 ) 
					$v |=	(1 <<($order[$i])); 	//	Add an element to the cumulative set
				if ( $dd[$i] > 0 )
					$z += ($dd[$i] - $dd[$i+1]) * $mu[$v]; 
				else
					$z += ($dd[$i] - $dd[$i+1]) * ( $mu[$v] - $mu[$v_all] ); 
		}
		return ($z);
}

/* -----------------------------------------------------------
	Sugeno Integral
		Input	nn: # of evaluation items
					mu:	Fuzzy measure
					d:	input values
		Output : Choquet Integrated Value
-----------------------------------------------------------	*/
function Sugeno_Integral($nn, $mu , $d){

	for ( $i = 0 ; $i < $nn ; $i++ ){
		$Order[$i] = $i; 
		$dd[$i] = $d[$i];
	}
			// Sorting
	$ff = 1;
	while ($ff == 1){
		$ff = 0;
		for( $i = 0 ; $i < ($nn - 1 ) ; $i++ ){
			if ($dd[$i] < $dd[$i + 1]){
				$ff = 1; 
				$tt = $dd[$i]; $dd[$i] = $dd[$i + 1]; $dd[$i + 1] = $tt;
				$it = $Order[$i]; 	$Order[$i] = $Order[$i + 1];	$Order[$i + 1] = $it;
		 	}
		}
	}
	
			// Calculation
	$v = 0;
	$ct=0;
	for( $i=0 ; $i < $nn ; $i++ ){
		$v |=	1 << ( $Order[$i] );	
		if ( $dd[$i] > $mu[$v] )	
			$ctt = $mu[$v]; 	
		else $ctt = $dd[$i];
		if ( $ctt > $ct )	
			$ct = $ctt;	
	}
	return( $ct );
}



		//	数値から集合を出す関数(数値)
function fuzzy_m_set( $num, $n ){

	$pp = 0;
	$buf = "{";
	$point	=	1;
	$first_f = 0;
	for( $i = 0 ; $i < $n ; $i++ ){
		if ( ( $num & $point ) != 0 ){
			if ( $first_f != 0 ){
					$buf = $buf . ',';
			}
			$first_f = 1;
			$buf = $buf . (1 + $i) ;
		}
		$point = $point * 2;
	}
	$buf = $buf	.'}';	
	return( $buf );
}

		//	数値から集合を出す関数（名前）
function fuzzy_m_set_name( $num, $n , $name){

	$pp = 0;
	$buf = "{";
	$point	=	1;
	$first_f = 0;
	for( $i = 0 ; $i < $n ; $i++ ){
		if ( ( $num & $point ) != 0 ){
			if ( $first_f != 0 ){
					$buf = $buf . ',';
			}
			$first_f = 1;
			$buf = $buf . $name[1 + $i] ;
		}
		$point = $point * 2;
	}
	$buf = $buf	.'}';	
	return( $buf );
}



function phixi_trans($xi, $u){
	//	 Φsにより、ファジィ測度を同定する。Φs変換をsのかわりにξで指定する。
	//				引数
	//				xi: ξ	ただし、0 <= ξ <= 1
	//				u: u
	//				関数値
	//				Φs変換値
	$eps = 0.000000001;			// 0. 0.5 , 1 と判別するときの閾値

	if ($xi < $eps){
						//ξ=0のときの処理
		if ((1 - $eps) < $u) {
			$outv = 1;
		}
		else{	
			$outv = 0;
		}
	}
	elseif ((1 - $eps) < $xi){
					//ξ=1の時の処理
		if ($u < $eps){
				$outv = 0;
		}
		else{
			$outv = 1;
		}
	}
	elseif (((0.5 - $eps) < $xi) && ($xi < (0.5 + $eps))){
						//ξ=0.5の時の処理
		$outv = $u;
	}
	else{
		$s = xi_tolambda($xi) + 1;
		$outv = (pow($s,$u) - 1) / ($s - 1);
	}
	return($outv);
}

function c_integral($xi,$n, $v, $w){
	$v[$n] = 0;
	$total = 0;
	$u = 0;
	for ( $i=0 ; $i < $n ; $i++ ){
		$u += $w[$i];
		$fm = phixitrans($xi, $u);
		$total += $fm * ($v[$i] - $v[$i + 1]);
	}
	return($total);

}


function c_int($xi, $n, $v, $w){

			//	配列の要素は，0から始まる数値
			//	被積分値vは非負の値

	$total = 0;
	for ($i=0; $i < $n ; $i++){
		$total += $w[$i];
	}

	if ($total == 0) {
		return(0);
	}

	for ($i=0; $i < $n ; $i++){
		$ww[$i] = $w[$i] / $total;
	}
		
	$ff = 0;		//	並べ替えのフラグ
						//並べ替え
	while ($ff == 0 ){
		$ff = 1;
		for( $i = 0; $i < ($n - 1 ); $i++ ){
			if( $v[$i] < $v[$i + 1]){
				$wv = $v[$i];
				$v[$i] = $v[$i + 1];
				$v[$i + 1] = $wv;
				$wv = $ww[$i];
				$ww[$i] = $ww[$i + 1];
				$ww[$i + 1] = $wv;
				$ff = 0;
			}
		}
	}

			//積分の計算
	return ( c_integral($xi, $n, $v, $ww));
}

/* --------------------------------------
  c_int 要素名を 文字で．
  項目数は自動でカウント．
 --------------------------------------*/
function c_int_k($xi, $v, $w){

	$i = 0;
	foreach( $v as $key => $value ) {
  	$vv[$i] = $v[$key];
  	$ww[$i] = $w[$key];
		$i++;
	} 
	return(c_int($xi, $i, $vv, $ww));
}

/* --------------------------------------
	区分線型関数で，メンバーシップ値を得る
	$n 点の数
	$inpt x座標
	$outt y座標
	$v 入力値
--------------------------------------*/
function piecew_l($n, $inpt, $outt, $v){

	if ( $v < $inpt[0] ){
		return( $outt[0] );
	}

	if ( $v >= $inpt[$n-1] ){
		return( $outt[$n-1] );
	}

	for ($i = 0 ; $i < ($n -1) ; $i++){
		if ( ($inpt[$i] <= $v) && ($v < $inpt[$i+1] )){
			$t = ( $v - $inpt[$i] ) / ( $inpt[$i+1] - $inpt[$i] );
			return( ( $outt[$i+1] - $outt[$i] ) * $t + $outt[$i] );
		}
	}
}

/* -----------------------------------------------------------
	Importance Index for the Criteria and the Rank R(i, j) Calculation
		input nn: size of evaluation items
		mu[]:	fuzzy measure
		output	
			R[i][j]: Importance Index for the Criteria and the Rank
				i: Criteria, 1,...,nn
				j: Rank, 1,...,nn
			sh[]: Shapley value
			Ro[]: the average contribution for the rank of the j-th element
			Orness[]: i-th criterion's orness , 1,...,nn
				Orness[0]: fuzzy measure orness
-----------------------------------------------------------	*/
function Calc_Rij ( $nn , $mu , &$R, &$sh, &$Ro, &$Orness ){

	$max_n = 1 << $nn;
	
	for ( $i = 1 ; $i <= $nn ; $i++ ){
		for ( $j = 1 ; $j <= $nn ; $j++ ){
			$R[$i][$j] = 0;
		}
	}

	for ( $i = 0 ; $i < $nn ; $i++ ){
		$II = 1 << $i; 
		$MaskI = ( $max_n - 1 ) - $II;
		for( $S=0 ; $S < $max_n ; $S++){
			if (( $S & $II ) > 0 ){
				$nS = 0; 
				for( $j=0 ; $j<$nn ; $j++ ){
					if (( $S & ( 1 << $j )) != 0 ) {
						$nS++;
					}
				}
				$R[$i+1][$nS] += ( fact( $nn - $nS ) * fact( $nS - 1 ) / fact($nn))*($mu[$S] - $mu[$S - $II]);
			}
		}
	}
			// Shapley Value
	for($i=1 ; $i <= $nn ; $i++){
		$sh[$i]=0;
		for($j=1 ; $j <= $nn ; $j++){
			$sh[$i] += $R[$i][$j];
		}
	}
			//	Calculate Ro
	for($j=1 ; $j <= $nn ; $j++){
		$Ro[$j]=0;
		for($i=1 ; $i <= $nn ; $i++){
			$Ro[$j] += $R[$i][$j];
		}
	}

			// Calculate Orness

	$total = 0;
	for ($i=1; $i <= $nn ; $i++){
		for($j=1; $j <= $nn ; $j++){
			$total += $R[$i][$j];
		}
	}

	for ($i=1; $i <= $nn ; $i++){
		$sh2[$i] = 0;
		for($j=1; $j <= $nn ; $j++){
			$R2[$i][$j] =$R[$i][$j] / $total;
			$sh2[$i] += $R2[$i][$j];
		}
	}

	for($i=1 ; $i <= $nn ; $i++){
		$Orness[$i] = 0;
		for($j=1 ; $j <= $nn ; $j++){
			if ( $sh2[$i] > 0 ){
				$Orness[$i] += ($nn - $j) * $R2[$i][$j] / $sh2[$i];
			}
		}
		$Orness[$i] = $Orness[$i] / ($nn - 1);
	}

			//	Calculate Ro2
	for($j=1 ; $j <= $nn ; $j++){
		$Ro2[$j]=0;
		for($i=1 ; $i <= $nn ; $i++){
			$Ro2[$j] += $R2[$i][$j];
		}
	}

	$total = 0;
	for($j=1 ; $j <= $nn ; $j++){
		$total +=  ($nn - $j) * $Ro2[$j];
	}
	$Orness[0] = $total / ($nn-1);
}

function Subset_Shapleyv($n, $g , $subset ){

			//	elemnts # of the subset
	$n_subset = 0;
	for ($i=0 ; $i < $n ; $i++){
		if (((0x01 << ($i)) & $subset) != 0){
			$subset_cores[$n_subset] = $i;
			$n_subset++;
		}
	}

	$k=0;
	for ($i=0; $i <= (pow(2,$n) - 1); $i++){
		if (((( (1 << 14) - 1) - $subset) & $i ) == 0) {   // if $i \subseteq $subset
			$gg[$k] = $g[$i];
			$k++;
		}
	}

	Shapley_value( $n_subset , $gg , $sub_sh );

	for ($i=0 ; $i < $n ; $i++){
		$sub_sh2[$i]=0;
	}
	for ($i=0 ; $i < $n_subset ; $i++){
		$sub_sh2[$subset_cores[$i]]=$sub_sh[$i];
	}

	return($sub_sh2);
}
?>

<?php
/* -----------------------------------------------------------
	Choquet Integral Evaluation Contributions
		Input	nn: # of evaluation items
					mu:	Fuzzy measure
					d:	input values
		Output : array
							nn: Choquet Integrated Value 
						 	0...nn-1 ; Evaluation item's contributions
-----------------------------------------------------------	*/

function Choquet_Integral_evacont($nn, $mu , $d ){

		for ($i = 0 ; $i < $nn ; $i++){
			$evacont[$i] = 0;
		}

		$v_all = (1 << $nn)	- 1; /* Universal Set */

		for( $i=0 ; $i < $nn ; $i++ )	{
				$dd[$i] = $d[$i];	
				$order[$i] = $i;
		}
		$dd[$nn] = 0; 
		$order[$nn]=-1;
				// Sorting
		$ff = 1;
		while ($ff == 1) {
			$ff = 0;
			for($i = 0; $i < $nn ; $i++ ){
				if ($dd[$i] < $dd[$i + 1]){
					$ff = 1;
					$tt = $dd[$i];		$dd[$i] = $dd[$i + 1]; 				$dd[$i + 1] = $tt;
					$it = $order[$i];	$order[$i] = $order[$i + 1];	$order[$i + 1] = $it;
				}
			}
		}
				//	Calculation
		
		$v = 0; //	cumulative set ( initial value -> empty set )
		$z=0;	//	output value
		for($i = 0; $i < $nn ; $i++ ){
				if ( $order[$i] != -1 ) {
					$v |=	(1 <<($order[$i])); 	//	Add an element to the cumulative set
				}
				$sh_sub =Subset_Shapleyv($nn, $mu , $v );
				if ( $dd[$i] > 0 ){
					$z += ($dd[$i] - $dd[$i+1]) * $mu[$v]; 
					for($k = 0; $k < $nn; $k++){
							$evacont[$k] += ($dd[$i] - $dd[$i+1]) * $sh_sub[$k];
					}
				}
				else{
					$z += ($dd[$i] - $dd[$i+1]) * ( $mu[$v] - $mu[$v_all] ); 
					for($k = 0; $k < $nn; $k++){
							$evacont[$k] += ($dd[$i] - $dd[$i+1]) * ( $sh_sub[$k] - $mu[$v_all] );
					}
				}
		}
		$evacont[$nn] = $z;
		return ($evacont);
}

?>

<?php
/* -----------------------------------------------------------
	Choquet Integral Rank Contributions
		Input	nn: # of evaluation items
					mu:	Fuzzy measure
					d:	input values
		Output : array
							nn: Choquet Integrated Value 
						 	0...nn-1 ; Rank contributions
-----------------------------------------------------------	*/

function Choquet_Integral_rankcont($nn, $mu , $d ){

		for ($i = 0 ; $i < $nn ; $i++){
			$rankcont[$i] = 0;
		}

		$v_all = (1 << $nn)	- 1; /* Universal Set */

		for( $i=0 ; $i < $nn ; $i++ )	{
				$dd[$i] = $d[$i];	
				$order[$i] = $i;
		}
		$dd[$nn] = 0; 
		$order[$nn]=-1;
				// Sorting
		$ff = 1;
		while ($ff == 1) {
			$ff = 0;
			for($i = 0; $i < $nn ; $i++ ){
				if ($dd[$i] < $dd[$i + 1]){
					$ff = 1;
					$tt = $dd[$i];		$dd[$i] = $dd[$i + 1]; 				$dd[$i + 1] = $tt;
					$it = $order[$i];	$order[$i] = $order[$i + 1];	$order[$i + 1] = $it;
				}
			}
		}
				//	Calculation
		
		$v = 0; //	cumulative set ( initial value -> empty set )
		$z=0;	//	output value
		for($i = 0; $i < $nn ; $i++ ){
				if ( $order[$i] != -1 ) {
					$v |=	(1 <<($order[$i])); 	//	Add an element to the cumulative set
				}
				if ( $dd[$i] > 0 ){
					$rankcont[$i] = ($dd[$i] - $dd[$i+1]) * $mu[$v]; 
					$z += $rankcont[$i];
				}
				else{
					$rankcont[$i] = ($dd[$i] - $dd[$i+1]) * ( $mu[$v] - $mu[$v_all] ); 
					$z += $rankcont[$i]; 
				}
		}
		$rankcont[$nn] = $z;
		return ($rankcont);
}

/* ---------------------------------------------------------
fmci_GV_Calc
	ショケ積分で総合評価値を計算する関数

入力：
	$n:	評価項目の数
	$m:	代替案の数
	$g: ファジィ測度
	$kobetu_h[][]:	個別評価値  
									$kobetu_h[$i][$j] : $i-1 番目の基準についての$j-1番目の代替案の個別評価値
									例：$kobetu_h[1][0] : 2番目の基準の1番目の代替案の個別評価値
  $GV_value[] : 総合評価値
	$GV_Rank[]	:  $GV_Rank[$i] :$i-1 位の代替案の番号
									$GV_Rank[0] -> 2：1位の代替案の番号は，2で（3番目の）代替案
	$cc[][] : 総合評価値を各基準に分解した値（和が総合評価値になる）
戻り値：
	なし
---------------------------------------------------------- */

			//	総合評価値を計算

function fmci_GV_Calc($n,$m,$bb, $g,$kobetu_h,&$GV_value, &$GV_Rank, &$cc){

	for ($i = 0 ; $i < $m ; $i++ ){		//	代替案分繰り返す
		for($j = 0 ; $j < $n ; $j++ ){  //  1個の代替案分の個別評価値を取り出す
			$h[$j] = $kobetu_h[$j][$i];
		}

		$eva_cintrib = Choquet_Integral_evacont($n, $g , $h );
	
		$GV_value[$i] = $eva_cintrib[$n];
		for($j = 0 ; $j < $n ; $j++ ){
			$cc[$j][$i] = $eva_cintrib[$j];
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

?>









