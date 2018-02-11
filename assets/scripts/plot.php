<?php

	$DEBUG = 0;

	$backR = "run_mdp.R" ;
	
	$expressionDataFile = "edata.tsv" ;
	
	$phenotypicDataFile = "pdata.tsv" ;
	
	$parameter = $_REQUEST['class'];
	
	$statisticsAverage = strtolower($_REQUEST['stats']);
	
	$standartDeviation = $_REQUEST['stan'];
	
	$topPertubedGenes = $_REQUEST['average'];
	
	$dataDir = "../../data/";
	$execDir = $dataDir . $_REQUEST['exec'];


	// R need variable HOME defined for user www-data
	putenv("HOME=/tmp");
	
	exec("Rscript " . $backR . " " . $expressionDataFile . " " . $phenotypicDataFile . " " . $parameter . " " . $statisticsAverage . " " . $standartDeviation . " " . $topPertubedGenes . " " . $execDir);

	if ($DEBUG) {

		echo "Rscript " . $backR . " " . $expressionDataFile . " " . $phenotypicDataFile . " " . $parameter . " " . $statisticsAverage . " " . $standartDeviation . " " . $topPertubedGenes . " " . $execDir . "<br><br>";

		echo "Parameter: " . $parameter . "<br>";
		echo "StatisticsAverage: " . $statisticsAverage . "<br>";
		echo "StandartDeviation: " . $standartDeviation . "<br>";
		echo "TopPertubedGenes: " . $topPertubedGenes . "<br>";
	}

	include ($execDir . "/plot1.html");

	include ($execDir . "/plot2.html");

	include ($execDir . "/plot3.html");

	include ($execDir . "/plot4.html");

?>