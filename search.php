<?php
$pageTitle = "Sweetwater Comment Search";
$currentNavButton = "search";

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/conn.php');
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
                
                <h1 class="subtleShadow pageHeading" style="text-align: left; font-weight: 700; font-family: 'Oswald', sans-serif;"><span class="sweetwaterLogoType">Sweetwater</span> Comments Search</h1>
                
                <div class="bodySection">
                    <p class="bodyParagraph">Search...</p>

                </div>
                

            </div>
            <div class="col-lg-2">
                
            </div>
        </div>
    </div>

    

<?php
require_once(__DIR__ . '/footer.php');
?>