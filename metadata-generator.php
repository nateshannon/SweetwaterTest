<?php declare(strict_types=1); 
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/common.php');
require_once(__DIR__ . '/conn.php');

$row_comments = "";
$row_sentences = "";
$row_shipdate_expected = "";
$row_id = -1;

$metadata = array(
    "call_wanted" => "0",
    "require_signature" => "0",
    "expected_ship_date" => "0000-00-00",
    "candy_assoc" => array(),
    "people_assoc" => array()
    );



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
    
    $metadata["expected_ship_date"] = strval($expected_ship_date["year"]) . "-" . str_pad(strval($expected_ship_date["mon"]), 2, "0", STR_PAD_LEFT) . "-" . str_pad(strval($expected_ship_date["mday"]), 2, "0", STR_PAD_LEFT);
    
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

if ($callpositive <= 0) {
    $metadata["call_wanted"] = "0";
} else {
    $metadata["call_wanted"] = "1";
}

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

$sigsubjectKeywords = array("signature", "sig", "sign", "door", "package", "porch");
$sigpositiveKeywords = array("include");
$signegativeKeywords = array("waive", "leave", "left", "remove", "not", "no", "without");
$sigpositive = 0;
for ($x = 0; $x < count($row_sentences); $x++) {    
    $sigpositive += subject_decision($row_sentences[$x], $sigsubjectKeywords, $sigpositiveKeywords, $signegativeKeywords);

}
echo "Signature Decision: " . $sigpositive;

if ($sigpositive <= 0) {
    $metadata["require_signature"] = "0";
} else {
    $metadata["require_signature"] = "1";
}

echo "<hr>";









//split comment into words
  //find sounds like candy words from candy table
    //create candy association
echo "<br>meta: " . metaphone_search('Bit o Honey', $row_comments);
echo "<br>exact: " . exact_search('Bit O Honey', $row_comments);

$candy_sql = "SELECT id, name FROM candy ORDER BY name ASC";
$candies = array();
$candy_result = mysqli_query($conn, $candy_sql);
if ($candy_result->num_rows > 0) {
    while($candy_row = $candy_result->fetch_assoc()) {
        $candies[] = array("id" => $candy_row["id"], "name" => $candy_row["name"]);
    }
}         

for ($c = 0; $c < count($candies); $c++) {
    if (exact_search($candies[$c]['name'], $row_comments)) {
        // exact match found
        echo $candies[$c]['name'] . " located exactly.<br>";
        $metadata["candy_assoc"][] = $candies[$c];
        // CREATE ASSOCIATION orderid <-> candyid
    } else {
        // exact match NOT found
        if (metaphone_search($candies[$c]['name'], $row_comments)) {
            // metaphone match found
            echo $candies[$c]['name'] . " located by metaphone.<br>";
            $metadata["candy_assoc"][] = $candies[$c];
            // CREATE ASSOCIATION orderid <-> candyid
        } else {
            // no match found
        }
    }    
}













echo "<hr>Names Found:<br>";


// DECENT!!!
//preg_match_all('/[A-Z][a-z]+\s+(?:[A-Z][a-z]*\.?\s*)?[A-Z][a-z]*/', $row_comments, $nameMatches, PREG_OFFSET_CAPTURE);

//OK
//preg_match_all('/(?:\b[A-Z][a-z]*\W+)*[A-Z][a-z]*\b/', $row_comments, $nameMatches, PREG_OFFSET_CAPTURE);



// FIND NAMES NOT IN SYSTEM
preg_match_all('/[A-Z][a-z\.]+\s+(?:[A-Z][a-z\.\-]*\s*)?[A-Z][A-Za-z\.\-]*/', $row_comments, $nameMatches, PREG_OFFSET_CAPTURE);

if (isset($nameMatches[0])) {
    
    for ($m = 0; $m < count($nameMatches[0]); $m++) {
        $matchedName = $nameMatches[0][$m][0];
        $matchedName = exclude_words($matchedName);
        //if (strtoupper($matchedName) != "I" && strtoupper($matchedName) != "A") {
            
            if ($matchedName != "") {
                if ($nameMatches[0][$m][0] != $matchedName) {
                    $temp = preg_replace("/(^\W*|\W*$)/", "", $matchedName);
                    $matchedName = $temp;
                }
                if (strtoupper($matchedName) != "I" && strtoupper($matchedName) != "A") {
                    echo "name: " . $nameMatches[0][$m][0] . " => " . $matchedName . ";<br>";

                    $personId = ensure_person_exists($matchedName, TRUE, TRUE, $conn); // check against system
                    if ($personId == -1) {
                        $personId = ensure_person_exists($matchedName, TRUE, FALSE, $conn); // check against auto-gen
                    }
                    echo "<br>Person Id: " . $personId . "<br>";
                }
                

            }       
        //}
    }
    
}
echo "<hr>";

//echo "<br>Person Id 2: " . ensure_person_exists("Mark Smith", TRUE, FALSE, $conn) . "<br>";










// sales
//30814225
//30823762
//30261410
//30812036
//
// referral
//30823762
//30596166
//30820472


echo "<hr>";



$people_sql = "SELECT id, name FROM people ORDER BY name ASC";
$people = array();
$people_result = mysqli_query($conn, $people_sql);
if ($people_result->num_rows > 0) {
    while($people_row = $people_result->fetch_assoc()) {
        $people[] = array("id" => $people_row["id"], "name" => $people_row["name"]);
    }
}    

for ($c = 0; $c < count($people); $c++) {
    $found_person = "";
    if (exact_search($people[$c]['name'], $row_comments)) {
        // exact match found
        $found_person = $people[$c]['name'];
        echo "<br>" . $found_person . " located exactly.<br>";
        // Look for referral or sales
        // CREATE ASSOCIATION orderid <-> personid
    } else {
        // exact match NOT found
        if (metaphone_search($people[$c]['name'], $row_comments)) {
            // metaphone match found
            $found_person = $people[$c]['name'];
            echo "<br>" . $found_person . " located by metaphone.<br>";
            // Look for referral or sales
            // CREATE ASSOCIATION orderid <-> personid
        } else {
            // no match found
        }
    }
    
    if ($found_person != "") {
        echo "<hr>Refer Distance: ";
        $refer_info = person_subject_assoc_distance($row_comments, $found_person, array("refer", "recommend", "told", "heard", "swears by"));
        echo $refer_info['distance'] . " (" . $refer_info['keyword'] . ")";

        
        // find shortest distance
        // insert relationship based on shortest distance
        //create_people_assoc_record(int $orderId, int $personId, int $relationType);
        
        
        echo "<hr>";
        
        echo "<hr>Sales Distance: ";
        $sales_info = person_subject_assoc_distance($row_comments, $found_person, array("sales", "engineer", "rep", "credit", "commission", "thank you", "attn", "help", "client"));
        echo $sales_info['distance'] . " (" . $sales_info['keyword'] . ")";
        
        
        
        
    }
//    $referral_subject_keywords = array("signature", "sig", "sign", "door", "package");
//    $referral_positive_keywords = array("include");
//    $referral_negative_keywords = array("waive", "leave", "left", "remove", "no", "without");
//    $referral_positive = 0;
//    for ($x = 0; $x < count($row_sentences); $x++) {    
//        $sigpositive += subject_decision($row_sentences[$x], $sigsubjectKeywords, $sigpositiveKeywords, $signegativeKeywords);
//
//    }
    
    
}





echo "<hr>";
print_debug($metadata);




//search comment for known names
  //make associations for found known names
    //use referal and sales keywords to record association type
  //use sounds like for fuzzy matches
  //!use regex to identify likely names
    //!create person
      //!create association and type

// if name is found in same sentence as refer or recommend or heard or told, it is a referral
// if name is in same sentence as sales rep or sales engineer or credit or commission or "thank you" or attn, it is sales


?>