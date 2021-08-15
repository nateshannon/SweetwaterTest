<!DOCTYPE html>
<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x"
            crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
            crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/7df19bc93b.js" crossorigin="anonymous"></script>
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Black+Ops+One&family=Cinzel+Decorative:wght@400;700;900&family=Cutive&family=Fontdiner+Swanky&family=Fredoka+One&family=Lancelot&family=Lato:wght@300;400;700;900&family=Libre+Barcode+128+Text&family=Libre+Barcode+39+Text&family=Libre+Barcode+EAN13+Text&family=Montserrat+Subrayada:wght@400;700&family=Montserrat:wght@300;400;700;900&family=Mr+Dafoe&family=Oswald:wght@200;400;700&family=Pattaya&family=Permanent+Marker&family=Solway:wght@700;800&display=swap" rel="stylesheet">
        <link href="content/sweetwater.css" rel="stylesheet">
        <script type="text/javascript">
            function navClick(page) {
                location.href = page;
            }
            function navClickConfirm(page, prompt) {
                if (confirm(prompt)) {
                    location.href = page;
                }
            }
            
            
            // read count of processed comments.
            // read unprocessed comments, return list of orderids
            // click process button
            //  queue next 10 comments
            //  send queue comments to server, wait for responses
            //  when response is received, update all counts, update interface, add one to queue
           
            
            
        </script>
        
        <meta charset="UTF-8">
        <title><?=$pageTitle; ?></title>
    </head>
    <body>
