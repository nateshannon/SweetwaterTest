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
            <div class="col-lg-6">

                
                <p class="text-left">
                    <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;">Home</button> 
                    <button type="button" class="btn btn-light sweetwaterLogoType" style="font-weight: 400; font-family: 'Permanent Marker', cursive;">Search</button> 
                    <button type="button" class="btn btn-light sweetwaterLogoType" style="font-weight: 400; font-family: 'Permanent Marker', cursive;">Manage Candy</button> 
                    <button type="button" class="btn btn-light sweetwaterLogoType" style="font-weight: 400; font-family: 'Permanent Marker', cursive;">Manage People</button>
                    <button type="button" class="btn btn-light sweetwaterLogoType" style="font-weight: 400; font-family: 'Permanent Marker', cursive;">Meta Data</button>
                </p>
                
                <h1 class="subtleShadow pageHeading" style="text-align: left; font-weight: 700; font-family: 'Oswald', sans-serif;"><span class="sweetwaterLogoType">Sweetwater</span> Order Comments</h1>
                
                <div class="bodySection">
                    <p class="bodyParagraph">Welcome to the Sweetwater Order Comments administration system.</p>

                    <p class="bodyParagraph">Use the buttons above to navigate. You can configure people and candy which will then be recognized and searchable within comments customers make on their orders. You can also search the comments for actionable items, such as customers that need to be called or orders that have special handling instructions like requiring a signature. You can also manage the meta data that is generated by the system.</p>

                </div>
                

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