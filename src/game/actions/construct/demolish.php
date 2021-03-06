<?php
// Check that the script is not being accessed directly
if ( !defined('PROMATHIUS') )
{
	die("Hacking attempt");
}
require($game_root_path."/lib/status.php");

$destroyrate = round(($users[land] * 0.02) + 2);
// if ($destroyrate > 400)	$destroyrate = 400;
$destroyrate = floor($urace[bpt] * $destroyrate);
//$salvage = round(($config[buildings] + ($users[land] * .1)) / 5);
	foreach($structures as $id => $name)
	{
		$salvage[$id] = round((($config['structure'][$id] / $config['game_factor']) + ($users[land] * .1)) / $config['demdepreciation']);
	}

$totbuildings = $users[land] - $users[freeland];

$destroy_max_labs = ($users[labs] - round($users[wizards]/175) - 1);
if($destroy_max_labs < 0)
	$destroy_max_labs = 0;

$safe = array();

function getDestroyAmount ($type)
{
	global $config, $users, $candestroy, $destroyrate, $safe, $urace;
	
	$sid =  'structure_'.$type;
	
	$candestroy[$type] = $destroyrate * $users[turns];
	if ($candestroy[$type] > $users[$sid]) 
		$candestroy[$type] = $users[$sid];
	$candestroy[all] += $candestroy[$type];
	if ($candestroy[all] > $users[turns] * $destroyrate)
		$candestroy[all] = $users[turns] * $destroyrate;
	$candestroy[$type] = gamefactor($candestroy[$type]);
	if($type == 'labs') {
		$safe['labs'] = ($users[labs] - round($users[wizards]/175) - 1);
	} else if($type == 'homes') {
		
		@$low_pop_base = 20 * (calcExpenses(0) / pci($users, $urace));
		if ($users['hero_peace'] == 3) // Hestia?
			$low_pop_base /= 5;
		
		$taxrate = $users[tax] / 100;
		if ($users[tax] > 40)
			$taxpenalty = ($taxrate - 0.40) / 2;
		if ($users[tax] < 20)
			$taxpenalty = ($taxrate - 0.20) / 2; 
		
		
		
		$safe['homes'] = round(($low_pop_base * 0.95 + $taxrate + $taxpenalty) - $users[land]*2 - $users[freeland]*5) - 1; // Lowest possible
		$safe['homes'] = $users['homes'] - $safe['homes'];
		//print("Lowest homes: $safe[homes] Because lowest pop base is $low_pop_base because of " . calcExpenses(0) . " expenses! with PCI of" . pci($users, $urace));

	} else if($type == 'barracks') {
		$safe['barracks'] = $users['barracks'] - round(0.3 * $users[land]);
	} else {
		$safe[$type] = $candestroy[$type];
	}

	$safe[$type] = max(0, min($users[$type], $safe[$type]));
} 

function destroyStructures ($type, $salvage)
{
	global $users, $uera, $demolish, $candestroy, $totaldestroyed, $totalsalvaged, $max, $safeck, $safe, $tpl, $config;
	fixInputNum($demolish[$type]);
	$amount = $demolish["structure$type"];
	if ($amount < 0)
	{
		mysql_query("UPDATE ".$config['prefixes'][1]."_players SET new_error='It is impossible to demolish a negative number of structures.' WHERE num='$users[num]';");
		$uri = urldecode($_SERVER['REQUEST_URI']);
		$action = substr($uri, strpos($uri, '?') + 1);
		header("Location: ?" . $action); 
	}
	if ($amount > $candestroy[$type])
	{
		$errormsg = "You cannot demolish this many structures.";
	}
	if(!$errormsg){
		$users['buildings'][$type] -= $amount/$config['game_factor'];

		if ($type == land){
			$users[freeland] -= $amount;
			$salvage = 1;
		}
		else {
			$users[freeland] += $amount*$uera['structure'.$type."land"];
			$totaldestroyed += $amount;
		} 
		$users[cash] += $amount * $salvage;
		$totalsalvaged += $amount * $salvage;
	}
} 

function printRow ($type, $num='')
{
	global $users, $uera, $candestroy, $safe, $ddemolish, $salvage;
	$users = loadUser($users['num']);
	$sid =  $type.'_'.$num;
	$refund = gamefactor($salvage[$num]);
	$type = $type.$num;
	$ddemolish[] = array('name' => ucwords($uera[$type]), 'type' => $type, 'refund' => $refund, 'userAmount' => commas(buildingfactor($users[$sid])), 'safeDestroy' => commas($safe[$type]), 'canDestroy' => commas($candestroy[$num]));
} 

	foreach($structures as $id => $name)
	{
		getDestroyAmount($id);
	}
$candestroy[land] = $users[freeland];
if ($do_demolish) {
	$errormsg = false;
	foreach($structures as $ids => $names)
	{
		if($candestroy[$ids] < 0) $candestroy[$ids] = 0;
		if ($demolish["structure$ids"] > $candestroy[$ids])
			$errormsg = "You cannot demolish that many structures.";
	}
	if(!$errormsg)
	{
		foreach($structures as $id => $name)
		{
			if ($demolish["structure$id"])
				destroyStructures($id, $salvage[$id]);
		}
	}
		destroyStructures(land, 0);

	$turns = ceil($totaldestroyed / $destroyrate);

	if ($users[turns] < $turns)
	{
		mysql_query("UPDATE ".$config['prefixes'][1]."_players SET new_error='We do not have requisite turns to demolish these structures.' WHERE num='$users[num]';");
		$uri = urldecode($_SERVER['REQUEST_URI']);
		$action = substr($uri, strpos($uri, '?') + 1);
		header("Location: ?" . $action); 
	}
	if ($users[land] == 0) {
		$users[land] = 1;
		$users[freeland] = 1;
	} 
	takeTurns($turns, demolish);
	saveUserData($users, "buildings cash freeland");

	foreach($structures as $id => $name)
	{
		getDestroyAmount($id);
	}
	$candestroy[land] = $users[freeland];
} 

	foreach($structures as $id => $name)
	{
		printRow(structure, $id);
	}
$tpl->assign('cnd', $cnd);
$tpl->assign('authstr', $authstr);
$tpl->assign('do_demolish', $do_demolish);
$tpl->assign('freeland', commas($users[freeland]));
$tpl->assign('demolish', $ddemolish);
$tpl->assign('candestroy', commas($candestroy[land]));
$tpl->assign('allyoucandestroy', commas($candestroy[all]));
$tpl->assign('destroyrate', commas($destroyrate));
//$tpl->assign('salvage', commas(gamefactor($salvage)));
$tpl->assign('totaldestroyed', commas($totaldestroyed));
$tpl->assign('turns', $turns);
$tpl->assign('totalsalvaged', commas(gamefactor($totalsalvaged)));
$tpl->assign('gamename', $gamename);
$tpl->assign('uera', $uera);
$tpl->assign('err', $errormsg);
//include($game_root_path."/lib/error_msg.php");
// Load the game graphical user interface
initGUI();
if ($turns > 0)
	echo $turnoutput;
$tpl->display("actions/construct/demolish.tpl");
endScript("");

?>
