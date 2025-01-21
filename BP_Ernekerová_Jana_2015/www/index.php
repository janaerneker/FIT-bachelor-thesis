<!DOCTYPE html>
<html>
<head>
<?php
	/* vlozeni exteniho souboru s globalnimi promennymi (napr. nazev DB)*/ 
    include 'configure.php';
?>
 	<!-- Převzato z veřejného archivu: http://www.iconarchive.com/show/ids-space-lab-icons-by-iron-devil.html -->
 	<link rel="icon" href="img/favicon.ico" type="image/x-icon"/>
 	<!--<link rel="icon" href="/var/www/graphic/icon.ico" type="image/x-icon"/>-->
	<meta charset="UTF-8"/>
	<title>OpenCPUvsSageCellServer</title>
	<link rel="stylesheet" type="text/css" href="css/style.css"/>
	<script type="text/javascript" src="jquery-2.1.3.js"></script>
</head>
<body>

<div id="blackBar"><ol class="navigation"><li class="pageTitle">OpenCPU vs. Sage Cell Server</li></ol></div>
<div id="wrap">

<p class="intro">Vítejte na stránkách vzniklých v rámci mojí bakalářské práce při studiu na Fakultě informačních
technologií na ČVUT v Praze. Tématem mé bakalářské práce je &bdquo;Webová demonstrace základních statistických výpočtů 
s využitím matematického software R a SAGE&ldquo; a tato webová aplikace slouží k testování možností integrace 
matematických softwarů R a Sage do webové stránky a jejich použití k základním statistickým výpočtům. Nyní se nacházíte
na hlavní stránce, můžete buď založit nový projekt (nahrát na server data k analýze v CSV formátu), nebo procházet již 
existující projekty.</p>


<form enctype="multipart/form-data" action="upload.php" method="POST">
<fieldset>
	<legend>Založit nový projekt</legend>
    <p>Název projektu: 
    <input type="text" name="projectname" onchange="myFunction(this.value)" required></p>
    <p>Zvol soubor typu CSV s daty: <input name="userfile" onchange="myFunction(this.value);" type="file" id="upload" required/></p>
    <p>CSV soubor má hlavičku: <input type="checkbox" name="header" value="header"></p>
    <p><input type="submit" value="Nahrát soubor" /></p>
</fieldset>
</form>           
<?php


try
{
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    $stmt = $conn->prepare("SELECT * FROM files"); 
    $stmt->execute();
    $result = $stmt->fetchAll();

    if ($result!=null) {
    
	    echo "<div class=\"tables\"><table>";
		echo "<tr><td>Název projektu</td><td>Název souboru s daty</td><td>Smazat projekt</td></tr>";
	    
	    foreach ($result as $row) {
	    	$id = $row['id'];
			//delete.png prevzat ze stranky: http://findicons.com/icon/16049/delete?id=337272
	    	echo "<tr>
			<td class=\"projName\"><a href=\"project.php?id=$id\">". $row['project_name'] ."</a></td>
			<td>". $row['user_file_name'] . "</td>
			<td><a href=\"deleteproject.php?id=$id\"><img src=\"img/delete.png\" alt=\"Smazat projekt\" style=\"width:20px;height:20px;border:0\"></a></td>
			</tr>";
	    }

	    echo "</table></div>";
	}

}
catch(PDOException $e)
{
	echo "Connection failed: " . $e->getMessage();
}
?>


<?php
//ukonceni spojeni s DB
	$conn = null;
?>
<div class="footer">&copy; 2015 | ernekjan | FIT ČVUT</div>
</div>

</body>
</html>  
       