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


    //Split Comments into Sentences
    $row_sentences = preg_split("/(?<!\w\.\w.)(?<![A-Z][a-z]\.)(?<=\.|\?)(\s|[A-Z].*)/", $row_comments);

    //Extract Expected Ship Date
    $expected_ship_date_split = explode("Expected Ship Date:", $row_comments);
    if (count($expected_ship_date_split) > 1) {
        $date_part = trim($expected_ship_date_split[count($expected_ship_date_split)-1]);
        $date_part_split = preg_split("/\s/", $date_part);
        $expected_ship_date = getdate(strtotime(trim($date_part_split[0])));
        $metadata["expected_ship_date"] = strval($expected_ship_date["year"]) . "-" . str_pad(strval($expected_ship_date["mon"]), 2, "0", STR_PAD_LEFT) . "-" . str_pad(strval($expected_ship_date["mday"]), 2, "0", STR_PAD_LEFT);
    }

    $callsubjectKeywords = array("call", "contact", "phone", "ring");
    $callpositiveKeywords = array("soon", "please", "asap");
    $callnegativeKeywords = array("do not", "don't", "no", "never");
    $callpositive = 0;
    for ($x = 0; $x < count($row_sentences); $x++) {    
        $callpositive += subject_decision($row_sentences[$x], $callsubjectKeywords, $callpositiveKeywords, $callnegativeKeywords);
    }

    if ($callpositive <= 0) {
        $metadata["call_wanted"] = "0";
    } else {
        $metadata["call_wanted"] = "1";
    }

    $sigsubjectKeywords = array("signature", "sig", "sign", "door", "package", "porch");
    $sigpositiveKeywords = array("include");
    $signegativeKeywords = array("waive", "leave", "left", "remove", "not", "no", "without");
    $sigpositive = 1;
    for ($x = 0; $x < count($row_sentences); $x++) {    
        $sigpositive += subject_decision($row_sentences[$x], $sigsubjectKeywords, $sigpositiveKeywords, $signegativeKeywords);
    }

    if ($sigpositive <= 0) {
        $metadata["require_signature"] = "0";
    } else {
        $metadata["require_signature"] = "1";
    }

    $candyFound = array();

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
            $metadata["candy_assoc"][] = $candies[$c];
        } else {
            if (metaphone_search($candies[$c]['name'], $row_comments)) {
                $metadata["candy_assoc"][] = $candies[$c];
            }
        }    
    }

    $namesFound = array();

    // FIND NAMES NOT IN SYSTEM
    preg_match_all('/[A-Z][a-z\.]+\s+(?:[A-Z][a-z\.\-]*\s*)?[A-Z][A-Za-z\.\-]*/', $row_comments, $nameMatches, PREG_OFFSET_CAPTURE);

    if (isset($nameMatches[0])) {
        for ($m = 0; $m < count($nameMatches[0]); $m++) {
            $matchedName = $nameMatches[0][$m][0];
            $matchedName = exclude_words($matchedName);
            if ($matchedName != "") {
                if ($nameMatches[0][$m][0] != $matchedName) {
                    $temp = preg_replace("/(^\W*|[\W\.]*$)/", "", $matchedName);
                    $matchedName = $temp;
                }
                if (strtoupper($matchedName) != "I" && strtoupper($matchedName) != "A") {
                    $personId = ensure_person_exists($matchedName, TRUE, TRUE, $conn); // check against system
                    if ($personId == -1) {
                        $personId = ensure_person_exists($matchedName, TRUE, FALSE, $conn); // check against auto-gen
                    }
                    $namesFound[$matchedName] = $personId;
                }
            }       
        }
    }

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
        } else {
            // exact match NOT found
            // TODO: TWEAK THIS, NOT AS ACCURATE AS I'D LIKE
            if (metaphone_search($people[$c]['name'], $row_comments)) {
                // metaphone match found
                $found_person = $people[$c]['name'];
            }            
        }

        if ($found_person != "") {
            $refer_info = person_subject_assoc_distance($row_comments, $found_person, array("refer", "recommend", "told", "heard", "swears by"));

            if ($refer_info['distance'] > -1) {
                if ($namesFound[$found_person] != null) {
                    $metadata["people_assoc"][] = array("PersonName" => $found_person, "PersonId" => $namesFound[$found_person], "Role" => "Refer");
                }  
            }

            $sales_info = person_subject_assoc_distance($row_comments, $found_person, array("sales", "engineer", "rep", "credit", "commission", "thank you", "attn", "help", "client"));

            if ($sales_info['distance'] > -1) {
                if ($namesFound[$found_person] != null) {
                    $metadata["people_assoc"][] = array("PersonName" => $found_person, "PersonId" => $namesFound[$found_person], "Role" => "Sales");
                }
            }
        }
    }

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