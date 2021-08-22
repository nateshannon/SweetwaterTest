<?php declare(strict_types=1); 
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/common.php');
require_once(__DIR__ . '/conn.php');




if (array_key_exists('action', $_POST)) {
    $action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
    switch ($action) {
        case "process" : {
            if (array_key_exists('id', $_POST)) {
                $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            }
            process_comment($id, $conn);
            die();
            
            break;
        }
        case "getPendingCommentIds" : {
            get_pending_comment_ids($conn);
            die();
            break;
        }
        case "getCommentCounts" : {            
            get_comment_counts($conn);
            die();            
            break;
        }
        case "completeCallToggle" : {
            $orderid = 0;
            if (array_key_exists('orderid', $_POST)) {
                $orderid = filter_var($_POST['orderid'], FILTER_VALIDATE_INT);
            }
            complete_call_toggle($orderid, $conn);
            die();            
            break;
        }
        case "clearMetadata" : {            
            reset_all_metadata($conn);
            die();            
            break;
        }
        default : {
            echo "Invalid input!";
        }
    }
    
    
    
    
    
    
    // do stuff with params
    //echo 'Yes, it works, do ' . $action . '! id=' . $id;

} else {
    echo 'Invalid parameters!';
}
die();




function complete_call_toggle($orderid, $conn) {
    $call_result = array(
        "orderid" => $orderid,
        "new_call_completed" => -1,
        "error" => ""
    );
    
    $currentCallState = 0;
    if($callResult = mysqli_query($conn, "SELECT call_completed FROM sweetwater_test WHERE orderid=" . $orderid . ";")){
        $row = $callResult->fetch_assoc();
        if ($callResult->num_rows == 1) {
            $currentCallState = $row["call_completed"];
        }
    }
    $callSql = "UPDATE sweetwater_test SET call_completed=ABS((1-call_completed)) WHERE orderid=" . $orderid;
    if($result = mysqli_query($conn, $callSql)){        
        $call_result["new_call_completed"] = 1;
        if (intval($currentCallState) == 1) {
            $call_result["new_call_completed"] = 0;
        } elseif (intval($currentCallState) == 0) {
            $call_result["new_call_completed"] = 1;
        }
    } else{
        $call_result["error"] = "SQL Statement: " . $callSql . " => Error: " . mysqli_error($conn);        
    }
    
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($call_result, JSON_PRETTY_PRINT);    
}



function get_pending_comment_ids($conn) {
    $commentIds = array();
    
    // Execute SQL Updates
    if($result = mysqli_query($conn, "SELECT orderid FROM `sweetwater_test` WHERE metadata_generated=0 ORDER BY orderid ASC;")){
        while ($row = mysqli_fetch_assoc($result)) {
            $commentIds[] = $row["orderid"];
        }
    } else{        
        //$commentCounts["error"] = "Error: " . mysqli_error($conn);
    }
    
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($commentIds, JSON_PRETTY_PRINT);
}



function get_comment_counts($conn) {
    
    $commentCounts = array(
        "processed" => "-1",
        "pending" => "-1",
        "error" => ""
        );
    
    // Execute SQL Updates
    if($result = mysqli_query($conn, "SELECT (SELECT COUNT(*) FROM `sweetwater_test` WHERE metadata_generated=1) Processed, (SELECT COUNT(*) FROM `sweetwater_test` WHERE metadata_generated=0) Pending;")){
        $row = $result->fetch_assoc();
        $commentCounts["processed"] = $row["Processed"];
        $commentCounts["pending"] = $row["Pending"];
        $commentCounts["error"] = "";
    } else{        
        $commentCounts["error"] = "Error: " . mysqli_error($conn);
    }
    
    // Set JSON Headers, return JSON metadata info
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($commentCounts, JSON_PRETTY_PRINT);
    
}



function process_comment($orderid, $conn) {
    
    
    sleep(1);

    $row_comments = "";
    $row_sentences = "";
    $row_shipdate_expected = "";
    $row_id = -1;

    $metadata = array(
        "metadata_status" => "pending",
        "metadata_message" => "",
        "orderid" => "0",
        "call_wanted" => "0",
        "require_signature" => "1",
        "expected_ship_date" => "0000-00-00",
        "candy_assoc" => array(),
        "people_assoc" => array()
        );



    //Load Comment Record
    //if (!empty($_GET)) {
        if (isset($orderid)) {
            if (filter_var($orderid, FILTER_VALIDATE_INT)) {
                $row_id = filter_var($orderid, FILTER_VALIDATE_INT);
                $metadata["orderid"] = $row_id;
                $sql = "SELECT * FROM sweetwater_test WHERE orderid=" . $row_id . " LIMIT 1";

                $result = mysqli_query($conn, $sql);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $row_comments = $row["comments"];
                    $row_shipdate_expected = $row["shipdate_expected"];
                }            
            }
        }
    //}




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





    //echo $row_comments;
    //echo "<hr>";


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
    //echo "Call Decision: " . $callpositive;

    if ($callpositive <= 0) {
        $metadata["call_wanted"] = "0";
    } else {
        $metadata["call_wanted"] = "1";
    }

    //echo "<hr>";
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
    $sigpositive = 1;
    for ($x = 0; $x < count($row_sentences); $x++) {    
        $sigpositive += subject_decision($row_sentences[$x], $sigsubjectKeywords, $sigpositiveKeywords, $signegativeKeywords);
        //echo "$sigpositive: " . $sigpositive . "<br>";
    }
    //echo "Signature Decision: " . $sigpositive;

    if ($sigpositive <= 0) {
        $metadata["require_signature"] = "0";
    } else {
        $metadata["require_signature"] = "1";
    }

    //echo "<hr>";







    //echo "<hr>Candy Found:<br>";

    $candyFound = array();

    //split comment into words
      //find sounds like candy words from candy table
        //create candy association
    //echo "<br>meta: " . metaphone_search('Bit o Honey', $row_comments);
    //echo "<br>exact: " . exact_search('Bit O Honey', $row_comments);

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
            //echo $candies[$c]['name'] . " located exactly.<br>";
            $metadata["candy_assoc"][] = $candies[$c];
            // CREATE ASSOCIATION orderid <-> candyid
        } else {
            // exact match NOT found
            if (metaphone_search($candies[$c]['name'], $row_comments)) {
                // metaphone match found
                //echo $candies[$c]['name'] . " located by metaphone.<br>";
                $metadata["candy_assoc"][] = $candies[$c];
                // CREATE ASSOCIATION orderid <-> candyid
            } else {
                // no match found
            }
        }    
    }













    //echo "<hr>Names Found:<br>";

    $namesFound = array();

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
                        $temp = preg_replace("/(^\W*|[\W\.]*$)/", "", $matchedName);
                        $matchedName = $temp;
                    }
                    if (strtoupper($matchedName) != "I" && strtoupper($matchedName) != "A") {
                        //echo "name: " . $nameMatches[0][$m][0] . " => " . $matchedName . ";<br>";

                        $personId = ensure_person_exists($matchedName, TRUE, TRUE, $conn); // check against system
                        if ($personId == -1) {
                            $personId = ensure_person_exists($matchedName, TRUE, FALSE, $conn); // check against auto-gen
                        }
                        //echo "<br>Person Id: " . $personId . "<br>";
                        
                        $namesFound[$matchedName] = $personId;
                    }


                }       
            //}
        }

    }
    //echo "<hr>";

    //echo "<br>Person Id 2: " . ensure_person_exists("Mark Smith", TRUE, FALSE, $conn) . "<br>";







    //echo "<hr>";



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
            //echo "<br>" . $found_person . " located exactly.<br>";
            // Look for referral or sales
            // CREATE ASSOCIATION orderid <-> personid
        } else {
            // exact match NOT found
            // TODO: DEBUG THIS, CAUSING PROBLEMS CURRENTLY
            if (metaphone_search($people[$c]['name'], $row_comments)) {
                // metaphone match found
                $found_person = $people[$c]['name'];
                //echo "<br>" . $found_person . " located by metaphone.<br>";
                // Look for referral or sales
                // CREATE ASSOCIATION orderid <-> personid
            } else {
                // no match found
            }
            
        }

        if ($found_person != "") {

            //$cleanPerson = preg_replace('/\.$/i', "", $found_person);


            //echo "<hr>Refer Distance: ";
            $refer_info = person_subject_assoc_distance($row_comments, $found_person, array("refer", "recommend", "told", "heard", "swears by"));
            //echo $refer_info['distance'] . " (" . $refer_info['keyword'] . ")";

            //$tempDist = $refer_info['distance'];
            // find/use shortest distance
            // insert relationship based on shortest distance
            //create_people_assoc_record(int $orderId, int $personId, int $relationType);



            //print_debug($found_person);
            //print_debug($namesFound);

            if ($refer_info['distance'] > -1) {
                //if (!isset($metadata["people_assoc"]["PersonName"])) {
                if ($namesFound[$found_person] != null) {
                    
                
                    $metadata["people_assoc"][] = array("PersonName" => $found_person, "PersonId" => $namesFound[$found_person], "Role" => "Refer");
                  }  
                //}
            }

            //echo "<hr>";

            //echo "<hr>Sales Distance: ";
            $sales_info = person_subject_assoc_distance($row_comments, $found_person, array("sales", "engineer", "rep", "credit", "commission", "thank you", "attn", "help", "client"));
            //echo $sales_info['distance'] . " (" . $sales_info['keyword'] . ")";

            if ($sales_info['distance'] > -1) {
                //if (!isset($metadata["people_assoc"]["PersonName"])) {
                    if ($namesFound[$found_person] != null) {
                $metadata["people_assoc"][] = array("PersonName" => $found_person, "PersonId" => $namesFound[$found_person], "Role" => "Sales");
                    }
                //}
            }


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







    //search comment for known names
      //make associations for found known names
        //use referal and sales keywords to record association type
      //use sounds like for fuzzy matches
      //!use regex to identify likely names
        //!create person
          //!create association and type

    // if name is found in same sentence as refer or recommend or heard or told, it is a referral
    // if name is in same sentence as sales rep or sales engineer or credit or commission or "thank you" or attn, it is sales




    //echo "<hr><h1>Processing...</h1><hr>";



    for ($i = 0; $i < count($metadata["candy_assoc"]); $i++) {
        create_candy_assoc_record($row_id, $metadata["candy_assoc"][$i]["id"], $conn);
    }

    for ($i = 0; $i < count($metadata["people_assoc"]); $i++) {
        if ($metadata["people_assoc"][$i]["Role"] == "Sales") {
            create_people_assoc_record($row_id, $metadata["people_assoc"][$i]["PersonId"], 1, $conn);
        }
        if ($metadata["people_assoc"][$i]["Role"] == "Refer") {
            create_people_assoc_record($row_id, $metadata["people_assoc"][$i]["PersonId"], 2, $conn);
        }
    }


    // Build Update SQL statement from processed metadata
    $sqlUpdates = array();
    $sqlUpdateCommand = "UPDATE sweetwater_test SET ";
    if ($metadata["expected_ship_date"] != "0000-00-00") {
        $sqlUpdates[] = array("db_field" => "shipdate_expected", "db_value" => $metadata["expected_ship_date"], "db_type" => "datetime");    
    }
    if ($metadata["call_wanted"] == "1") {
        $sqlUpdates[] = array("db_field" => "call_wanted", "db_value" => "1", "db_type" => "int"); 
    }
    if ($metadata["require_signature"] == "1") {
        $sqlUpdates[] = array("db_field" => "require_signature", "db_value" => "1", "db_type" => "int"); 
    }
    $sqlUpdates[] = array("db_field" => "metadata_generated", "db_value" => "1", "db_type" => "int"); 
    for ($s = 0; $s < count($sqlUpdates); $s++) {
        if ($s > 0) {
            $sqlUpdateCommand .= ", ";
        }
        switch ($sqlUpdates[$s]["db_type"]) {
            case "datetime" : {
                $sqlUpdateCommand .= $sqlUpdates[$s]["db_field"] . "='" . $sqlUpdates[$s]["db_value"] . "'";
                break;
            }
            case "int" : {
                $sqlUpdateCommand .= $sqlUpdates[$s]["db_field"] . "=" . $sqlUpdates[$s]["db_value"];
                break;
            }
            default : {

            }
        }    
    }
    $sqlUpdateCommand .= " WHERE orderid=" . $row_id . ";";


    // Execute SQL Updates
    if($result = mysqli_query($conn, $sqlUpdateCommand)){
        $metadata["metadata_status"] = "processed";
    } else{
        $metadata["metadata_status"] = "error";
        $metadata["metadata_message"] = "SQL Statement: " . $sqlUpdateCommand . " => Error: " . mysqli_error($conn);
    }

    // Set JSON Headers, return JSON metadata info
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($metadata, JSON_PRETTY_PRINT);

}

    
    
?>