#! /usr/bin/env php -q
<?php

// 3500 mg Potassium (Min), 4700 mg Potassium (Max)
// 1200 mg Sodium (Min), 2000 mg Sodium (Max)
// 25 g Fiber (Min), 35 g Fiber (Max)

// TODO
// Freshness (Days Fresh)
// Cost (Local, Record)
// Store in CSV Until Schema
// Suggest Calories Based Upon Exercises Done
// List Exercises By Type / Time of Day
// Set Goals in Terms of Run Time, Swimming Laps, Etc
// Body Fat Weigh In
// Allergens
// Likes / Dislikes
// Select Most/Moderately/Least Often Weighting on Food
// Sleep Tracking
// Breakdown by Phase of LL's Book

// 6% Total
$pre_morning_workout_snack_percentage = 0.06;
// 6% Total
$post_morning_workout_snack_percentage = 0.06;
// 23% Total
$breakfast_meal_percentage = 0.23;
// 5% Total
$mid_morning_snack_percentage = 0.05;
// 20% Total
$lunch_meal_percentage = 0.20;
// 5% Total
$mid_afternoon_snack_percentage = 0.05;
// 6% Total
$post_workout_snack_percentage = 0.06;
// 24%
$dinner_meal_percentage = 0.24;
// 5% Total
$evening_snack_percentage = 0.05;

// 6% Total
$pre_morning_workout_snack = null;
// 6% Total
$post_morning_workout_snack = null;
// 23% Total
$breakfast_meal = null;
// 5% Total
$mid_morning_snack = null;
// 20% Total
$lunch_meal = null;
// 5% Total
$mid_afternoon_snack = null;
// 6% Total
$post_workout_snack = null;
// 24%
$dinner_meal = null;
// 5% Total
$evening_snack = null;

// Food Lists By Time
$pre_morning_workout_snacks = array();
$post_morning_workout_snacks = array();
$breakfast_meals = array();
$mid_morning_snacks = array();
$lunch_meals = array();
$mid_afternoon_snacks = array();
$post_afternoon_workout_snacks = array();
$dinner_meals = array();
$evening_snacks = array();
$post_workout_snacks = array();

// Food Lists By Type
$meals = array();
$snacks = array();
$meats = array();
$vegetables = array();
$carbs = array();
$liquid = array();
$vitamins = array();
$supplements = array();

function calculate_bmr_men( $weight_in_lbs, $height_in_inches, $age_in_years, $gender = 'male' ) {
	$retVal = 0;

	// Women: BMR = 655 + ( 4.35 x weight in pounds ) + ( 4.7 x height in inches ) - ( 4.7 x age in years )
	// Men: BMR = 66 + ( 6.23 x weight in pounds ) + ( 12.7 x height in inches ) - ( 6.8 x age in year )
	$retVal = ( 66 + (6.23 * $weight_in_lbs) + (12.7 * $height_in_inches) - ( 6.8 * $age_in_years ) );

	// TODO Add a lean multiplier
	// TODO Add a alcohol multiplier
	// TODO Add a obese multiplier
	return $retVal;
}

/**
 * Katch-McArdle Formula (BMR): P = 370 + (21.6*LBM),
 * where LBM is the lean body mass in kg.
 */
function calculate_bmr_km( $weight_in_lbs, $height_in_inches, $age_in_years, $bf ) {
	$retVal = 0;

	$lean_mass = ((1 - ($bf/100)) * $weight_in_lbs) / 2.20462262;

	$retVal = 370 + ( 21.6* $lean_mass );

	// TODO Add a lean multiplier
	// TODO Add a alcohol multiplier
	// TODO Add a obese multiplier
	return $retVal;
}

/**
 * Cunningham Formula (RMR): P = 500 + (22*LBM),
 * where LBM is the lean body mass in kg.
 */
function calculate_bmr_cunningham( $weight_in_lbs, $height_in_inches, $age_in_years, $bf ) {
	$retVal = 0;

	$lean_mass = ((1 - ($bf/100)) * $weight_in_lbs) / 2.20462262;

	$retVal = 500 + ( 22* $lean_mass );

	// TODO Add a lean multiplier
	// TODO Add a alcohol multiplier
	// TODO Add a obese multiplier
	return $retVal;
}

/*
There are approximately 3500 calories in a pound of stored body fat. So, if you create a 3500-calorie deficit through diet, exercise
or a combination of both, you will lose one pound of body weight. (On average 75% of this is fat, 25% lean tissue) If you create a 7000
calorie deficit you will lose two pounds and so on. The calorie deficit can be achieved either by calorie-restriction alone, or by a
combination of fewer calories in (diet) and more calories out (exercise). This combination of diet and exercise is best for lasting
weight loss. Indeed, sustained weight loss is difficult or impossible without increased regular exercise.

If you want to lose fat, a useful guideline for lowering your calorie intake is to reduce your calories by at least 500, but not more
than 1000 below your maintenance level. For people with only a small amount of weight to lose, 1000 calories will be too much of a deficit.
As a guide to minimum calorie intake, the American College of Sports Medicine (ACSM) recommends that calorie levels never drop below 1200
calories per day for women or 1800 calories per day for men. Even these calorie levels are quite low.

An alternative way of calculating a safe minimum calorie-intake level is by reference to your body weight or current body weight. Reducing
calories by 15-20% below your daily calorie maintenance needs is a useful start. You may increase this depending on your weight loss goals.
*/

function calculate_rates( $weight, $bmr, $profile_info = null, $method = 'hbe' ) {
	if ( $method === 'hbe' ) {
		calculate_rates_hbe( $weight, $bmr, $profile_info );
	} else if ( $method === 'scooter' ) {
		calculate_rates_scooter( $weight, $bmr, $profile_info );
	}
}

function calculate_rates_hbe( $weight, $bmr, $profile_info = null ) {
	$multipliers = array(
		// array('multiplier' => 1.20, 'description' => 'If you are sedentary (little or no exercise)'),
		// array('multiplier' => 1.375, 'description' => 'If you are lightly active (light exercise/sports 1-3 days/week)'),
		array('multiplier' => 1.55, 'description' => 'If you are moderatetely active (moderate exercise/sports 3-5 days/week)'),
		array('multiplier' => 1.725, 'description' => 'If you are very active (hard exercise/sports 6-7 days a week)'),
		// array('multiplier' => 1.90, 'description' => 'If you are extra active (very hard exercise/sports & physical job or 2x training)')
	);

	$base_deficit = 363.64; // Tune this as desired
	$base_surplus = 225.81; // Tune this as desired

	if ( isset($profile_info) ) {
		echo $profile_info['age'] . ' Year Old, ' . $profile_info['height'] . ' '. $profile_info['gender_label'] . ' @ ';
	}

	echo $weight . ' lbs' . "\n\n";

	foreach ( $multipliers as $multiplier_info ) {
		$multiplier = $multiplier_info['multiplier'];
		$description = $multiplier_info['description'];

		$formatted_multiplier = sprintf("%01.3f", $multiplier);
		$calorie_needs = round( ( $bmr*$multiplier ), 2 );
		$calorie_deficit = round( $base_deficit * $multiplier, 2 );
		$calorie_deficit_target = round( ($calorie_needs - $calorie_deficit), 2 );

		$calorie_surplus = round( $base_surplus * $multiplier, 2 );
		$calorie_surplus_target = round( ($calorie_needs + $calorie_surplus), 2 );

		echo "\t" . $description . ":\n\n";
		echo "\t" . round($bmr, 2) . ' BMR x ' . $formatted_multiplier . ' = ' . sprintf("%01.2f", $calorie_needs) . " Calories\n";

		echo "\t   " . 'Lose: ' . $calorie_deficit_target . ' Calories (-' . $calorie_deficit . ' Calories)' . "\n";
		echo "\t      " . 'Protein: ' . round( ($calorie_deficit_target * 0.45), 2 ) . ' (' . round( ( ($calorie_deficit_target * 0.45)/4 ), 2 ) . 'g)' . "\n";
		echo "\t      " . 'Carb: ' . round( ($calorie_deficit_target * 0.35), 2 ) . ' (' . round( ( ($calorie_deficit_target * 0.35)/4 ), 2 ) . 'g)' . "\n";
		echo "\t      " . 'Fat: ' . round( ($calorie_deficit_target * 0.20), 2 ) . ' (' . round( ( ($calorie_deficit_target * 0.20)/9 ), 2 ) . 'g)' . "\n";

		echo "\t   " . 'Maintain: ' . $calorie_needs . " Calories\n";
		echo "\t      " . 'Protein: ' . round( ($calorie_needs * 0.35), 2 ) . ' (' . round( ( ($calorie_needs * 0.45)/4 ), 2 ) . 'g)' . "\n";
		echo "\t      " . 'Carb: ' . round( ($calorie_needs * 0.40), 2 ) . ' (' . round( ( ($calorie_needs * 0.35)/4 ), 2 ) . 'g)' . "\n";
		echo "\t      " . 'Fat: ' . round( ($calorie_needs * 0.25), 2 ) . ' (' . round( ( ($calorie_needs * 0.20)/9 ), 2 ) . 'g)' . "\n";

		echo "\t   " . 'Gain: ' . $calorie_surplus_target . ' Calories (+' . $calorie_surplus . ' Calories)' . "\n";
		echo "\t      " . 'Protein: ' . round( ($calorie_surplus_target * 0.42), 2 ) . ' (' . round( ( ($calorie_surplus_target * 0.45)/4 ), 2 ) . 'g)' . "\n";
		echo "\t      " . 'Carb: ' . round( ($calorie_surplus_target * 0.38), 2 ) . ' (' . round( ( ($calorie_surplus_target * 0.35)/4 ), 2 ) . 'g)' . "\n";
		echo "\t      " . 'Fat: ' . round( ($calorie_surplus_target * 0.20), 2 ) . ' (' . round( ( ($calorie_surplus_target * 0.20)/9 ), 2 ) . 'g)' . "\n";

		echo "\n\n";
	}

	echo "\n";
}

/**
 * "I like to use the simple calculation of your body weight * 10 to lose weight, your body weight * 12.5
 * to maintain weight, and your body weight * 15 to gain weight." - Scooter
 *
 */
function calculate_rates_scooter( $weight, $bmr, $profile_info = null ) {
	$multipliers = array(
		1
	);

	if ( isset($profile_info) ) {
		echo $profile_info['age'] . ' Year Old, ' . $profile_info['height'] . ' '. $profile_info['gender_label'] . ' @ ';
	}

	echo $weight . ' lbs' . "\n\n";

	foreach ( $multipliers as $multiplier ) {
		$formatted_multiplier = sprintf("%01.3f", $multiplier);
		$calorie_needs = round( ( $weight * 12.5 ), 2 );

		$calorie_deficit_target = round( ($weight * 10), 2 );
		$calorie_deficit = $calorie_needs - $calorie_deficit_target;

		$calorie_surplus_target = round( ($weight * 15), 2 );
		$calorie_surplus = $calorie_surplus_target - $calorie_needs;

		echo "\t" . 'Lose: ' . $calorie_deficit_target . ' Calories (-' . $calorie_deficit . ' Calories)' . "\n";
		echo "\t   " . 'Protein: ' . round( ($calorie_deficit_target * 0.45), 2 ) . ' (' . round( ( ($calorie_deficit_target * 0.45)/4 ), 2 ) . 'g)' . "\n";
		echo "\t   " . 'Carb: ' . round( ($calorie_deficit_target * 0.35), 2 ) . ' (' . round( ( ($calorie_deficit_target * 0.35)/4 ), 2 ) . 'g)' . "\n";
		echo "\t   " . 'Fat: ' . round( ($calorie_deficit_target * 0.20), 2 ) . ' (' . round( ( ($calorie_deficit_target * 0.20)/9 ), 2 ) . 'g)' . "\n";

		echo "\t" . 'Maintain: ' . $calorie_needs . " Calories\n";
		echo "\t   " . 'Protein: ' . round( ($calorie_needs * 0.35), 2 ) . ' (' . round( ( ($calorie_needs * 0.45)/4 ), 2 ) . 'g)' . "\n";
		echo "\t   " . 'Carb: ' . round( ($calorie_needs * 0.40), 2 ) . ' (' . round( ( ($calorie_needs * 0.35)/4 ), 2 ) . 'g)' . "\n";
		echo "\t   " . 'Fat: ' . round( ($calorie_needs * 0.25), 2 ) . ' (' . round( ( ($calorie_needs * 0.20)/9 ), 2 ) . 'g)' . "\n";

		echo "\t" . 'Gain: ' . $calorie_surplus_target . ' Calories (+' . $calorie_surplus . ' Calories)' . "\n";
		echo "\t   " . 'Protein: ' . round( ($calorie_surplus_target * 0.42), 2 ) . ' (' . round( ( ($calorie_surplus_target * 0.45)/4 ), 2 ) . 'g)' . "\n";
		echo "\t   " . 'Carb: ' . round( ($calorie_surplus_target * 0.38), 2 ) . ' (' . round( ( ($calorie_surplus_target * 0.35)/4 ), 2 ) . 'g)' . "\n";
		echo "\t   " . 'Fat: ' . round( ($calorie_surplus_target * 0.20), 2 ) . ' (' . round( ( ($calorie_surplus_target * 0.20)/9 ), 2 ) . 'g)' . "\n";

		echo "\n";
	}

	echo "\n";
}

// Example Usage:

$bmr_array = array();

// 5' 5", 28 Year Old Male
// 5' 3.75" = 60 + 3.75 = 63.75
// $height = 63.75;
$height = 65;
$age = 28;
$bf_percent = 9.45;
$bf_fraction = $bf_percent / 100;
$current_weight = 158.6;
$current_fat_in_lbs = ($bf_fraction) * $current_weight;

for( $weight = $current_weight; $weight >= 151; $weight-- ) {
	// $bmr_array[$weight] = calculate_bmr_men( $weight, $height, $age );
	$bmr_array[$weight] = calculate_bmr_km( $weight, $height, $age, $bf_percent );
	// $bmr_array[$weight] = calculate_bmr_cunningham( $weight, $height, $age, 17.8 );
	break;
}

foreach( $bmr_array as $weight => $bmr_value ) {
	calculate_rates( $weight, $bmr_value, array('gender_label' => 'Male', 'height' => '5\' 5"', 'age' => $age ), 'hbe' );
}

for( $target_bf_percent = 6; $target_bf_percent <= 9; $target_bf_percent += 1 ) {
	$target_bf_fraction = $target_bf_percent / 100;
	$target_loss = -1 * (($current_weight * $target_bf_fraction) + (-1 * $current_fat_in_lbs));
	$target_weight = $current_weight - $target_loss;
	$total_weeks = $target_loss / 1.4;

	echo 'For ' . $target_bf_percent . '% Body Fat: Lose ' . $target_loss . ' lbs ';
	echo '(Final Weight: ' . $target_weight . ') (Weeks Out: ' . $total_weeks . ')';
	echo "\n";
}

echo "\n";


