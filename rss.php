<?
/*  jbs (jamba blog script) 
 * 
 *  Copyright (C) 2011 Jon Robbins and others
 *  http://jbs.jrobb.org
 *    please see LICENSE file
 * *****************************************
 * rss generator
 * *****************************************
*/
  require_once("common.php");

  header("Content-type: text/xml");
  echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
  echo "<rss version=\"2.0\">\n";
  echo "<channel>\n";
  echo "<title>" . $blogname . "</title>\n";
  echo "<link>" . $blogurl . "</link>\n";
  echo "<description>" . $rss_blogdesc . "</description>\n";

  $fp = fopen($article_topfilename, "rt");
  $num = 0;
  if ($fp) {
    $str = fgets($fp, 1024);
    $num = $str;
  }
  fclose($fp);
  $n = $rss_num_articles;
  for ($i = 0; $i < $n; $i++) {
    $p = $num - $i;
    if ($p < 0) break;

    $fn = "$article_dir/$p";

    $subcount = 0xffff;

    if (file_exists($fn)) {
      $fp = fopen($fn, "rt");
      if ($fp === false) continue;
      if (($firstline = trim(fgets($fp, 1024))) === false) continue;
      $didone = 0;
      if ((($r = strtotime($firstline))==-1) || 
            $r <= strtotime("now") ) {
        while (($str = fgets($fp, 1024)) !== false) {
          $str = trim(strip_tags($str));
          if (substr($str, 0, 1) == "\\") {
            if ($didone) break;
            $didone = 1;

            $str = substr($str, 1);
            // pick up some desc

            if ($rss_use_full_html)
              $desc = make_full($fp);
            else
              $desc = make_desc($fp);
            echo "<item>\n";
            echo   "<title>" . htmlspecialchars($str) . "</title>\n";
            $url = $p;

            echo   "<link>$blogurl?fromrss=y&amp;article=$url</link>\n";
            $date = date("D, d M Y H:i:s O",strtotime($firstline) + 60*60*10 + 60*14);
  	    echo   "<pubDate>$date</pubDate>\n";
            echo   "<description>" . htmlspecialchars($desc) . "</description>\n";
            echo "</item>\n";
          }
        }
      }	// !inthefuture
      fclose($fp);
    }
  }
  echo "</channel>\n</rss>\n";
?>
