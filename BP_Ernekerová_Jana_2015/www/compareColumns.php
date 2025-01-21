<?php
//start relace
	session_start();
?>
<!DOCTYPE html>
<html>
<head>
<?php 
//vlozeni externiho souboru s globalnimi promennymi (napr.nazev DB)
    include 'configure.php';
?>
<!-- Převzato z veřejného archivu: http://www.iconarchive.com/show/ids-space-lab-icons-by-iron-devil.html -->
<link rel="icon" href="img/favicon.ico" type="image/x-icon"/>
<meta charset="UTF-8"/>
<title>Srovnání dvou náhodných veličin | OpenCPUvsSageCellServer</title>
<script type="text/javascript" src="jquery-1.11.2.js"></script>

<!-- For Sage -->
<script src="https://sagecell.sagemath.org/static/jquery.min.js"></script>
<script src="https://sagecell.sagemath.org/static/embedded_sagecell.js"></script>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/style.css"/>

<script>


$(document).ready(function(){
        activaTab('rproject');

    });

    function activaTab(tab){
        $('.nav-tabs a[href="#' + tab + '"]').tab('show');
    };

//for sage
$(function () {
    // Make *any* div with class 'compute' a Sage cell
    sagecell.makeSagecell({inputLocation: 'div.compute',
                          template: sagecell.templates.minimal,
                          autoeval: true,
                          hide: ["evalButton",]});
    });
//autoeval - automaticke vyhodnoceni kodu bunky pri nacteni stranky
//hide: evalButton - skryti vyhodnocovaciho tlacitka

</script>
</head>
<body>
<?php
$dataId = $_GET["id"];
$tablename = "table".$dataId;

$firstId = $_POST['firstColumn'];
$secondId = $_POST['secondColumn'];
//v pripade znovunacteni stranky, zvolene veliciny k porovnani ulozeny v php session
if($firstId==NULL or $secondId==NULL){
	if($firstId==NULL) $firstId = $_SESSION["fId"];
	if($secondId==NULL) $secondId = $_SESSION["sId"];
}else{
	$_SESSION["fId"] = $firstId;
	$_SESSION["sId"] = $secondId;
}

try
	{	
	$conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT variable FROM `".$tablename."` WHERE id=".$firstId;
    $stmt = $conn->prepare($query); 
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	foreach ($result as $key=> $row){
        $firstHeader = $row['variable'];
    }

    $query = "SELECT variable FROM `".$tablename."` WHERE id=".$secondId;
    $stmt = $conn->prepare($query); 
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	foreach ($result as $key=> $row){
        $secondHeader = $row['variable'];
    }

}
catch(PDOException $e)
{
	echo "Connection failed: " . $e->getMessage();
}

$firstFile = "http://webdev.fit.cvut.cz/~ernekjan/uploads/data".$dataId."_col".$firstId.".csv";
$secondFile = "http://webdev.fit.cvut.cz/~ernekjan/uploads/data".$dataId."_col".$secondId.".csv";

//navigace
echo "<div id=\"blackBar\"><ol class=\"navigation\">
<li class=\"pageTitle\">OpenCPU vs. Sage Cell Server</li>
<li><a href=\"index.php\">Zvolit jiný projekt</a></li>
<li><a href=\"project.php?id=$dataId\">Zvolit jiné veličiny k porovnání</a></li></ol></div>";

?>

<div id="wrap">

<?php if($firstId=='' or $secondId==''){
    echo "Nevybral si správně data k porovnání.";
    }else{ ?>


<ul class="nav nav-tabs">
    <li><a href="#rproject" data-toggle="tab">R</a></li>
    <li><a href="#sagemath" data-toggle="tab">Sage</a></li>
</ul>
<div class="tab-content" id="tabs">
    
<div class="tab-pane" id="sagemath">

<?php
	echo "<h1>".$firstHeader." x ".$secondHeader."</h1>";
?>

<h3>Základní charakteristiky</h3>

<div class="compute">
    <script type="text/x-sage">
import urllib2
import numpy as np
import scipy.stats as stats

firstURL = '<?php Print($firstFile); ?>'
secondURL = '<?php Print($secondFile); ?>'
fData = urllib2.urlopen(firstURL)
sData = urllib2.urlopen(secondURL)
fArray = []
sArray = []

headerFlag = 0

for line in fData:
    if headerFlag==0:
        headerFlag=1
    else:
        if len(line.strip())!=0:
            fArray.append(float(line))

headerFlag = 0

for line in sData:
    if headerFlag==0:
        headerFlag=1
    else:
        if len(line.strip())!=0:
            sArray.append(float(line))


tmpfSampleVariance = np.var(fArray)
fSampleVariance = float(len(fArray))/(float(len(fArray))-1)*tmpfSampleVariance
tmpsSampleVariance = np.var(sArray)
sSampleVariance = float(len(sArray))/(float(len(sArray)-1))*tmpsSampleVariance
covarMatrix = np.cov(fArray,sArray)
pearsCorCoef = stats.pearsonr(fArray, sArray)

print "Výběrový rozptyl první veličiny je: %.3f" % (fSampleVariance)
if fArray != sArray:
	print "Výběrový rozptyl druhé veličiny je: %.3f" % (sSampleVariance)
else:
	print "Veličiny jsou shodné."

print "Korelační koeficient těchto veličin je: %.3f " % (pearsCorCoef[0])
print "Kovariance: %.3f " % (covarMatrix[0][1])
    </script>
</div>


<h3>Scatter plot</h3>
<div class="compute">
    <script type="text/x-sage">
import urllib2
import numpy as np
import matplotlib.pyplot as plt
firstURL = '<?php Print($firstFile); ?>'
secondURL = '<?php Print($secondFile); ?>'
fData = urllib2.urlopen(firstURL)
sData = urllib2.urlopen(secondURL)
fArray = []
sArray = []

headerFlag = 0

for line in fData:
    if headerFlag==0:
        headerFlag=1
    else:
        if len(line.strip())!=0:
            fArray.append(float(line))

headerFlag = 0

for line in sData:
    if headerFlag==0:
        headerFlag=1
    else:
        if len(line.strip())!=0:
            sArray.append(float(line))

plt.scatter(fArray, sArray, c="white", alpha=0.5)
plt.title("Scatter plot")
plt.xlabel("Hodnota 1.veliciny")
plt.ylabel("Hodnota 2.veliciny")
plt.show()
    </script>
</div>


<h3>Párový t-test o rovnosti středních hodnot &mu;<sub>1</sub> a &mu;<sub>2</sub></h3>

<div class="compute">
	<script type="text/x-sage">
import urllib2
import scipy.stats as stats
import numpy as np
np.seterr(invalid='ignore')
firstURL = '<?php Print($firstFile); ?>'
secondURL = '<?php Print($secondFile); ?>'
fData = urllib2.urlopen(firstURL)
sData = urllib2.urlopen(secondURL)
fArray = []
sArray = []

headerFlag = 0

for line in fData:
    if headerFlag==0:
        headerFlag=1
    else:
        if len(line.strip())!=0:
            fArray.append(float(line))

headerFlag = 0

for line in sData:
    if headerFlag==0:
        headerFlag=1
    else:
        if len(line.strip())!=0:
            sArray.append(float(line))

if fData==sData:
	print "Veličiny jsou shodné."
else:
	@interact
	def _(alpha=Selector(["0.05", "0.03", "0.02", "0.01"], default="0.05", selector_type='list')):
		fData = np.array(fArray)
		sData = np.array(sArray)
		results = stats.ttest_rel(fData,sData)
		pValue = results[1]
		if pValue < float(alpha):
			print "Hladina testu alpha: %.2f" % (float(alpha))
			print "Nulová hypotéza se ZAMÍTÁ."
			print "Alternativní hypotéza: střední hodnota veličiny se nerovnají, p-hodnota testu: %f" % (pValue) 
		else:
			print "Hladina testu alpha: %.2f" % (float(alpha))
			print "Nulová hypotéza se NEZAMÍTÁ."
			print "p-hodnota testu: %f" % (pValue)


	</script>
</div>

<h3>Kolmogorov-Smirnovův test shodnosti rozdělení</sub></h3>

<div class="compute">
    <script type="text/x-sage">
import urllib2
import scipy.stats as stats

firstURL = '<?php Print($firstFile); ?>'
secondURL = '<?php Print($secondFile); ?>'
fData = urllib2.urlopen(firstURL)
sData = urllib2.urlopen(secondURL)
fArray = []
sArray = []

headerFlag = 0

for line in fData:
    if headerFlag==0:
        headerFlag=1
    else:
        if len(line.strip())!=0:
            fArray.append(float(line))

headerFlag = 0

for line in sData:
    if headerFlag==0:
        headerFlag=1
    else:
        if len(line.strip())!=0:
            sArray.append(float(line))
@interact
def _(alpha=Selector(["0.05", "0.03", "0.02", "0.01"], default="0.05", selector_type='list')):
	results=stats.ks_2samp(fArray,sArray)
	pValue=results[1]
	if pValue < float(alpha):
		print "Hladina testu alpha: %.2f" % (float(alpha))
		print "Nulová hypotéza se ZAMÍTÁ."
		print "Alternativní hypotéza: veličiny nejsou ze stejného rozdělení, p-hodnota testu: %f" % (pValue) 
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
	//nadpis
	echo "<h1>".$firstHeader." x ".$secondHeader."</h1>";

	echo "<h3>Základní charakteristiky</h3>";

	//cteni dat ze souboru
	$functionurl = "https://ernekjan.ocpu.io/newPackage/R/readFile";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$functionurl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,"path=\"$firstFile\"");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $result = curl_exec($ch);
    curl_close($ch); 
    $getpath = explode('/ocpu/', $result);
    $tmpKey = explode('/', $getpath[1]);
    $sessionKey = $tmpKey[1];

    //vyberovy rozptyl prvni veliciny
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
    $fSampleVar = file_get_contents($pathVal);
    $fSampleVar = preg_replace('/\[[1]]/', '', $fSampleVar);

    if($firstId != $secondId){
    	//pripadne totez pro druhou velicinu, pokud to neni ta sama
    	$functionurl = "https://ernekjan.ocpu.io/newPackage/R/readFile";
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$functionurl);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS,"path=\"$secondFile\"");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	    $result = curl_exec($ch);
	    curl_close($ch); 
	    $getpath = explode('/ocpu/', $result);
	    $tmpKey = explode('/', $getpath[1]);
	    $sessionKey = $tmpKey[1];


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
	    $sSampleVar = file_get_contents($pathVal);
    	$sSampleVar = preg_replace('/\[[1]]/', '', $sSampleVar);
    }
   		
    //covariance
    $functionurl = "https://ernekjan.ocpu.io/newPackage/R/callCov";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$functionurl);
    curl_setopt($ch, CURLOPT_POST, 2);
    curl_setopt($ch, CURLOPT_POSTFIELDS,"firstPath=\"$firstFile\"&secondPath=\"$secondFile\"");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $result = curl_exec($ch);
    curl_close($ch); 
    $getpath = explode('/ocpu/', $result);
    $pathVal = "https://public.opencpu.org/ocpu/". $getpath[1]."/print";
    $pathVal = preg_replace('/\s+/', '', $pathVal);
    $covariance = file_get_contents($pathVal);
    $covariance = preg_replace('/\[[1]]/', '', $covariance);

    //korelace
    $functionurl = "https://ernekjan.ocpu.io/newPackage/R/callCor";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$functionurl);
    curl_setopt($ch, CURLOPT_POST, 2);
    curl_setopt($ch, CURLOPT_POSTFIELDS,"firstPath=\"$firstFile\"&secondPath=\"$secondFile\"");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $result = curl_exec($ch);
    curl_close($ch); 
    $getpath = explode('/ocpu/', $result);
    $pathVal = "https://public.opencpu.org/ocpu/". $getpath[1]."/print";
    $pathVal = preg_replace('/\s+/', '', $pathVal);
    $corCoef = file_get_contents($pathVal);
    $corCoef = preg_replace('/\[[1]]/', '', $corCoef);


    echo "<div class=\"characteristics\">";
	echo "<p>Výběrový rozptyl veličiny " . $firstHeader. " je: " . $fSampleVar ."</p>";
	if ($firstId != $secondId) {
		echo "<p>Výběrový rozptyl veličiny " . $secondHeader. " je: " . $sSampleVar ."</p>";
	}else{
		echo "<p>Veličiny jsou shodné.</p>";
	}
	echo "<p>Korelační koeficient těchto veličin je: " . $corCoef."</p>";
	echo "<p>Kovariance: " . $covariance."</p>";   
	echo "</div>";

    $countValuesFlag = 1;
    if (strpos($formattedResult[3],'Different') !== false) {
        $countValuesFlag = 0;
    }
    //scatter plot
    if($countValuesFlag == 1){
    	echo "<h3>Scatter plot</h3>";
        $functionurl = "https://ernekjan.ocpu.io/newPackage/R/scatterPlot";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$functionurl);
        curl_setopt($ch, CURLOPT_POST, 2);
        curl_setopt($ch, CURLOPT_POSTFIELDS,"firstPath=\"$firstFile\"&secondPath=\"$secondFile\"");
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
    }
?>

<!--Parovy Ttest -->
<div id="TTestpaired" class="testForm">
	<form id ="pairedTTest" method="post">
	    <fieldset>
		    <legend>Párový t-test o rovnosti středních hodnot &mu;<sub>1</sub> a &mu;<sub>2</sub></legend>
		    	<p><select id="TTestAlpha" name="alphapTTest" class="styled-select">
		    		<option disabled selected> -- zvolte hladinu významnosti &alpha; -- </option>
			      		<?php
				      		for ($i=0; $i < count($alpha); $i++){
				      			$value = $alpha[$i]; 
				      			echo "<option value=\"".$value."\">".$value."</option>";
				      		}
			      		?>
				</select></p>
			<input class="KSTwoButton" type="submit" name="pTTest" value="Otestovat hypotézu" onsubmit="<?=$_SERVER['PHP_SELF']?>"/>
		</fieldset>
	</form>
</div>
</div>


<?php

$parAlphapTTest = 0.05;
if(isset($_POST['alphapTTest'])){
	$parAlphapTTest = $_POST['alphapTTest'];

	if($firstId==$secondId){
		echo "<p>Veličiny jsou shodné, není nutné provádět test.</p>.";
	}else{
		$functionurl = "https://ernekjan.ocpu.io/newPackage/R/pairedTTest";
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$functionurl);
	    curl_setopt($ch, CURLOPT_POST, 3);
	    curl_setopt($ch, CURLOPT_POSTFIELDS,"firstPath=\"$firstFile\"&secondPath=\"$secondFile\"&alpha=$parAlphapTTest");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	    $result = curl_exec($ch);
	    curl_close($ch); 
	    $getpath = explode('/ocpu/', $result);
	    $pathVal = "https://public.opencpu.org/ocpu/". $getpath[1]."/print";
	    $pathVal = preg_replace('/\s+/', '', $pathVal);
	    $results = file_get_contents($pathVal);
	    $resultsArr = explode(' ', $results);

		echo "<p>Hladina testu alpha: ".$parAlphapTTest."</p>";
		if ($resultsArr[1]==0){
			echo "<p>Nulová hypotéza se ZAMÍTÁ.</p>";
			echo "<p>Alternativní hypotéza: veličiny nejsou ze stejného rozdělení, p-hodnota testu: ".$resultsArr[2]."</p>";
		}else{
			echo "<p>Nulová hypotéza se NEZAMÍTÁ.</p>";
			echo "<p>p-hodnota testu: ".$resultsArr[2]."</p>"; 
		}
	}
}
        
?>

<!--Kolmogorov-Smirnov test -->
<div id="KSTest" class="testForm">
<form id ="KStestTS" method="post">
		    <fieldset>
			    <legend>Kolmogorov-Smirnovův test shodnosti rozdělení</legend>

			    	<p><select id="KSalpha" name="alphaKSTS" class="styled-select">
			    		<option disabled selected> -- zvolte hladinu významnosti &alpha; -- </option>
				      		<?php
					      		for ($i=0; $i < count($alpha); $i++){
					      			$value = $alpha[$i]; 
					      			echo "<option value=\"".$value."\">".$value."</option>";
					      		}
				      		?>
					</select></p>
				<input type="submit" name="KStestTS" value="Otestovat hypotézu" onsubmit="<?=$_SERVER['PHP_SELF']?>"/>
			</fieldset>
		</form>
</div>


<?php
		
if(isset($_POST['alphaKSTS'])){
	$parKSTestTS = $_POST['alphaKSTS'];
	
    $functionurl = "https://ernekjan.ocpu.io/newPackage/R/KSTwoSam";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$functionurl);
    curl_setopt($ch, CURLOPT_POST, 3);
    curl_setopt($ch, CURLOPT_POSTFIELDS,"firstPath=\"$firstFile\"&secondPath=\"$secondFile\"&alpha=$parKSTestTS");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $result = curl_exec($ch);
    curl_close($ch); 
    $getpath = explode('/ocpu/', $result);
    $pathVal = "https://public.opencpu.org/ocpu/". $getpath[1]."/print";
    $pathVal = preg_replace('/\s+/', '', $pathVal);
    $results = file_get_contents($pathVal);
    $resultsArr = explode(' ', $results);

	echo "<p>Hladina testu alpha: ".$parKSTestTS."</p>";
	if ($resultsArr[1]==0){
		echo "<p>Nulová hypotéza se ZAMÍTÁ.</p>";
		echo "<p>Alternativní hypotéza: veličiny nejsou ze stejného rozdělení, p-hodnota testu: ".$resultsArr[2]."</p>";
	}else{
		echo "<p>Nulová hypotéza se NEZAMÍTÁ.</p>";
		echo "<p>p-hodnota testu: ".$resultsArr[2]."</p>"; 
	}
}
		
?>



</div>
</div>

</div>

<?php } 
//ukonceni spojeni s DB
	$conn = null;
?>
<div class="footer">&copy; 2015 | ernekjan | FIT ČVUT</div>
</div>
</body>
</html>