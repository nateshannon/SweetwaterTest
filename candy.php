<?php
$pageTitle = "Sweetwater Candy Configuration";
$currentNavButton = "candy";

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/conn.php');

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
                    <table class="table table-striped table-hover">
                        <thead>
                          <tr>
                            <th scope="col">Id</th>
                            <th scope="col" style="text-align:left;">Name</th>
                            <th scope="col" style="text-align:left;">Description</th>
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
                            <td style="text-align:right;"><button type="button" class="btn btn-outline-dark btn-sm" style="font-weight: 400; font-family: 'Permanent Marker', cursive;">Find Comments</button> <button type="button" class="btn btn-outline-danger btn-sm" style="font-weight: 400; font-family: 'Permanent Marker', cursive;" onclick="navClickConfirm('candy.php?action=delete&id=<?= $row["id"]; ?>', 'Are you sure you want to delete <?= $row["name"]; ?>?')">Delete Candy</button></td>
                          </tr>
                          <?php
                          }
                          ?>
                        </tbody>
                    </table>
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

<?php
$conn->close();
require_once(__DIR__ . '/footer.php');
?>