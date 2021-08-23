<?php
$pageTitle = "Sweetwater Comment Metadata";
$currentNavButton = "metadata";

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
                
                <h1 class="subtleShadow pageHeading" style="text-align: left; font-weight: 700; font-family: 'Oswald', sans-serif;"><span class="sweetwaterLogoType">Sweetwater</span> Comment Metadata</h1>
                
                <div class="bodySection">
                    <p class="bodyParagraph">
                        
                        
                    <div class="shadow p-3 mb-5 bg-body rounded">
                        <div class="progress" style="height: 80px;border-radius:5px;">
                            
                            <div class="progress-bar bg-dark" role="progressbar" style="font-weight: 700; font-family: 'Oswald', sans-serif;font-size:1.25em;text-shadow:rgba(0, 0, 0, 1.0) 0px 0px 5px;width: 20%;box-shadow: inset rgba(0, 0, 0, 0.5) 0px 0px 25px, inset rgba(0, 0, 0, 0.75) 0px 0px 10px;" id="processedRecordsBar"><div><span id="processedRecordsTitle">Processed Records</span><br><span id="processedRecordsCount" style="font-weight: 400;line-height:0.8em; font-size:2.0em;font-family: 'Permanent Marker', cursive;color:#0172B9;"></span></div></div>
                            <!--
                            <div class="progress-bar progress-bar-striped bg-info" role="progressbar" style="width: 10%" ></div>
                            -->
                            <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" style="width: 25%;" id="processingRecordsBar"><div style="writing-mode: vertical-rl;font-size:1.8em;font-weight: 700; font-family: 'Oswald', sans-serif;"><span id="processingRecordsTitle">Queue</span><br><span id="processingRecordsCount" style="font-weight:400;"></span></div></div>
                            <!--
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 5%" ><div style="writing-mode: vertical-rl;font-weight:400;font-family: 'Permanent Marker', cursive;">Errors</div></div>
                            -->
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-secondary" role="progressbar" style="font-weight: 700; font-family: 'Oswald', sans-serif;font-size:1.25em;text-shadow:rgba(0, 0, 0, 1.0) 0px 0px 5px;width: 80%;box-shadow: inset rgba(0, 0, 0, 0.5) 0px 0px 25px, inset rgba(0, 0, 0, 0.75) 0px 0px 10px;" id="pendingRecordsBar"><div><span id="pendingRecordsTitle">Ready To Process</span><br><span id="pendingRecordsCount" style="font-weight: 400;line-height:0.8em; font-size:2.0em;font-family: 'Permanent Marker', cursive;color:#D61721;"></span></div></div>
                            
                            
                        </div>
                    </div>
                    
                    <br><br>
                    
                    <div id="debugDiv"></div>
                    
                    <br><br>
                    
                    
                    <button type="button" class="btn btn-dark sweetwaterLogoType btnDisable" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" id="loadQueueBtn" onclick="startMetadataProcessing();">Process Pending Comments</button> 
                   
                    <button type="button" class="btn btn-dark sweetwaterLogoType btnDisable" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" id="cancelProcessingBtn" onclick="cancelProcessing();">Cancel Processing</button> 
                    
                    <!--
                    <button type="button" class="btn btn-dark sweetwaterLogoType btnDisable" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" id="startProcessingBtn" onclick="processQueue();">Process Queue</button> 
                    
                    
                    
                    <hr><hr>
                    
                    
                    <button type="button" class="btn btn-dark sweetwaterLogoType btnDisable" id="processCommentsBtn" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="beginCommentProcessing();">Process Comments</button> 
                    
                    <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="cancelCommentProcessing();">Cancel Processing</button> 
                    
                    <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="beginProcessing();">Process</button> 
                        
                    <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="alert(getCommentCount('processing'));">Test</button> 
                    
                    <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="updateProgressBar();">Update</button> 
                    -->
                    <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="clearMetadata();">Clear Metadata</button> 
                    
                    
                    </p>

                </div>
                

            </div>
            <div class="col-lg-2">
                
            </div>
        </div>
    </div>

    <script type="text/javascript">
        //27908914
        
        
        
        var commentsPending = 0;
        var commentsProcessing = 0;
        var commentsProcessed = 0;
        var commentsProcessedInitial = 0;
        var commentsList = [];
        var comments = [];
        var commentsReady = false;
        var processing = false;
        
        function startMetadataProcessing() {
            processing = true;
            
            
            
           //alert(readCommentCount("processing"));
           
           //console.log(commentsList);
           //commentUpdate(25006706, "processing", "test");
           //
           //console.log(commentsList);
           //alert(readCommentCount("processing"));
           //25006706
           //syncProgressBar();
           
            console.log(commentsList);
           
            document.getElementById("processedRecordsBar").classList.add("progress-bar-striped");
            document.getElementById("processedRecordsBar").classList.add("progress-bar-animated");
            document.getElementById("pendingRecordsBar").classList.add("progress-bar-striped");
            document.getElementById("processingRecordsBar").classList.add("progress-bar-animated");
            document.getElementById("pendingRecordsBar").classList.add("progress-bar-animated");
            //document.getElementById("pendingRecordsTitle").innerHTML = "Pending Processing";
            //document.getElementById("processingRecordsTitle").innerHTML = "Processing";
           
           
           //commentProcess(25006706);
           //commentProcess(25825796);
           //TODO: queue with a brief pause between each, to make it more visually nice
           //alert(readCommentCount("pending"));
           //queueCommentsForProcessing(10);
           fillQueue();
           //console.log(commentsList);
           
        }
        
        function cancelProcessing() {
//            processing = false;
//            for (var m = 0; m < commentsList.length; m++) {
//                if (commentsList[m].status == "processing") {
//                    commentUpdate(commentsList[m].orderid, "pending", "");                    
//                }
//            }
//            commentsProcessed = readCommentCount("processed");
//            //console.log("Processed: " + readCommentCount("processed"));
//            commentsProcessing = readCommentCount("processing");
//            //console.log("Processing: " + readCommentCount("processing"));
//            commentsPending = readCommentCount("pending");
            location.href='metadata.php';
        }
        
        function fillQueue() {
            document.getElementById("cancelProcessingBtn").classList.remove("btnDisable");
            let pendingComments = readCommentCount("pending");
            if (pendingComments > 0) {
                console.log("Pending: " + pendingComments);
                setTimeout(function(){
                    queueCommentsForProcessing(1);
                    fillQueue();
                }, 10);
            } else {
                processQueue();
            }
//            if (readCommentCount("processing") > 0) {
//                document.getElementById("startProcessingBtn").classList.remove("btnDisable");
//            }
        }
        
//        function processQueue2() {
//            let processingComments = readCommentCount("processing");
//            if (processingComments > 0) {
//                //setTimeout(function(){
//                    // get next comment
//                    for (var m = 0; m < commentsList.length; m++) {
//                        if (commentsList[m].status == "processing") {
//                            commentProcess(commentsList[m].orderid);
//                            break;
//                        }
//                    }
//                    processQueue();
//                //}, 100);
//            }
//        }
        
        function processQueue() {
            
            
            document.getElementById("loadQueueBtn").classList.add("btnDisable");
            
            console.log("pre-processing: " + readCommentCount("processing"));
            //console.log(commentsList);
            //var commentsRemaining = parseInt(readCommentCount("processing"));
//            while (parseInt(readCommentCount("processing")) > 0) {
//                console.log("readCommentCount -- " + (parseInt(readCommentCount("processing")) > 0));
//            //while (readCommentCount("processing") > 0) {
//                //console.log("commentsRemaining: " + commentsRemaining);
//                console.log("Processing Queue Size: " + readCommentCount("processing"));
//                for (var i = 0; i < commentsList.length; i++) {
//                    if (commentsList[i].status == "processing") {
//                        //commentProcess(commentsList[i].orderid);
//                        commentUpdate(commentsList[i].orderid, "processed", "");
//                        syncProgressBar();
//                       
//                        
//                       console.log("Current Queue Size (" + commentsList[i].orderid + "): " + readCommentCount("processing"));
//                       //commentProcess(commentsList[i].orderid);
//                       //console.log("commentsRemaining -= 1;");
//                       //commentsRemaining -= 1;
//                       
//                       
//                    }                    
//                }
//            }
            //30804268
            //commentProcess(commentsList[0].orderid);
            
            //if (processing) {
                for (var m = 0; m < commentsList.length; m++) {
                    if (commentsList[m].status == "processing") {
                        //commentProcess(commentsList[m].orderid);
                        console.log("commentsList[m].orderid=" + commentsList[m].orderid);
                        commentProcess(commentsList[m].orderid);
                    }
                }
            //}
            
            
            
            console.log("post-processing: " + readCommentCount("processing"));
            //console.log(commentsList);
            //console.log(readCommentCount("processing"));
            
        }
        
        function queueCommentsForProcessing(numberToQueue) {
            
            var queueSize = numberToQueue;
            var commentQueue = [];
            for (var q = 0; q < commentsList.length && commentQueue.length < queueSize; q++) {
                if (commentsList[q].status == "pending") {
                    commentQueue.push(commentsList[q].orderid);
                    commentUpdate(commentsList[q].orderid, "processing", "");
                }
                //console.log("commentQueue.length=" + commentQueue.length);
            }
            syncProgressBar();
            
            
            
            
            console.log(commentQueue);
            
        }
        
        function checkForFinished() {

            console.log(readCommentCount("processing") + " remaining...");
            if (parseInt(readCommentCount("processing")) == 0) {
                document.getElementById("processedRecordsBar").classList.remove("progress-bar-striped");
                document.getElementById("processedRecordsBar").classList.remove("progress-bar-animated");
                document.getElementById("pendingRecordsBar").classList.remove("progress-bar-striped");
                document.getElementById("processingRecordsBar").classList.remove("progress-bar-animated");
                document.getElementById("pendingRecordsBar").classList.remove("progress-bar-animated");
                //document.getElementById("pendingRecordsTitle").innerHTML = "Ready To Process";
                //document.getElementById("processingRecordsTitle").innerHTML = "...";
                document.getElementById("cancelProcessingBtn").classList.add("btnDisable");
            }
        }
        
        function commentProcess(commentId) {
            
            var xmlhttp = new XMLHttpRequest();
            console.log("Begin Processing: " + commentId);
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    //console.log("Try: " + commentId);
                    try {
                        
                    

                        var jsonResponse = JSON.parse(xmlhttp.responseText);
                        //var jsonResponse = xmlhttp.responseText;

                        console.log("xmlhttp.responseText: " + xmlhttp.responseText);

                        console.log("Status: " + jsonResponse["metadata_status"]);  //alert(jsonResponse["metadata_status"] + "\n" + jsonResponse["metadata_message"]);

                        commentUpdate(commentId, jsonResponse["metadata_status"], jsonResponse["metadata_message"]);

                        commentsProcessed = readCommentCount("processed");
                        //console.log("Processed: " + readCommentCount("processed"));
                        commentsProcessing = readCommentCount("processing");
                        //console.log("Processing: " + readCommentCount("processing"));
                        commentsPending = readCommentCount("pending");
                        //console.log("Pending: " + readCommentCount("pending"));
                        //console.log("...test...");
                        //updateProgressBar();
                        //console.log(commentsList);


                        console.log("Finished Processing: " + commentId);


                        document.getElementById("debugDiv").innerHTML = jsonResponse["expected_ship_date"];
//                        $metadata = array(
//                        "metadata_status" => "pending",
//                        "metadata_message" => "",
//                        "orderid" => "0",
//                        "call_wanted" => "0",
//                        "require_signature" => "1",
//                        "expected_ship_date" => "0000-00-00",
//                        "candy_assoc" => array(),
//                        "people_assoc" => array()
//                        );


                    
                    } catch (error) {
                        //console.error("XXX::: " + xmlhttp.responseText);
                        commentUpdate(commentId, "processed", "error detected");
                    }
                    
                    
                    syncProgressBar();
                    checkForFinished();
                }
                //updateProgressBar();
            }
            
            xmlhttp.open("POST", "metadata-generator.php", true);
            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=process&id=" + commentId);
        }
        
        function initializeMetadataProcessing() {
            var xmlhttp = new XMLHttpRequest();

            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {                    
                    
                    var jsonResponse = JSON.parse(xmlhttp.responseText);
                    
                    //alert(xmlhttp.responseText);
                    if (jsonResponse["error"] == "") {
                        //alert("te");
                        //commentCounts["processed"] = jsonResponse["processed"];
                        //commentCounts["pending"] = jsonResponse["pending"];
                        commentsProcessed = jsonResponse["processed"];
                        commentsProcessedInitial = jsonResponse["processed"];
                        commentsPending = jsonResponse["pending"];
                        
                        document.getElementById("processedRecordsBar").style.display = "none";
                        document.getElementById("processingRecordsBar").style.display = "none";
                        document.getElementById("pendingRecordsBar").style.display = "none";
                        
                        document.getElementById("pendingRecordsBar").classList.remove("progress-bar-animated");
                        document.getElementById("processingRecordsBar").classList.remove("progress-bar-animated");
                        document.getElementById("processedRecordsBar").classList.remove("progress-bar-animated");
                        
                        //alert("commentsProcessed: " + commentsProcessed + "\n" + "commentsPending: " + commentsPending + "\n");
                        
                        loadPendingComments();
                        
                        
                    } else {
                      alert("err: " + jsonResponse["error"]);
                    }                    
                }
            }
            
            xmlhttp.open("POST", "metadata-generator.php", true);
            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=getCommentCounts");
        }
        
        function loadPendingComments() {
            var pendingComment = {
                orderid: "0",
                status: "pending",
                message: ""
            }
            
            var xmlhttp = new XMLHttpRequest();

            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    var jsonResponse = JSON.parse(xmlhttp.responseText);
                    
                    for (var x = 0; x < jsonResponse.length; x++) {
                        var thisComment = JSON.parse(JSON.stringify(pendingComment));
                        thisComment.orderid = jsonResponse[x];
                        commentsList.push(thisComment);
                    }
                    
                    // execute processing on comment array
                    commentsReady = true;
                    
                    
                    
                    commentsProcessed = readCommentCount("processed");
                        
                    commentsPending = readCommentCount("pending");
                    commentsProcessing = readCommentCount("processing");
                    
                    if (commentsPending > 0) {
                        document.getElementById("loadQueueBtn").classList.remove("btnDisable");
                    
                    }
                    
                    //alert(JSON.stringify(pendingComments));
                    //console.log(commentsList);
                    syncProgressBar();
                }
            }
            
            xmlhttp.open("POST", "metadata-generator.php", true);
            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=getPendingCommentIds");
        }
        
        function commentUpdate(commentId, status, message) {
            for (var ctr = 0; ctr < commentsList.length; ctr++) {
                if (commentsList[ctr].orderid == commentId) {
                    commentsList[ctr].status = status;
                    commentsList[ctr].message = message;                    
                    break;
                }
            }
            //console.log(pendingComments);
        }
        
        function readCommentCount(statusToCount) {
            //console.log(pendingComments);
            var statusCount = 0;
            for (var ctr = 0; ctr < commentsList.length; ctr++) {
                if (commentsList[ctr].status == statusToCount) {
                    statusCount++;
                }
            }
            if (statusToCount == "processed") {
                statusCount += parseInt(commentsProcessedInitial);
            }
            //console.log(statusToCount + ": " + statusCount);
            return statusCount;
        }
        
        function syncProgressBar() {
            
            var totalCount = parseInt(readCommentCount("processed")) + parseInt(readCommentCount("processing")) + parseInt(readCommentCount("pending"));
            //alert(totalCount);
            var processedCommentPct = (100 / totalCount) * parseInt(readCommentCount("processed"));
            var processingCommentPct = (100 / totalCount) * parseInt(readCommentCount("processing"));
            var pendingCommentPct = (100 / totalCount) * parseInt(readCommentCount("pending"));
            
            if (parseInt(readCommentCount("processed")) == 0) {
                processedCommentPct = 0;
            }
            if (parseInt(readCommentCount("processing")) == 0) {
                processingCommentPct = 0;
            }            
            if (parseInt(readCommentCount("pending")) == 0) {
                pendingCommentPct = 0;
            }
            
            //document.getElementById("debug").innerHTML = "processedCommentCount=" + readCommentCount("processed") + " / processingCommentCount=" + readCommentCount("processing") + " / pendingCommentCount=" + readCommentCount("pending");
            //document.getElementById("debug").innerHTML += "<br>processedComment%=" + Math.round(parseFloat(processedCommentPct)) + " / processingComment%=" + Math.round(parseFloat(processingCommentPct)) + " / pendingComment%=" + Math.round(parseFloat(pendingCommentPct));
            
            
            //alert("processedCommentPct: " + parseInt(processedCommentPct) + "\n" + "processingCommentPct: " + parseInt(processingCommentPct) + "\n" + "pendingCommentPct: " + parseInt(pendingCommentPct) + "\n" );
            
            if (processedCommentPct == 0) {
                document.getElementById("processedRecordsBar").style.display = "none";
            } else {
                document.getElementById("processedRecordsBar").style.display = "flex";
                document.getElementById("processedRecordsBar").style.width = Math.round(parseFloat(processedCommentPct)) + "%";
            }
            
            if (processingCommentPct == 0) {
                document.getElementById("processingRecordsBar").style.display = "none";
            } else {
                document.getElementById("processingRecordsBar").style.display = "flex";
                document.getElementById("processingRecordsBar").style.width = Math.round(parseFloat(processingCommentPct)) + "%";
            }
            
            if (pendingCommentPct == 0) {
                document.getElementById("pendingRecordsBar").style.display = "none";
            } else {
                document.getElementById("pendingRecordsBar").style.display = "flex";
                document.getElementById("pendingRecordsBar").style.width = Math.round(parseFloat(pendingCommentPct)) + "%";
            }
            
           
            
            document.getElementById("processedRecordsCount").innerHTML = readCommentCount("processed");
            document.getElementById("processingRecordsCount").innerHTML = readCommentCount("processing");
            document.getElementById("pendingRecordsCount").innerHTML = readCommentCount("pending");
        }
        
        initializeMetadataProcessing();
        
        
        
        
        
        
        
        
        
        
//        
//        
//        //var readyToProcess = 0;
//        var initialProcessedCommentCount = 0;
//        var processedCommentCount = 0;
//        var processingCommentCount = 0;
//        var pendingCommentCount = 0;
//        var pendingComments = [];
//        var readyToProcess = false;
//        
//        function getCommentCount(statusToCount) {
//            //console.log(pendingComments);
//            var statusCount = 0;
//            for (var ctr = 0; ctr < pendingComments.length; ctr++) {
//                if (pendingComments[ctr].status == statusToCount) {
//                    statusCount++;
//                }
//            }
//            if (statusToCount == "processed") {
//                statusCount += parseInt(initialProcessedCommentCount);
//            }
//            return statusCount;
//        }
//        
//        function updateComment(commentId, status, message) {
//            for (var ctr = 0; ctr < pendingComments.length; ctr++) {
//                if (pendingComments[ctr].orderid == commentId) {
//                    pendingComments[ctr].status = status;
//                    pendingComments[ctr].message = message;
//                    updateProgressBar();
//                    break;
//                }
//            }
//            console.log(pendingComments);
//        }
//        
//        function beginCommentProcessing() {
//            
//            document.getElementById("processingRecordsBar").classList.add("progress-bar-animated");
//            document.getElementById("pendingRecordsBar").classList.add("progress-bar-animated");
//            document.getElementById("pendingRecordsTitle").innerHTML = "Pending Processing";
//            document.getElementById("processingRecordsTitle").innerHTML = "Processing";
//            
//            //alert(pendingComments.length);
//            //alert("statusCount: " + getCommentCount("pending"));
//            var queueSize = 50;
//            var commentQueue = [];
//            //var temp = "";
//            
//            
//            
//            for (var q = 0; q < pendingComments.length && commentQueue.length < queueSize; q++) {
//                if (pendingComments[q].status == "pending") {
//                    commentQueue.push(pendingComments[q].orderid);
//                    updateComment(pendingComments[q].orderid, "processing", "");
//                }
//            }
//            
//            console.log(commentQueue);
//            
//            updateProgressBar();
//            //alert(JSON.stringify(commentQueue));
//            //
//            //
//            //updateComment(commentId, status, message)
//            
//            for (var x = 0; x < commentQueue.length; x++) {
//                
//                processComment(parseInt(commentQueue[x]));
//            }
//            //updateProgressBar();
//            
//            //updateProgressBar();
//        }
//        
//        function cancelCommentProcessing() {
//            document.getElementById("processingRecordsBar").classList.remove("progress-bar-animated");
//            document.getElementById("pendingRecordsBar").classList.remove("progress-bar-animated");
//            document.getElementById("pendingRecordsTitle").innerHTML = "Ready To Process";
//            
//            updateProgressBar();
//        }
//        
//        function updateProgressBar() {
//            
//            //processedCommentCount = getCommentCount("processed");
//            //pendingCommentCount = getCommentCount("pending");
//            
//            var totalCount = parseInt(processedCommentCount) + parseInt(processingCommentCount) + parseInt(pendingCommentCount);
//            //alert(totalCount);
//            var processedCommentPct = (100 / totalCount) * parseInt(processedCommentCount);
//            var processingCommentPct = (100 / totalCount) * parseInt(processingCommentCount);
//            var pendingCommentPct = (100 / totalCount) * parseInt(pendingCommentCount);
//            
//            document.getElementById("debug").innerHTML = "processedCommentCount=" + processedCommentCount + " / processingCommentCount=" + processingCommentCount + " / pendingCommentCount=" + pendingCommentCount;
//            
//            
//            if (processedCommentPct == 0) {
//                document.getElementById("processedRecordsBar").style.display = "none";
//            } else {
//                document.getElementById("processedRecordsBar").style.display = "flex";
//                document.getElementById("processedRecordsBar").style.width = processedCommentPct + "%";
//            }
//            
//            if (processingCommentPct == 0) {
//                document.getElementById("processingRecordsBar").style.display = "none";
//            } else {
//                document.getElementById("processingRecordsBar").style.display = "flex";
//                document.getElementById("processingRecordsBar").style.width = processingCommentPct + "%";
//            }
//            
//            if (pendingCommentPct == 0) {
//                document.getElementById("pendingRecordsBar").style.display = "none";
//            } else {
//                document.getElementById("pendingRecordsBar").style.display = "flex";
//                document.getElementById("pendingRecordsBar").style.width = pendingCommentPct + "%";
//            }
//            
//            
//            document.getElementById("processedRecordsCount").innerHTML = processedCommentCount;
//            document.getElementById("processingRecordsCount").innerHTML = processingCommentCount;
//            document.getElementById("pendingRecordsCount").innerHTML = pendingCommentCount;
////            if (processingCommentCount > 0) {
////                document.getElementById("pendingRecordsTitle").innerHTML = "Pending Processing";
////            } else {
////                document.getElementById("pendingRecordsTitle").innerHTML = "Ready To Process";
////            }
//        }
//        
//        function beginProcessing() {
////            var xmlhttp = new XMLHttpRequest();
////
////            xmlhttp.onreadystatechange = function() {
////                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
////                    var jsonResponse = JSON.parse(xmlhttp.responseText);
////                    alert(jsonResponse["metadata_status"]);
////                }
////            }
////
////            xmlhttp.open("POST", "metadata-generator.php", true);
////            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
////            xmlhttp.send("id=27908914&action=process");
//;
//            //alert(getCommentCount("processed"));
//            processComment(28787145);
//        }
//        
//        function processComment(commentId) {
//            
//            var xmlhttp = new XMLHttpRequest();
//
//            xmlhttp.onreadystatechange = function() {
//                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
//                    var jsonResponse = xmlhttp.responseText;
//                    console.log("xmlhttp.responseText...: " + xmlhttp.responseText);
//                    
//                    updateComment(commentId, jsonResponse["metadata_status"], jsonResponse["metadata_message"]);
//                    
//                    processedCommentCount = getCommentCount("processed");
//                    console.log("processedCommentCount: " + processedCommentCount);
//                    processingCommentCount = getCommentCount("processing");
//                    console.log("processingCommentCount: " + processingCommentCount);
//                    pendingCommentCount = getCommentCount("pending");
//                    console.log("pendingCommentCount: " + pendingCommentCount);
//                    //console.log("...test...");
//                    updateProgressBar();
//                }
//                //updateProgressBar();
//            }
//            
//            xmlhttp.open("POST", "metadata-generator.php", true);
//            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
//            xmlhttp.send("action=process&id=" + commentId);
//        }
//        
        function clearMetadata() {
            
            var xmlhttp = new XMLHttpRequest();

            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    navClick('metadata.php')
                }
            }
            
            xmlhttp.open("POST", "metadata-generator.php", true);
            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=clearMetadata");
        }
//        
//        function getInitialCommentCounts() {
//            //var commentCounts = {"processed":0,"pending":0} 
//            
//            var xmlhttp = new XMLHttpRequest();
//
//            xmlhttp.onreadystatechange = function() {
//                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
//                    
//                    
//                    var jsonResponse = JSON.parse(xmlhttp.responseText);
//                    
//                    //alert(xmlhttp.responseText);
//                    if (jsonResponse["error"] == "") {
//                        //alert("te");
//                        //commentCounts["processed"] = jsonResponse["processed"];
//                        //commentCounts["pending"] = jsonResponse["pending"];
//                        initialProcessedCommentCount = jsonResponse["processed"];
//                        processedCommentCount = jsonResponse["processed"];
//                        processingCommentCount = 0;
//                        pendingCommentCount = jsonResponse["pending"];
//                        
//                        document.getElementById("processedRecordsBar").style.display = "none";
//                        document.getElementById("processingRecordsBar").style.display = "none";
//                        document.getElementById("pendingRecordsBar").style.display = "none";
//                        
//                        document.getElementById("pendingRecordsBar").classList.remove("progress-bar-animated");
//                        document.getElementById("processingRecordsBar").classList.remove("progress-bar-animated");
//                        document.getElementById("processedRecordsBar").classList.remove("progress-bar-animated");
//                        
//                        updateProgressBar();
//                        
//                        getPendingCommentIds();
//                        
//                    } else {
//                       //alert("err: " + jsonResponse["error"]);
//                    }                    
//                }
//            }
//            
//            xmlhttp.open("POST", "metadata-generator.php", true);
//            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
//            xmlhttp.send("action=getCommentCounts");
//            
//            //return commentCounts;
//        }
//            
//        function getPendingCommentIds() {
//
//            //var pendingComments = [];               
//            var pendingComment = {
//                orderid: "0",
//                status: "pending",
//                message: ""
//            }
//            
//            var xmlhttp = new XMLHttpRequest();
//
//            xmlhttp.onreadystatechange = function() {
//                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
//                    var jsonResponse = JSON.parse(xmlhttp.responseText);
//                    
//                    for (var x = 0; x < jsonResponse.length; x++) {
//                        var thisComment = JSON.parse(JSON.stringify(pendingComment));
//                        thisComment.orderid = jsonResponse[x];
//                        pendingComments.push(thisComment);
//                    }
//                    
//                    // execute processing on comment array
//                    readyToProcess = true;
//                    document.getElementById("processCommentsBtn").classList.remove("btnDisable");
//                    //alert(JSON.stringify(pendingComments));
//                }
//            }
//            
//            xmlhttp.open("POST", "metadata-generator.php", true);
//            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
//            xmlhttp.send("action=getPendingCommentIds");
//            
//            //return commentCounts;
//        }
            
            
        //getInitialCommentCounts();
    </script>

<?php
require_once(__DIR__ . '/footer.php');
?>