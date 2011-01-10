<?
 
  $rss = substr($_REQUEST['rss'],0,1) == "y";

  $article="rc";
  require_once("common.php");

  if (!$rss)
  {
    echo "$jbs_pretext<h2>Recent comments:</h2>";
  }
  else
  {
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
    echo "<rss version=\"2.0\">\n";
    echo "<channel>\n";
    echo "<title>$blogname comments</title>\n";
    echo "<link>$blogurl</link>\n";
    echo "<description>$rss_blogdesc recent comments</description>\n";
  }

  if (!isset($showcnt)) $showcnt=50;

  $d=opendir($comments_dir);
  if ($d)
  {
    while (($f = readdir($d)))
    {
      if (substr($f,0,1) != ".")
      {
        $arr[$f][0]=filemtime("$comments_dir/$f");
      }
    }
    closedir($d);

    arsort($arr);

    reset($arr);

    $cnt=$showcnt;
    while (($f = key($arr)) && $cnt-- > 0)
    {
      next($arr);

      $ll=filegetcontents("$comments_dir/$f");

      $tc = explode($comments_sep, $ll);
      $l=sizeof($tc);
      if (($l%4) == 0)
      {
        for ($i = 0; $i < $l; $i += 4) 
        {
          $lf = $f . "-" . $i;
          $carr[$lf][0]=strtotime($tc[$i+3]);
          $carr[$lf][1]=$tc[$i+0];
          $carr[$lf][2]=$tc[$i+1];
          $carr[$lf][3]=$tc[$i+2];
          $carr[$lf][4]=$tc[$i+3];
          $carr[$lf][5]=$f;
          $carr[$lf][6]=(int) ($i/4);
        }
      }
    }
    arsort($carr);
    reset($carr);
    $cc=$showcnt;
    while (($f = key($carr)) && $cc-- > 0)
    {
      next($carr);
      $name=$carr[$f][1];
      $ip=$carr[$f][2];
      $text=$carr[$f][3];
      $date=$carr[$f][4];
      $fn=$carr[$f][5];
      $cid=$carr[$f][6];

      $tmp=strrchr($ip,".");
      $ip=substr($ip,0,-strlen($tmp)) . ".x";

      if ($rss)
      {
	echo "<item>\n";
        echo "<title>Posted by " . htmlspecialchars(comments_formatoutput($name)) . " on $date from $ip</title>\n";

	echo   "<link>$blogurl?fromrss=y&amp;article=$fn#cl$cid</link>\n";
        $date = date("D, d M Y H:i:s O",strtotime($date));
        echo   "<pubDate>$date</pubDate>\n";
        echo   "<description>" . str_replace("\\&quot;","&quot;",str_replace("\\'","'",htmlspecialchars(trim(html_entity_decode($text))))) . "</description>\n";


	echo "</item>\n";

      }
      else
      {
        echo "<a href=\"index.php?article=$fn#cl$cid\">Posted</a> by <b>" . comments_formatoutput($name) . "</b> on $date from $ip<ul> " . comments_formatoutput($text) . "</ul>$comments_dispsep";
      }
    }
  }
  $counter_enabled=0;

  if (!$rss)
  {
    if (!($jbs_outer_table===false)) echo "</td></tr></table></center>";
    
    echo "$jbs_bottomline$jbs_bottomline_end</body>";
  }
  else
  {
    echo "</channel>\n</rss>\n";
  }

?>
