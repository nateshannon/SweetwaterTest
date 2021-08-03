<?php
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/conn.php');

$row_comments = "";
$row_sentences = "";
$row_id = -1;


//Load Comment Record
if (!empty($_GET)) {
    if (isset($_GET['orderid'])) {
        if (filter_var($_GET['orderid'], FILTER_VALIDATE_INT)) {
            $row_id = filter_var($_GET['orderid'], FILTER_VALIDATE_INT);
            $sql = "SELECT * FROM sweetwater_test WHERE orderid=" . $row_id . " LIMIT 1";
            
            $result = mysqli_query($conn, $sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $row_comments = $row["comments"];
            }            
        }
    }
}


//Split Comments into Sentences
$row_sentences = preg_split("/(?<!\w\.\w.)(?<![A-Z][a-z]\.)(?<=\.|\?)(\s|[A-Z].*)/", $row_comments);



//Extract Expected Ship Date
$expectedShipDate = getdate(strtotime(trim(explode("Expected Ship Date:", $row_comments)[1])));
// NEED TO WRITE BACK TO DATABASE




echo print_r($expectedShipDate);








//parse call wanted status
  // please ring, call, contact


//parse signature required
  //words to associate with NO = waive, no, not, fedex, ups (assume if mentioned that we dont want a sig), leave. left, remove signature
  //words to associate with YES = require


//split comment into words
  //find sounds like candy words from candy table
    //create candy association


//search comment for known names
  //make associations for found known names
    //use referal and sales keywords to record association type
  //use sounds like for fuzzy matches
  //use regex to identify likely names
    //create person
      //create association and type

// if name is found in same sentence as refer or recommend or heard or told, it is a referral
// if name is in same sentence as sales rep or sales engineer or credit or commission or "thank you" or attn, it is sales


?>