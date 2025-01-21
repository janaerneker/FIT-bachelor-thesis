<?php
/* start relace */
    session_start();
?>

<!DOCTYPE html>
<html>
<head>
<?php
    /* vlozeni globalnich promennych (nazev DB apod.) */ 
    include 'configure.php';
?>
    <!-- Převzato z veřejného archivu: http://www.iconarchive.com/show/ids-space-lab-icons-by-iron-devil.html -->
    <link rel="icon" href="img/favicon.ico" type="image/x-icon"/>
    <meta charset="UTF-8"/>
    <title>Popis projektu | OpenCPUvsSageCellServer</title>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
    <script type="text/javascript" src="jquery-2.1.3.js"></script>
</head>
<body>
<div id="blackBar"><ol class="navigation">
    <li class="pageTitle">OpenCPU vs. Sage Cell Server</li>
    <li><a href="index.php">Zvolit jiný projekt</a></li>
</ol></div>
<div id="wrap">
<script type="text/javascript"> 
</script>
<?php

$id = $_GET["id"];
$tablename = "table".$id;

try
{   
    //connecting to DB
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM files WHERE id=".$id); 

    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach ($result as $row) {
        $projectname = $row['project_name'];
        echo "<h1>". $projectname . "</h1>";
        $userfilename=$row['user_file_name'];
        $filename=$row['file_name'];
        $hasheader=$row['has_header'];

        $query = "SELECT * FROM `".$tablename."`";
        $stmt = $conn->prepare($query); 
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //porovnavani dvou nahodnych velicin
        if(count($rows) >= 2){
        
            echo "<form id=\"comparing\" name=\"comparing\" enctype=\"multipart/form-data\" action=\"compareColumns.php?id=$id\" method=\"POST\">";
            echo "<fieldset>";
            echo "<legend>Porovnej dvě veličiny z tohoto souboru dat:</legend>";
                echo "<p><select name=\"firstColumn\" style=\"min-width: 10%\" class=\"styled-select\">";
                        echo "<option disabled selected> -- vyber veličinu -- </option>";
                foreach ($rows as $key=> $row){
                    if ($row['type']==number){
                        $var = $row['variable'];
                        $varId = $row['id'];
                        echo "<option value=\"".$varId."\">".$var."</option>";
                    }
                }
                echo "</select></p>";
            
                echo "<p><select name=\"secondColumn\" style=\"min-width: 10%\" class=\"styled-select\">";
                        echo "<option disabled selected> -- vyber veličinu -- </option>";
                foreach ($rows as $key=> $row){
                    if ($row['type']==number){
                        $var = $row['variable'];
                        $varId = $row['id'];
                        echo "<option value=\"".$varId."\">".$var."</option>";
                    }
                }
                echo "</select></p>";
            echo "<input type=\"submit\" value=\"Porovnej\"/>";
            echo "</fieldset>";
            echo "</form>"; 
        }
    }
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}

if($rows != null){
    echo "<div class=\"tables\"><table><tr><td>Proměnná</td><td>Typ</td><td>Rozbor veličiny</td></tr>";
    foreach ($rows as $key=> $row){
        echo "<tr><td>".$row['variable']."</td><td>".$row['type']."</td>";
        if ($row['type']==number){
            $columnId = $row['id'];
            //stats.png ze stránky: http://findicons.com/icon/64876/stats?id=285294
            echo "<td><a href=\"content.php?dataId=$id&colId=$columnId\"><img src=\"img/stats.png\" alt=\"Rozbor veličiny\" style=\"width:20px;height:20px;border:0\"></a></td>";
        }else{
            echo "<td></td>";
        }
        echo "</tr>";
    }
    echo "</table></div>";
}
//ukonceni spojeni z databazi
$conn = null;
?> 
<div class="footer">&copy; 2015 | ernekjan | FIT ČVUT</div>
</div>
</body>
</html>