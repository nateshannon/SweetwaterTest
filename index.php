<?php
$pageTitle = "Sweetwater Comment Post-Processor";

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/conn.php');
require_once(__DIR__ . '/header.php');
?>

    <div class="px-4 py-4 my-4 text-center">
        <div class="row">
            <div class="col-lg-3">     
                
            </div>
            <div class="col-lg-6 text-center">

                <h1 class="subtleShadow" style="text-align: left; font-weight: 700; font-family: 'Oswald', sans-serif;"><span class="sweetwaterLogoType">Sweetwater</span> Order Comments</h1>
                
                <h4 class="" style="text-align: left; font-weight: 400; font-family: 'Montserrat', sans-serif;">Body copy</h4>
                
                <h4 class="" style="text-align: left; font-weight: 400; font-family: 'Permanent Marker', cursive;">Accent copy</h4>                
                
                
                

            </div>
            <div class="col-lg-3">
                
            </div>
        </div>
    </div>
        
        <?php
        
        $result = mysqli_query($conn, "SELECT * FROM sweetwater_test WHERE orderid=25006706 LIMIT 1");
        $row = mysqli_fetch_assoc($result);
        echo $row['comments'];
        
        ?>
        
    
    

<?php
require_once(__DIR__ . '/footer.php');
?>