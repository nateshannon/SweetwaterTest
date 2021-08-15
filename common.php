<?php


function print_debug($var) {
    $formatted = print_r($var, true); 
    print "<pre>" . htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8', true) . "</pre>";
}



function reset_all_metadata($conn) {
    
    if($result = mysqli_query($conn, "TRUNCATE TABLE candy_assoc;")){

    } else{
        echo "<hr>ERROR: " . mysqli_error($conn);
    }
    
    if($result = mysqli_query($conn, "TRUNCATE TABLE people_assoc;")){

    } else{
        echo "<hr>ERROR: " . mysqli_error($conn);
    }
    
    if($result = mysqli_query($conn, "DELETE FROM people WHERE auto_detected=1;")){

    } else{
        echo "<hr>ERROR: " . mysqli_error($conn);
    }
    
    if($result = mysqli_query($conn, "UPDATE sweetwater_test SET metadata_generated=0, call_wanted=0, call_completed=0, require_signature=0, shipdate_expected = '1970-01-01 08:00:00' WHERE 1=1;")){

    } else{
        echo "<hr>ERROR: " . mysqli_error($conn);
    }
    
}


function create_people_assoc_record($orderId, $personId, $relationType, $conn) {
    $result = -1;
    // relation_type: 1 = sales, 2 = referrer
    $sql = "INSERT INTO people_assoc (orderid, peopleid, relation_type) VALUES (" . $orderId . ", " . $personId . ", " . $relationType . ");";
    if (mysqli_query($conn, $sql)) {
        $result = mysqli_insert_id($conn);        
    }
    return $result;
}


function create_candy_assoc_record($orderId, $candyId, $conn) {
    $result = -1;
    $sql = "INSERT INTO candy_assoc (orderid, candyid) VALUES (" . $orderId . ", " . $candyId . ");";
    
//    if($result = mysqli_query($conn, $sql)){
//        
//    } else{
//        echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
//    }
    
    if (mysqli_query($conn, $sql)) {
        $result = mysqli_insert_id($conn);        
    }
    return $result;
}


function ensure_person_exists(string $person, bool $truncatedSearch, bool $preferManualEntries, $conn) {

    $people_sql = "SELECT id, name FROM people";
    if ($truncatedSearch) {
        $people_sql .= " WHERE name LIKE '" . $person . "%'";
        if ($preferManualEntries) {
            $people_sql .= " AND auto_detected=0";
        } else {
            $people_sql .= " AND auto_detected=1";
        }
    } else {
        $people_sql .= " WHERE name='" . $person . "'";
    }
    
    $person_id = -1;
    //echo $people_sql;
    $people_result = mysqli_query($conn, $people_sql);
    if ($people_result->num_rows == 1) {
        // found one entry, use located person
        $person_row = $people_result->fetch_assoc();
        $person_id = $person_row["id"];
    } else {
        // found none, or multiple entries, insert new as specified
        if ($people_result->num_rows == 0) {   
            $sql = "INSERT INTO people (name, auto_detected) VALUES ('" . $person . "', 1);";
            if (mysqli_query($conn, $sql)) {
                $last_id = mysqli_insert_id($conn);
                $person_id = $last_id;
            }
        }
        if ($people_result->num_rows > 1) {   
            // what to do if multiples are found?
        
        }
    }
    
    return $person_id;
}



function subject_decision(string $textSource, array $subjectKeywords, array $positiveKeywords, array $negativeKeywords) {
    $result = 0;
    for ($y = 0; $y < count($subjectKeywords); $y++) {
        preg_match('/\b' . $subjectKeywords[$y] . '\b/i', $textSource, $thisMatch, PREG_OFFSET_CAPTURE);
        if (isset($thisMatch[0])) {
            // check for positive and negative words
            for ($zp = 0; $zp < count($positiveKeywords); $zp++) {
                preg_match('/\b' . $positiveKeywords[$zp] . '\b/i', $textSource, $thisMatchPositive, PREG_OFFSET_CAPTURE);
                if (isset($thisMatchPositive[0])) {
                    $result++;
                }
            }
            for ($zn = 0; $zn < count($negativeKeywords); $zn++) {
                preg_match('/\b' . $negativeKeywords[$zn] . '\b/i', $textSource, $thisMatchNegative, PREG_OFFSET_CAPTURE);
                if (isset($thisMatchNegative[0])) {
                    $result--;
                }
            }
        }
    }
    return $result;
}


function person_subject_assoc_distance(string $textSource, string $person, array $subjectKeywords) {
    $resultIndex = -1;
    $resultKeyword = "";
    $personIndex = -1;
    
    // return shortest distance between $person and any of the $subjectKeywords
    
    preg_match('/' . $person . '/i', $textSource, $thisPerson, PREG_OFFSET_CAPTURE);
    if (isset($thisPerson[0])) {
        //echo $thisPerson[0][0] . " found at " . $thisPerson[0][1] . "<br>";
        $personIndex = $thisPerson[0][1];
    }
    
    if ($personIndex > -1) {
        // person found, look for keywords
        
        for ($y = 0; $y < count($subjectKeywords); $y++) {
            //echo $subjectKeywords[$y] . "<br>";
            preg_match('/' . $subjectKeywords[$y] . '/i', $textSource, $thisMatch, PREG_OFFSET_CAPTURE);
            if (isset($thisMatch[0])) {
                // subject found, calculate distance
                
                $distance = intval($personIndex) - intval($thisMatch[0][1]);
                //echo "<br>dist" . $resultIndex . "<br>";
                
                if ($resultIndex == -1) {
                    $resultIndex = abs($distance);
                    $resultKeyword = $subjectKeywords[$y];
                } else {
                    if (abs($distance) < $resultIndex) {
                        $resultIndex = abs($distance);
                        $resultKeyword = $subjectKeywords[$y];
                    }
                }
                
                
                //print_debug($thisMatch);
            }
        }
    }
    
    return array('personId' => $personIndex . "-@", 'distance' => $resultIndex, 'keyword' => $resultKeyword);
}



function metaphone_search(string $needle, string $haystack) {
    $needle_words = preg_split("/\s/", $needle);
    $needle_metaphone = "";
    $haystack_words = preg_split("/\s/", $haystack);
    $haystack_metaphone = "";
    
    //echo "<hr>needle_metaphone: " . $needle_metaphone;
    //echo "<hr>haystack_metaphone: " . $haystack_metaphone;
    
    for ($i = 0; $i < count($needle_words); $i++) {
        $needle_metaphone .= metaphone($needle_words[$i]) . " ";
    }
    $needle_metaphone = trim($needle_metaphone);
    
    for ($i = 0; $i < count($haystack_words); $i++) {
        $haystack_metaphone .= metaphone($haystack_words[$i]) . " ";
    }
    $haystack_metaphone = trim($haystack_metaphone);
    
    preg_match('/\b' . $needle_metaphone . '\b/i', $haystack_metaphone, $thisMatch, PREG_OFFSET_CAPTURE);
    if (isset($thisMatch[0])) {
        return 1;
    }
    return 0;
}



function exact_search(string $needle, string $haystack) {
    preg_match('/\b' . $needle . '\b/i', $haystack, $thisMatch, PREG_OFFSET_CAPTURE);
    if (isset($thisMatch[0])) {
        return 1;
    }
    return 0;
}



function exclude_words(string $subject) {
    $result = $subject;
    $exclusionArray = Constants::getExcludedWordPatterns();
    $result = trim(preg_replace($exclusionArray, "", $result));
    return $result;
}


class Constants {
    private static $excludedWordPatternss = array("/\bfed ex\b/i", "/\bbag\b/i", "/\bboss\b/i", "/\binstructions\b/i", "/\bgift\b/i", "/\bhome\b/i", "/\bbook\b/i", "/\bpro\b/i", "/\boutstanding\b/i", "/\bservice\b/i", "/\bmr\b/i", "/\bfriendly\b/i", "/\bcustomer\b/i", "/\bstatement\b/i", "/\bmission\b/i", "/\bsweetwater\b/i", "/\bexpected\b/i", "/\bship\b/i", "/\bdate\b/i", "/\ba's\b/i", "/\bable\b/i", "/\babout\b/i", "/\babove\b/i", "/\baccording\b/i", "/\baccordingly\b/i", "/\bacross\b/i", "/\bactually\b/i", "/\bafter\b/i", "/\bafterwards\b/i", "/\bagain\b/i", "/\bagainst\b/i", "/\bain't\b/i", "/\ball\b/i", "/\ballow\b/i", "/\ballows\b/i", "/\balmost\b/i", "/\balone\b/i", "/\balong\b/i", "/\balready\b/i", "/\balso\b/i", "/\balthough\b/i", "/\balways\b/i", "/\bam\b/i", "/\bamong\b/i", "/\bamongst\b/i", "/\ban\b/i", "/\band\b/i", "/\banother\b/i", "/\bany\b/i", "/\banybody\b/i", "/\banyhow\b/i", "/\banyone\b/i", "/\banything\b/i", "/\banyway\b/i", "/\banyways\b/i", "/\banywhere\b/i", "/\bapart\b/i", "/\bappear\b/i", "/\bappreciate\b/i", "/\bappropriate\b/i", "/\bare\b/i", "/\baren't\b/i", "/\baround\b/i", "/\bas\b/i", "/\baside\b/i", "/\bask\b/i", "/\basking\b/i", "/\bassociated\b/i", "/\bat\b/i", "/\bavailable\b/i", "/\baway\b/i", "/\bawfully\b/i", "/\bbe\b/i", "/\bbecame\b/i", "/\bbecause\b/i", "/\bbecome\b/i", "/\bbecomes\b/i", "/\bbecoming\b/i", "/\bbeen\b/i", "/\bbefore\b/i", "/\bbeforehand\b/i", "/\bbehind\b/i", "/\bbeing\b/i", "/\bbelieve\b/i", "/\bbelow\b/i", "/\bbeside\b/i", "/\bbesides\b/i", "/\bbest\b/i", "/\bbetter\b/i", "/\bbetween\b/i", "/\bbeyond\b/i", "/\bboth\b/i", "/\bbrief\b/i", "/\bbut\b/i", "/\bby\b/i", "/\bc'mon\b/i", "/\bc's\b/i", "/\bcame\b/i", "/\bcan\b/i", "/\bcan't\b/i", "/\bcannot\b/i", "/\bcant\b/i", "/\bcause\b/i", "/\bcauses\b/i", "/\bcertain\b/i", "/\bcertainly\b/i", "/\bchanges\b/i", "/\bclearly\b/i", "/\bco\b/i", "/\bcom\b/i", "/\bcome\b/i", "/\bcomes\b/i", "/\bconcerning\b/i", "/\bconsequently\b/i", "/\bconsider\b/i", "/\bconsidering\b/i", "/\bcontain\b/i", "/\bcontaining\b/i", "/\bcontains\b/i", "/\bcorresponding\b/i", "/\bcould\b/i", "/\bcouldn't\b/i", "/\bcourse\b/i", "/\bcurrently\b/i", "/\bdefinitely\b/i", "/\bdescribed\b/i", "/\bdespite\b/i", "/\bdid\b/i", "/\bdidn't\b/i", "/\bdifferent\b/i", "/\bdo\b/i", "/\bdoes\b/i", "/\bdoesn't\b/i", "/\bdoing\b/i", "/\bdon't\b/i", "/\bdone\b/i", "/\bdown\b/i", "/\bdownwards\b/i", "/\bduring\b/i", "/\beach\b/i", "/\bedu\b/i", "/\beg\b/i", "/\beight\b/i", "/\beither\b/i", "/\belse\b/i", "/\belsewhere\b/i", "/\benough\b/i", "/\bentirely\b/i", "/\bespecially\b/i", "/\bet\b/i", "/\betc\b/i", "/\beven\b/i", "/\bever\b/i", "/\bevery\b/i", "/\beverybody\b/i", "/\beveryone\b/i", "/\beverything\b/i", "/\beverywhere\b/i", "/\bex\b/i", "/\bexactly\b/i", "/\bexample\b/i", "/\bexcept\b/i", "/\bfar\b/i", "/\bfew\b/i", "/\bfifth\b/i", "/\bfirst\b/i", "/\bfive\b/i", "/\bfollowed\b/i", "/\bfollowing\b/i", "/\bfollows\b/i", "/\bfor\b/i", "/\bformer\b/i", "/\bformerly\b/i", "/\bforth\b/i", "/\bfour\b/i", "/\bfrom\b/i", "/\bfurther\b/i", "/\bfurthermore\b/i", "/\bget\b/i", "/\bgets\b/i", "/\bgetting\b/i", "/\bgiven\b/i", "/\bgives\b/i", "/\bgo\b/i", "/\bgoes\b/i", "/\bgoing\b/i", "/\bgone\b/i", "/\bgot\b/i", "/\bgotten\b/i", "/\bgreetings\b/i", "/\bhad\b/i", "/\bhadn't\b/i", "/\bhappens\b/i", "/\bhardly\b/i", "/\bhas\b/i", "/\bhasn't\b/i", "/\bhave\b/i", "/\bhaven't\b/i", "/\bhaving\b/i", "/\bhe\b/i", "/\bhe's\b/i", "/\bhello\b/i", "/\bhelp\b/i", "/\bhence\b/i", "/\bher\b/i", "/\bhere\b/i", "/\bhere's\b/i", "/\bhereafter\b/i", "/\bhereby\b/i", "/\bherein\b/i", "/\bhereupon\b/i", "/\bhers\b/i", "/\bherself\b/i", "/\bhi\b/i", "/\bhim\b/i", "/\bhimself\b/i", "/\bhis\b/i", "/\bhither\b/i", "/\bhopefully\b/i", "/\bhow\b/i", "/\bhowbeit\b/i", "/\bhowever\b/i", "/\bi'd\b/i", "/\bi'll\b/i", "/\bi'm\b/i", "/\bi've\b/i", "/\bie\b/i", "/\bif\b/i", "/\bignored\b/i", "/\bimmediate\b/i", "/\bin\b/i", "/\binasmuch\b/i", "/\binc\b/i", "/\bindeed\b/i", "/\bindicate\b/i", "/\bindicated\b/i", "/\bindicates\b/i", "/\binner\b/i", "/\binsofar\b/i", "/\binstead\b/i", "/\binto\b/i", "/\binward\b/i", "/\bis\b/i", "/\bisn't\b/i", "/\bit\b/i", "/\bit'd\b/i", "/\bit'll\b/i", "/\bit's\b/i", "/\bits\b/i", "/\bitself\b/i", "/\bjust\b/i", "/\bkeep\b/i", "/\bkeeps\b/i", "/\bkept\b/i", "/\bknow\b/i", "/\bknown\b/i", "/\bknows\b/i", "/\blast\b/i", "/\blately\b/i", "/\blater\b/i", "/\blatter\b/i", "/\blatterly\b/i", "/\bleast\b/i", "/\bless\b/i", "/\blest\b/i", "/\blet\b/i", "/\blet's\b/i", "/\blike\b/i", "/\bliked\b/i", "/\blikely\b/i", "/\blittle\b/i", "/\blook\b/i", "/\blooking\b/i", "/\blooks\b/i", "/\bltd\b/i", "/\bmainly\b/i", "/\bmany\b/i", "/\bmay\b/i", "/\bmaybe\b/i", "/\bme\b/i", "/\bmean\b/i", "/\bmeanwhile\b/i", "/\bmerely\b/i", "/\bmight\b/i", "/\bmore\b/i", "/\bmoreover\b/i", "/\bmost\b/i", "/\bmostly\b/i", "/\bmuch\b/i", "/\bmust\b/i", "/\bmy\b/i", "/\bmyself\b/i", "/\bname\b/i", "/\bnamely\b/i", "/\bnd\b/i", "/\bnear\b/i", "/\bnearly\b/i", "/\bnecessary\b/i", "/\bneed\b/i", "/\bneeds\b/i", "/\bneither\b/i", "/\bnever\b/i", "/\bnevertheless\b/i", "/\bnew\b/i", "/\bnext\b/i", "/\bnine\b/i", "/\bno\b/i", "/\bnobody\b/i", "/\bnon\b/i", "/\bnone\b/i", "/\bnoone\b/i", "/\bnor\b/i", "/\bnormally\b/i", "/\bnot\b/i", "/\bnothing\b/i", "/\bnovel\b/i", "/\bnow\b/i", "/\bnowhere\b/i", "/\bobviously\b/i", "/\bof\b/i", "/\boff\b/i", "/\boften\b/i", "/\boh\b/i", "/\bok\b/i", "/\bokay\b/i", "/\bold\b/i", "/\bon\b/i", "/\bonce\b/i", "/\bone\b/i", "/\bones\b/i", "/\bonly\b/i", "/\bonto\b/i", "/\bor\b/i", "/\bother\b/i", "/\bothers\b/i", "/\botherwise\b/i", "/\bought\b/i", "/\bour\b/i", "/\bours\b/i", "/\bourselves\b/i", "/\bout\b/i", "/\boutside\b/i", "/\bover\b/i", "/\boverall\b/i", "/\bown\b/i", "/\bparticular\b/i", "/\bparticularly\b/i", "/\bper\b/i", "/\bperhaps\b/i", "/\bplaced\b/i", "/\bplease\b/i", "/\bplus\b/i", "/\bpossible\b/i", "/\bpresumably\b/i", "/\bprobably\b/i", "/\bprovides\b/i", "/\bque\b/i", "/\bquite\b/i", "/\bqv\b/i", "/\brather\b/i", "/\brd\b/i", "/\bre\b/i", "/\breally\b/i", "/\breasonably\b/i", "/\bregarding\b/i", "/\bregardless\b/i", "/\bregards\b/i", "/\brelatively\b/i", "/\brespectively\b/i", "/\bright\b/i", "/\bsaid\b/i", "/\bsame\b/i", "/\bsaw\b/i", "/\bsay\b/i", "/\bsaying\b/i", "/\bsays\b/i", "/\bsecond\b/i", "/\bsecondly\b/i", "/\bsee\b/i", "/\bseeing\b/i", "/\bseem\b/i", "/\bseemed\b/i", "/\bseeming\b/i", "/\bseems\b/i", "/\bseen\b/i", "/\bself\b/i", "/\bselves\b/i", "/\bsensible\b/i", "/\bsent\b/i", "/\bserious\b/i", "/\bseriously\b/i", "/\bseven\b/i", "/\bseveral\b/i", "/\bshall\b/i", "/\bshe\b/i", "/\bshould\b/i", "/\bshouldn't\b/i", "/\bsince\b/i", "/\bsix\b/i", "/\bso\b/i", "/\bsome\b/i", "/\bsomebody\b/i", "/\bsomehow\b/i", "/\bsomeone\b/i", "/\bsomething\b/i", "/\bsometime\b/i", "/\bsometimes\b/i", "/\bsomewhat\b/i", "/\bsomewhere\b/i", "/\bsoon\b/i", "/\bsorry\b/i", "/\bspecified\b/i", "/\bspecify\b/i", "/\bspecifying\b/i", "/\bstill\b/i", "/\bsub\b/i", "/\bsuch\b/i", "/\bsup\b/i", "/\bsure\b/i", "/\bt's\b/i", "/\btake\b/i", "/\btaken\b/i", "/\btell\b/i", "/\btends\b/i", "/\bth\b/i", "/\bthan\b/i", "/\bthank\b/i", "/\bthanks\b/i", "/\bthanx\b/i", "/\bthat\b/i", "/\bthat's\b/i", "/\bthats\b/i", "/\bthe\b/i", "/\btheir\b/i", "/\btheirs\b/i", "/\bthem\b/i", "/\bthemselves\b/i", "/\bthen\b/i", "/\bthence\b/i", "/\bthere\b/i", "/\bthere's\b/i", "/\bthereafter\b/i", "/\bthereby\b/i", "/\btherefore\b/i", "/\btherein\b/i", "/\btheres\b/i", "/\bthereupon\b/i", "/\bthese\b/i", "/\bthey\b/i", "/\bthey'd\b/i", "/\bthey'll\b/i", "/\bthey're\b/i", "/\bthey've\b/i", "/\bthink\b/i", "/\bthird\b/i", "/\bthis\b/i", "/\bthorough\b/i", "/\bthoroughly\b/i", "/\bthose\b/i", "/\bthough\b/i", "/\bthree\b/i", "/\bthrough\b/i", "/\bthroughout\b/i", "/\bthru\b/i", "/\bthus\b/i", "/\bto\b/i", "/\btogether\b/i", "/\btoo\b/i", "/\btook\b/i", "/\btoward\b/i", "/\btowards\b/i", "/\btried\b/i", "/\btries\b/i", "/\btruly\b/i", "/\btry\b/i", "/\btrying\b/i", "/\btwice\b/i", "/\btwo\b/i", "/\bun\b/i", "/\bunder\b/i", "/\bunfortunately\b/i", "/\bunless\b/i", "/\bunlikely\b/i", "/\buntil\b/i", "/\bunto\b/i", "/\bup\b/i", "/\bupon\b/i", "/\bus\b/i", "/\buse\b/i", "/\bused\b/i", "/\buseful\b/i", "/\buses\b/i", "/\busing\b/i", "/\busually\b/i", "/\bvalue\b/i", "/\bvarious\b/i", "/\bvery\b/i", "/\bvia\b/i", "/\bviz\b/i", "/\bvs\b/i", "/\bwant\b/i", "/\bwants\b/i", "/\bwas\b/i", "/\bwasn't\b/i", "/\bway\b/i", "/\bwe\b/i", "/\bwe'd\b/i", "/\bwe'll\b/i", "/\bwe're\b/i", "/\bwe've\b/i", "/\bwelcome\b/i", "/\bwell\b/i", "/\bwent\b/i", "/\bwere\b/i", "/\bweren't\b/i", "/\bwhat\b/i", "/\bwhat's\b/i", "/\bwhatever\b/i", "/\bwhen\b/i", "/\bwhence\b/i", "/\bwhenever\b/i", "/\bwhere\b/i", "/\bwhere's\b/i", "/\bwhereafter\b/i", "/\bwhereas\b/i", "/\bwhereby\b/i", "/\bwherein\b/i", "/\bwhereupon\b/i", "/\bwherever\b/i", "/\bwhether\b/i", "/\bwhich\b/i", "/\bwhile\b/i", "/\bwhither\b/i", "/\bwho\b/i", "/\bwho's\b/i", "/\bwhoever\b/i", "/\bwhole\b/i", "/\bwhom\b/i", "/\bwhose\b/i", "/\bwhy\b/i", "/\bwill\b/i", "/\bwilling\b/i", "/\bwish\b/i", "/\bwith\b/i", "/\bwithin\b/i", "/\bwithout\b/i", "/\bwon't\b/i", "/\bwonder\b/i", "/\bwould\b/i", "/\bwouldn't\b/i", "/\byes\b/i", "/\byet\b/i", "/\byou\b/i", "/\byou'd\b/i", "/\byou'll\b/i", "/\byou're\b/i", "/\byou've\b/i", "/\byour\b/i", "/\byours\b/i", "/\byourself\b/i", "/\byourselves\b/i", "/\bzero\b/i");
    public static function getExcludedWordPatterns() {
        return self::$excludedWordPatternss;
    }
}



?>