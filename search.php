<?php
$pageTitle = "Sweetwater Comment Search";
$currentNavButton = "search";

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/conn.php');
require_once(__DIR__ . '/common.php');
require_once(__DIR__ . '/header.php');


$sql_select_count = "SELECT count(orderid) AS CountOfRecords FROM sweetwater_test";
$sql_select_fields = "SELECT sw.*, DATE_FORMAT(sw.shipdate_expected, '%b. %D, %Y') AS shipdate_expected_short, (sw.shipdate_expected > '1970-01-02') AS has_ship_date, (SELECT count(id) FROM candy_assoc WHERE orderid=sw.orderid) AS candy_count, (SELECT count(id) FROM people_assoc WHERE orderid=sw.orderid) AS people_count FROM sweetwater_test sw";

$sql_where = " WHERE metadata_generated=1 "; // only include comments that have been processed

$filterSignatureRequired = "";
$filterCallWanted = "";
$filterCallCompleted = "";
$filterPeopleText = "";
$filterCandyText = "";
$filterKeywordText = "";
$filterShipDateBegin = "";
$filterShipDateEnd = "";
$filterCandy = "";
$filterPeople = "";

$sortFields = array("orderid");
$sortField = "orderid";
$sortDirection = "asc";
$page_size = 8;
$page_current = 1;
if (!empty($_GET)) {
    if (isset($_GET["page"])) {
        $page_current = filter_var($_GET["page"], FILTER_VALIDATE_INT);
        if ($page_current < 1) {
            $page_current = 1;
        }
    }
    if (isset($_GET["sort-field"])) {        
        if (in_array($_GET["sort-field"], $sortFields)) {
            $sortField = $_GET["sort-field"];
        }
    }
    if (isset($_GET["sort-dir"])) {
        if ($_GET["sort-dir"] == "desc") {
            $sortDirection = "desc";
        } else {
            $sortDirection = "asc";
        }
    }
    
    // WHERE CLAUSE
    if (isset($_GET["people-text"])) {
        $filterPeopleText = filter_var($_GET['people-text'], FILTER_SANITIZE_STRING);
        $filterPeopleText = preg_replace("/[^A-Za-z0-9\ \,\.\"\(\)\'\-]/", "", $filterPeopleText);        
        if (strlen($filterPeopleText) > 0) {
            $peopleList = explode(",", $filterPeopleText);
            $peopleLookupWhere = "WHERE ";
            $hasEntries = 0;
            for ($l = 0; $l < count($peopleList); $l++) {
                if (strlen($peopleList[$l]) > 0) {
                    $hasEntries++;
                    if ($l > 0) {
                        $peopleLookupWhere .= " OR ";
                    }
                    $peopleLookupWhere .= "name LIKE '%" . $peopleList[$l] . "%' OR name SOUNDS LIKE '" . $peopleList[$l] . "'";
                }
            }
            if (count($peopleList) > 0 && $hasEntries > 0) {
                $sql_peopleLookup = "SELECT id FROM people " . $peopleLookupWhere;
                $sql_where .= " AND orderid IN (SELECT orderid FROM people_assoc WHERE peopleid IN (" . $sql_peopleLookup . "))";
            }
        }
    }
    if (isset($_GET["candy-text"])) {
        $filterCandyText = filter_var($_GET['candy-text'], FILTER_SANITIZE_STRING);
        $filterCandyText = preg_replace("/[^A-Za-z0-9\ \,\!\.\?\'\-\&]/", "", $filterCandyText);
        if (strlen($filterCandyText) > 0) {
            $candies = explode(",", $filterCandyText);
            $candyLookupWhere = "WHERE ";
            $hasEntries = 0;
            for ($c = 0; $c < count($candies); $c++) {
                if (strlen($candies[$c]) > 0) {
                    $hasEntries++;
                    if ($c > 0) {
                        $candyLookupWhere .= " OR ";
                    }
                    $candyLookupWhere .= "name LIKE '%" . $candies[$c] . "%' OR name SOUNDS LIKE '" . $candies[$c] . "'";
                }
            }
            if (count($candies) > 0 && $hasEntries > 0) {
                $sql_candyLookup = "SELECT id FROM candy " . $candyLookupWhere;
                $sql_where .= " AND orderid IN (SELECT orderid FROM candy_assoc WHERE candyid IN (" . $sql_candyLookup . "))";
            }
        }        
    }
    if (isset($_GET["keyword-text"])) {
        $filterKeywordText = filter_var($_GET['keyword-text'], FILTER_SANITIZE_STRING);
        $sql_where .= " AND comments LIKE '%" . $filterKeywordText . "%'";        
    }
    if (isset($_GET["ship-date-start"])) {
        $startDateParts = getdate(strtotime($_GET["ship-date-start"]));
        $filterShipDateBegin = $startDateParts["year"] . "-" . str_pad($startDateParts["mon"], 2, "0", STR_PAD_LEFT) . "-" . str_pad($startDateParts["mday"], 2, "0", STR_PAD_LEFT);

        $origin = new DateTime('1970-1-2');
        $target = new DateTime($filterShipDateBegin);
        $interval = $origin->diff($target);
            
        if (intval($interval->format('%R%a')) > 0) {
            $sql_where .= " AND shipdate_expected>='" . $filterShipDateBegin . "'";
        } else {
            $filterShipDateBegin = "";
        }
    }
    if (isset($_GET["ship-date-end"])) {
        $endDateParts = getdate(strtotime($_GET["ship-date-end"]));
        $filterShipDateEnd = $endDateParts["year"] . "-" . str_pad($endDateParts["mon"], 2, "0", STR_PAD_LEFT) . "-" . str_pad($endDateParts["mday"], 2, "0", STR_PAD_LEFT);

        $origin = new DateTime('1970-1-2');
        $target = new DateTime($filterShipDateEnd);
        $interval = $origin->diff($target);
            
        if (intval($interval->format('%R%a')) > 0) {
            $sql_where .= " AND shipdate_expected<='" . $filterShipDateEnd . "'";
        } else {
            $filterShipDateEnd = "";
        }
    }
    if (isset($_GET["sig-req"])) {
        if ($_GET["sig-req"] == "yes") {
            $sql_where .= " AND require_signature=1";
        } elseif ($_GET["sig-req"] == "no") {
            $sql_where .= " AND require_signature=0";
        }
    }
    if (isset($_GET["call-wanted"])) {
        if ($_GET["call-wanted"] == "yes") {
            $sql_where .= " AND call_wanted=1";
        } elseif ($_GET["call-wanted"] == "no") {
            $sql_where .= " AND call_wanted=0";
        }
    }
    if (isset($_GET["call-completed"])) {
        if ($_GET["call-completed"] == "yes") {
            $sql_where .= " AND call_completed=1";
        } elseif ($_GET["call-completed"] == "no") {
            $sql_where .= " AND call_completed=0";
        }
    }
    if (isset($_GET["people-type"])) {
        if ($_GET["people-type"] == "sales") {
            $sql_where .= " AND orderid IN (SELECT orderid FROM people_assoc WHERE relation_type=1)";
        } elseif ($_GET["people-type"] == "referral") {
            $sql_where .= " AND orderid IN (SELECT orderid FROM people_assoc WHERE relation_type=2)";
        } elseif ($_GET["people-type"] == "none") {
            $sql_where .= " AND orderid NOT IN (SELECT orderid FROM people_assoc WHERE relation_type=1 OR relation_type=2)";
        }
    }

}

$sql_select_fields .= $sql_where;
$sql_select_count .= $sql_where;

$sql_select_fields .= " ORDER BY " . $sortField . " " . $sortDirection;
$sql_select_fields .= " LIMIT " . (($page_current - 1) * $page_size) . ", " . $page_size;

function is_box_checked($field, $value) {
    $result = false;    
    if (isset($_GET[$field])) {
        if ($_GET[$field] == $value) {
            $result = true;
        }
    }    
    return $result;
}

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
                
                <div class="bodySection"  style="text-align:left;">
                    
                    
                    
                    <div id="commentFilters" class="progress-bar-striped" style="background-color:#eeeeee;padding:0px 10px 0px 10px;padding-bottom:10px; border-bottom:4px solid #D61721;border-bottom-left-radius: 26px;border-bottom-right-radius: 26px; border-top:4px solid #0172B9;border-top-left-radius: 26px;border-top-right-radius: 26px;">
                    
                    
<form action="search.php" method="get" id="filter-form" name="filter-form">
  
    <input type="hidden" name="page" id="filterPage" value="<?= $page_current;?>" />
    <input type="hidden" name="sort-field" id="filterSortField" value="<?= $sortField;?>" />
    <input type="hidden" name="sort-dir" id="filterSortDirection" value="<?= $sortDirection;?>" />
    
  <div class="row mb-3">
    <label class="col-sm-2 col-form-label" style="text-align:right;"><b>Ship Date Range:</b></label>
    <div class="col-sm-5">      
        <label for="filterStartShipDate" style="font-size:.75rem;">Beginning</label>  
        <input type="date" class="form-control" id="filter-ship-date-start" name="ship-date-start" value="<?= $filterShipDateBegin; ?>">          
    </div>    
    <div class="col-sm-5">      
        <label for="filterStartShipDate" style="font-size:.75rem;">End</label>  
        <input type="date" class="form-control" id="filter-ship-date-end" name="ship-date-end" value="<?= $filterShipDateEnd; ?>">  
    </div>
  </div>
  <div class="row mb-3">
      
          <legend class="col-form-label col-sm-2 pt-0" style="text-align:right;"><b>People Mentioned:</b></legend>
            <div class="col-sm-2">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="people-type" id="people-type-1" value="sales" <?= (is_box_checked("people-type", "sales")) ? "checked" : "" ?>>
                <label class="form-check-label" for="people-type-1">
                    <span class="sweetwaterLogoType" style="text-align: left; font-weight: 700; font-family: 'Oswald', sans-serif;">Sweetwater</span> <span style="text-align: left; font-weight: 700; font-family: 'Oswald', sans-serif;">Sales</span>
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="people-type" id="people-type-2" value="referral" <?= (is_box_checked("people-type", "referral")) ? "checked" : "" ?>>
                <label class="form-check-label" for="people-type-2">
                  Referrers
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="people-type" id="people-type-3" value="none" <?= (is_box_checked("people-type", "none")) ? "checked" : "" ?>>
                <label class="form-check-label" for="people-type-3">
                  No Mentions
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="people-type" id="people-type-4" value="opt" <?= (!is_box_checked("people-type", "sales") && !is_box_checked("people-type", "referral") && !is_box_checked("people-type", "none")) ? "checked" : "" ?>>
                <label class="form-check-label" for="people-type-4">
                  Optional
                </label>
              </div>
            </div>
          
          <legend class="col-form-label col-sm-2 pt-0" style="text-align:right;"><b>Text Search:</b></legend>
            <div class="col-sm-6">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text bg-secondary text-light progress-bar-striped" style="border-radius:10px 0px 0px 10px;font-weight:700;" id="textsearch-keyword">Keyword</span>
                <input type="text" class="form-control form-control-sm"  aria-describedby="textsearch-keyword" id="keyword" name="keyword-text" value="<?= $filterKeywordText; ?>">
              </div>
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text bg-secondary text-light progress-bar-striped" style="border-radius:10px 0px 0px 10px;font-weight:700;" id="textsearch-person">People</span>
                <input class="form-control" type="email" multiple list="personDatalistOptions" id="personDataList" name="people-text" placeholder="" value="<?= $filterPeopleText; ?>" aria-describedby="textsearch-person">
                <datalist id="personDatalistOptions">
<?php
        $sql_personList = "SELECT p.* FROM people p WHERE p.auto_detected=0 ORDER BY p.name ASC";
        $personList_result = mysqli_query($conn, $sql_personList);
        if ($personList_result->num_rows > 0) {
            while($personList_row = $personList_result->fetch_assoc()) {
                ?>
                <option value="<?= $personList_row["name"]?>">
                <?php                
            }
        }
?>                      
                </datalist>
              </div>  
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text bg-secondary text-light progress-bar-striped" style="border-radius:10px 0px 0px 10px;font-weight:700;" id="textsearch-candy">Candy</span>
                <input class="form-control" type="email" multiple list="candyDatalistOptions" id="candyDataList" name="candy-text" placeholder="" value="<?= $filterCandyText; ?>" aria-describedby="textsearch-candy">
                <datalist id="candyDatalistOptions">
<?php
        $sql_candyList = "SELECT * FROM candy ORDER BY name ASC";
        $candyList_result = mysqli_query($conn, $sql_candyList);
        if ($candyList_result->num_rows > 0) {
            while($candyList_row = $candyList_result->fetch_assoc()) {
                ?>
                <option value="<?= $candyList_row["name"]?>">
                <?php                
            }
        }
?>                      
                </datalist>
              </div> 
            </div>
      
  </div>
  <fieldset class="row mb-3">
      
      
    <legend class="col-form-label col-sm-2 pt-0" style="text-align:right;"><b>Signature Required:</b></legend>
    <div class="col-sm-2">
      <div class="form-check">
        <input class="form-check-input" type="radio" name="sig-req" id="filter-sig-req-1" value="yes" <?= (is_box_checked("sig-req", "yes")) ? "checked" : "" ?>>
        <label class="form-check-label" for="filter-sig-req-1">
          Yes
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="sig-req" id="filter-sig-req-2" value="no" <?= (is_box_checked("sig-req", "no")) ? "checked" : "" ?>>
        <label class="form-check-label" for="filter-sig-req-2">
          No
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="sig-req" id="filter-sig-req-3" value="opt" <?= (!is_box_checked("sig-req", "yes") && !is_box_checked("sig-req", "no")) ? "checked" : "" ?>>
        <label class="form-check-label" for="filter-sig-req-3">
          Optional
        </label>
      </div>
    </div>
      
    <legend class="col-form-label col-sm-2 pt-0" style="text-align:right;"><b>Call Wanted:</b></legend>
    <div class="col-sm-2">
      <div class="form-check">
        <input class="form-check-input" type="radio" name="call-wanted" id="call-wanted-1" value="yes" <?= (is_box_checked("call-wanted", "yes")) ? "checked" : "" ?>>
        <label class="form-check-label" for="call-wanted-1">
          Yes
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="call-wanted" id="call-wanted-2" value="no" <?= (is_box_checked("call-wanted", "no")) ? "checked" : "" ?>>
        <label class="form-check-label" for="call-wanted-2">
          No
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="call-wanted" id="call-wanted-3" value="opt" <?= (!is_box_checked("call-wanted", "yes") && !is_box_checked("call-wanted", "no")) ? "checked" : "" ?>>
        <label class="form-check-label" for="call-wanted-3">
          Optional
        </label>
      </div>
    </div>
    
    <legend class="col-form-label col-sm-2 pt-0" style="text-align:right;"><b>Call Completed:</b></legend>
    <div class="col-sm-2">
      <div class="form-check">
        <input class="form-check-input" type="radio" name="call-completed" id="call-completed-1" value="yes" <?= (is_box_checked("call-completed", "yes")) ? "checked" : "" ?>>
        <label class="form-check-label" for="call-completed-1">
          Yes
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="call-completed" id="call-completed-2" value="no" <?= (is_box_checked("call-completed", "no")) ? "checked" : "" ?>>
        <label class="form-check-label" for="call-completed-2">
          No
        </label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="call-completed" id="call-completed-3" value="opt" <?= (!is_box_checked("call-completed", "yes") && !is_box_checked("call-completed", "no")) ? "checked" : "" ?>>
        <label class="form-check-label" for="call-completed-3">
          Optional
        </label>
      </div>
    </div>
  </fieldset>
    <div style="text-align:center;">
        <button type="button" class="btn btn-success progress-bar-striped progress-bar-animated" style="font-weight:700;border-radius:20px;font-family: 'Oswald', sans-serif;font-size:1em;" id="filterCommentsBtn" onclick="navPage(1);">Filter Comments <i class="fas fa-play" style="margin-left:0.5em;margin-right:0.0em;font-size:1.0em;"></i></button>
    </div>
</form>              
   
                    </div>
                    
   
                    
                    
                    
                    
                    
<div id="searchResults" style="margin-top:15px;">
     

<?php
       
mysqli_query($conn, "set character_set_results='utf8'"); 
$result = mysqli_query($conn, $sql_select_fields);
$result_count = mysqli_query($conn, $sql_select_count);

$prev_page = -1;
$next_page = -1;
$total_pages = -1;
$total_records = -1;

if ($result_count->num_rows == 1) {
    $total_records = $result_count->fetch_assoc();    
} else {
    // INVALID SITUATION, should die();
}

$total_pages = ceil($total_records["CountOfRecords"] / $page_size);

if ($page_current > $total_pages) {
    $page_current = $total_pages;
}
if ($page_current < 1) {
    $page_current = 1;
}

if ($page_current < $total_pages) {
    $next_page = ($page_current + 1);
}

if (($page_current - 1) > 0) {
    $prev_page = ($page_current - 1);
}

$page_proximity = 4;
$page_range_size = (($page_proximity * 2) + 1);
$page_range_start = -1;
$page_range_end = -1;

$max_page = $page_current + $page_proximity;
$min_page = $page_current - $page_proximity;

if ($max_page > $total_pages) {
    $offset = $max_page - $total_pages;
    $max_page = $total_pages;    
    $min_page = $min_page - $offset;
}

if ($total_pages < $page_range_size) {
    $page_range_size = $total_pages;
}

if ($max_page < $page_range_size) {
    $max_page = $page_range_size;
}

if ($min_page < 1) {
    $min_page = 1;
}

if ($result->num_rows > 0) {
    // FOUND RESULTS
   
?>
    
    
<div class="row row-cols-1 row-cols-md-4 g-4">                    
<?php

while($row = $result->fetch_assoc()) {
    
    $commentBody = filter_var($row["comments"], FILTER_SANITIZE_STRING);
    
    // Remove expected ship date from comment body
    if ($row["has_ship_date"] == 1) {
        $commentBody = preg_replace("/Expected Ship Date: \d\d\/\d\d\/\d\d/", "", $commentBody);
    }
    
    // Highlight Candy
    if ($row["candy_count"] > 0) {
        $sql_candy = "SELECT c.* FROM candy c "
               . "INNER JOIN candy_assoc ca ON ca.candyid = c.id "
               . "WHERE ca.orderid = " . $row["orderid"];
        $candy_result = mysqli_query($conn, $sql_candy);
        if ($candy_result->num_rows > 0) {
            while($candy_row = $candy_result->fetch_assoc()) {
                // Note: metaphone matches won't be found
                $commentBody = preg_replace("/(" . $candy_row["name"] . ")/i", "<span style=\"color:#fff;background-color:#" . $candy_row["primary_color"] . ";box-shadow:#" . $candy_row["secondary_color"] . " 2px 0px 1px, #" . $candy_row["secondary_color"] . " -2px 0px 1px, #" . $candy_row["secondary_color"] . " 0px 2px 1px, #" . $candy_row["secondary_color"] . " 0px -2px 1px;text-shadow:#000 1px 0px 0px, #000 -1px 0px 0px, #000 0px 1px 0px, #000 0px -1px 0px;border-radius:5px;font-weight:700;\">&nbsp;$1&nbsp;</span>", $commentBody);
            }
        }
    }    
    
    // Highlight People
    if ($row["people_count"] > 0) {
        $sql_people = "SELECT p.*, pa.relation_type FROM people p "
                    . "INNER JOIN people_assoc pa ON pa.peopleid = p.id "
                    . "WHERE pa.orderid = " . $row["orderid"];
        $people_result = mysqli_query($conn, $sql_people);
        
        if ($people_result->num_rows > 0) {
            while($people_row = $people_result->fetch_assoc()) {
                // Note: metaphone matches won't be found
                // TODO: Strip off non-word chars from beginning and end.
                
                $color1 = "000000";
                $color2 = "FFFFFF";
                $peopleClass = "light";
                $swFont = "";
                $swShadow = "";
                $swTextDec1 = "";
                $swTextDec2 = "";
                if ($people_row["relation_type"] == 1) { // Sales
                    $color1 = "CCCCEE";
                    $color2 = "000000";
                    $peopleClass = "bg-primary";
                    $swFont = "font-family: 'Oswald', sans-serif;";
                    $swShadow = "inset 0 -0.2em 0.5em rgba(0,0,0,0.75), ";
                    $swTextDec1 = "<i class=\"fas fa-user\" style=\"margin-left:0.0em;margin-right:0.0em;font-size:1.0em;\"></i>&nbsp;<span style=\"text-decoration: underline;text-decoration-color: #D61721;text-decoration-thickness: 2px;\">";
                    $swTextDec2 = "</span>";
                } elseif ($people_row["relation_type"] == 2) { // Referral
                    $color1 = "DDCC00"; // FFDD33
                    $color2 = "BBAA22";
                    $peopleClass = "";
                    $swTextDec1 = "<i class=\"fas fa-award\" style=\"margin-left:0.0em;margin-right:0.0em;font-size:1.0em;\"></i>&nbsp;<span style=\"text-decoration: none;text-decoration-color: #FFFFFF;text-decoration-thickness: 1px;\">";
                    $swTextDec2 = "</span>";
                }
                $commentBody = preg_replace("/(" . $people_row["name"] . ")/i", "<span class=\"" . $peopleClass . " progress-bar-striped progress-bar-animated\" style=\"color:#fff;background-color:#" . $color1 . ";box-shadow:" . $swShadow . "#" . $color2 . " 2px 0px 1px, #" . $color2 . " -2px 0px 1px, #" . $color2 . " 0px 2px 1px, #" . $color2 . " 0px -2px 1px;text-shadow:#000 1px 0px 0px, #000 -1px 0px 0px, #000 0px 1px 0px, #000 0px -1px 0px;border-radius:5px;font-weight:700;" . $swFont . "\">&nbsp;" . $swTextDec1 . "$1" . $swTextDec2 . "&nbsp;</span>", $commentBody);
            }
        }        
    }

    $commentCallWanted = "";
    $commentCallCompleted = "";
    $commentSignatureRequired = "";
    if ($row["call_wanted"] == 1) {
        $commentCallWanted = " checked";
    }
    if ($row["call_completed"] == 1) {
        $commentCallCompleted = " checked";
    }
    if ($row["require_signature"] == 1) {
        $commentSignatureRequired = " checked";
    }
                
?>
                    
           
        <div class="col card-group">              
            <div class="card bg-light text-dark mb-3" style="max-width: 18rem;">
              <div class="card-header bg-primary progress-bar-striped text-light">#<?= $row["orderid"]; ?></div>
              <div class="card-body">
                <p class="card-text"><?= $commentBody ?></p>
              </div>
              <div class="card-header">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="flexSwitchCallWanted" <?= $commentCallWanted; ?> disabled>
                    <label class="form-check-label" for="flexSwitchCallWanted">Call Wanted</label>
                  </div>
                  <?php
                  if ($row["call_wanted"] == 1) {
                  ?>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" onclick="completeCallToggle(<?= $row["orderid"]; ?>);" id="call-completed-<?= $row["orderid"]; ?>" <?= $commentCallCompleted; ?>>
                    <label class="form-check-label" for="call-completed-<?= $row["orderid"]; ?>">Call Completed</label> <span id="save-notice-call-<?= $row["orderid"]; ?>" class="bg-warning" style="display:none;padding:5px;border-radius:5px;">Saved!</span>
                  </div>
                  <?php
                  }
                  ?>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="flexSwitchRequireSignature" <?= $commentSignatureRequired; ?> disabled>
                    <label class="form-check-label" for="flexSwitchRequireSignature">Require Signature</label>
                  </div>
              </div>
              <?php
              if ($row["has_ship_date"] == 1) {
              ?>
              <div class="card-footer bg-dark">
                  <small class="text-light">Expected Ship Date: <b><?= $row["shipdate_expected_short"]; ?></b></small>
              </div>
              <?php
              }
              ?>
            </div>
        </div>
                     
<?php
}

} else {
    // NO RESULTS
    
}
?>
                      
</div> <!-- Each Result -->
                  

                    <div class="progress-bar-striped" style="background-color:#eeeeee;padding:20px 10px 5px 10px; border-bottom:4px solid #D61721;border-bottom-left-radius: 26px;border-bottom-right-radius: 26px; border-top:4px solid #0172B9;border-top-left-radius: 26px;border-top-right-radius: 26px;">
                        <nav aria-label="...">
                            <ul class="pagination justify-content-center">
                              <!--<li class="page-item disabled">
                                <span class="page-link">Previous</span>
                              </li>-->
                              <li class="page-item">
                                <a class="page-link" href="#" onclick="prevPage();">Previous</a>
                              </li>

                              <?php
                              if ($page_current > 1 && $min_page > 1) {
                              ?>
                              <li class="page-item">
                                <a class="page-link" href="#" onclick="navPage(1);">First</a>
                              </li>
                              <?php
                              }



                              for ($p = $min_page; $p <= $max_page; $p++) {
                                if ($p == $page_current) {
                                ?>
                                    <li class="page-item active " aria-current="page">
                                      <span class="page-link"><?=$p?></span>
                                    </li>
                                <?php  
                                } else {
                                ?>
                                    <li class="page-item"><a class="page-link" href="#" onclick="navPage(<?=$p?>);"><?=$p?></a></li>
                                <?php  
                                }                            
                              }

                              if ($max_page < $total_pages) {
                              ?>
                              <li class="page-item">
                                <a class="page-link" href="#" onclick="navPage(<?= $total_pages?>);">Last</a>
                              </li>
                              <?php
                              }              
                              ?>




                              <li class="page-item">
                                <a class="page-link" href="#" onclick="nextPage();">Next</a>
                              </li>
                            </ul>
                          </nav>      
                    </div>


                </div> <!-- Search Results Div -->                
                </div> <!-- bodySection Div -->

            </div>
            <div class="col-lg-2">
                
            </div>
        </div>
    </div>

    <script type="text/javascript">
    
    
    function nextPage() {
       filterComments(<?= $next_page; ?>);
    }
    
    function prevPage() {
       filterComments(<?= $prev_page; ?>);
    }
    
    function navPage(pageNum) {
       filterComments(pageNum);
    }
    
    function filterComments(resultPage) {
        var filterForm = document.forms['filter-form'];
        var filterFormPage = filterForm.elements['page']; 
        filterFormPage.value = resultPage;
        document.getElementById('filter-form').submit();
    }
    
    function flashSaved(orderid) {
        document.getElementById('save-notice-call-' + orderid).style.display = 'inline';
        document.getElementById('save-notice-call-' + orderid).style.opacity = '1.0';
        setTimeout("fadeSaved(" + orderid + ", 100, 10, 100);", 1000);
    }
    
    function fadeSaved(orderid, opacity, stepSize, stepDelay) {        
        if (opacity > 0) {   
            document.getElementById('save-notice-call-' + orderid).style.opacity = Number.parseFloat((opacity / 100)).toFixed(2);            
            setTimeout("fadeSaved(" + orderid + ", " + (opacity - stepSize) + ", " + stepSize + ", " + stepDelay + ");", stepDelay);            
        } else {
            setTimeout("clearSaved(" + orderid + ");", 100);
        }
    }
    
    function clearSaved(orderid) {
        document.getElementById('save-notice-call-' + orderid).style.display = 'none';
    }
    
    function completeCallToggle(orderId) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var jsonResponse = JSON.parse(xmlhttp.responseText);
                if (document.forms['filter-form'].elements['call-completed'].value != "opt") {
                    //results set may be stale now
                    if (confirm("The update you processed has altered current query results. Click OK to refresh.")) {
                        filterComments(1);
                    }
                } else {
                    flashSaved(orderId);
                }
            }
        }
        xmlhttp.open("POST", "metadata-generator.php", true);
        xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xmlhttp.send("action=completeCallToggle&orderid=" + orderId);
    }
    
    
    </script>

<?php
require_once(__DIR__ . '/footer.php');
?>