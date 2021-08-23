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
                                <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" style="width: 25%;" id="processingRecordsBar"><div style="writing-mode: vertical-rl;font-size:1.8em;font-weight: 700; font-family: 'Oswald', sans-serif;"><span id="processingRecordsTitle">Queue</span><br><span id="processingRecordsCount" style="font-weight:400;"></span></div></div>
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-secondary" role="progressbar" style="font-weight: 700; font-family: 'Oswald', sans-serif;font-size:1.25em;text-shadow:rgba(0, 0, 0, 1.0) 0px 0px 5px;width: 80%;box-shadow: inset rgba(0, 0, 0, 0.5) 0px 0px 25px, inset rgba(0, 0, 0, 0.75) 0px 0px 10px;" id="pendingRecordsBar"><div><span id="pendingRecordsTitle">Ready To Process</span><br><span id="pendingRecordsCount" style="font-weight: 400;line-height:0.8em; font-size:2.0em;font-family: 'Permanent Marker', cursive;color:#D61721;"></span></div></div>

                            </div>
                        </div>

                        <button type="button" class="btn btn-dark sweetwaterLogoType btnDisable" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" id="loadQueueBtn" onclick="startMetadataProcessing();">Process Pending Comments</button> 
                        <button type="button" class="btn btn-dark sweetwaterLogoType btnDisable" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" id="cancelProcessingBtn" onclick="cancelProcessing();">Cancel Processing</button> 
                        <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="clearMetadata();">Clear Metadata</button> 

                    </p>
                </div>                

            </div>
            <div class="col-lg-2">
                
            </div>
        </div>
    </div>

    <script type="text/javascript">

        var commentsPending = 0;
        var commentsProcessing = 0;
        var commentsProcessed = 0;
        var commentsProcessedInitial = 0;
        var commentsList = [];
        var comments = [];
        var commentsReady = false;
        var processing = false;

        //
        // Executed by user by clicking Process Pending Comments button.
        // 
        // Sets-up visual and functional interface.
        //
        function startMetadataProcessing() {
            processing = true;
            console.log(commentsList);
           
            document.getElementById("processedRecordsBar").classList.add("progress-bar-striped");
            document.getElementById("processedRecordsBar").classList.add("progress-bar-animated");
            document.getElementById("pendingRecordsBar").classList.add("progress-bar-striped");
            document.getElementById("processingRecordsBar").classList.add("progress-bar-animated");
            document.getElementById("pendingRecordsBar").classList.add("progress-bar-animated");

            fillQueue();         
        }        
        
        //
        // Recursively looks for pending comments, queues them for processing.
        // 
        // After all pending comments have been added to the queue, it executes the queue processing fucntion.
        //
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
                syncProgressBar();
                processQueue();
            }
        }
        function queueCommentsForProcessing(numberToQueue) {
            var queueSize = numberToQueue;
            var commentQueue = [];
            for (var q = 0; q < commentsList.length && commentQueue.length < queueSize; q++) {
                if (commentsList[q].status == "pending") {
                    commentQueue.push(commentsList[q].orderid);
                    commentUpdate(commentsList[q].orderid, "processing", "");
                }
            }
            syncProgressBar();
            console.log(commentQueue);            
        }
        
        //
        // Read comments array, execute processing on comments flagged for processing.
        //
        function processQueue() {            
            document.getElementById("loadQueueBtn").classList.add("btnDisable");            
            console.log("pre-processing: " + readCommentCount("processing"));
            for (var m = 0; m < commentsList.length; m++) {
                console.log("comment(" + m + ")");
                if (commentsList[m].status == "processing") {
                    console.log("commentProcess(" + commentsList[m].orderid + ");");
                    commentProcess(commentsList[m].orderid);
                }
            }
            console.log("post-processing: " + readCommentCount("processing"));
        }
        
        //
        // Process comment on server.
        //
        function commentProcess(commentId) {            
            var xmlhttp = new XMLHttpRequest();
            console.log("Begin Processing: " + commentId);
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    try {
                        var jsonResponse = JSON.parse(xmlhttp.responseText);

                        console.log("xmlhttp.responseText: " + xmlhttp.responseText);
                        console.log("Status: " + jsonResponse["metadata_status"]);

                        commentUpdate(commentId, jsonResponse["metadata_status"], jsonResponse["metadata_message"]);

                        commentsProcessed = readCommentCount("processed");
                        commentsProcessing = readCommentCount("processing");
                        commentsPending = readCommentCount("pending");
                        
                        console.log("Finished Processing: " + commentId);

                    } catch (error) {
                        commentUpdate(commentId, "processed", "error detected");
                    }
                    syncProgressBar();
                    checkForFinished();
                }
            }            
            xmlhttp.open("POST", "metadata-generator.php", true);
            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=process&id=" + commentId);
        }
        
        function cancelProcessing() {
            location.href='metadata.php';
        }
                
        function checkForFinished() {
            console.log(readCommentCount("processing") + " remaining...");
            if (parseInt(readCommentCount("processing")) == 0) {
                document.getElementById("processedRecordsBar").classList.remove("progress-bar-striped");
                document.getElementById("processedRecordsBar").classList.remove("progress-bar-animated");
                document.getElementById("pendingRecordsBar").classList.remove("progress-bar-striped");
                document.getElementById("processingRecordsBar").classList.remove("progress-bar-animated");
                document.getElementById("pendingRecordsBar").classList.remove("progress-bar-animated");
                document.getElementById("cancelProcessingBtn").classList.add("btnDisable");
            }
        }
                
        function initializeMetadataProcessing() {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {                    
                    var jsonResponse = JSON.parse(xmlhttp.responseText);
                    if (jsonResponse["error"] == "") {

                        commentsProcessed = jsonResponse["processed"];
                        commentsProcessedInitial = jsonResponse["processed"];
                        commentsPending = jsonResponse["pending"];
                        
                        document.getElementById("processedRecordsBar").style.display = "none";
                        document.getElementById("processingRecordsBar").style.display = "none";
                        document.getElementById("pendingRecordsBar").style.display = "none";
                        
                        document.getElementById("pendingRecordsBar").classList.remove("progress-bar-animated");
                        document.getElementById("processingRecordsBar").classList.remove("progress-bar-animated");
                        document.getElementById("processedRecordsBar").classList.remove("progress-bar-animated");
                        
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
        }
        
        function readCommentCount(statusToCount) {
            var statusCount = 0;
            for (var ctr = 0; ctr < commentsList.length; ctr++) {
                if (commentsList[ctr].status == statusToCount) {
                    statusCount++;
                }
            }
            if (statusToCount == "processed") {
                statusCount += parseInt(commentsProcessedInitial);
            }
            return statusCount;
        }
        
        function syncProgressBar() {            
            var totalCount = parseInt(readCommentCount("processed")) + parseInt(readCommentCount("processing")) + parseInt(readCommentCount("pending"));
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
        
        initializeMetadataProcessing();
        
        
    </script>

<?php
require_once(__DIR__ . '/footer.php');
?>