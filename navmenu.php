<div class="navBar">
    <?php if ($currentNavButton == "home") { ?>
    <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="navClick('index.php');">Home</button> 
    <?php } else { ?>
    <button type="button" class="btn btn-light sweetwaterLogoType" style="font-weight: 400; font-family: 'Permanent Marker', cursive;" onclick="navClick('index.php');">Home</button> 
    <?php } ?>
    <?php if ($currentNavButton == "search") { ?>
    <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="navClick('search.php');">Search</button>
    <?php } else { ?>
    <button type="button" class="btn btn-light sweetwaterLogoType" style="font-weight: 400; font-family: 'Permanent Marker', cursive;" onclick="navClick('search.php');">Search</button> 
    <?php } ?>
    <?php if ($currentNavButton == "candy") { ?>
    <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="navClick('candy.php');">Manage Candy</button> 
    <?php } else { ?>
    <button type="button" class="btn btn-light sweetwaterLogoType" style="font-weight: 400; font-family: 'Permanent Marker', cursive;" onclick="navClick('candy.php');">Manage Candy</button> 
    <?php } ?>
    <?php if ($currentNavButton == "people") { ?>
    <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="navClick('people.php');">Manage People</button>
    <?php } else { ?>
    <button type="button" class="btn btn-light sweetwaterLogoType" style="font-weight: 400; font-family: 'Permanent Marker', cursive;" onclick="navClick('people.php');">Manage People</button>
    <?php } ?>
    <?php if ($currentNavButton == "metadata") { ?>
    <button type="button" class="btn btn-dark sweetwaterLogoType" style="font-weight: 700; font-family: 'Permanent Marker', cursive;" onclick="navClick('metadata.php');">Metadata</button>
    <?php } else { ?>
    <button type="button" class="btn btn-light sweetwaterLogoType" style="font-weight: 400; font-family: 'Permanent Marker', cursive;" onclick="navClick('metadata.php');">Metadata</button>
    <?php } ?>    
</div>