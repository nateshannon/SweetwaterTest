<?php
$pageTitle = "Sweetwater Order Comments";
$currentNavButton = "home";

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
                if (!$showMenu) {
                ?>
                    
                <h1 class="subtleShadow pageHeading" style="text-align: left; font-weight: 700; font-family: 'Oswald', sans-serif;"><span class="sweetwaterLogoType">Sweetwater</span> Setup</h1>
                
                <div class="bodySection">
                    <p class="bodyParagraph">Welcome to the Sweetwater comment management system, built by Nate Shannon as a development test.</p>
                    
                    <p class="bodyParagraph">You are seeing this Setup page because the database has not been connected and/or the data imported. I'm certain that if you are viewing this page that you are familiar with the data in question, and could set everything up without instructions. But for the sake of completeness, and in the off-chance that someone unfamiliar with the data is setting this up, the instructions for getting everything up-and-running are below.</p>
                
                    <div class="row" style="margin:25px;">   
                        <div class="col-lg-2">     

                        </div>
                        <div class="col-lg-8" style="padding:0px 0px 0px 0px;box-shadow:rgba(0, 0, 0, 0.5) 5px 5px 15px;">
                        <ol class="list-group list-group-numbered">
                          <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto" style="text-align:left;">
                              <div class="fw-bold">Create Database</div>
                              Create a new database, or locate a MySQL database which you want to expand. Remember or write down the database server, username, password, and database name.<br><br>
                              <u><b>Note:</b></u> <i>By default the system looks for a database on "localhost", named "sweetwater", using a username of "root", and no password.</i>
                            </div>
                          </li>
                          <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto" style="text-align:left;">
                              <div class="fw-bold">Import Database Tables and Data</div>
                              Download the <b><a href="content/db.sql" download>db.sql</a></b> file, located in the content sub-directory of this website. Import the <b>db.sql</b> file into the database created or selected in step #1.
                            </div>
                          </li>
                          <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto" style="text-align:left;">
                              <div class="fw-bold">Update the Config.php File</div>
                              If you are not using the default configuration noted in step #1, open the <b>config.php</b> file for this website. Update the MySQL configuration variables for server name, username, password, and database name based on your system configuration.
                            </div>
                          </li>
                          <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto" style="text-align:left;">
                              <div class="fw-bold">Reload Website</div>
                              Refresh this page. If the <b>db.sql</b> file was imported correctly, and the <b>config.php</b> file was updated correctly, this page will change to a welcome screen with a number of navigation buttons available at the top of the page.
                            </div>
                          </li>
                        </ol>
                        </div>
                        <div class="col-lg-2">     

                        </div>
                    </div>                 
                    
                </div>
                
               
                
                    
                <?php
                } else {
                ?>
                
                
                <?php                
                require_once(__DIR__ . '/navmenu.php');
                ?>
                
                <h1 class="subtleShadow pageHeading" style="text-align: left; font-weight: 700; font-family: 'Oswald', sans-serif;"><span class="sweetwaterLogoType">Sweetwater</span> Order Comments</h1>
                
                <div class="bodySection">
                    <p class="bodyParagraph">Welcome to the Sweetwater Order Comments administration system.</p>

                    <p class="bodyParagraph">Use the buttons above to navigate. If this is your first time using the system, you need to generate metadata for the comments. This detects people and candy mentioned in comments, parses expected ship dates, whether people want a call, and whether a signature is required upon delivery.</p>
                    
                    <p class="bodyParagraph">To generate the metadata, click the <a href="metadata.php">Metadata</a> tab at the top of the page. Then click the Process Pending Comments button. The progress bar will track and report the metadata generation process status as is occurs. When finished the progress bar will stop animating and will report only the total number of processed records.</p>
                    
                    <p class="bodyParagraph">You can click the Cancel Processing button at any point during the metadata generation process to stop the process. It can be resumed later at any point using the Process Pending Comments button again. New comments added to the system after metadata has been generated will need to be processed before they appear in search results.</p>
                    
                    <p class="bodyParagraph">If you wish to clear the metadata out of the system, either to regenerate it, or just to watch the generation process occur again, you can click the Clear Metadata button.</p>

                </div>
                
                
                <?php
                }
                ?>
                
                
                
            </div>
            <div class="col-lg-2">
                
            </div>
        </div>
    </div>
    

<?php
require_once(__DIR__ . '/footer.php');
?>