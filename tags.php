<?  
/*  jbs (jamba blog script) 
 * 
 *  Copyright (C) 2011 Jon Robbins and others
 *  http://jbs.jrobb.org
 *    please see LICENSE file
 * *****************************************
 * tag finder (creates "cloud" of tags)
 * *****************************************
*/

  require_once("common.php");
  echo $jbs_pretext;
 
  if ($refer_log)
  {
    $refer=$_SERVER["HTTP_REFERER"];

    $pos=0;
    while ($refer != "" && $refer_ignorelist[$pos])
    {
      if (stristr($refer,$refer_ignorelist[$pos])) $refer="";
      $pos++;
    }
  
    if ($refer != "")
    {
       if (strstr($refer,"<") || strstr($refer,">") || strstr($refer,"\"")) $refer="";
    }
  }
  else $refer="";
  
  if ($refer != "")
  {
    if (($fp = @fopen("$refer_dir/m","a+")))
    {
      fseek($fp,0,SEEK_SET);
      while (($x = fgets($fp,1024)))
      {
        if (rtrim($x) == $refer) break;
      }
      if (rtrim($x) != $refer)
      {
        fseek($fp,0,SEEK_END);
        if (@flock($fp,LOCK_EX)) fwrite($fp,"$refer\n");
      }
      fclose($fp);
    }
  }

    // this one displays the list of articles from article.top on down
    $num = 0;
    $num_tot=0;
    if (is_file($article_topfilename)) {
      $fp = fopen($article_topfilename, "rt");
      if ($fp) {
        $str = fgets($fp, 128);
        $num_tot = $num = $str|0;
        fclose($fp);
      }
    }

    $viewstart = $_REQUEST['viewstart'];
    if ($viewstart == "") $viewstart = $num;
    $viewstart |= 0;
    if ($viewstart < 0) $viewstart=0;
    if ($viewstart == $num+1)  {$num++; $num_tot++; }

    if ($viewstart < $num) $num=$viewstart;

    echo "<table border=0 width=\"100%\">";
 
    
    if ($searchstr != "")
    {
      $searchitems = explode(" ",$searchstr);
      $search_skip = $_REQUEST['skip'] | 0;

      echo "<B>Searching for '$searchstr' in (";
      if ($search_comments == 2) echo "comments";
      else if ($search_comments > 0) echo "articles, comments";
      else echo "articles";
      echo ")...";
      if ($search_skip > 0) echo " skipping $search_skip results...";
      echo " [<a href=\"$blogurl\">back to index</a>]</B><BR><BR>";
      if ($search_skip > 0)
      {
        $nv=$search_skip - $num_articles;
        if ($nv < 0) $nv=0;
        echo "$nav_preprevlink<a href=\"?search=" . rawurlencode($searchstr) . "&search_tags=$search_tags&search_comments=$search_comments&skip=" . $nv . "\" rel=\"nofollow\">$nav_newsearchresults</a>$nav_postprevlink<BR>";
      }
    }

    $n = $num_articles;
    for ($i = 0, $articles_done = 0; $articles_done < $n; $i ++) {
      $p = $num - $i;
      if ($p < 0) break;    //no articles left  
      if ($tagstr != "")
      {
        $contents="";
        $fp = @fopen("$article_dir/$p","r"); //open file
        if (!$fp) continue; //if it doesn't open then keep going
        while (($x=fgets($fp,1024))) { $contents .= strip_tags($x) . " "; } //store all of the content from the post
        fclose($fp);       
        
        getAllPostTags($contents, $arTag, false);
      }
    }
    //display the tags in a list
    $tagView = $_REQUEST['tagView'];
    if ($tagView == "") $tagView = "cloud";
    
    echo " [<a href=\"$blogurl\">back to index</a>]</B><BR><BR>";
    if (count($arTag) == 0)
      echo "no tags found";
    else {
      sort($arTag); //sort the array    
      array_unique($arTag); //remove duplicates
            
      for($x = 0; $x < count($arTag); $x ++)    
        if ($arTag[$x] != "" && strlen($arTag[$x]) > 3) {            
          if (!(arPos($tagMainArray,strtolower($arTag[$x]),false)===false)) {
            $fontsize = 6; //main tags are larger
          } else { 
            $fontsize = 3;   
          }
          echo "$tagCloudSeparator<font size=$fontsize>" . GetTagLink($arTag[$x],false,false) . "</font>";
        }
        echo "$tagCloudSeparator";
    }
    echo "</table>";  
     
  // display search form
  if ($display_search == "always" || ($display_search == "index" && $article == "")) 
     echo "$nav_searchfmt<form action=\"index.php?article=$article\" method=\"GET\">" .
       "<input class=myform type=\"text\" size=\"30\" maxlength=\"100\" name=\"search\" value=\"" . ($searchstr) . "\">" . 
       "<input class=myform type=\"submit\" value=\"Search site\"><br>" . 
       "<input class=myform name=\"search_comments\" type=\"checkbox\" value=\"1\" " . ($search_comments ? "checked" : "") . ">Search comments" .
       " - <input class=myform name=\"search_tags\" type=\"checkbox\" value=\"0\" " . ($search_tags ? "" : "checked") . ">Ignore HTML tags" .
       "</form>$nav_searchfmt_end";


  if (!($jbs_outer_table===false)) echo "</td></tr></table></center>";

  echo $jbs_bottomline;

  if ($counter_enabled && $article == "" && 
      $_REQUEST['viewstart'] == "" && $searchstr == "")
  {
    $fp = @fopen($counter_filename, "r+");
    if ($fp)
    {
      $blah = (fgets($fp, 128)|0) + 1;
      if (($counter_disallowip == "" || !strstr($_SERVER['REMOTE_ADDR'],$counter_disallowip)) && flock($fp, LOCK_EX))
      {
        fseek($fp, 0);	// rewind
        fputs($fp, $blah);
        fclose($fp);
      }
      if (!($counter_echo === false)) echo "$counter_echo$blah";
    } 
  }

  echo "$jbs_bottomline_end";
  if ($postFooter != "")
     echo $postFooter;
  echo "\n</body>";
?>
