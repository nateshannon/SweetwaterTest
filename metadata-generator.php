<?php declare(strict_types=1); 
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/common.php');
require_once(__DIR__ . '/conn.php');

$row_comments = "";
$row_sentences = "";
$row_shipdate_expected = "";
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
                $row_shipdate_expected = $row["shipdate_expected"];
            }            
        }
    }
}




//Split Comments into Sentences
$row_sentences = preg_split("/(?<!\w\.\w.)(?<![A-Z][a-z]\.)(?<=\.|\?)(\s|[A-Z].*)/", $row_comments);



//Extract Expected Ship Date
$expected_ship_date_split = explode("Expected Ship Date:", $row_comments);
if (count($expected_ship_date_split) > 1) {
    // We have a date
    // NEED TO WRITE BACK TO DATABASE
    
    $date_part = trim($expected_ship_date_split[count($expected_ship_date_split)-1]);
    $date_part_split = preg_split("/\s/", $date_part);
    
    
    $expected_ship_date = getdate(strtotime(trim($date_part_split[0])));
    
    //print_debug($expected_ship_date);
        
    //detect if database has been updated
    //echo ($row_shipdate_expected == '0000-00-00 00:00:00');
    
} else {
    // No date available, skip processing
}





echo $row_comments;
echo "<hr>";


//no calls
//30812881
//30838097

//call
//30815508
//30809067


$callsubjectKeywords = array("call", "contact", "phone", "ring");
$callpositiveKeywords = array("soon", "please", "asap");
$callnegativeKeywords = array("do not", "don't", "no", "never");
$callpositive = 0;
for ($x = 0; $x < count($row_sentences); $x++) {    
    $callpositive += subject_decision($row_sentences[$x], $callsubjectKeywords, $callpositiveKeywords, $callnegativeKeywords);

}
echo "Call Decision: " . $callpositive;

echo "<hr>";
//no sig
//30021238
//30418828
//
//yes sig
//
//

//subjects = signature, sig, sign
//positive = require, include
//negative = waive, leave, left, no, remove, without

$sigsubjectKeywords = array("signature", "sig", "sign", "door", "package");
$sigpositiveKeywords = array("include");
$signegativeKeywords = array("waive", "leave", "left", "remove", "no", "without");
$sigpositive = 0;
for ($x = 0; $x < count($row_sentences); $x++) {    
    $sigpositive += subject_decision($row_sentences[$x], $sigsubjectKeywords, $sigpositiveKeywords, $signegativeKeywords);

}
echo "Signature Decision: " . $sigpositive;


echo "<hr>";






echo "<br>meta: " . metaphone_search('Bit o Honey', $row_comments);

echo "<br>exact: " . exact_search('Bit O Honey', $row_comments);




$candy_sql = "SELECT name FROM candy ORDER BY name ASC";
$candies = array();
$candy_result = mysqli_query($conn, $candy_sql);
if ($candy_result->num_rows > 0) {
    while($candy_row = $candy_result->fetch_assoc()) {
        $candies[] = $candy_row["name"];
    }
}         

for ($c = 0; $c < count($candies); $c++) {
    if (exact_search($candies[$c], $row_comments)) {
        // exact match found
        echo $candies[$c] . " located exactly.<br>";
        // CREATE ASSOCIATION
    } else {
        // exact match NOT found
        if (metaphone_search($candies[$c], $row_comments)) {
            // metaphone match found
            echo $candies[$c] . " located by metaphone.<br>";
            // CREATE ASSOCIATION
        } else {
            // no match found
        }
    }    
}




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