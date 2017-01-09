<?
// Check that the script is not being accessed directly
if ( !defined('PROMATHIUS') )
{
	die("Hacking attempt");
}
$size = calcSizeBonus($users[networth]);


function calcFoodCon() {
	global $users, $config, $urace, $uera; 
	
	$foodcon = 0;
	foreach($config[troop] as $num => $mktcost) {
		if($users[troop][$num] > 0)
			$foodcon += $users[troop][$num] * 0.5 * $config['troop'.$num]['foodconsumption'];
			//echo "Troop " . $users[troop][$num] . " cost is " . $mktcost;
	}
	//echo "<br><br>Troop food consumption is " . $foodcon;
	
	$foodcon += $users[wizards] * .25 * $config[wpl]/100;
	$foodcon *= $urace[food];
	
	if($users['hero_peace'] == 3)
		$foodcon += ($users[peasants] * $config['PopValue'] * .004);
	else
	{
		$foodcon += ($users[peasants] * $config['PopValue'] * 5 * 1000);
		//	echo "<br><br>Peasant food consumption is " . ($users[peasants] * $config['PopValue'] * 5 * 1000);
	}
	
	if(round($foodcon) < 0)
		$foodcon = 0;
	return round($foodcon);
	
}


function calcFoodPro() {
	global $config, $users, $urace;

	$foodpro = round(($users[freeland] * 5) + ((getBuildings('farms', $users)*$config['buildingoutput']) * 75) * $urace[farms]) * (10/$config['food_sell']) * (10/$config['food_sell']);
	
	if ($users['hero_peace'] == 1) // Demeter?
		$foodpro = round(1.5 * $foodpro);
	return round($foodpro);
}

function calcWizards()
{
	global $users, $config, $peasants, $real;
	$wplmod = $config['wpl'] * 0.01;
    // These values in the midst of adjustment
    if ($users[wizards] < ((getBuildings('agents', $users)*$config['buildingoutput']) * 25 * $wplmod))
           $wizards = round((getBuildings('agents', $users)*$config['buildingoutput']) * 0.25 * $wplmod);
    elseif ($users[wizards] < ((getBuildings('agents', $users)*$config['buildingoutput']) * 50 * $wplmod))
           $wizards = round((getBuildings('agents', $users)*$config['buildingoutput']) * 0.20 * $wplmod);
    elseif ($users[wizards] < ((getBuildings('agents', $users)*$config['buildingoutput']) * 90 * $wplmod))
           $wizards = round((getBuildings('agents', $users)*$config['buildingoutput']) * 0.10 * $wplmod);
   	elseif ($users[wizards] < ((getBuildings('agents', $users)*$config['buildingoutput']) * 100 * $wplmod))
          $wizards = 0;
   	elseif ($users[wizards] < ((getBuildings('agents', $users)*$config['buildingoutput']) * 175 * $wplmod))
          $wizards = 0;
    elseif ($users[wizards] > ((getBuildings('agents', $users)*$config['buildingoutput']) * 175 * $wplmod))
          $wizards = round($users[wizards] * -.05);

		$users[peasants] -= gamefactor($wizards);
		$peasants -= gamefactor($wizards);
	return $wizards;
}

function calcIncome() {
	global $users, $urace, $config, $size, $tradeincome, $taxincome, $marketincome, $config, $tradeincomearr;
	$size = calcSizeBonus($users[networth]);
	
	if(getBuildings('markets', $users))
		$marketincome = getMarketProfits ();
	$tradeincome = 0;
	$taxincome = 0;
	foreach (getBuildings('income', $users) as $id => $structureincome)
	{
		$tradeincomearr[$id] = (($structureincome*$config['shopsincome']*$config['buildingoutput']) * 2000)  /$size;
		$no += 1;
		$tradeincome += $tradeincomearr[$id];
	}
	$taxincome = (pci($users, $urace) * (($users[tax] * $config['TaxIncomeMod']) / 5) * $users[peasants]*10 * $config['PopValue']);
	$taxincome /= $size;
	$income = round(($taxincome + $tradeincome + $marketincome));
	if(round($income) < 0)
		$income = 0;
	$users[trade_economy] = $income;
	saveUserData($users, "trade_economy");
	return round($income);
}

function calcExpenses($if) {
	global $users, $urace, $expbonus, $warflag, $config, $corruption, $landupkeep, $wizardcosts;
	
	$loanpayed = round($users[loan] / 200);
	$expenses = 0;
	foreach($config[troop] as $num => $mktcost)
		$expenses += $users[troop][$num] * $config['troopcosts'] * ($config[troop][$num] / $config[troop][0]);
	$troopcosts = $expenses;
	$wizardcosts = $users[wizards] * .5;
	$landupkeep = ($users[land] - 233) * 8;
	$expenses += ($landupkeep + $wizardcosts);
	$expenses = round($expenses);
	//$expbonus = round($expenses * ($urace[costs] - (($users[barracks]*$config['buildingoutput']) / ($users[land]+1))));
	//if ($expbonus > $expenses) // expenses bonus limit
	//	$expbonus = round($expenses);
	//$expenses -= $expbonus;
	$corruption = ((100 -$users[health]) * (24.52)) * $config['corruption'] * (gamefactor($users['trade_economy']))/10000;
	$expenses += $corruption/$config['game_factor'];
	$corruption = round($corruption);
	
	$wartax = 0;
	if ($warflag) // war tax?
		$wartax = $networth / 1000; 
	if(!$if)
		return round($expenses + $loanpayed + $wartax);
	else
		return commas(gamefactor($troopcosts));
}

function calcLoanPay()  {
	global $users, $urace, $expbonus, $warflag, $config;
	return round($users[loan] / 200);
}

function calcPublicOrderOLD($args) {
	global $users, $urace, $config, $size, $food;
	$size = calcSizeBonus($users[networth]);
	
	if($users[attackpenalty] > 0 && $users[lastattack] > $config['AttackRecovery'])
	{
		$users[attackpenalty]--;
		saveUserData($users, "attackpenalty");
	}
   	foreach ($users[troop] as $num => $numb)
    {
       	$troopbonus += $users[troop][$num];
   	}
	$foodnet = calcFoodPro() - calcFoodCon();
	if($foodnet < 0)
		$foodpenalty = gamefactor($foodnet)/100*$config['FoodShortagePen'];
	$money = calcIncome() - calcExpenses(0);
	if($money < 0)
		$moneypenalty = gamefactor($money)/200*$config['CashShortagePen'];
	$towerbonus = (getBuildings('homes', $users)/1600)*$config['towerbonus']*$config['buildingoutput'];
	$homesbonus = (getBuildings('homes', $users)/1600)*$config['homesbonus']*$config['buildingoutput'];
	$troopbonus /= 1600;
	$troopbonus *= $config['troopbonus'];
	if($args == 1)
		$troopbonus = gamefactor($troopbonus);
	$taxgpenalty = $users[tax] * $config['TaxOrderPenalty'];
	$order = $troopbonus + $towerbonus + $homesbonus - $users[attackpenalty] - $taxgpenalty + $foodpenalty + $moneypenalty;
	$order = (($order * ($users[peasants]*$config['PopValue']))/2000);
	// To decrease the sensitivity, we set a halfway marker of 50
	$order = 50 + $order/18;
	if ($order > 100)
		$order = 100;
	if ($order < 0)
		$order = 0;
	return round($order);
}


function generateFactors() {
	global $uera, $config, $users, $publicorder, $employment, $effectiveStructures;
	$publicorder = array();
	
//////////////////////////////////////////////////
// Factor: Military Control
//////////////////////////////////////////////////

	foreach($config['troop'] as $num => $name)
	{
		$Units 		= 	gamefactor($users[troop][$num]);
		$UnitOrder	= 	$uera['troop'.$num.'orderbonus'];
		$UnitBonus 	= 	$Units * $UnitOrder;
		$UnitTotal += $UnitBonus;
		$ArmyTotal += $Units;
	}
	
	$Type		= 	'Military Control';
	$Formula 	= 	0 + $UnitTotal / ( (($users['peasants'] + 1 + $ArmyTotal) * $config['PopValue']) / 20) * $config['order_military'];
	$Positive	= 	true;

	addPublicOrderFactor ($Type, $Formula, $Positive);

//////////////////////////////////////////////////
// Factor(s): Structures that provide a fixed bonus
//////////////////////////////////////////////////
	
	foreach($config['structures']['PublicOrder'] as $num => $name)
	{
		$Type		= 	$config['structures']['PublicOrderCategory'][$num];
		$Benefit 	= 	0 + buildingfactor($effectiveStructures[$users['num']][$num]) * $config['structures']['PublicOrder'][$num] * $config['order_structures'];
		if($Benefit >= 0)
		{
			$Formula = $Benefit;
			$Positive	= 	true;
		}
		else
		{
			$Formula = $Benefit * -1;
			$Positive	= 	false;
		}

		if($Benefit != 0)
			addPublicOrderFactor ($Type, $Formula, $Positive);
	}

//////////////////////////////////////////////////
// Factor(s): Structures that fulfill needs i.e. consider other structures as well
//////////////////////////////////////////////////

	foreach($config['structures']['FulfillNeed'] as $num => $name)
	{
		$value = (buildingfactor($effectiveStructures[$users['num']][$num])) * $config['structures']['FulfillNeed'][$num];
		$total += $value;
		$averages[$num] = $value;
		$averagetotal += 1;
	}
	$average = $total / $averagetotal + 1;
	echo "average order benefit: " . $average;
	
	foreach($config['structures']['FulfillNeed'] as $num => $name)
	{
		$Type		= 	$config['structures']['FulfillNeedCategory'][$num];
		$Benefit 	= 	0 + (buildingfactor($effectiveStructures[$users['num']][$num]) * $config['structures']['FulfillNeed'][$num] * $average * $config['order_structures'])/120;
		if($Benefit >= 0)
		{
			$Formula = $Benefit;
			$Positive	= 	true;
		}
		else
		{
			$Formula = $Benefit * -1;
			$Positive	= 	false;
		}
		if($Benefit != 0)
			addPublicOrderFactor ($Type, $Formula, $Positive);
	}
	

//////////////////////////////////////////////////
// Factor: Good Opinion
//////////////////////////////////////////////////
	
	$Type		= 	'Patriotism';
	$Formula 	= 	0 + $users['goodopinion'] *  ($users['peasants'] * $config['PopValue'] / 150) * $config['order_patriotism'];
	$Positive	= 	true;

	addPublicOrderFactor ($Type, $Formula, $Positive);
	
//////////////////////////////////////////////////
// Factor: Size
//////////////////////////////////////////////////

	$Type		= 	'Political Unrest';
	$Formula 	= 	0 + ($users['land'] / 75) * ($users['peasants'] * $config['PopValue'] / 250)  * $config['order_size'];
	$Positive	= 	false;
	
	addPublicOrderFactor ($Type, $Formula, $Positive);

//////////////////////////////////////////////////
// Factor: Taxes
//////////////////////////////////////////////////

	$Type		= 	'Taxes';
	$Formula 	= 	0 + $users['tax'] * ($users['peasants'] * $config['PopValue'] / 150) * $config['order_taxes'];
	$Positive	= 	false;
	
	addPublicOrderFactor ($Type, $Formula, $Positive);

//////////////////////////////////////////////////
// Factor: Bad Opinion
//////////////////////////////////////////////////
	
	$Type		= 	'Shame';
	$Formula 	= 	0 + $users['badopinion'] *  ($users['peasants'] * $config['PopValue'] / 150) * $config['order_shame'];
	$Positive	= 	false;

	addPublicOrderFactor ($Type, $Formula, $Positive);
	
//////////////////////////////////////////////////
// Factor: Unemployment / Unfilled Jobs
//////////////////////////////////////////////////

echo  "<Br>" . $employment['unemployed'];
	if($employment['unemployed'])
	{
		$Type		= 	'Unemployment';
		$Formula 	= 	0 + $employment['unemployed'] * $config['order_unemployment'] / 5;
		$Positive	= 	false;
		addPublicOrderFactor ($Type, $Formula, $Positive);
	}
	elseif($employment['workersneeded'])
	{
		$Type		= 	'Unfilled Jobs';
		$Formula 	= 	0 + $employment['workersneeded'] * $config['order_workersneeded'] / 5;
		$Positive	= 	false;
		addPublicOrderFactor ($Type, $Formula, $Positive);
	}
	
}

function addPublicOrderFactor($factor, $factor_value, $positive)
{
	global $publicorder;

	$publicorder['Total'] += $factor_value;
	$publicorder['Factors'][$factor] = $factor_value;

	if($positive)
	{
		$publicorder['positives'] += $factor_value;
		$publicorder['positivefactors'][$factor] = $factor_value;
	}
	else
	{
		$publicorder['negatives'] += $factor_value;
		$publicorder['negativefactors'][$factor] = $factor_value;
	}
}

function getPublicOrder()
{
	global $publicorder;
	
	foreach($publicorder['positivefactors'] as $factor => $value)
	{
		// Convert to percent
		$publicorder['positivefactors'][$factor] /= $publicorder['Total'];
		$publicorder['positivefactors'][$factor] *= 100;
	}
	foreach($publicorder['negativefactors'] as $factor => $value)
	{
		// Convert to percent
		$publicorder['negativefactors'][$factor] /= $publicorder['Total'];
		$publicorder['negativefactors'][$factor] *= 100;
	}
	$publicorder['positives'] /= $publicorder['Total'];
	$publicorder['positives'] *= 100;
	$publicorder['negatives'] /= $publicorder['Total'];
	$publicorder['negatives'] *= 100;
}

function calcPublicOrder()
{
	global $publicorder;
	
	generateFactors();
	getPublicOrder();
	
	$order = $publicorder['positives'];
	if ($order > 100)
		$order = 100;
	if ($order < 0)
		$order = 0;

	return round($order);
}



// The new "percentage-based" algorithm
// Add up all the points, and then reduce them to a denominator of 100
// Point are allocated to:
// + Troops * Troop order bonus (each)
// + Buildings (e.g. defenses, academies)
// + Awe (from scripts only)
// - Taxes
// - Lack of infrastructure (housing/sanitation)
// - Lack of food
// - Shame (from scripts only)
function calculatePopulaceLoyalty($args){
	global $users, $urace, $config, $size, $food;
	$size = calcSizeBonus($users[networth]);
	 
}

?>
