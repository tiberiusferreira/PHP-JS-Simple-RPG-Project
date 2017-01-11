<?php
session_start() ; //Begin PHP section
//Actions and respective function calls
if (isset ($_REQUEST["action"]) && isset ($_SESSION["username"]) && $_REQUEST['action']==='map') {
    map_action();
    exit(0);
}
if (isset ($_REQUEST["action"]) && isset ($_SESSION["username"]) && $_REQUEST['action']==='where') {
    get_pose();
    exit(0);
}
if (isset ($_REQUEST["action"]) && isset ($_SESSION["username"]) && $_REQUEST['action']==='take_j') {
    take_action_j($_REQUEST['take_what']);
    exit(0);
}
if (isset ($_REQUEST["action"]) && isset ($_SESSION["username"]) && $_REQUEST['action']==='update_env') {
    update_env();
    exit(0);
}
if (isset ($_REQUEST["action"]) && isset ($_SESSION["username"]) && isset ($_REQUEST["using"]) && $_REQUEST['action']==='go_j') {
    go_j($_REQUEST['room'],$_REQUEST['using']);
    exit(0);
}
if (isset ($_REQUEST["action"]) && isset ($_SESSION["username"]) && $_REQUEST['action']==='default') {
    default_action();
    exit(0);
}
//Define world and other variables if user already logged in
if(!isset ($_SESSION["username"])) {
    $_SESSION['world'] = array(
        0 => array(
            "name" => "Cave part one",
            "action" => "go in the cave",
            "outs" => array("passage" => array(1,"requirements" => array())),
            "stuff" => array(
                1 => 1),
            "color" => "Red",
            "points" => array(array(0,120),array(56,120),array(0,185),array(56,185))
        ),
        1 => array(
            "name" => "Cave part two",
            "action" => "go into the deeper part of the cave",
            "outs" => array("a walk" => array(2,"requirements" => array()),"a walk back" => array(0,"requirements" => array())),
            "stuff" => array(
                0 => 1,
                3 => 3),
            "color" => "Blue",
            "points" => array(array(58,100),array(145,100),array(58,150),array(145,150))

        ),
        2 => array(
            "name" => "Cave part three",
            "action" => "go to the deepest part of the cave",
            "outs" => array("rappel" => array(3,"requirements" => array(0)),"the door" => array(4,"requirements" => array(5)),
                "a walk back" => array(1,"requirements" => array())),
            "stuff" => array(
                2 => 1),
            "color" => "Yellow",
            "points" => array(array(148,120),array(205,120),array(148,210),array(205,210))

        ),
        3 => array(
            "name" => "Well",
            "action" => "go into the well",
            "outs" => array("rappel back" => array(2,"requirements" => array())),
            "stuff" => array(
                5 => 1),
            "color" => "Pink",
            "points" => array(array(155,130),array(175,130),array(155,145),array(175,145))

        ),
        4 => array(
            "name" => "House",
            "action" => "go in the creepy house",
            "outs" => array("the door" => array(5,"requirements" => array(1)),"a walk back" => array(2,"requirements" => array())),
            "stuff" => array(
                0 => 0),
            "color" => "Purple",
            "points" => array(array(205,90),array(265,90),array(205,130),array(265,130))
        ),
        5 => array(
            "name" => "Final Room",
            "action" => "enter the room",
            "outs" => array("a walk back" => array(4,"requirements" => array())),
            "stuff" => array(
                0 => 0),
            "color" => "Green",
            "points" => array(array(176,20),array(235,20),array(176,90),array(235,90))
        )
    );
    $_SESSION["room"] = 0;
    $_SESSION["inventory"] = array(
        0 => 0,
        1 => 0,
        2 => 0,
        3 => 0,
        4 => 0,
        5 => 0,
        6 => 0
    );
}

//define objects
$_SESSION["objects"] = array (
    0=> 'Rope',
    1=> 'Dead body',
    2=> 'Sword',
    3=> 'Bananas',
    4=> 'Old cloths',
    5=> 'Rusted key',

);

//get world to javascript so it can render
function map_action(){
    header('Content-Type: application/json');
    echo json_encode($_SESSION['world']);
}

//get hero position to javascript to render
function get_pose(){
    header('Content-Type: application/json');
    $posx=$_SESSION['world'][$_SESSION["room"]]["points"][0][0];
    $posy=$_SESSION['world'][$_SESSION["room"]]["points"][0][1];
    $dimx = $_SESSION['world'][$_SESSION["room"]]["points"][1][0]-$_SESSION['world'][$_SESSION["room"]]["points"][0][0];
    $dimy = $_SESSION['world'][$_SESSION["room"]]["points"][2][1]-$_SESSION['world'][$_SESSION["room"]]["points"][0][1];
    echo json_encode([$posx+$dimx/2,$posy+$dimy/2,[$_SESSION["room"]]]);
}

//This is not used, I used it once. It is here just because the project demands it
function world_to_json(){
    $world_str = "{\"color\":{";
    $i2 = 0;
    $len2 = count($_SESSION['world']);
    foreach ($_SESSION['world'] as $oneroom){
        $world_str .= "\"{$oneroom["color"]}";
        $world_str .= "\":{ \"points\":[";
        $i = 0;
        $len = count($oneroom["points"]);
        foreach ($oneroom["points"] as $value) {
            $world_str .= " [" . $value[0] . ", " . $value[1] . "] ";
            if ($i != $len - 1) {
                $world_str .= ",";
            }
            $i++;
        }
        if($i2!=$len2 - 1) {
            $world_str .= "] }, ";
        }else{
            $world_str .= "] } ";
        }
        $i2++;
    }
    $world_str .= "} }";

    return $world_str;
}


?>
<br>
<body>
<script src="jquery-1.12.3.js"></script>
<script>
    // This part is to make the canvas HiDPI aware because it was blurry =)
    var PIXEL_RATIO = (function () {
        var ctx = document.createElement("canvas").getContext("2d"),
            dpr = window.devicePixelRatio || 1,
            bsr = ctx.webkitBackingStorePixelRatio ||
                ctx.mozBackingStorePixelRatio ||
                ctx.msBackingStorePixelRatio ||
                ctx.oBackingStorePixelRatio ||
                ctx.backingStorePixelRatio || 1;

        return dpr / bsr;
    })();

    createHiDPICanvas = function(w, h, ratio) {
        if (!ratio) { ratio = PIXEL_RATIO; }
        var canvas = document.createElement("canvas");
        canvas.width = w * ratio;
        canvas.height = h * ratio;
        canvas.style.width = w + "px";
        canvas.style.height = h + "px";
        canvas.getContext("2d").setTransform(ratio, 0, 0, ratio, 0, 0);
        return canvas;
    };

    var myCanvas = createHiDPICanvas(300, 300);
    var canvascontext = myCanvas.getContext("2d");
    document.body.appendChild(myCanvas);

    //variable to store the heros position
    var char_pos;

    //function which draws the hero at the x y canvas position
    function draw_char(x,y)
    {
        base_image = new Image();
        base_image.src = 'img/hero.png';
        base_image.style.width = '10%';
        base_image.style.height = 'auto';
        base_image.onload = function(){
            canvascontext.drawImage(base_image, x, y,20,20);
        }
    }
    //function to update the heros position when he moves from room to room
    //it also keeps track of where hes been to so it only display location aware messages once
    var been_to4=0, been_to3=0, been_to5=0, been_to2=0;
    function update_charPos()
    {
        $.getJSON("./game.php?action=where", function (data) {
            char_pos = [data[0],data[1]];
            draw_char(data[0], data[1]);
            if(data[2]==4 && been_to4==0){
                alert("Some wild dogs are blocking the door. I need something to distract them!");
                been_to4=1;
            }
            if(data[2]==3 && been_to3==0){
                alert("You used the ropes! Great idea!");
                been_to3=1;
            }
            if(data[2]==5 && been_to5==0){
                alert("You used the dead body to distract the dogs and escape! Great idea! You're awesome! (You won the game!)");
                been_to5=1;
            }
            if(data[2]==2 && been_to2==0){
                alert("A well? There could be something interesting there, but how could I get down there?");
                been_to2=1;
            }
        });
    }
    //function to draw the map
    function draw_map(){
        //clear canvas so we keep only one hero image at a given time
        canvascontext.clearRect(0, 0, myCanvas.width, myCanvas.height);
        //get the map description
        $.getJSON( "./game.php?action=map",function(data) {
                var world = data;
                for (var i = 0; i < world.length; i++) {
                    var points = world[i].points;
                    var color = world[i].color;
                    var upperleftx = points[0][0];
                    var upperlefty = points[0][1];
                    var dimx = points[1][0] - points[0][0];
                    var dimy = points[2][1] - points[0][1];
                    var middlex = upperleftx + dimx / 2;
                    var middley = upperlefty + dimy / 2;
                    canvascontext.beginPath();
                    canvascontext.rect(upperleftx, upperlefty, dimx, dimy);
                    canvascontext.strokeStyle = color;
                    canvascontext.textAlign = "center";
                    canvascontext.fillText(world[i].name, middlex, middley, dimx);
                    canvascontext.stroke();
                }
            }
        )}
</script>
</body>
<?php

//get php actions and call appropriate function
if (isset ($_SESSION["username"])){
    //if username is set
    if (isset ($_GET["action"]) && $_GET["action"] === 'logout' ){
        logout_action();
    } else if (!isset ($_GET["action"])){
        default_action();
    } else {
        unknown_action();
    }
    //if username is not set log him in
}else if (isset ($_GET["action"]) && $_GET["action"] === 'login'){
    login_action();
}else{
    show_login_form_action();
}


//show form to allow login
function show_login_form_action(){
    ?>
    <html>
    <div style='text-align:center'>
        <H1>Welcome!</H1>
        <H1>This game is a simple PHP RPG project! Have fun!</H1>

    <form action="game.php" method="GET">
        <input type=text name="name" placeholder="Enter your name">
        <input type="hidden" name="action" value="login">
        <input type="submit" name="Submit" value="Send!" />
    </form>
    </div>

    </html>

    <?php

}
//should never happen under normal usage
function unknown_action(){
    echo "Something terrible happened!";
    nl2br ("\n");
    echo "<a href=\"./game.php?action=logout\">";
    echo "Logout?";
    echo "</a>";
}


//function which moves the hero to $where using $how ex: to cave using door
//and returns the exits already formatted and in form of string
function go_j($where,$how)
{
    //if -1 -1 it means it just wants to show the messages to the user, not move the hero
    if($where!="-1" && $how!="-1") {

        go_action($where, $how);
    }
    $env_str = "";
    $env_str .= nl2br("Current location: " . $_SESSION['world'][$_SESSION["room"]]['name'] ."\n");
    foreach ($_SESSION['world'][$_SESSION["room"]]["outs"] as $key => $value) {
        $env_str .= "<button onclick=\"go_room(";
        $env_str .= $value[0];
        $env_str .= ",";
        $env_str .= "'$key";
        $env_str .= "')\">Take $key and ";
        $env_str .= $_SESSION['world'][$value[0]]['action'];
        $env_str .= "</button>";
        $env_str .= nl2br("\n");
    }
    echo $env_str;
}

//checks if hero has requirements to change rooms and if he does, move him from one room to the other.
// If not, says which are the requirements
function go_action($room,$using){
    $str_var = "";
    $missing=0;
    for($x=0; $x < count($_SESSION['world'][$_SESSION["room"]]["outs"][$using]["requirements"]); $x++) {
        if($_SESSION["inventory"][$_SESSION['world'][$_SESSION["room"]]["outs"][$using]["requirements"][$x]]==0){
            $str_var .= "To go through this " . $using . " you need " . $_SESSION['objects'][$_SESSION['world'][$_SESSION["room"]]["outs"][$using]["requirements"][$x]];
            $str_var .= "<br>";
            $missing++;
        }
    }
    if($missing>0){
        echo $str_var;
        return;
    }
    $_SESSION["room"]=$room;
    echo $str_var;
    return;
}

//Update the heros inventory and world, transfering $take_what from world to him
function take_action_j($take_what){
    $_SESSION["inventory"][$take_what]++;
    $_SESSION['world'][$_SESSION["room"]]["stuff"][$take_what]--;
    echo (show_inv());
}

//logs the user in
function login_action(){
    $_SESSION["username"]=$_GET["name"];
    echo "<div style='text-align:center'>";
    echo "<p> Hello " . $_SESSION["username"] . "</span></p>";
    echo "</div>";
    echo nl2br ("\n");
    default_action();
}

//logs user out
function logout_action(){
    session_destroy();
    echo "<div style='text-align:center'>";
    echo nl2br("See you later!\n");
    echo "Logging out...";
    echo "</div>";
    show_login_form_action();
}

// writes the inventory in a string to later show the user
function show_inv(){
    $inv_str = "";
    foreach ($_SESSION["inventory"] as $key => $value){
        if($value!=0){
            $inv_str .=  $value . ' ' . $_SESSION['objects'][$key] . nl2br ("\n");
        }
    }
    return $inv_str;
}

//function to update the items available to the user
function update_env(){
    $env_str = nl2br("Here we can find:\n");
    foreach ($_SESSION['world'][$_SESSION["room"]]["stuff"] as $key => $value){
        if($value>0) {
            $env_str .=  $value . " ";
            $env_str  .= $_SESSION['objects'][$key] . " ";

            $env_str .= "<button onclick='take_stuff($key)'>Take?</button>";
            $env_str .= nl2br("\n");
        }
    }
    echo $env_str;
}

//function which is called by default
function default_action(){
    //Show message about game context
    echo "<div style='text-align:center'>";
    echo nl2br("\n");
    echo nl2br ("You woke up in a cave. You have no memory of what happened. You need to escape by getting to the final room!\n");
    echo nl2br ("\n");
    //space where exits will be displayed and updated
    echo "<p>";
    echo "<span id='exits'></span> ";
    echo "</p>";
    ?>
    <!--space where the inventory will be displayed and updated-->
    <div style='text-align:center'>
        <p>You have: <span id='inventory'></span></p>
    </div>
    <?php
    //space where objects in the room will be displayed and updated
    echo "<p>";
    echo "<span id='env_stuff'></span> ";
    echo "</p>";

    echo "<a href=\"./game.php?action=logout\">";
    echo nl2br("\n");
    echo "Logout.";
    echo "</a>";
    echo "</div>";
}
?>
<script>
    //show what objects are in the room
    update_env();
    //render the map the first time the user logs in
    go_room(-1,-1);
    //render the heros position the first time he logs in
    update_charPos();
    //show the heros inventory and handle transfer of object "what" between room and the hero
    function take_stuff(what) {
        var get_inv = new XMLHttpRequest();
        //get the new inventory already formatted in form of string and update respective HTML
        get_inv.onreadystatechange = function () {
            if (get_inv.readyState == 4 && get_inv.status == 200) {
                document.getElementById("inventory").innerHTML = get_inv.responseText;
            }
        };
        get_inv.open("GET", "./game.php?action=take_j&take_what="+what, false);
        get_inv.send();
        update_env();
    }
    //update the list of objects in the room
    function update_env(){
        var get_env = new XMLHttpRequest();
        //get the updated object list already formatted and update the corresponding HTML
        get_env.onreadystatechange = function () {
            if (get_env.readyState == 4 && get_env.status == 200) {
                if(document.getElementById("env_stuff")!=null) {
                    document.getElementById("env_stuff").innerHTML = get_env.responseText;
                }
            }
        };
        get_env.open("GET", "./game.php?action=update_env", false);
        get_env.send();
    }
    //handles the movement of the hero from the current room to "where" passing by "how"
    //also updates the exits available and calls the function which update the heros position, the map and the
    //items available in the room.
    function go_room(where, how){
        //send action to change room, get new exits and update them
        var get_exit = new XMLHttpRequest();
        get_exit.onreadystatechange = function () {
            if (get_exit.readyState == 4 && get_exit.status == 200) {
                if(document.getElementById("exits")!=null) {
                    document.getElementById("exits").innerHTML = get_exit.responseText;
                }
            }
        };
        get_exit.open("GET", "./game.php?action=go_j&room="+where+"&using="+how, false);
        get_exit.send();
        update_env();
        update_charPos();
        draw_map();
    }
</script>
