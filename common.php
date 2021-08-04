<?php


function print_debug($var) {
    $formatted = print_r($var, true); 
    print "<pre>" . htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8', true) . "</pre>";
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



function metaphone_search(string $needle, string $haystack) {
    $needle_words = preg_split("/\s/", $needle);
    $needle_metaphone = "";
    $haystack_words = preg_split("/\s/", $haystack);
    $haystack_metaphone = "";
    
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



?>