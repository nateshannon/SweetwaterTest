<?php
$pageTitle = "Sweetwater Candy Configuration";
$currentNavButton = "candy";

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/conn.php');
require_once(__DIR__ . '/common.php');

if (array_key_exists('action', $_POST)) {
    $action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
    switch ($action) {
        case "setCandyColor" : {            
            $candyId = -1;
            $candyType = "primary";
            $candyColor = "#000000";
            if (array_key_exists('id', $_POST)) {
                $candyId = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            }
            if (array_key_exists('type', $_POST)) {
                $candyType = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
            }
            if (array_key_exists('color', $_POST)) {
                $candyColor = filter_var($_POST['color'], FILTER_SANITIZE_STRING);
            }
            $candy_result = array(
                "id" => $candyId,
                "error" => ""
            );
            
            if (strlen($candyColor) == 7) {
                $candyColor = substr($candyColor, 1);
            }
            
            $colorUpdateSql = "UPDATE candy SET " . $candyType . "_color='" . $candyColor . "' WHERE id=" . $candyId;
            if($color_result = mysqli_query($conn, $colorUpdateSql)){        
                
            } else{
                $candy_result["error"] = "SQL Statement: " . $colorUpdateSql . " => Error: " . mysqli_error($conn);        
            }
            
            
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-type: application/json');
            echo json_encode($candy_result, JSON_PRETTY_PRINT);  
            die();            
            break;
        }        
        default : {
            echo "Invalid input!";
        }
    }
}



if (!empty($_GET)) {
    if (isset($_GET['action']) && isset($_GET['id'])) {
        if ($_GET['action'] == "delete") {
            if (filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
                $filteredId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
                $sql = "DELETE FROM candy WHERE id=" . $filteredId;
                
                if ($conn->query($sql) === TRUE) {
                    header("Location: candy.php");
                    die();
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                    die();
                }
            }            
        }        
    }
}


if (!empty($_POST)) {
    if (isset($_POST['candyName'])) {
    
        $newCandyName = filter_var($_POST['candyName'], FILTER_SANITIZE_STRING);
        $newCandyDescription = filter_var($_POST['candyDescription'], FILTER_SANITIZE_STRING);

        $sql = "INSERT INTO candy (name, description)
        VALUES ('" . $newCandyName . "', '" . $newCandyDescription . "')";

        if ($conn->query($sql) === TRUE) {
            header("Location: candy.php");
            die();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
            die();
        }
                
    }
}


require_once(__DIR__ . '/header.php');
?>

    <div class="px-4 py-4 my-4 text-center">
        <div class="row">
            <div class="col-lg-2">     
                
            </div>
            <div class="col-lg-8">
                
                <?php                
                require_once(__DIR__ . '/navmenu.php');
                ?>
                
                <h1 class="subtleShadow pageHeading" style="text-align: left; font-weight: 700; font-family: 'Oswald', sans-serif;"><span class="sweetwaterLogoType">Sweetwater</span> Candy Configuration</h1>
                
                <div class="bodySection">
                    
                    
<?php
        

$result = mysqli_query($conn, "SELECT * FROM candy ORDER BY name ASC");
     
if ($result->num_rows > 0) {
?>
                    <form name="candy-list" id="candy-list">
                    <table class="table table-striped table-hover">
                        <thead>
                          <tr>
                            <th scope="col">Id</th>
                            <th scope="col" style="text-align:left;">Name</th>
                            <th scope="col" style="text-align:left;">Description</th>
                            <th scope="col" style="text-align:center;">Color</th>
                            <th scope="col" style="text-align:right;"><button type="button" class="btn btn-primary" style="font-weight: 400; font-family: 'Permanent Marker', cursive;" data-bs-toggle="modal" data-bs-target="#staticBackdrop">New Candy</button></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          while($row = $result->fetch_assoc()) {
                          ?>
                          <tr>
                            <th scope="row"><?= $row["id"]; ?></th>
                            <td style="text-align:left;"><?= $row["name"]; ?></td>
                            <td style="text-align:left;"><?= $row["description"]; ?></td>
                            <td style="text-align:center;"><input type="color" class="form-control-color" style="border-color:rgba(0, 0, 0, 1.0);border-radius:3px;" onchange="setCandyColor(<?= $row["id"]; ?>, 'primary');" id="primaryColor<?= $row["id"]; ?>" name="primaryColor<?= $row["id"]; ?>" value="#<?= $row["primary_color"]; ?>"> <input type="color" class="form-control-color" style="border-color:rgba(0, 0, 0, 1.0);border-radius:3px;" onchange="setCandyColor(<?= $row["id"]; ?>, 'secondary');" id="secondaryColor<?= $row["id"]; ?>" name="secondaryColor<?= $row["id"]; ?>" value="#<?= $row["secondary_color"]; ?>"></td>
                            <td style="text-align:right;"><button type="button" class="btn btn-outline-dark btn-sm" style="font-weight: 400; font-family: 'Permanent Marker', cursive;" onclick="navClick('search.php?candy-text=<?= $row["name"]; ?>');">Find Comments</button> <button type="button" class="btn btn-outline-danger btn-sm" style="font-weight: 400; font-family: 'Permanent Marker', cursive;" onclick="navClickConfirm('candy.php?action=delete&id=<?= $row["id"]; ?>', 'Are you sure you want to delete <?= $row["name"]; ?>?')">Delete Candy</button></td>
                          </tr>
                          <?php
                          }
                          ?>
                        </tbody>
                    </table>
                    </form>
<?php
} else {    
    
}
?>
                    
                    
                </div>                

            </div>
            <div class="col-lg-2">
                
            </div>
        </div>
    </div>

    


<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <form action="candy.php" method="post" id="create-candy-form" name="create-candy-form">
           
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">New Candy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">

                      <div class="mb-3">
                        <label for="candyName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="candyName" name="candyName">
                      </div>
                      <div class="mb-3">
                        <label for="candyDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="candyDescription" name="candyDescription" rows="3"></textarea>
                      </div>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('create-candy-form').submit();">Create</button>
              </div>
            </div>
        </div>
        
    </form>
</div>


<script type="text/javascript">

    function setCandyColor(id, colorType) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                flashColorSaved(id, colorType);
            }
        }
        xmlhttp.open("POST", "candy.php", true);
        xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xmlhttp.send("action=setCandyColor&id=" + id + "&type=" + colorType + "&color=" + document.forms['candy-list'].elements[colorType + 'Color' + id].value);
    }
    
    
    function flashColorSaved(id, colorType) {
        
        document.getElementById(colorType + 'Color' + id).style.backgroundColor = 'rgba(255, 255, 0, 0.3)';
        document.getElementById(colorType + 'Color' + id).style.borderColor = 'rgba(255, 255, 0, 1.0)';
        document.getElementById(colorType + 'Color' + id).style.boxShadow = 'rgba(255, 255, 0, 1.0) 0px 0px 0px';
        
        setTimeout("fadeColorSaved(" + id + ", '" + colorType + "', 100, 110, 3, 20);", 20);
    }
    
    function fadeColorSaved(id, colorType, opacity, shadowSize, stepSize, stepDelay) {
        if (opacity > 0) {
 
            var buttonBgOpacity = Number.parseFloat(((0.3 * opacity) / 100)).toFixed(2);
            var inverseOpacity = (100 - opacity);
            var shadowStepSize = (shadowSize / 100) * inverseOpacity;
            document.getElementById(colorType + 'Color' + id).style.backgroundColor = 'rgba(255, 255, 0, ' + buttonBgOpacity + ')';
            document.getElementById(colorType + 'Color' + id).style.borderColor = 'rgba(' + ((255 / 100) * opacity) + ', ' + ((255 / 100) * opacity) + ', 0, 1.0)';
            document.getElementById(colorType + 'Color' + id).style.boxShadow = 'rgba(255, 255, 0, 1.0) 0px 0px ' + shadowStepSize + 'px';
            
            setTimeout("fadeColorSaved(" + id + ", '" + colorType + "', " + (opacity - stepSize) + ", " + shadowSize + ", " + stepSize + ", " + stepDelay + ");", stepDelay);
        } else {
            setTimeout("clearColorSaved(" + id + ", '" + colorType + "');", 100);
        }
    }
    
    function clearColorSaved(id, colorType) {
        document.getElementById(colorType + 'Color' + id).style.boxShadow = 'none';
    }
    
    
</script>
    
<?php
$conn->close();
require_once(__DIR__ . '/footer.php');
?>