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
    <title>Smazat projekt | OpenCPUvsSageCellServer</title>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
    <script type="text/javascript" src="jquery-2.1.3.js"></script>
</head>
<body>
<div id="blackBar"><ol class="navigation"><li class="pageTitle">OpenCPU vs. Sage Cell Server</li><li><a href="index.php">Zvolit jiný projekt</a></li></ol></div>
<div id="wrap">


<script type="text/javascript">

//proti reloadovani stranky
function disable_f5(e)
{
  if (((e.which || e.keyCode) == 116) ||(e.ctrlKey && ((e.keycode||e.which) == 82)))
  {
      e.preventDefault();
  }
}

$(document).ready(function(){
    $(document).bind("keydown", disable_f5);    
});
 
</script>

<?php

$id = $_GET["id"];

$tablename = "table".$id;

try 
{
	//pripojeni k databazi
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT file_name FROM files WHERE id=".$id);
    $stmt->execute();
    $result = $stmt->fetchAll();
    foreach ($result as $row) {
    	$filename = $row['file_name'];
    }

    $sql = "DELETE FROM files WHERE id=".$id."";
    $conn->exec($sql);
    
    $query = "SELECT file_name FROM `".$tablename."` WHERE file_created=true";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    $sql = "DROP TABLE `".$tablename."`";
    $conn->exec($sql);
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM files");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_BOTH);

    if($result[0]==0){
    	$sql = "TRUNCATE TABLE files";
    	$conn->exec($sql);
    }
    
    echo "<div class=\"valid_message\"><p>Projekt úspěšně smazán.</p></div>";

}
catch(PDOException $e)
{
	echo $sql . "<br>" . $e->getMessage();
}

foreach ($rows as $key=> $row){
     unlink($row['file_name']);
}

unlink($filename);

//ukonceni spojeni s DB
$conn = null;

?>
<div class="footer">&copy; 2015 | ernekjan | FIT ČVUT</div>
</div>
</body>
</html>