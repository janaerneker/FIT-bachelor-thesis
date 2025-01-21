<!DOCTYPE html>
<html>
<head>
<?php 
	include 'configure.php';
 ?>
<!-- Převzato z veřejného archivu: http://www.iconarchive.com/show/ids-space-lab-icons-by-iron-devil.html -->
<link rel="icon" href="img/favicon.ico" type="image/x-icon"/>
<meta charset="UTF-8"/>
<title>Analýza náhodné veličiny | OpenCPUvsSageCellServer</title>
<script type="text/javascript" src="jquery-1.11.2.js"></script>

<!-- For Sage -->
<script src="https://sagecell.sagemath.org/static/jquery.min.js"></script>
<script src="https://sagecell.sagemath.org/static/embedded_sagecell.js"></script>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css"/>

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>

<script>

//manipulace se zalozkami
$(document).ready(function(){
        activaTab('rproject');
       
    });

    function activaTab(tab){
        $('.nav-tabs a[href="#' + tab + '"]').tab('show');
    };

    function activaRPill(tab){
    	$('.nav-pills a[href=#' + tab + '"]').tab('show');
    };

//for sage
$(function () {
    // Make *any* div with class 'compute' a Sage cell
    sagecell.makeSagecell({inputLocation: 'div.compute',
                          template: sagecell.templates.minimal,
                          autoeval: true,
                      	  hide: ["evalButton",],
                      	  languages: ["sage"],
                      	});
    });

//autoeval - automaticke vyhodnoceni kodu bunky pri nacteni stranky
//hide: evalButton - skryje vyhodnocovaci tlacitko
</script>
</head>
<body>
<?php
	
$dataId = $_GET["dataId"];
$tablename = "table".$dataId;
$colId = $_GET["colId"];
$filepath = "http://webdev.fit.cvut.cz/~ernekjan/uploads/data".$dataId."_col".$colId.".csv";

//navigace
echo "<div id=\"blackBar\"><ol class=\"navigation\">
<li class=\"pageTitle\">OpenCPU vs. Sage Cell Server</li>
<li><a href=\"index.php\">Zvolit jiný projekt</a></li>
<li><a href=\"project.php?id=$dataId\">Zvolit jiný sloupec dat</a></li>
</ol></div>";


try
{	
	//pripojeni do databaze
	$conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT variable FROM `".$tablename."` WHERE id=".$colId;
    $stmt = $conn->prepare($query); 
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	foreach ($result as $key=> $row){
        $header = $row['variable'];
    }
}
catch(PDOException $e)
{
	echo "Connection failed: " . $e->getMessage();
}

?>
<!-- zalozky -->
<div id="wrap">
<ul class="nav nav-tabs">
	<li><a href="#rproject" data-toggle="tab">R</a></li>
	<li><a href="#sagemath" data-toggle="tab">Sage</a></li>
</ul>
<div class="tab-content" id="tabs">
	
	<div class="tab-pane" id="sagemath">

<?php echo "<h1 class=\"header\"> ".$header." </h1>"; ?>

<h3>Základní charakteristiky</h3>
<div class="compute">
	<script type="text/x-sage">
import urllib2
import numpy as np

url = '<?php Print($filepath); ?>'
values = urllib2.urlopen(url)

headerFlag = 0
array = []

for line in values:
	if headerFlag==0:
		#print line
		headerFlag=1
	else:
		if len(line.strip())!=0:
			array.append(float(line))

median = np.median(array)
arithMean = np.mean(array)
tmpSampleVariance = np.var(array)
sampleVariance = float(len(array))/(float(len(array))-1)*tmpSampleVariance
standardDeviation = sqrt(sampleVariance)

print "Medián: %.3f" % (median)
print "Výběrový průměr: %.3f" % (arithMean)
print "Výběrový rozptyl: %.3f" % (sampleVariance)
print "Výběrová směrodatná odchylka: %.3f" % (standardDeviation)
	</script>
</div>
<h3>Vykreslit graf: </h3>
<ul class="nav nav-pills" id="SagePills">
	  	<li><a href="#histSage" data-toggle="tab">Histogram</a></li>
		<li><a href="#edfSage" data-toggle="tab">EDF</a></li>
		<li><a href="#qqplotSage" data-toggle="tab">Q-Q plot</a></li>
	</ul>

	<div class="tab-content" id="tabs">
	    <div class="tab-pane" id="histSage" class="active">
	   <div class="compute">
	<script type="text/x-sage">
path = '<?php Print($filepath); ?>'
import urllib2
import matplotlib.pyplot as plt
import numpy as np
import scipy.stats as stats
url = '<?php Print($filepath); ?>'
values = urllib2.urlopen(url)
 
headerFlag = 0
array = []
 
for line in values:
 
    if headerFlag==0:
        #print line
        header = line
        headerFlag=1
    else:
        if len(line.strip())!=0:
            #print line
            array.append(float(line))

minArr=int(min(array))
maxArr=int(max(array))

nMean = np.mean(array)
lArr = len(array)
nSd = sqrt(((lArr-1)/lArr)*np.var(array))
npArr = np.array(array)


@interact
def _(sirka_binu=[1,2,3,4,5,6,7,8,9,10], normal = checkbox(False, "Normální"), exp = checkbox(False, "Exponenciální"), uniform = checkbox(False, "Rovnoměrné")):
	plt.clf()
	if normal:
		nFit = stats.norm.pdf(array, nMean, nSd) 
		plt.plot(array,nFit, color="darkblue")
	if exp:
		expFit = stats.expon.pdf(array,nMean)
		plt.plot(npArr,expFit, color="red")
	if uniform:
		unifFit = stats.uniform.pdf(array,minArr,maxArr)
		plt.plot(npArr,unifFit, color="darkgreen")

	plt.hist(array, bins=list(range((minArr-sirka_binu), (maxArr+sirka_binu), sirka_binu)), color="yellow", normed=True)
	plt.title(header)
	plt.xlabel("Hodnota")
	plt.ylabel("Pravdepodobnost")
	plt.show()
	</script>
</div>
</div>
<div class="tab-pane" id="edfSage">
	<div class="compute">
		<script type="text/x-sage">
import urllib2
import matplotlib.pyplot as plt
import numpy as np
import statsmodels.api as sm 
import scipy.stats as stats
colId = "<?php Print($colId); ?>"
dataId = "<?php Print($dataId); ?>"
url = '<?php Print($filepath); ?>'
values = urllib2.urlopen(url)

headerFlag = 0
array = []

for line in values:

	if headerFlag==0:
		#print line
		header = line
		headerFlag=1
	else:
		if len(line.strip())!=0:
			#print line
			array.append(float(line))

minArr=min(array)
maxArr=int(max(array))


nMean = np.mean(array)
lArr = len(array)
nSd = sqrt(((lArr-1)/lArr)*np.var(array))
#lines(dnorm(vec, mean=nMean, sd=nSd), col="darkblue")
#lambda = (1/np.mean(array))
npArr = np.array(array)


@interact
def _(normal = checkbox(False, "Normální"), exp = checkbox(False, "Exponenciální"), uniform = checkbox(False, "Rovnoměrné")):
	plt.clf()
	if normal:
		nFit = stats.norm.cdf(array, nMean, nSd) 
		plt.plot(array,nFit, color="darkblue")
	if exp:
		expFit = stats.expon.cdf(array, nMean)
		plt.plot(array,expFit, color="red")
	if uniform:
		unifFit = stats.uniform.cdf(array, minArr, maxArr)
		plt.plot(array,unifFit, color="darkgreen")

	ecdf = sm.distributions.ECDF(array)
	x = np.linspace(min(array), max(array))
	y = ecdf(x)
	plt.step(x, y)
	plt.title("ECDF")
	#plt.legend([header], prop={'size':10}, loc=2)
	plt.show()
		</script>
	</div>
</div>
<div class="tab-pane" id="qqplotSage">
	<div class="compute">
		<script type="text/x-sage">
import urllib2
import matplotlib.pyplot as plt
import numpy as np
import scipy.stats as stats

url = '<?php Print($filepath); ?>'
values = urllib2.urlopen(url)

headerFlag = 0
array = []

for line in values:

	if headerFlag==0:
		#print line
		header = line
		headerFlag=1
	else:
		if len(line.strip())!=0:
			#print line
			array.append(float(line))

minArr=min(array)
maxArr=int(max(array))


nMean = np.mean(array)
lArr = len(array)
nSd = sqrt(((lArr-1)/lArr)*np.var(array))

@interact
def _(rozdeleni=["normal","exponencial","uniform"]):
	plt.clf()
	if rozdeleni=="exponencial":
		stats.probplot(array, sparams=(array, nMean), dist='expon', plot=plt)
	else:
		if rozdeleni=="uniform":
			stats.probplot(array, sparams=(array, maxArr), dist='uniform', plot=plt)
		else:
			stats.probplot(array, sparams=(array, nMean), dist='norm', plot=plt)
	
	plt.title("Q-Q plot")
	plt.show()
		</script>
	</div> 
</div>
</div>

<h3>Oboustranný t-test o střední hodnotě &mu;</h3>

<div class="compute">
	<script type="text/x-sage">
from scipy import stats

import urllib2
url = '<?php Print($filepath); ?>'
values = urllib2.urlopen(url)
 

headerFlag = 0
array = []

for line in values:

	if headerFlag==0:
		#print line
		header = line
		headerFlag=1
	else:
		if len(line.strip())!=0:
			#print line
			array.append(float(line))

@interact
def _(nulova_hypoteza=input_box(default=0), alpha=Selector(["0.05", "0.03", "0.02", "0.01"], default="0.05", selector_type='list')):
	results = stats.ttest_1samp(array, nulova_hypoteza)
	pValue = results[1]
	if pValue < float(alpha):
		print "Hladina testu alpha: %.2f" % (float(alpha))
		print "Nulová hypotéza se ZAMÍTÁ."
		print "Alternativní hypotéza: střední hodnota veličiny není rovna %.f, p-hodnota testu: %f" % (nulova_hypoteza, pValue) 
	else:
		print "Hladina testu alpha: %.2f" % (float(alpha))
		print "Nulová hypotéza se NEZAMÍTÁ, p-hodnota testu: %f" % (pValue)
	</script>
</div>

<h3>Kolmogorov-Smirnovův test dobré shody</h3>

<div class="compute">
	<script type="text/x-sage">
from scipy import stats
import numpy as np

import urllib2
url = '<?php Print($filepath); ?>'
values = urllib2.urlopen(url)

headerFlag = 0
array = []

for line in values:

	if headerFlag==0:
		#print line
		header = line
		headerFlag=1
	else:
		if len(line.strip())!=0:
			#print line
			array.append(float(line))

minArr=min(array)
maxArr=max(array)

nMean = np.mean(array)
lArr = len(array)
nSd = sqrt(((lArr-1)/lArr)*np.var(array))

@interact
def _(rozdeleni=["normalni","exponencialni","rovnomerne"], alpha=Selector(["0.05", "0.03", "0.02", "0.01"], default="0.05", selector_type='list')):
	if rozdeleni=="exponencialni":
		arguments=(nMean,)
		distr='expon'
	else:
		if rozdeleni=="rovnomerne":
			arguments=(minArr,maxArr)
			distr='expon'
		else:
			arguments=(nMean,nSd)
			distr='norm'
		
	results = stats.kstest(array, distr, arguments, alternative='two-sided', mode='approx')
	pValue = results[1]

	if pValue < float(alpha):
		print "Hladina testu alpha: %.2f" % (float(alpha))
		print "Nulová hypotéza se ZAMÍTÁ."
		print "Alternativní hypotéza: veličina není z vybraného (%s) rozdělení, p-hodnota testu: %f" % (rozdeleni,pValue) 
	else:
		print "Hladina testu alpha: %.2f" % (float(alpha))
		print "Nulová hypotéza se NEZAMÍTÁ." 
		print "p-hodnota testu: %f" % (pValue)
	</script>
</div>
</div>
<div class="tab-pane" id="rproject">
	<div> 
 
<?php 

echo "<h1 class=\"header\"> ".$header." </h1>";

echo "<h3>Základní charakteristiky</h3>";

//cteni dat ze souboru
$functionurl = "https://ernekjan.ocpu.io/newPackage/R/readFile";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$functionurl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,"path=\"$filepath\"");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$result = curl_exec($ch);
curl_close($ch); 
$getpath = explode('/ocpu/', $result);
$tmpKey = explode('/', $getpath[1]);
$sessionKey = $tmpKey[1];


//vyberovy prumer
$functionurl = "https://ernekjan.ocpu.io/newPackage/R/callMean";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$functionurl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,"vec=$sessionKey");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$result = curl_exec($ch);
curl_close($ch); 
$getpath = explode('/ocpu/', $result);
$pathVal = "https://public.opencpu.org/ocpu/". $getpath[1]."/print";
$pathVal = preg_replace('/\s+/', '', $pathVal);
$mean = file_get_contents($pathVal);
$mean = preg_replace('/\[[1]]/', '', $mean);


//median
$functionurl = "https://ernekjan.ocpu.io/newPackage/R/callMedian";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$functionurl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,"vec=$sessionKey");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$result = curl_exec($ch);
curl_close($ch); 
$getpath = explode('/ocpu/', $result);
$pathVal = "https://public.opencpu.org/ocpu/". $getpath[1]."/print";
$pathVal = preg_replace('/\s+/', '', $pathVal);
$median = file_get_contents($pathVal);
$median = preg_replace('/\[[1]]/', '', $median);



//vyberovy rozptyl
$functionurl = "https://ernekjan.ocpu.io/newPackage/R/sampleVar";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$functionurl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,"vec=$sessionKey");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$result = curl_exec($ch);
curl_close($ch); 
$getpath = explode('/ocpu/', $result);
$pathVal = "https://public.opencpu.org/ocpu/". $getpath[1]."/print";
$pathVal = preg_replace('/\s+/', '', $pathVal);
$sampleVariance = file_get_contents($pathVal);
$sampleVariance = preg_replace('/\[[1]]/', '', $sampleVariance);


//vyberova smerodatna odchylka
$functionurl = "https://ernekjan.ocpu.io/newPackage/R/stDeviation";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$functionurl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,"vec=$sessionKey");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$result = curl_exec($ch);
curl_close($ch); 
$getpath = explode('/ocpu/', $result);
$pathVal = "https://public.opencpu.org/ocpu/". $getpath[1]."/print";
$pathVal = preg_replace('/\s+/', '', $pathVal);
$standardDeviation = file_get_contents($pathVal);
$standardDeviation = preg_replace('/\[[1]]/', '', $standardDeviation);

echo "<div class=\"characteristics\">";
echo "<p>Medián: " . $median ."</p>";
echo "<p>Výběrový průměr: " . $mean . "</p>";
echo "<p>Výběrový rozptyl: " . $sampleVariance."</p>";
echo "<p>Výběrová směrodatná odchylka: " . $standardDeviation."</p>";   
echo "</div>";
?>

<!--histogram-->
<h3>Histogram</h3>
<form id ="binsR" method="post">
    <fieldset>
			<p><select name="numberOfBins" class="styled-select" label="sirka_binu">
				<option disabled selected> -- zvolte šířku binu -- </option>
		      		<?php
			      		for ($i=1; $i <= 10; $i++) {
			      			$value = $i; 
			      			echo "<option value=\"".$value."\">".$value."</option>";
			      		}
		      		?>
			</select></p>
		<input type="submit" name="binsSubmit" value="Překreslit histogram" onsubmit="<?=$_SERVER['PHP_SELF']?>">
	</fieldset>
</form>



<?php 

if(isset($_POST['binsSubmit']) ){
	$bins = $_POST['numberOfBins'];
}else{	
  	$bins = 5;
}

$functionurl = "https://ernekjan.ocpu.io/newPackage/R/drawHist";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$functionurl);
curl_setopt($ch, CURLOPT_POST, 2);
curl_setopt($ch, CURLOPT_POSTFIELDS,"vec=$sessionKey&widthOfBin=$bins");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$result = curl_exec($ch);
curl_close($ch); 
$getpath = explode('/', $result);
$pathVal = "https://public.opencpu.org/ocpu/tmp/". $getpath[3]."/graphics/last/png"; 

// Read image path, convert to base64 encoding
$imageData = base64_encode(file_get_contents($pathVal));

// Format the image SRC:  data:{mime};base64,{data};
$src = 'data: '.mime_content_type($pathVal).';base64,'.$imageData;

// Echo out a sample image
echo '<img src="',$src,'">';


?>

<!--empiricka distribucni funkce-->
<h3>Graf empirické distribuční funkce</h3>
<?php
$functionurl = "https://ernekjan.ocpu.io/newPackage/R/edfPlot";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$functionurl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,"vec=$sessionKey");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$result = curl_exec($ch);
curl_close($ch); 
$getpath = explode('/', $result);
$pathVal = "https://public.opencpu.org/ocpu/tmp/". $getpath[3]."/graphics/last/png"; 
// Read image path, convert to base64 encoding
$imageData = base64_encode(file_get_contents($pathVal));
// Format the image SRC:  data:{mime};base64,{data};
$src = 'data: '.mime_content_type($pathVal).';base64,'.$imageData;
// Echo out a sample image
echo '<img src="',$src,'">';
?>

<h3>Q-Q plot</h3>	
<form id ="tTest" method="post">
    <fieldset>
			<p><select name="qqdistribution" class="styled-select">
				<option disabled selected> -- zvolte rozdělení -- </option>
		      		<option value="normal">Normální</option>
		      		<option value="uniform">Exponenciální</option>
		      		<option value="exponencial">Rovnoměrné</option>
			</select></p>
		<input type="submit" name="qqdistr" value="Překreslit graf" onsubmit="<?=$_SERVER['PHP_SELF']?>"/>
	</fieldset>
</form>

<?php

if(isset($_POST['qqdistr']) ){
	$distr = $_POST['qqdistribution'];
}else{
	$distr = "normal";
}

//Q-Q plot
$functionurl = "https://ernekjan.ocpu.io/newPackage/R/drawQQ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$functionurl);
curl_setopt($ch, CURLOPT_POST, 2);
curl_setopt($ch, CURLOPT_POSTFIELDS,"vec=$sessionKey&distribution=\"$distr\"");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$result = curl_exec($ch);
curl_close($ch); 
$getpath = explode('/', $result);
$pathVal = "https://public.opencpu.org/ocpu/tmp/". $getpath[3]."/graphics/last/png"; 

// Read image path, convert to base64 encoding
$imageData = base64_encode(file_get_contents($pathVal));

// Format the image SRC:  data:{mime};base64,{data};
$src = 'data: '.mime_content_type($pathVal).';base64,'.$imageData;

// Echo out a sample image
echo '<img src="',$src,'">';

?> 

<!--oboustranny t-test o stredni hodnote -->
<div class="testForm">
	<form id ="tTest" method="post">
	    <fieldset>
		    <legend>Oboustranný t-test o střední hodnotě &mu;</legend>
				<p>Nulová hypotéza H<sub>0</sub>: &mu;<sub>0</sub> = <input type="text" name="estimation" value="0" required></p>
				<p><select name="alphaTT" class="styled-select">
					<option disabled selected> -- zvolte hladinu významnosti &alpha; -- </option>
			      		<?php
				      		for ($i=0; $i < count($alpha); $i++){
				      			$value = $alpha[$i]; 
				      			echo "<option value=\"".$value."\">".$value."</option>";
				      		}
			      		?>
				</select></p>
			<input type="submit" name="hypothesis" value="Otestovat hypotézu" onsubmit="<?=$_SERVER['PHP_SELF']?>"/>
		</fieldset>
	</form>
</div>

<?php

$parAlpha = 0.05;
$mu0 = 0;
if(isset($_POST['hypothesis'])){
	$mu0 = $_POST['estimation'];
	
	$parAlpha = $_POST['alphaTT'];
		
	$functionurl = "https://ernekjan.ocpu.io/newPackage/R/twoTailedTTest";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$functionurl);
	curl_setopt($ch, CURLOPT_POST, 3);
	curl_setopt($ch, CURLOPT_POSTFIELDS,"vec=$sessionKey&mu0=$mu0&alpha=$parAlpha");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	$result = curl_exec($ch);
	curl_close($ch); 
	$getpath = explode('/ocpu/', $result);
	$tmpKey = explode('/', $getpath[1]);
	$pathVal = "https://public.opencpu.org/ocpu/". $getpath[1]."/print";
	$pathVal = preg_replace('/\s+/', '', $pathVal);
	$results = file_get_contents($pathVal);
	$resultsArr = explode(' ', $results);

	echo "<p>Hladina testu alpha: ".$parAlpha."</p>";
	if($resultsArr[0]==0){
		echo "<p>Hypotéza H<sub>0</sub> se ZAMÍTÁ.</p>";
		echo "<p>Alternativní hypotéza: střední hodnota veličiny není rovna &mu;<sub>0</sub>.</p>";
		echo "<p>p-hodnota testu je ".$resultsArr[2]."</p>";
	}else{
		echo "<p>Hypotéza H<sub>0</sub> se NEZAMÍTÁ.</p>";
		echo "<p>p-hodnota testu je ".$resultsArr[2]."</p>";
	}
}

?>

<!--Kolmogorov-Smirnov test-->
<div class="testForm">
	<form id ="KSTest" method="post">
	    <fieldset>
		    <legend>Kolmogorov-Smirnovův test dobré shody</legend>

		    	<p><select name="dist" class="styled-select">
					<option disabled selected> -- zvolte rozdělení pro porovnání -- </option> 
				      		<option value="normal">normální</option>
				      		<option value="exponencial">exponenciální</option>
				      		<option value="uniform">rovnoměrné</option>
				</select></p>

		    	<p><select name="alphaKS" class="styled-select">
		    		<option disabled selected> -- zvolte hladinu významnosti &alpha; -- </option>
			      		<?php
				      		for ($i=0; $i < count($alpha); $i++){
				      			$value = $alpha[$i]; 
				      			echo "<option value=\"".$value."\">".$value."</option>";
				      		}
			      		?>
				</select></p>
			<input type="submit" name="ksTest" value="Otestovat hypotézu" onsubmit="<?=$_SERVER['PHP_SELF']?>"/>
		</fieldset>
	</form>
</div>
	

<?php
$parAlphaKS = 0.05;
$distrToComp = "normal";
if(isset($_POST['dist'])){
	$distrToComp = $_POST['dist'];
	$parAlphaKS = $_POST['alphaKS'];

	
	$functionurl = "https://ernekjan.ocpu.io/newPackage/R/KSTest";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$functionurl);
	curl_setopt($ch, CURLOPT_POST, 2);
	curl_setopt($ch, CURLOPT_POSTFIELDS,"vec=$sessionKey&distribution=\"$distrToComp\"&alpha=$parAlphaKS");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	$result = curl_exec($ch);
	curl_close($ch); 
	$getpath = explode('/ocpu/', $result);
	$tmpKey = explode('/', $getpath[1]);
	$pathVal = "https://public.opencpu.org/ocpu/". $getpath[1]."/print";
	$pathVal = preg_replace('/\s+/', '', $pathVal);
	$results = file_get_contents($pathVal);
	$resultsArr = explode('"', $results);
	$distribution = preg_replace('/"/', '', $resultsArr[3]);
	$pValue = preg_replace('/"/', '', $resultsArr[9]);

	echo "<p>Hladina testu alpha: ".$parAlphaKS."</p>";
	if ($resultsArr[1]==0){
		echo "<p>Nulová hypotéza se ZAMÍTÁ.</p>";
		echo "<p>Alternativní hypotéza: veličina není z vybraného (".$distribution.") rozdělení, p-hodnota testu: ".$pValue."</p>";
	}else{
		echo "<p>Nulová hypotéza se NEZAMÍTÁ.</p>";
		echo "<p>p-hodnota testu: ". $pValue."</p>";
	}
}
?>

</div>
</div>


<?php
//ukonceni spojeni s databazi 
$conn = null; 
?>
<div class="footer">&copy; 2015 | ernekjan | FIT ČVUT</div>
</div>
</body>
</html>