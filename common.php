<?php
/*  jbs (jrobb blog script) 
 *  
 *  Copyright (C) 2011 Jon Robbins and others
 *  http://jbs.jrobb.org
 *    please see LICENSE file
 * *****************************************
 * common functions
 * *****************************************
*/
require_once("config.php");

$codeWords = array("code","list","html");

function get_param($param_name)
{
  global $HTTP_POST_VARS;
  global $HTTP_GET_VARS;
  $param_value = "";
  if(isset($HTTP_POST_VARS[$param_name]))
    $param_value = $HTTP_POST_VARS[$param_name];
  else if(isset($HTTP_GET_VARS[$param_name]))
    $param_value = $HTTP_GET_VARS[$param_name];
  return $param_value;
}

function filegetcontents($fn)
{
  $ret = "";

  $fp = fopen($fn, "r");
  if($fp)
  {
    $ret = fread($fp, filesize($fn));
    fclose($fp);
  }

  return($ret);
}


function clean_user_input($str) {
  global $comments_sep;
  $str = strip_tags($str);
  $str = htmlspecialchars($str);

  do
  {
    $lstr=$str;
    $str=str_replace($comments_sep,"",$lstr);
  } while ($lstr != $str);

  return $str;
}

function comments_addcomment($articleid, $username, $userip, $comment, $time) {
  global $comments_dir, $comments_sep;

  if (($username == $comment && !strstr($username," ")) ||
      strstr($username,"\n") ||
      stristr($username,"MIME-Version") || // ignore some weird spam stuff
      stristr($comment,"MIME-Version")) return 0;

  // verify user input
  $articleid |= 0;
  $username = clean_user_input($username);
  $comment = clean_user_input($comment);

  $oldcomments = "";

  $towrite = $username.$comments_sep.$userip.$comments_sep.$comment.$comments_sep.$time."\n";

  $fp = fopen("$comments_dir/$articleid", "a+");
  if($fp) {

    if (flock($fp,2))
    {
      fseek($fp,0,SEEK_END);
      $pos=ftell($fp);
      if ($pos > 0) fwrite($fp,$comments_sep);
      fwrite($fp, $towrite);
    }

    fclose($fp);
    return 1;
  }

  return 0;
}

function comments_getcomments($articleid)
{
  global $comments_dir, $comments_sep;

  if(file_exists("$comments_dir/$articleid"))
    return(filegetcontents("$comments_dir/$articleid"));
  else
    return("");
}

function auto_link($str)
{
  $res="";
  while (($x = stristr($str,"http://")))
  { 
    $res .= substr($str,0,strlen($str)-strlen($x));

    $list = array('<','>','"',' ',"\t","\r","\n",0);
    $ny=0;
    for ($y = 0; $list[$y]; $y ++)
    {
      if (($tmp=strstr($x,$list[$y])) && strlen($tmp) > 0)
        if (strlen($tmp) > $ny) $ny=strlen($tmp);
    }
    $ny = strlen($x)-$ny;
    
    if ($ny > 7)
    {
      $su=substr($x,0,$ny);
      $ml=64;
      $sub=substr($su,7,$ml) . ($ny > $ml-7 ? "..." : "");
      $res .= "<a href=\"$su\" rel=\"nofollow\">$sub</a>";
    }

    $str=substr($x,$ny);
  }
  $res .= $str;
  return $res;
}

function comments_formatoutput($str)
{
  $str = trim($str);
  if ($str == "") return "-";
  $ret = "";

  $ret = str_replace("  ", "&nbsp;&nbsp;", str_replace("\n", "<br />", stripslashes(htmlentities(auto_link(strip_tags($str))))));
  $ret = html_entity_decode($ret); //str_replace("&amp;quot;","&quot;",$ret);

  return($ret);
}

function getFirstInt($str) {
  for($x = 0; $x < strlen($str)-1; $x ++) {
    $ret = substr($str,$x,1);
    if (is_numeric($ret)) {
      break;
    } else {
      $ret = false;
    }
  }
  return $ret;
}

function getLastInt($str) {
  for($x = 0; $x < strlen($str)-1; $x ++) {
    $y= (strlen($str)-1)-$x; //count backwards
    $ret = substr($str,$y,1);
    if (is_numeric($ret)) {
      break;
    } else {
      $ret = false;
    }
  }
  return $ret;
}

function createCaptcha() {
  // creates captcha problem and stores along with answer in SESSION vars
  //Thanks to @jezra for this:  http://www.jezra.net/blog/creating_a_CAPTCHA
	
	$ip_address = VISITORS_IP; //get the visitors IP address
	$appended_ip = $ip_address + date("zB"); //append the numeric day of the year + 	Swatch Internet time to the ip_address
	$salt = "go away spammers"; //add some salt to the appended_ip
	$salted_string = $appended_ip+$salt;
	$md5_string = md5($salted_string) ; //get the md5sum of the salted string
	$first_int = getFirstInt($md5_string); //get the first and last integer of the md5_string
	$last_int = getLastInt($md5_string);
	//if the first int is greater than the last 
	if($first_int>$last_int)
	{
		//this is a subtraction problem
		$problem = $first_int . " minus " . $last_int;
		$answer = $first_int-$last_int;
	}else{
		//this is an addition problem
		$problem = $first_int . " plus " . $last_int;
		$answer = $first_int+$last_int;
	}
  
  //set answer with session var
  $_SESSION['canswer'.$problem] = $answer;  
  $_SESSION['cproblem'] = $problem;  //set the problem, so can retrieve the answer later
  return $problem;
}

function getCaptchaAnswer($problem=""){  
  if ($problem=="") {
    $problem = $_SESSION['cproblem'];
  }
  unset($_SESSION['cproblem']);
  
  $ret = $_SESSION['canswer'.$problem];
  unset($_SESSION['canswer'.$problem]);
  
  return $ret;
}

function comments_getdispcomments($article)
{
  global $comments_enabled, $comments_sep, $comments_dispsep, $comments_datestr, $article_comments, $blogurl;
  global $comments_email_enabled, $comments_email_subject;
  $article_comments = "";

  if($comments_enabled)
  {    
    // process new comment 
    if(get_param("addcomment") != "")
    {
      $user_info = $_SERVER['REMOTE_ADDR'];
      $entry_id = get_param("nid");	// untrusted!
      $text = get_param("addcomment");	// untrusted!
      $name = get_param("name");	// untrusted!
      $time = date($comments_datestr);
      $uAnswer = get_param("answer");
			if (!($uAnswer==""))
        //$cProblem = get_param("prob"); //untrusted
        $cProblem="";
        $cAnswer = getCaptchaAnswer($cProblem);
        if (!($uAnswer=="")) {
          if ($cAnswer==$uAnswer) {
            $res = comments_addcomment($article, $name, $user_info, $text, $time);
            if ($res) {
              //can customize this
              if ($comments_email_enabled>0) {
                $notification_msg  = "article: $blogurl?article=$article \n\r ";
                $notification_msg .= "Name: $name \n\r ";
                $notification_msg .= "UserInfo: $user_info \n\r ";
                $notification_msg .= "Time: $time \n\r ";
                $notification_msg .= "Comment: $text";
                sendEmailAlert($comments_email_subject,$notification_message);
              }
            }
          }
        }
			else
				echo("<script>document.location='$blogurl?article={$article}';</script>");
    }

    $comments = comments_getcomments($article);  

    
    {
      $comments = explode($comments_sep, $comments);
      $commentcount = (sizeof($comments) / 4)|0;

      $article_comments = "<table width=100% border=0><tr><td colspan=3><a name=\"c".$article."\"></a><b><u>Comments($commentcount)</u></b><br />";
      
      if($commentcount >= 1)
      {
        for($i = 0; $i < $commentcount * 4; $i += 4)
        {
          $name = $comments[$i];
          $ip = $comments[$i+1];
          $text = $comments[$i+2];
          $datestr = $comments[$i+3];

          $tmp=strrchr($ip,".");
          $ip=substr($ip,0,-strlen($tmp)) . ".x";

          $ii=(int) ($i/4);
          $article_comments .= "<a name=\"cl$ii\"><font class=\"comments\">Posted by <b>" . comments_formatoutput($name) . "</b> on $datestr <ul>" . comments_formatoutput($text) . "</ul></font>$comments_dispsep";
        }
      }
     $cProblem = createCaptcha(); //get captcha vals
     //<input type=\"hidden\" name=\"prob\" value=\"$cProblem\"> //dont need this
     $article_comments .= "<P></P><form action=\"$blogurl?article=$article\" method=\"POST\" style=\"display: inline;\">
            <input type=\"hidden\" name=\"nid\" value=\"$article\">            
            <table width=\"50%\" cellpadding=0 cellspacing=0 border=0>
              <tr>
              <td><b>Name:</b></td>
              <td align=\"left\"><input class=myform type=\"text\" size=\"75\" maxlength=\"100\" name=\"name\"></td>
              </tr>
              <tr>
              <td><b>Comment:</b></td>
              <td align=\"left\"><textarea class=myform cols=75 rows=5 name=\"addcomment\"></textarea></td>
              </tr>
              
              <tr>
              <td><font size=2>Captcha problem:</font></td>
              <td align=\"left\">$cProblem</td>
              </tr>
              <tr>
              <td><font size=2>Answer:</font></td>
              <td align=\"left\"><input class=myform type=\"text\" size=\"75\" maxlength=\"100\" name=\"answer\"></td>
              </tr>
              
              <tr><td></td>
              <td colspan=><input class=myform type=\"submit\" value=\"Add Comment\" style=\"display: inline;\"></td>
              </tr>
              </table>
              </form></td></tr></table><P></P>";
    }
  return($article_comments);
  }
  return("");
}

function comments_getcommentlink($article)
{
  global $comments_dir, $comments_sep;

  $article |= 0;

  if (file_exists("$comments_dir/$article") && ($comments = filegetcontents("$comments_dir/$article")))
  {
    $arr = explode($comments_sep, $comments);
    $count = ((sizeof($arr) / 4))|0;
  }
  else $count=0;

  return("<a href=\"?article=$article\">Comments ($count)</a>");
}

  function make_desc($fp) {
    global $rss_num_desc_words;
    $prevpos = ftell($fp);
    $wc = 0;
    $ret = "";
    while ($wc < $rss_num_desc_words) {
      $str = fgets($fp, 1024);
      if ($str === false) break;
      if (substr($str, 0, 1) == "|") {
        continue;
      }
      
      $str = LinkAllPostTags($str,false); //link the article tags
      
      $str = str_replace("\r", "", $str);	// lame, should be trim
      $str = str_replace("\n", " ", $str);
      if (substr($str, 0, 1) == "\\") break;
      $wc += str_word_count(strip_tags($str));
      $ret .= $str;
      $prevpos = ftell($fp);
    }
    fseek($fp, $prevpos);
    $ret = strip_tags($ret);
    if ($wc >= $rss_num_desc_words) {
      $ret = rtrim($ret, " .,?!");
      if (substr($ret, -1) != "\"") $ret .= "..."; // "bla endquote"... looks bad
    }
    return $ret;
  }

  function make_full($fp) {
    $ret = "";
    $prevpos = ftell($fp);

    global $center_article;
    if ($center_article) $ret .= "<center>";

    $last_empty=1;
    $curr_codeword="";
    $cw_ON = false;
    while (($str = fgets($fp, 1024)) !== false) {
      $shortenThis = false;
      if ((strpos(rtrim($str),$post_truncate_) !== false) and ($rss_truncate_more>0))
      {
        $shortenThis = true;
      }  
      if (($curr_codeword == "") or ($cw_ON===false)) {
        $curr_codeword = codeWordStart($str); //check for start
        if ($curr_codeword != "") $cw_ON = true;
      }

      if (!($cw_ON))      
        $str = LinkAllPostTags($str,false); //link the article tags
      $str = str_replace("\r", "", $str);	// lame, should be trim

      $str=config_rss_fixstr($str);

      if ((rtrim($str) == "") and ($curr_codeword==""))
      {
        if (!$last_empty) $str="<p>";
        else $last_empty=1;
      }
      else $last_empty=0;

      //$str=eregi_replace("<pre>","",$str); //don't need this.
      //$str=eregi_replace("</pre>","",$str);

      if ($cw_ON) {      
        $str=codifyPost($str, $curr_codeword);
        if ($curr_codeword == "") $cw_ON = false; //update if changed
      } else {
        if (substr(ltrim($str),0,1) == "*" || substr(ltrim($str),0,1) == '+')
        {
            $str = "<p>$str" ;
        }
        $str=eregi_replace("<img src.*>",'\\0<p>',$str);
      }

      if (substr($str, 0, 1) == "\\") break;
      $ret .= $str;
      if ($shortenThis == true) {
        $ret .= " [...]";
        break;
      }
    }
    fseek($fp, $prevpos);
    if ($center_article) $ret .= "</center>";
    return $ret;
  }

   function getStrBetween($wholeStr, $Str1, $Str2, $start=0) {
     //gets string from between two values
      $ret = "";
      $start = strpos($wholeStr, $Str1, $start);
      if (!$start) return $ret;
      $stop = strpos($wholeStr, $Str2, $start);
      if (!$stop) return $ret;
      
      $ret = chop(substr($wholeStr, $start, ($stop-$start))) ;
    return $ret;
  }

function RemRogueTag($tag) {
  //cleans up rougue tag characters.
  global $tagstr;
  $tag = strip_tags($tag);  
  $tag = trim($tag, " \.;:'/,");
  $tag = trim($tag, '"');
  $tag = trim($tag, $tagstr);
  
  if (strpos($tag,"\"") | strpos($tag,"<") | strpos($tag,">") | strpos($tag,":")   | strpos($tag,";"))
    $tag = "";
    
  return $tag;
}  

function GetTagStop($contents, $tagStart) {
  //get the stop position of the tag
  global $tagstr;
  global $tagStopStrs;   

  //echo "[[tagstop: " . $tagStop . " contents: " . $contents . " ]]" . "\n <br>";
  
  $arTagStop = explode(",",$tagStopStrs);    
  $tagStop = -1;
  for($x = 0; $x < count($arTagStop); $x ++) {
    $tagStop = stripos($contents, $arTagStop[$x], $tagStart+1);
    if ($tagStop >= 0)
      break;
  }  
  if ($tagStop === false)
    $tagStop = stripos($contents, "\n", $tagStart+1); //check for line break
  if ($tagStop === false)
    $tagStop = stripos($contents, ",", $tagStart+1); //check for comma
  if ($tagStop === false)
    $tagStop = strlen($contents) -1;
  return $tagStop;
}

function GetNextTag($contents, &$Pos) {
  //returns the next tag string
  global $tagstr, $tagStopStrs;
  $startPos = $Pos;
  $tag_list="";
  
  $startPos = strpos($contents, $tagstr, $startPos); //search for occurrence of tag string
  if ($startPos <= 0) { //no tags found at all!
    $Pos = -1;
    return false;
  }
  
  do {
    $nextTagStart = strpos($contents, $tagstr, $startPos+1);  
    $thisTagStop = GetTagStop($contents, $startPos);
    if (($thisTagStop <=0) | ($startPos >= strlen($contents))) {
      $Pos = -1;
      return false;
    }
      
    if ($nextTagStart < $thisTagStop)
      break;
    else
      $startPos++;
  } while ($nextTagStart < $thisTagStop); //next tag starts before this one quits
      
  $tag = substr($contents,$startPos,$thisTagStop-$startPos);    
  //$tag = getStrBetween($contents, $tagstr, " ", $startPos);
  
  //check for rogue characters
  $tag = RemRogueTag($tag);
  
  if ($tag == "") {
    $Pos = -1;
    return false;
  }
   
  $Pos = $startPos; 
  return $tag;
}

function arPos($ar,$value,$caseSensitive=true) {
  //position of $value inside of $ar[] (or false)
	for($x = 0; $x < count($ar); $x ++) {
		if ($value==$ar[$x]) {
			return $x; 			
		} else
		{
			if ($caseSensitive===false) {
				if (stripos($ar[$x], $value) === false) {
					continue;
				} else {
					return $x;
				}
			}
		}
	}
	return false;
}

function GetAllPostTags($contents, &$tag_list=array(""), $caseSensitive=true) {
  //returns all tags within post ($contents) in an array $tag_list[]
  global $tagstr;
  $startPos = 0;
  do {    
    $tag = GetNextTag($contents,$startPos);
    //echo $tag . " ";
    if (($startPos < 0) | ($tag == ""))
      break; //tag could not be assigned, list is done
    
    if (arPos($tag_list,$tag,$caseSensitive)===false)
		$tag_list[]=$tag;
		
    $startPos++; //increment so we don't read the same character twice
  } while (($startPos > 0)); 
  
  return $tag_list;
}

function GetTagLink($tag, $linkTagChr=false, $showTagChr=true) {
  //creates html link for tag
  global $blogurl;
  global $tagstr;
  
  if (($linkTagChr===false) and ($showTagChr===true)) $tagLink = $tagstr;
  $tagLink .= "<a href=\"$blogurl" . "index.php?search=" . urlencode($tagstr) . urlencode($tag)  . "\""  . ">";
  if ($linkTagChr===true) $tagLink .= $tagstr;
  $tagLink .= $tag .  "</a>";
  if ($tagLink=="") $tagLink=$tag;
  
  return $tagLink;
}

function LinkAllPostTags($contents, $linktagstr=true) {
  //return all tags, and replace them with html links
  global $tagstr;
  
  $arTag = getAllPostTags($contents);
  
  if (count($arTag) == 0)
    return $contents;
  else {
    sort($arTag); //sort the array    
  
    for($x = 0; $x < count($arTag); $x ++)    
      if ($arTag[$x] != "" && strlen($arTag[$x]) > 2) {
        $tagLink = GetTagLink($arTag[$x],$linktagstr,true);
        $contents = str_replace($tagstr . $arTag[$x],$tagLink, $contents);
      }
  }
  return $contents;      
}

function codeWordStart($str) { 
  //check for codewords
  global $codeWords;
  $L_side = "[";
  $R_side = "]";
    
  for($x = 0; $x < count($codeWords); $x ++)    
      if (strpos($str,$L_side . $codeWords[$x] . $R_side) !== false) {
          return $codeWords[$x]; //return the codeword
      }
  return false;
}

function codifyPost($contents, &$codeword) {
  if (strpos(trim($contents),"[$codeword]") !== false) 
    $isStart=true;
  else
    $isStart=false;

  if (strpos(trim($contents),"[/$codeword]") !== false) 
    $isStop=true;
  else
    $isStop=false;

  
  switch ($codeword) {
  case "code":
    if ($isStart) {
      $contents = str_replace("[$codeword]" ,'<center><table width="75%">' . "\n<tr><td align=\"left\">\n<pre class=\"code\">\n", $contents);
    } else {
      if ($isStop) {
        $contents = str_replace("[/$codeword]" ,"</pre>\n</td></tr>\n</table>\n</center>\n", $contents);
        $codeword="";
      } else {
        //just get rid of the characters
        $contents = htmlentities($contents);
      }
    }
    break;
  case "list":
	$contents = trim($contents);
	if ($isStart) {
		$contents = str_replace("[$codeword]" ,"<ul>",$contents);
	} else {
		if ($isStop) {
			$contents = str_replace("[/$codeword]" ,"</ul>",$contents);
			$codeword="";
		} else {
			//first character is new list item
			if (substr($contents,0,2)=="* ") //can handle "* " leading items, or just new line separations
				//$contents = str_replace("* ","",$contents);
				$contents = substr($contents,1,strlen($contents)-2);
			if ((strpos($contents,"<li>")) || (strpos($contents,"</li>"))) {
				$contents = str_replace("<li>","",$contents);
				$contents = str_replace("</li>","",$contents);
			}
			$contents = "<li>" . "<font class=\"post\">" . $contents . "</font>" . "</li>";
		}
	}
	break;
  case "html":
	if ($isStart) {
		$contents = str_replace("[$codeword]" ,"",$contents);
	} else if ($isStop) {
		$contents = str_replace("[/$codeword]" ,"",$contents);
	} else {
		//no changes, just use raw html with no formatting
	}
	break;
  }
  //$str=eregi_replace("<pre>","",$str); //wtf?
  //$str=eregi_replace("</pre>","",$str);
  return $contents;
}

function display_article($article_parm, $adone, $show_full=false)
  {
    global $article_dir, $viewstart;
    global $hls_article_date_column_width, $center_article, $comments_enabled, $is_palm;

    global $refer_maxitems, $refer_maxdispsize,$refer_dir, $refer_label;

    //$show_full = substr($article_parm,-1) == "F";
    //$article_parm |= 0; //if this is uncommented, articles MUST be numbers.

    $fn = "$article_dir/$article_parm";
    $fp = @fopen($fn, "rt");
    if (!$fp) return $adone;

    $firstline = trim(fgets($fp, 1024));
    if ($viewstart != "" ||
        (($r = strtotime($firstline))==-1) || 
          $r <= strtotime("now") ) 
    {
      if ($adone>0)
      {
        echo "<tr><td colspan=3>";
        echo "<hr width=\"85%\">\n";
        echo "</td></tr>\n";
      }

      echo "<tr><td width=\"$jbs_article_date_column_width\" valign=top>";

      if (substr($firstline, 0, 1) != "\\") 
      {
        echo $firstline;
        $r = strtotime($firstline);
        if ($r !== -1 && $r !== 0) {
          echo "<br><b><font face=\"courier\" size=\"-1\">". 
          strtolower(strftime("%A", $r)) . "</font>\n";
          echo "</b>\n";
        }
        $firstline = "";
      }
      echo "&nbsp;</td><td>";
      if ($center_article) echo "<center>";
    }

    // process article
    $last_empty=1;
    $curr_codeword="";
    $cw_ON = false;
    while ($firstline != "" || ($str = fgets($fp, 4096)) !== false) 
    {
      // recycle first line if they put a topic there
      if ($firstline != "") $str = $firstline;
      $firstline = "";
      // new topic
      if (substr($str, 0, 1) == "\\") 
      {
       if (substr($str, 0, 2) == "\\\\") 
       {
         echo substr($str, 1);
       } 
       else 
       {
         $str = trim(substr($str, 1));	// trim off \ and \n
         $link1="";
         $link2="";
         if (!$show_full) 
         { 
			$link1="<a href=\"?article=$article_parm\" name=\"art$article_parm\">"; 
			$link2="</a>"; 
		 } else {
			$link1="<a name=\"art$article_parm\">";
			$link2="</a>";
		 }
         echo "<font size=\"+2\"><b>$link1" . $str . "$link2</b></font><br>\n<br>\n";		
       }
     } 
     else 
     {
      if (($curr_codeword == "") or ($cw_ON===false)) {
        $curr_codeword = codeWordStart($str); //check for start
        if ($curr_codeword != "") $cw_ON = true;
      }
       if ((rtrim($str) == "") and ($curr_codeword == ""))
       {
         if (!$last_empty) {
			 $str="";
			 echo "<P>\n";
		 }
         else $last_empty=1;
       }
       else $last_empty=0;
      
      if (!($cw_ON))       
        $str=LinkAllPostTags($str, false); //link the tags
      
      //JR 2011.01.03 (for printing excertps only!)
     if((!$show_full) and (!$cw_ON))
     {
      if (strpos(rtrim($str),"<!--more-->") !== false)
      {
        echo "<P>" . $link1 . "[Read More]" . $link2;
        break;
        }
      } else {
      }  
             
      if ($cw_ON) {      
        $str=codifyPost($str, $curr_codeword);
        if ($curr_codeword == "") $cw_ON = false; //update if changed
      } else {
        if (substr(ltrim($str),0,1) == "*" || substr(ltrim($str),0,1) == '+') $str = "<br>$str" ;
        $str=eregi_replace("<img src.*>",'\\0<p>',$str);
      }

	  if (($str != "") && (!$curr_codeword) && (trim($str) != "</ul>")) {
		$str= '<font class="post">' . $str . "</font>";
	  }
	  
	  echo "$str\n";
    }
   }
   fclose($fp);

   if ($center_article) echo "</center>";   
   echo "</td></tr>\n";
   if ($comments_enabled) 
   {
     echo("<tr><td>&nbsp;</td><td colspan=2>");
     echo "<P></P>";
     if(!$show_full) echo(comments_getcommentlink($article_parm));
     else echo(comments_getdispcomments($article_parm));
     echo("</td></tr>");
   }
   if ($show_full && !$is_palm)
   {
     $rfp = @fopen("$refer_dir/$article_parm",$refer == "" ? "r" : "a+");
     $rcnt=0;
     if ($rfp)
     {
       fseek($rfp,0,SEEK_SET);
       while (($x = fgets($rfp,1024)))
       {
         $x=rtrim($x);
         if ($x != "") 
         {
           if (!$rarr[$x])
           {
             if (!$rcnt)
             {
               echo "<tr><td></td><td><hr>$refer_label<BR>";
             }
             $rcnt++;
           }
           $rarr[$x]=1;
           $sx=$x;
           if (strlen($sx) > $refer_maxdispsize) $sx = substr($sx,0,$refer_maxdispsize) . "...";
           if ($rcnt < $refer_maxitems) echo "<a href=\"$x\" rel=\"nofollow\">$sx</a><BR>";
         }
       }
       if ($refer != "" && !$rarr[$refer])
       {
         if (@flock($rfp,LOCK_EX)) fwrite($rfp,$refer . "\n");
       }
       fclose($rfp);
       if ($rcnt)
       {
         echo "</td></tr>";
       }
     }
    }
    return $adone+1;
  }

function sendEmailAlert($subject,$message) {
  global $adminEmail;
  global $emailFrom;
  $to      = $adminEmail;
  //$subject = 'the subject';
  //$message = 'hello';
  $headers = 'From: ' . $emailFrom . "\r\n" .
      'Reply-To: ' . $emailFrom . "\r\n" .
      'X-Mailer: PHP/' . phpversion();

  mail($to, $subject, $message, $headers);
}

?>
