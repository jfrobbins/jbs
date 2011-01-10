<?
/*  jbs (jamba blog script) 
 * 
 *  Copyright (C) 2011 Jon Robbins and others
 *  http://jbs.jrobb.org
 *    please see LICENSE file
 * *****************************************
 * main index
 * *****************************************
*/
  session_start(); // Use $HTTP_SESSION_VARS with PHP 4.0.6 or less
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

  $searchstr = strip_tags($_REQUEST['search']);
  $search_comments = $_REQUEST['search_comments'] | 0;
  $search_tags = $_REQUEST['search_tags'];
  if ($search_tags == "") $search_tags = 0;
  else $search_tags = 1;

  $article = $_REQUEST['article'];

  if ($article == "")
  {
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

    if ($searchstr == "" && $num_tot - $num_articles > 0) 
    {
     
      $i=ceil($num_tot/$num_articles);

      $offs = ceil((($num_tot-$viewstart)/$num_articles)-((int) ($nav_maxviewpages/2)));
      if ($offs > $i-$nav_maxviewpages) $offs=$i-$nav_maxviewpages;
      if ($offs<0) $offs=0;

      $i -= $offs;

      echo $nav_preprevlink;

      if ($offs > 0)
        echo "<a href=\"?viewstart=\" rel=\"nofollow\">$nav_preslink</a> ... ";
      for ($x = $offs; $x < $i+$offs; $x ++)
      {
        if ($x > $offs + $nav_maxviewpages) { echo " ... "; break; }
        $p=$num_tot-$x*$num_articles;
      
        if ($p >= $viewstart && $p < $viewstart+$num_articles) echo "<b>" . ($p == $num_tot ? "$nav_preslink" : $x) . "</b> ";
        else if ($p == $num_tot) echo "<a href=\"?viewstart=\" rel=\"nofollow\">$nav_preslink</a> ";
        else echo "<a href=\"?viewstart=$p\" rel=\"nofollow\">$x</a> ";
      }

      if ($viewstart < $num_articles) echo "<b>$nav_pastlink</b>";
      else 
      {
        $poo=$num_tot % $num_articles; if (!$poo) $poo=$num_articles;
        echo "<a href=\"?viewstart=" . $poo . "\" rel=\"nofollow\"\">$nav_pastlink</a>";
      }
      echo $nav_postprevlink;
      echo "<br>";
    }
	
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
      if ($p < 0) break;
      if ($searchstr != "" && count($searchitems) > 0)
      {
        $contents="";
        if ($search_comments != 2)
        {
          $fp = @fopen("$article_dir/$p","r");
          if (!$fp) continue;
          while (($x=fgets($fp,1024))) { if ($search_tags) $contents.=$x . " "; else $contents .= strip_tags($x) . " "; }
          fclose($fp);
        }

        if ($search_comments > 0)
        {
          $cstr="";
          $fp = @fopen("$comments_dir/$p","r");
          if ($fp) { while (($x=fgets($fp,1024))) $cstr.=$x . " "; fclose($fp); }
          $carr = explode($comments_sep, $cstr);

          for($x = 0; $x < count($carr); $x += 4)
            $contents .= $carr[$x] . " " . $carr[$x+2] .  " ";
        }

        for ($x = 0; $x < count($searchitems) && stristr($contents,$searchitems[$x]); $x ++);
        if ($x < count($searchitems)) continue;
       
        if ($search_skip-- > 0) continue;
      }
      $articles_done = display_article($p,$articles_done); //excerpt
    }
    echo "</table>";
	
	
    if ($searchstr != "")
    {
      if (!$articles_done) echo "No articles found<BR>\n";
      echo "<br>";
      if ($articles_done == $n)
        echo "$nav_preprevlink<a href=\"?search=" . rawurlencode($searchstr) . "&search_tags=$search_tags&search_comments=$search_comments&skip=" . ($_REQUEST['skip'] + $num_articles) . "\" rel=\"nofollow\">$nav_oldsearchresults</a>$nav_postprevlink<BR>";
    }
    else if ($num_tot - $num_articles > 0) {
     
      $i=ceil($num_tot/$num_articles);


      $offs = ceil((($num_tot-$viewstart)/$num_articles)-((int) ($nav_maxviewpages/2)));
      if ($offs > $i-$nav_maxviewpages) $offs=$i-$nav_maxviewpages;
      if ($offs<0) $offs=0;

      $i -= $offs;

      echo $nav_preprevlink;

      if ($offs > 0)
        echo "<a href=\"?viewstart=\" rel=\"nofollow\">$nav_preslink</a> ... ";
      for ($x = $offs; $x < $i+$offs; $x ++)
      {
        if ($x > $offs + $nav_maxviewpages) { echo " ... "; break; }
        $p=$num_tot-$x*$num_articles;
      
        if ($p >= $viewstart && $p < $viewstart+$num_articles) echo "<b>" . ($p == $num_tot ? "$nav_preslink" : $x) . "</b> ";
        else if ($p == $num_tot) echo "<a href=\"?viewstart=\" rel=\"nofollow\">$nav_preslink</a> ";
        else echo "<a href=\"?viewstart=$p\" rel=\"nofollow\">$x</a> ";
      }

      if ($viewstart < $num_articles) echo "<b>$nav_pastlink</b>";
      else 
      {
        $poo=$num_tot % $num_articles; if (!$poo) $poo=$num_articles;
        echo "<a href=\"?viewstart=" . $poo . "\" rel=\"nofollow\"\">$nav_pastlink</a>";
      }
      echo $nav_postprevlink;
    }
     
  }
  else // display a single article
  {
    if (is_numeric($article)===true) {
      $article |= 0;
      echo $nav_preprevlink;
      if ($article >= 1) echo "<a href=\"?article=" . ($article-1) . "\" rel=\"nofollow\">$nav_lastlink</a>";
    
      $topa=0;
      $fp=@fopen($article_topfilename,"r");
      if ($fp) { $topa=fgets($fp,1024)|0; fclose($fp); }

      if ($article > 0) echo " | ";
      $vs=$article+(($topa-$article)%$num_articles);
      echo "<a href=\"?viewstart=$vs#art$article\" rel=\"nofollow\">$nav_inindexlink</a>";

      if ($article < $topa)
      {
        echo " | <a href=\"?article=" . ($article+1) . "\">$nav_nextlink</a>";
      }
      echo "$nav_postprevlink<BR>";
    } else {
      echo '<a href="index.php" rel="nofollow">' . "[ Return to Index ]" . '</a>';
    }
    echo "<table border=0 width=\"100%\">";
    display_article($article, $articles_done, true); //full article
    echo "</table>";
  }
  
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

  echo "$jbs_bottomline_end</body>";
?>
