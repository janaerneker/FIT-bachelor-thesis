<?php
/* start relace */
	session_start();
?>
<!DOCTYPE html>
<html>
<head>
<?php 
/* vlozeni souboru s globalnimi promennymi (napr. nazev DB) */
    include 'configure.php';
?>
<!-- Převzato z veřejného archivu: http://www.iconarchive.com/show/ids-space-lab-icons-by-iron-devil.html -->
<link rel="icon" href="img/favicon.ico" type="image/x-icon"/>
<meta charset="UTF-8"/>
<title>Vytvoření projektu | OpenCPUvsSageCellServer</title>
<link rel="stylesheet" type="text/css" href="css/style.css"/>
<script type="text/javascript" src="jquery-2.1.3.js"></script>
</head>
<body>
<script type="text/javascript">

/* proti reloadovani stranky */
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
<div id="blackBar"><ol class="navigation"><li class="pageTitle">OpenCPU</li><li><a href="index.php">Zvolit jiný projekt</a></li></ol></div>
<div id="wrap">

<?php

$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
$headerFlag = false;
$flag = false;
$uploadOk = 1;

$projectname = $_POST['projectname'];

// Check file size
if ($_FILES["userfile"]["size"] > 50000) {
    $errmessage = "Váš soubor je příliš velký. ";
    $uploadOk = 0;
}

$fileType = pathinfo($uploadfile,PATHINFO_EXTENSION);
// Allow certain file formats
if($fileType != "csv") {
    $errmessage = $errmessage . "Je možné nahrávat pouze CSV soubory. ";
    $uploadOk = 0;
}

if ($uploadOk == 0) {
    echo "<div class=\"failed_message\"><p>Omlouváme se, Váš soubor nebyl nahrán. ". $errmessage . "</p></div>";
// if everything is ok, try to upload file
} else {

    try
    {
        $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT id FROM files WHERE id=(SELECT max(id) FROM files)"); 
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_BOTH);
        
        if($result[0] == null){
            $filecounter = 1;
        }else{
            $filecounter = $result[0]+1; 
        }
    }
    catch(PDOException $e)
    {
        echo "Connection failed: " . $e->getMessage();
    }

    $newname = $uploaddir . 'data' . $filecounter . '.csv';

    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $newname)) {

    //CSV file has a header
    if (isset($_POST['header'])){
        $headerFlag = true;
    }

	$userfilename = basename($_FILES['userfile']['name']);
	
    try
    {
        // prepare sql and bind parameters
        $stmt = $conn->prepare("INSERT INTO files (id, project_name, user_file_name, file_name, has_header) 
        VALUES (null, :projectname, :userfilename, :filename, :hasheader)");
        $stmt->bindParam(':projectname', $projectname);
        $stmt->bindParam(':userfilename', $userfilename);
        $stmt->bindParam(':filename', $newname);
        $stmt->bindParam(':hasheader', $headerFlag);
        $stmt->execute();

        $filecounter = $conn->lastInsertId();
    }
    catch(PDOException $e)
    {
        echo "Connection failed: " . $e->getMessage();
    }
      

echo "<div class=\"valid_message\"><p>Soubor byl úspěšně nahrán na server.</p></div>";
echo "<h1>".$projectname."</h1>";    

$myfile = fopen($newname, "r") or die("Unable to open file!");




if($headerFlag==true){
    $str = fgets($myfile);
    $tmphead = explode(",",$str);
}

$i = 1;
foreach ($tmphead as $value) {
    $head[$i] = $value;
    $i++;
}

$tmprow = 1;
while (($tmpdata = fgetcsv($myfile,",")) !== FALSE) {
    $tmpcol = count($tmpdata);
    if($flag==false && $headerFlag == false){
        for($i=1; $i <= $tmpcol; $i++){
            $head[$i] = "var" . $i;
        }
        $flag = true;
    }
    
    for ($c=0; $c < $tmpcol; $c++) {
        $tmptable[$tmprow] = $tmpdata;
    }
    $tmprow++;
}
$tmprow--;

/**
*  class for a single column
*/
class Column 
{
    public $columnheader;
    public $isnumeric;
    public $arrvalues;

    function __construct($columnheader, $isnumeric, $arrvalues)
    {
        $this->columnheader = $columnheader;
        $this->isnumeric = $isnumeric;
        $this->arrvalues = $arrvalues;
    }
}

for ($i=0; $i < $tmpcol; $i++) {
   
    $numericflag = true;
    for ($j=1; $j < $tmprow; $j++) {

        if ($tmptable[$j][$i]!="" && is_numeric($tmptable[$j][$i])==false){
            $numericflag = false;
        }
        $tmparr[$j] = $tmptable[$j][$i];
    }

    $tmpcolumn = new Column($head[$i+1],$numericflag,$tmparr);
    $table[$i] = $tmpcolumn;

}
$rows = $tmpcol;
$columns = $tmprow;


$tablename = "table". $filecounter;

try
{
    $sql = "CREATE TABLE `".$tablename."` (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    variable VARCHAR(256) NOT NULL,
    type VARCHAR(256) NOT NULL,
    file_created BOOLEAN,
    file_name VARCHAR(256)
    )";
    $conn->exec($sql);

}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
    
if($rows >= 2){
    echo "<form id=\"comparing\" name=\"comparing\" enctype=\"multipart/form-data\" action=\"compareColumns.php?id=$filecounter\" method=\"POST\">";
    echo "<fieldset>";
    echo "<legend>Porovnej dvě veličiny z tohoto souboru dat:</legend>";

        echo "<p><select name=\"firstColumn\" style=\"min-width: 10%\" class=\"styled-select\">";
                echo "<option disabled selected> -- vyber veličinu -- </option>";
        for ($i=0; $i < $rows; $i++) { 
            if($table[$i]->isnumeric==true){
                $idcolumn = $i+1;
                echo "<option value=\"".$idcolumn."\">".$table[$i]->columnheader."</option>";
            }
        }
        echo "</select></p>";
    

        echo "<p><select name=\"secondColumn\" style=\"min-width: 10%\" class=\"styled-select\">";
                echo "<option disabled selected> -- vyber veličinu -- </option>";
        for ($i=0; $i < $rows; $i++) { 
            if($table[$i]->isnumeric==true){
                $idcolumn = $i+1;
                echo "<option value=\"".$idcolumn."\">".$table[$i]->columnheader."</option>";
            }
        }
        echo "</select></p>";
    echo "<input type=\"submit\" value=\"Porovnej\"/>";
    echo "</fieldset>";
    echo "</form>"; 
}

echo "<table class=\"tables\" id=\"vars\">";
echo "<tr><td>Proměnná</td><td>Typ</td><td>Rozbor veličiny</td></tr>";
for ($i=0; $i < $rows; $i++) { 

    echo "<tr>";
    echo "<td>". $table[$i]->columnheader ."</td>";

    
    if($table[$i]->isnumeric==true){

        $idcolumn = $i+1;
        $tmpfilename = $uploaddir."data".$filecounter."_col".$idcolumn.".csv";
        $myfile = fopen($tmpfilename, "w")or die("Unable to open file!");

        $txt = $table[$i]->columnheader."\n";
        fwrite($myfile, $txt);

        for ($j=1; $j < $columns; $j++) { 

            $txt = $table[$i]->arrvalues[$j]."\n"; 
            fwrite($myfile, $txt);
        }
        fclose($myfile);
    }

    try {

        if($table[$i]->isnumeric==true){
            $sql = "INSERT INTO `".$tablename."` (id, variable, type, file_created, file_name)
            VALUES (null, '".$table[$i]->columnheader."', 'number', true, '".$tmpfilename."')";
        }else{
            $sql = "INSERT INTO `".$tablename."` (id, variable, type, file_created, file_name)
            VALUES (null, '".$table[$i]->columnheader."', 'string', false, null)";
        }    

        // use exec() because no results are returned
        $conn->exec($sql);

        }
    catch(PDOException $e)
        {
        echo $sql . "<br>" . $e->getMessage();
        }

    if($table[$i]->isnumeric==true){
        echo "<td> number </td>";
    }else{
        echo "<td> string </td>";
    }

    if($table[$i]->isnumeric==true){

        echo "<td class=\"projName\"><a href=\"content.php?dataId=$filecounter&colId=$idcolumn\" ><img src=\"img/stats.png\" alt=\"Rozbor veličiny\" style=\"width:20px;height:20px;border:0\"></a></td>";

    }else{
        echo "<td></td>";
    }

    echo "</tr>";
}

echo "</table>";

//uzavreni souboru
fclose($myfile);

}else{
      echo "<div class=\"failed_message\"><p>Nahrávání na server selhalo.</p></div>";
    }
}

//ukonceni spojeni s DB
$conn = null;

?> 
<div class="footer">&copy; 2015 | ernekjan | FIT ČVUT</div>
</div>
</body>
</html>