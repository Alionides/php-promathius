<?
// Check that the script is not being accessed directly
if ( !defined('PROMATHIUS') )
{
	die("Hacking attempt");
}
include($game_root_path.'/header.php');
// Load the game graphical user interface
initGUI("");
if ($lockdb)
	endScript("Raffle is currently not available!");

$jtyps = array('cash', 'food');

$tickcost = array();
$tickcost[cash] = round($users[networth] / 2);
$tickcost[food] = $tickcost[cash] / $config[food];

$num_all_tickets = sqlsafeeval("SELECT COUNT(*) FROM $lotterydb WHERE num!=0") + 5;

foreach($jtyps as $jtyp) {
	$jackpot[$jtyp] = sqlsafeeval("SELECT amt FROM $lotterydb WHERE num=0 AND ticket=$tick_curjp AND jtyp='$jtyp';");
	$lastjackpot[$jtyp] = sqlsafeeval("SELECT amt FROM $lotterydb WHERE num=0 AND ticket=$tick_lastjp AND jtyp='$jtyp';");
	$lastnum[$jtyp] = sqlsafeeval("SELECT amt FROM $lotterydb WHERE num=0 AND ticket=$tick_lastnum AND jtyp='$jtyp';");
	$lastwin[$jtyp] = sqlsafeeval("SELECT amt FROM $lotterydb WHERE num=0 AND ticket=$tick_lastwin AND jtyp='$jtyp';");
	$jackpotgrow[$jtyp] = sqlsafeeval("SELECT amt FROM $lotterydb WHERE num=0 AND ticket=$tick_jpgrow AND jtyp='$jtyp';");
	$tickets[$jtyp] = mysql_safe_query("SELECT * FROM $lotterydb WHERE num=$users[num] AND jtyp='$jtyp';");
}


if ($do_ticket) {
	foreach($jtyps as $jtyp) {
		$name = "lb$jtyp";
		if(isset($$name))
			break;
	}
	if (mysql_num_rows($tickets[$jtyp]) >= $maxtickets)
		endScript("You can't buy any more $jtyp tickets!");
        if ($users[$jtyp] < $tickcost[$jtyp])
		endScript("You don't have enough for a ticket!");
	else {
		$ticknum = $num_all_tickets+1;
		$jackpot[$jtyp] += $tickcost[$jtyp];
		sqlQuotes($jtyp);
		fixInputNum($tickcost[$jtyp]);
		fixInputNum($jackpot[$jtyp]);
		mysql_safe_query("INSERT INTO $lotterydb (num,ticket,amt,jtyp) VALUES ($users[num],$ticknum,$tickcost[$jtyp],'$jtyp');");
		mysql_safe_query("UPDATE $lotterydb SET amt=$jackpot[$jtyp] WHERE num=0 AND ticket=$tick_curjp AND jtyp='$jtyp';");
		$users[$jtyp] -= $tickcost[$jtyp];
		saveUserData($users,"networth food cash");
	}
	foreach($jtyps as $jtyp)
		$tickets[$jtyp] = mysql_safe_query("SELECT * FROM $lotterydb WHERE num=$users[num] AND jtyp='$jtyp';");
}


$tpl->assign('cashcost', $tickcost[cash]);
$tpl->assign('foodcost', $tickcost[food]);
$tpl->assign('cashpot', commas($jackpot[cash]));
$tpl->assign('foodpot', commas($jackpot[food]));

$tpl->assign('numcasht', sqlsafeeval("SELECT COUNT(*) FROM $lotterydb WHERE num!=0 AND jtyp='cash';"));
$tpl->assign('numfoodt', sqlsafeeval("SELECT COUNT(*) FROM $lotterydb WHERE num!=0 AND jtyp='food';"));

$tpl->assign('numuct', mysql_num_rows($tickets['cash']));
$tpl->assign('numuft', mysql_num_rows($tickets['food']));
$tpl->assign('maxtickets', $maxtickets);

$tpl->assign('ucash', $users['cash']);
$tpl->assign('ufood', $users['food']);


foreach($jtyps as $jtyp) {
	$enemy = loadUser($lastwin[$jtyp]);
	$tpl->assign('last_'.$jtyp.'n', $lastnum[$jtyp]);
	$tpl->assign('last_'.$jtyp.'e', "$enemy[empire] <a class=proflink href=?profiles&num=$enemy[num]$authstr>(#$enemy[num])</a>");
	$tpl->assign('last_'.$jtyp.'w', commas($lastjackpot[$jtyp]));

	$ticks = array();
	while($ticket = mysql_fetch_array($tickets[$jtyp]))
		$ticks[] = $ticket;

	$tpl->assign($jtyp.'_ticks', $ticks);
}


$tpl->display('raffle.tpl');
?>
