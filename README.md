b8man
=====

PHP script for calculating basal metabolic rate and extrapolating calorie needs and dietary splits based upon individual needs and activity.

Features:
====
Katch-McArdle Formula (BMR): P = 370 + (21.6*LBM), where LBM is the lean body mass in kg

Cunningham Formula (RMR): P = 500 + (22*LBM), where LBM is the lean body mass in kg.

Harris-Benedict Equation (BMR):
	Men	BMR = 88.362 + (13.397 x weight in kg) + (4.799 x height in cm) - (5.677 x age in years)
	Women	BMR = 447.593 + (9.247 x weight in kg) + (3.098 x height in cm) - (4.330 x age in years)

Usage
====
First version is a set of functions that need to be polished up and plugged into a cleaner interface.

To use, open b8man.php and go down to the section prefaced with "// Example Usage:"

Edit the following values to match your own needs:

$height = 65;
$age = 28;
$bf_percent = 9.45;
$current_weight = 158.6;

This code is not production ready, but should be fun for those curious about their dietary needs.

Inspiration
====

Christian Bale's diet & workout regiment for American Psycho & LL Cool J's Platinum Workout
