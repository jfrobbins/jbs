<?php
//
// Copyright (C) 2011 Jacob Barkdull
//
//   This program is free software: you can redistribute it and/or modify
//   it under the terms of the GNU Affero General Public License as
//   published by the Free Software Foundation, either version 3 of the
//   License, or (at your option) any later version.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU Affero General Public License for more details.
//
//   You should have received a copy of the GNU Affero General Public License
//   along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//	http://www.tildehash.com/?article=new-identica-share-button
//
//use like this:
/*  
<div id="identishare" style="vertical-align: bottom;"></div>
<script type="text/javascript" src="identishare/identishare.php" defer="defer"></script> 

*/

if(isset($_GET["source"])){
 header("Content-type: text/plain");
 readfile(".".$_SERVER["PHP_SELF"]);
 die();
}
$referringurl=str_replace(array("http://", "www."), "", $_SERVER["HTTP_REFERER"]);

if($referringurl==str_replace("www.", "", $_SERVER["SERVER_NAME"])."/"){
 $referringurl=$_SERVER["SERVER_NAME"]."/&";
}
$rssdata=implode('', file("http://identi.ca/api/search.atom?q=".$referringurl."&rpp=100"));
$results=round((0+substr_count($rssdata, $referringurl)-1)*0.25);

if($results<=0){
 $results="0";
}
if(isset($_GET["title"])){
 $title=$_GET["title"]." ";
}else{
 $title='"+document.title+" - ';
}
?>
document.getElementById("identishare").style.display="inline-block";
document.getElementById("identishare").style.width="61px";
document.getElementById("identishare").style.overflow="hidden";
document.getElementById("identishare").innerHTML="<a href=\"http://identi.ca/index.php?action=newnotice&status_textarea=<?php echo $title.$_SERVER["HTTP_REFERER"];?>\" target=\"_blank\" style=\"display: inline-block; background-image: url('http://<?php echo $_SERVER["SERVER_NAME"];?>/identishare/identishare.png'); width: 61px; height: 60px; font-size: 35px; text-decoration: none; color: #000000; text-align: center;\" title=\"Identi.ca\"><b><?php echo $results;?></b></a>";

