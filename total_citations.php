
<!-- This was created by J. Paul Liu, see for more detail: https://www.meas.ncsu.edu/sealevel/google/howto.php -->
<!-- modified to return only the total number of citations -->

<?php

if($GScontent=="")
	//this will make sure you include the curl.php line above
{
	echo "<font color=red>you have to include curl.php with userid and language first</font>";
	echo "<br>";
}else
{
	print get_total_citations($GScontent, $url );
}

function get_total_citations($GScontent, $url)
{
	$GSText= '';
	$output = preg_match_all('/<div class="gsc_rsb_s gsc_prf_pnl" id="gsc_rsb_cit" role="region" aria-labelledby="gsc_prf_t-cit">(.*)<\/div><div class="gsc_rsb_s gsc_prf_pnl" id="gsc_rsb_co" role="region" aria-labelledby="gsc_prf_t-ath">/',$GScontent,$matches);

	if(!isset($matches[1][0]))
	{
		$output = preg_match_all('/<div class="gsc_rsb_s gsc_prf_pnl" id="gsc_rsb_cit" role="region" aria-labelledby="gsc_prf_t-cit">(.*)<\/div><div class="gsc_lcl" role="main" id="gsc_prf_w">/',$GScontent,$matches);
	}

	$GSText= isset($matches[1][0])?$matches[1][0]:'e1';
	// GET THE TOTAL CITATIONS
	preg_match_all('/Citations<\/a><\/td><td class="gsc_rsb_std">(\d+)<\/td>/is',$GSText,$matches);
	$citations = isset($matches[1][0])?$matches[1][0]:'e2';


	$text = ' <a href="'.$url.'">Google Scholar</a> <a href="'.$url.'" target=_blank>('.$citations.')</a>' ;
	return $text;
}

?>
