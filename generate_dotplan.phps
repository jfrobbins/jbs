#!/usr/local/bin/php 
<?
/*
This bit of code generates a .plan from a hl-- state. It sucks, but works for me.
Don't run this from apache, run it from a shell (chmod +x, etc)
*/

$inpath = "/home/username/public_html/hl--/articles";
$outfile = "/home/username/.plan";

$topfp = fopen("$inpath/article.top","r");
if (!$topfp)
{
  die("error opening $inpath/article.top\n");
}

$atop=rtrim(fgets($topfp,1024));

fclose($topfp);

$plan = fopen($outfile,"w");
if (!$plan)
{
  die("error opening $outfile\n");
}

function get_links($str)
{
  $ret="";
  while (strlen($str) > 3)
  {
    if (!strcasecmp(substr($str,0,3),"<a "))
    { 
      while (strlen($str) > 0 && strcasecmp(substr($str,0,6),"href=\"") && substr($str,0,1) != ">")
      {
        $str=substr($str,1);
      }
      if (!strcasecmp(substr($str,0,6),"href=\""))
      {
        $str=substr($str,6);
        $o = strpos($str,"\"");
        if (!($o === FALSE))
        {
          $ret .= "Link: " . substr($str,0,$o) . "\n";
          $str=substr($str,$o); 
        }
      }
    }
    else if (!strcasecmp(substr($str,0,5),"<img "))
    { 
      while (strlen($str) > 0 && strcasecmp(substr($str,0,5),"src=\"") && substr($str,0,1) != ">")
      {
        $str=substr($str,1);
      }
      if (!strcasecmp(substr($str,0,5),"src=\""))
      {
        $str=substr($str,5);
        $o = strpos($str,"\"");
        if (!($o === FALSE))
        {
          $url = substr($str,0,$o) ;
          if (!strstr($url,"://")) $url = "http://1014.org/$url";
  
          $ret .= "Image: " . $url . "\n";
          $str=substr($str,$o); 
        }
      }
    }
    $str=substr($str,1);
  }
  return $ret;
}

echo "top: $atop\n";
$need_div=0;

while ($atop >= 0)
{
  $thisf=@fopen("$inpath/$atop","r");
  if ($thisf)
  {
    if ($need_div) fwrite($plan,"\n------------------------------------------------------------------------------\n");
    $tmp="";
    $tmp2="";
    $first=true;
    $line="";
    while (($x = fgets($thisf,4096)))
    {
      $x=eregi_replace("<pre>","",$x);
      $x=trim(eregi_replace("</pre>","",$x));
      $fc=substr($x,0,1);
      if ($fc == "\\") { $x="=== " . substr($x,1); $first=true; }

      if ($fc == '+' || $fc == '*') $first=1;

      if ($first && $line != "") { $tmp .= wordwrap(ltrim(strip_tags($line))) . "\n"; $tmp2 .= $line . "\n"; $line=""; }

      if (!$first && $line != "" && $x != "") $line .= " ";
      $line .= $x;

      if ($first && $line != "") { $tmp .= wordwrap(ltrim(strip_tags($line))) . "\n"; $tmp2 .= $line . "\n"; $line=""; }
      $first=false;

      if ($x == "")
      {
        if ($line != "") { $tmp .= wordwrap(ltrim(strip_tags($line))) . "\n\n"; $tmp2 .= $line ."\n"; }
        $line="";
      }
    }
    if ($line != "") { $tmp .= wordwrap(ltrim(strip_tags($line))) . "\n"; $tmp2 .= $line . "\n";}
    fwrite($plan,$tmp);
    $n = get_links($tmp2);
    if (strlen($n)) fwrite($plan, "\n" . $n);
  
 
    fclose($thisf);
    $need_div=1;
  }
  $atop -= 1;
}

fclose($plan);

?>

