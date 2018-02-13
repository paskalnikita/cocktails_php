<?php
	session_start();
	header('Content-type: text/html; charset=utf-8');
	$connect_error = "Can't connect!";
	MYSQL_connect("Localhost","root","") or die(mysql_error());
	MYSQL_select_db("cocktails") or die ($connect_error);
	@mysql_query ("SET NAMES `UTF8`");
	mysql_query("SET NAMES 'utf8';");
	mysql_query("SET CHARACTER SET 'utf8';");
	mysql_query("SET SESSION collation_connection = 'utf8_general_ci';");

	function httpResponseCode($url) {
		$headers = get_headers($url);
		return substr($headers[0], 9, 3);
	}

	function arrayToObject($array){
		if(count($array)>0){
			foreach($array as $key => $value){
				if(is_array($value)){
					$array[$key] = arrayToObject($value);
					}
				}
			return (object)$array;
		}else{
			return FALSE;
		}
	}

$i=1;
while(true){
	$url = "http://jakidrink.pl/drink/get/".$i;
	$http_response_code = httpResponseCode($url);
		if($http_response_code == 200){
			$json = @file_get_contents($url);
			$cocktail = json_decode($json);
			$cocktail_id=$cocktail->id;
			$cocktail_name=$cocktail->name;
			$components_array = $cocktail->components;
			$cocktail_components = json_decode(json_encode($cocktail->components),True);
			foreach($cocktail_components as $component_info){
				$component_id = $component_info["id"];
				$component_name = $component_info["name"];
				$component_unitShortName = $component_info["unitShortName"];
				$component_typeName = $component_info["componentTypeName"];
				$component_volume = $component_info["volume"];
				// $component_unitName = $component_info["unitName"];
				// $component_link = $component_info["link"];
				// $component_description = $component_info["description"];
				$query=mysql_query("INSERT INTO cocktail(
													`cocktail_id`,
													`cocktail_name`,
																`component_id`,
																`component_name`,
																`component_unitShortName`,
																`component_typeName`,
																`component_volume`)
										VALUES(		'$cocktail_id',
													'$cocktail_name',
																'$component_id',
																'$component_name',
																'$component_unitShortName',
																'$component_typeName',
																'$component_volume')") or die(mysql_error());
			}
		}else{
			break;
		}
	$i++;
}

$query = mysql_query(" (SELECT `component_id`,`component_name`, COUNT(`component_id`)
					FROM `cocktail`
					WHERE (`component_typeName`='alkohol' OR `component_typeName`='likier')
					GROUP BY `component_id`
					ORDER BY COUNT(`component_id`) DESC LIMIT 2)
			UNION ALL
				(SELECT `component_id`, `component_name`, COUNT(`component_id`) FROM `cocktail`
					WHERE (	`component_typeName`<>'alkohol' AND
							`component_typeName`<>'likier' AND
							`component_unitShortName`<>'szt.')
					GROUP BY `component_id`
					ORDER BY COUNT(`component_id`) DESC LIMIT 3)") or die(mysql_error());

$five_components_ids = array();
$five_components_names = array();
while($components_list = mysql_fetch_assoc($query)){
	array_push($five_components_ids,$components_list['component_id']);
	array_push($five_components_names,$components_list['component_name']);
}

	echo "<div id='ids'>";
		echo "Ids of five most popular components:\n \n";
		foreach ($five_components_ids as &$component_id){
			echo "<br>".$component_id;
		}
	echo "</div>";

	echo "<div id='names'>";
		echo "Names of five most popular components:\n \n";
		foreach ($five_components_names as &$component_name){
				echo "<br>".$component_name;
			}
	echo "</div>";

$component_1= $five_components_ids['0'];
$component_2= $five_components_ids['1'];
$component_3= $five_components_ids['2'];
$component_4= $five_components_ids['3'];
$component_5= $five_components_ids['4'];

$query = mysql_query("SELECT `cocktail_name`
						FROM `cocktail`
						WHERE `component_id` IN (	'$component_1',
													'$component_2',
													'$component_3',
													'$component_4',
													'$component_5')
					") or die(mysql_error());
$all_cocktails_you_can_make=array();
while($all_five_comp_cocktails = mysql_fetch_assoc($query)){
	array_push($all_cocktails_you_can_make,$all_five_comp_cocktails['cocktail_name']);
}
$cocktails_you_can_make=array_unique($all_cocktails_you_can_make);
	echo "<div class='cocktails-list'>";
		echo "List of cocktails which you can make using 5 most popular components: ";
		foreach($cocktails_you_can_make as &$cocktail){
			echo "<br>".$cocktail;
		}
	echo "</div>";
?>
<style>
	#names{
		text-align:center;
		margin:5px;
		padding:15px;
		width:auto;
		background-color: #90c695;
		border-radius:5px;
	}

	#ids{
		text-align:center;
		margin:5px;
		padding:15px;
		width:auto;
		background-color: #8ba1d2;
		border-radius:5px;
	}

	#send{
		margin-bottom: 5px;
		border: none;
		border:solid 5px #0cff00;
		border-radius: 10px;
		padding: 5px;
		text-align: center;
		min-width: 205px;
		cursor: pointer;
	}

	.cocktails-list{
		text-align:center;
		margin:5px;
		width: 250px;
		padding:15px;
		width:auto;
		background-color: #7bb3ff;
		border-radius:5px;
	}
</style>