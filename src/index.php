<?php
date_default_timezone_set('Europe/Prague') ;
ini_set('display_errors','on');
ini_set('error_reporting', E_ALL & ~E_STRICT & ~E_DEPRECATED);


require_once('sys.php');
$loader = require __DIR__ . '/vendor/autoload.php';
#$loader->add('', __DIR__.'/src/');


$configurator = new Nette\Configurator;
$configurator->setDebugMode(true);
$configurator->enableDebugger(__DIR__ . '/log');
$configurator->setTempDirectory(__DIR__ . '/temp');

\Tracy\Debugger::$email = 'pcirman@pre.cz';
#\Tracy\Debugger::$maxDepth = 10; // default: 3


ini_set('error_reporting', E_ALL & ~E_STRICT & ~E_DEPRECATED); // je nutno znova,m jinak nette debugger ukazuje stricty, pac si to sam prenastavi

$latte = new Latte\Engine;
$latte->setTempDirectory('temp');

/*
$parameters['items'] = ['one', 'two', 'three'];
$latte->render('templates/template.latte', $parameters);
#$html = $latte->renderToString('template.latte', $parameters);
#*/

function generate($lidi) {

$jizda = 3;
$kola = 6;
$out = '';

for ($i=1;$i<=$jizda;++$i) {

foreach($lidi as $clovek=>$data) {
	$out .= "[$clovek]\n";
	for ($j=1;$j<=$kola;++$j) {
		$out.="$j = 1:".rand(19,58).'.'.rand(1,999)."\n";
	}
	$out.="\n";
}
$file = "jizda$i.ini";
system("rm -f $file");
file_put_contents($file, $out, FILE_APPEND | LOCK_EX);
system("chmod 664 $file");

}

exit;
}


$engine = new \biibtech\tdc\Engine(__DIR__);
exit;

#generate($lidi);
$jizdy1 = $j[0];
$jizdy2 = $j[1];
#var_dump($jizdy2);exit;

#


function getLidi($data) {
	$lidi = array();
	foreach($data as $jizda=>$kola) {
		foreach($kola as $kolo=>$lide) {
			foreach($lide as $clovek=>$cas) {
				$lidi[$clovek]=1;
			}
		}
	}
	return array_keys($lidi);
}

function getSameLidi($data) {
	$lidi = array();
	$tmp = array();
	foreach($data as $jizda=>$kola) {
		foreach($kola as $kolo=>$lide) {
			foreach($lide as $clovek=>$cas) {
				$tmp[$jizda][$clovek]=1;
			}
		}
	}
	foreach($tmp as $jizda=>$lide) {
		$lidi[]=array_keys($lide);
	}
	$result = call_user_func_array('array_intersect',$lidi);
	return $result;
}


function getZaba($data, $lvi) {
	$pozice=array();
	$pocet_jizd = count(array_keys($data));
	$lidi = getSameLidi($data);
	for ($i=1;$i<=$pocet_jizd;++$i) {
		$pozice[$i] = getRacePositions($data[$i]);
	}
	$pozice_cloveka = array();
	foreach ($pozice as $i=>$poradi) {
		$j = 1;
		foreach ($poradi as $clovek=>$cas) {
			if (in_array($clovek, $lidi)) {
				if (!isset($pozice_cloveka[$clovek])) $pozice_cloveka[$clovek] = array(); 
				$pozice_cloveka[$clovek][]=$j;
			}
			++$j;
		}
	}
	$poradi_body=array();
	foreach($pozice_cloveka as $clovek=>$por) {
		$i = 1;
		foreach($por as $_por) {
			if ($i>1) {
				$diff = $_por-$previous;
				if (!isset($poradi_body[$clovek])) $poradi_body[$clovek] = 0; 
				if ($diff<0) $poradi_body[$clovek]+=1;
			}
			$previous = $_por;
			++$i;
		}
	}

	$objs = array();
	foreach($poradi_body as $clovek=>$body) {
		$x = new Stdclass;
		$x->body = $body;
		$x->clovek = $clovek;
		$i = 1;
		foreach ($lvi as $_clovek=>$cas) {
			if ($_clovek==$clovek) {
				$x->lvi_poradi = $i;
				break;
			}
			++$i;
		}
		$objs[$clovek]=$x;
	}
	sort_frogs($objs, array('body','lvi_poradi'));
	$tmp = array();
	foreach($objs as $x) {
#		print $x->clovek."<br>";
		$tmp[$x->clovek]=$x->body;
	}
	#arsort($poradi_body);
	return $tmp;

}

function getZralok($data1, $data2) {
	$tmp = array();
	$lidi = getLidi($data2);
	foreach($lidi as $clovek) {
		$tmp[$clovek]=getTimeDiffs($clovek, $data1);
	}
	asort($tmp);
	return $tmp;
}

function getTimeDiffs($_clovek,$data) {
	$sum = array();
	foreach($data as $jizda=>$kola) {
		$casy=array();
		foreach($kola as $clovek=>$kola) {
			if ($clovek == $_clovek) {
				foreach($kola as $kolo=>$cas) {
					$casy[]=$cas;
				}
			}
		}
		$diffs = array();
		$i = 1;
		foreach($casy as $c) {
			if ($i>1) {
				$diff = abs($c-$prev);
				$diffs[]=$diff;
			}
			$prev = $c;
			++$i;
		}
		asort($diffs);
		array_pop($diffs);
		$sum[$jizda]=array_sum($diffs);
	}
	$_sum = array_sum($sum);
	return $_sum;
}

function getMyBestLapTime($clovek, $kolo, $jizda, $data) {
	$jizda = $data[$jizda];
	$casy = $jizda[$clovek];
	sort($casy);
	return array_shift($casy);
}


function getBestRaceTime($data) {
	$_cas = 99999;
	foreach($data as $kolo=>$lide) {
		foreach($lide as $clovek=>$cas) {
			if ($_cas>$cas) $_cas=$cas;
		}
	}
	return $_cas;

}

function getBestLapTime($data) {
	$_cas = 99999;
	foreach($data as $clovek=>$cas) {
		if ($_cas>$cas) $_cas=$cas;
	}
	return $_cas;

}

function getRacePositions($data) {
	$tmp = array();
	foreach($data as $kolo=>$lide) {
		foreach($lide as $clovek=>$cas) {
			if (!isset($tmp[$clovek])) $tmp[$clovek] = $cas; 
			if ($tmp[$clovek]>$cas) $tmp[$clovek]=$cas;
		}
	}
	asort($tmp);
	return $tmp;
}

function getOpice($data) {
#	var_dump($data);
	$poradi = array();
	foreach($data as $jizda=>$kola) {
		foreach($kola as $kolo=>$lide) {
			asort($lide);
			$i = 1;
			foreach($lide as $clovek=>$cas) {
				$poradi[$clovek][]=$i;
				++$i;
			}
		}
	}
	foreach($poradi as $clovek=>$por) {
		$i = 1;
		foreach($por as $_por) {
			if ($i>1) {
				$diff = $_por-$previous;
				if (!isset($poradi_body[$clovek])) $poradi_body[$clovek] = 0; 
				if ($diff<0) $poradi_body[$clovek]+=1;
			}
			$previous = $_por;
			++$i;
		}
	}
	arsort($poradi_body);
	return $poradi_body;


}

function getBalvani($data) {
	$prumer = array();
	$prumer2 = array();

	foreach($data as $jizda=>$kola) {
		$tmp = array();
		foreach($kola as $kolo=>$lide) {
			foreach($lide as $clovek=>$cas) {
				if (!isset($tmp[$clovek])) {
					$tmp[$clovek]=array($cas);
				} else $tmp[$clovek][]=$cas;
			}
		}

		foreach ($tmp as $clovek => $casy) {
			natsort($casy);
			array_shift($casy);
			$tmp[$clovek]=$casy;
		}

		foreach ($tmp as $clovek => $casy) {
			$prumer[$clovek][$jizda] =  array_sum($casy)/count($casy);
		}

	}

	foreach($prumer as $clovek => $prumery) {
		$prumer2[$clovek] =  array_sum($prumery)/count($prumery);
	}

	arsort($prumer2);
	return $prumer2;
/*
	print "<pre>";
	print_r($prumer2);
	print_r($prumer);
	print "</pre>";
 */

}


function getLvi($data) {
	$prumer = array();
	$prumer2 = array();
	foreach($data as $jizda=>$kola) {
		$tmp = array();
		foreach($kola as $kolo=>$lide) {
			foreach($lide as $clovek=>$cas) {
				if (!isset($tmp[$clovek])) {
					$tmp[$clovek]=array($cas);
				} else $tmp[$clovek][]=$cas;
			}
		}

		foreach ($tmp as $clovek => $casy) {
			natsort($casy);
			array_pop($casy);
			$tmp[$clovek]=$casy;
		}

		foreach ($tmp as $clovek => $casy) {
			$prumer[$clovek][$jizda] =  array_sum($casy)/count($casy);
		}

	}

	foreach($prumer as $clovek => $prumery) {
		$prumer2[$clovek] =  array_sum($prumery)/count($prumery);
	}

	asort($prumer2);
	return $prumer2;
/*
	print "<pre>";
	print_r($prumer2);
	print_r($prumer);
	print "</pre>";
 */

}

function getZajici($data) {
	$tmp = array();

	foreach($data as $jizda=>$kola) {
		foreach($kola as $kolo=>$lide) {
			foreach($lide as $clovek=>$cas) {
				if (!isset($tmp[$clovek])) $tmp[$clovek]=$cas;
				if ($cas<$tmp[$clovek]) $tmp[$clovek]=$cas;
			}
		}
	}

	asort($tmp);
	return $tmp;
}

function getSneci($data) {
	$tmp = array();

	foreach($data as $jizda=>$kola) {
		foreach($kola as $kolo=>$lide) {
			foreach($lide as $clovek=>$cas) {
				if (!isset($tmp[$clovek])) {
					$tmp[$clovek]=$cas;
				}
				else if ($cas>$tmp[$clovek]) $tmp[$clovek]=$cas;
			}
		}
	}

	arsort($tmp);
	return $tmp;
}

function render($kat,$kategorie,$data,$lidi) {
echo "<td valign=top>

	<div class=\"panel panel-default\">
	  <!-- Default panel contents -->
	    <div class=\"panel-heading\"><span class=\"glyphicon glyphicon-flag\" aria-hidden=\"true\"></span> ".$kategorie[$kat]["title"]."</div>
<table class=\"table table-condensed\" width=\"400px\">
	<thead>
		<tr>
			<th>#</th>
			<th></th>
			<th>jméno</th>
		</tr>
	</thead>
	<tbody>";
$i = 0;
foreach($data as $clovek=>$cas) {
	echo "
	<tr". ($i==0?" style=\"background-color: lightgreen\"":"").">
	<td valign=top>".($i+1)."</td>
		<td>
		<span class=\"badge\">".($kat=='zaba' || $kat=='opice'?round($cas):sec2time($cas))."</span></td><td>".$lidi[$clovek]['surname']."</td>
		</tr>";
	++$i;
}
echo "
	</tbody>
	</table>
	</div>
	</td>";
}


$zraloci = getZralok($jizdy1, $jizdy2);
$zajici = getZajici($jizdy2);
$opice = getOpice($jizdy2);
$balvani = getBalvani($jizdy2);
$lvi = getLvi($jizdy2);
$zaby = getZaba($jizdy2, $lvi);
$sneci = getSneci($jizdy2);

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<title>Výsledky</title>

		<!-- Bootstrap -->
		<link href="css/bootstrap.min.css" rel="stylesheet">

		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/bootstrap.min.js"></script>

<div align="center">
		<h1>Výsledky 15.6.2016</h1>
<div align="left">
<pre>
TODO

algoritmy:
// snek, balvan - vyhodit nejpomalejsi kolo v kazde jizde

informace:
// zobrazit prumery v jizde vedle "poradi v ramci jizdy"
// zobrazeni configu
// zobrazeni rekordu: 1. nejlepsi kolo osoby napric zavodama, 2. nejlepsi prumer  a nejmensi diff  osoby napric zavodama
</pre>
</div>
<h2>Pořadí v kategoriích</h2>
<table>
<tr>
<?php
foreach($kategorie as $kat=>$data) {
	print "<td valign=bottom><div class=\"panel panel-default\">
		  <div class=\"panel-body\">
		  ".$data['desc']."
			    </div>
				</div></td>";
}
?>
</tr>
<tr>
<?php
render('lev',$kategorie,$lvi,$lidi);
render('zajic',$kategorie,$zajici,$lidi);
render('zralok',$kategorie,$zraloci,$lidi);
render('zaba',$kategorie,$zaby,$lidi);
render('opice',$kategorie,$opice,$lidi);
render('snek',$kategorie,$sneci,$lidi);
render('balvan',$kategorie,$balvani,$lidi);
?>
</tr></table>
<h2>Podrobné výsledky</h2>


<table>
<tbody>
<?php
foreach($jizdy2 as $jizda=>$kola) {
?>
<tr>
<td>
jizda <?php echo $jizda; ?>&nbsp;
<?php
	foreach($kola as $kolo=>$lide) {
?>
<td valign=top>
    <div class="panel panel-default">
	<div class="panel-heading">kolo <?php echo $kolo ?></div>

<table  class="table table-condensed">
    <thead>
        <tr>
            <th>#</th>
            <th></th>
            <th>jméno</th>
        </tr>
    </thead>
<tbody>
<?php
		asort($lide);
		$i = 1;
		foreach($lide as $clovek=>$cas) {
			print "<tr".(getBestRaceTime($kola)==$cas?" style=\"background-color: green\"":(getBestLapTime($lide)==$cas?" style=\"background-color: lightgreen\"":""))."><td>$i</td><td><span class=\"badge\">".sec2time($cas)."</span></td><td>".$lidi[$clovek]['surname']."</td></tr>";
			++$i;
		}
?>
</tbody>
</table>
</div>
</td>
<?php
	}
?>
</td>
<td valign=top style="padding-left: 20px">
<?php
$racepositions = getRacePositions($jizdy2[$jizda]);
?>
    <div class="panel panel-default">
    <div class="panel-heading">poradi v ramci jizdy</div>

<table class="table table-condensed">
    <thead>
        <tr>
            <th>#</th>
            <th></th>
            <th>jméno</th>
        </tr>
    </thead>
<tbody>
<?php
$i = 1;
		foreach($racepositions as $clovek=>$cas) {
			print "<tr".($i==1?" style=\"background-color: green\"":"")."><td>$i</td><td><span class=\"badge\">".sec2time($cas)."</span></td><td>".$lidi[$clovek]['surname']."</td></tr>";
			++$i;
		}
?>
</tbody>
</table>
</div>

</td>

</tr>
<?php
}
?>

</tr>
</tbody>
</table>
<h2>Naměřené hodnoty dle člověka</h2>
<pre>
<table>
<?php
foreach(getLidi($jizdy2) as $clovek) {
	print "<tr>";
foreach($jizdy1 as $jizda=>$lide) {
	foreach($lide as $_clovek=>$casy) {
		if ($_clovek ==$clovek) {
?>
<td valign=top>
    <div class="panel panel-default">
	<div class="panel-heading"><?php echo $lidi[$clovek]['surname']?> - jizda <?php echo ($jizda+1)?></div>

<table class="table table-condensed">
    <thead>
        <tr>
            <th>#</th>
            <th></th>
        </tr>
    </thead>
<tbody>
<?php
$i = 1;
foreach($casy as $cas) {
			print "<tr". (getMyBestLapTime($clovek,$i,$jizda,$jizdy1)==$cas?" style=\"background-color: lightgreen\"":"")."><td>$i</td><td><span class=\"badge\">".sec2time($cas)."</span></td></tr>";
			++$i;
}
?>
</tbody>
</table>
</div>
</td>
<?php
		}
	}
}
echo "</tr>";
}
?>
</table>
</pre>

</div>

	</body>
</html>

