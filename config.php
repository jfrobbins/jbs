<?
/*  jbs (jamba blog script) 
 *  v0.2.1
 * 
 *  Copyright (C) 2011 Jon Robbins and others
 *  http://jbs.jrobb.org
 *    please see LICENSE file
 * *****************************************
 * this is the main config file
 * *****************************************
*/

  set_magic_quotes_runtime(FALSE); // just in case

  //********************************************************
  //*** basic configuration
  //********************************************************
    $blogname = "jamba blog script (jbs)";
    $mainPage="http://jbs.jrobb.org/";
    //$blogurl = $mainPage . "blog/";   // URL to main page  //for blog at "mysite/blog"
    $blogurl = $mainPage;   // URL to main page  //for blog at "mysite/"
    $staticPage = false;   // URL to main page  //for blog at "mysite/" with no static front page 
    //$staticPage = "main";   // URL to main page  //for blog at "mysite/" with static front page    
      //static page blog is not yet enabled
    $blogurl_rel = "//jbs.jrobb.org/";    // URL in format for <LINK> tags, without any index.php etc, just the path
    
    $num_articles = 6;    // how many articles to show on front page
    $post_truncate_str = "<!--more-->"; //if this string occurs on main page (not single article), post will truncate.
    
    $refer_log=0;  // log referers, show sites that link (configuration below)
    
    $menuFile = "menu.htm"; //use menu text/html file (ie. "../content/menu.htm")
    //$menuFile = ""; //no menu file
    $styleSheet = "mystyle.css";  //stylesheet, if you wish to use one. otherwise comment out

  //********************************************************
  //*** comments
  //********************************************************
    $comments_enabled = 1; // allow user comments (configuration below)    
    $comments_email_enabled = 1; //send email to $adminEmail when a comment is posted (0 to turn off)
    $comments_email_subject = "$blogname comment posted"; //email subject
    $adminEmail = 'jon@factorq.net'; //email to send notification to
    $emailFrom = 'DoNotReply@factorq.net'; //from email address

  //********************************************************
  //*** path configuration
  //********************************************************
    $config_path = "articles";  // relative path to articles directory, which
    			        // usually includes articles, comments subdir,
                                // counter file, and referer logs, but you
                                // can customize those paths below, too.
                                // must NOT have trailing slash

    // these are subdirectories of config_path that can safely be left at 
    // their defaults.
    $article_dir = "$config_path";   // articles go in the directory directly
    $article_topfilename = "$config_path/article.top";
    $counter_filename = "$config_path/counter.txt";
    $comments_dir = "$config_path/comments"; // create this dir with 0777 perms if you will use comments
    $refer_dir = $config_path . "/refers";   // create this dir with 0777 perms if you will use referer logging

  //********************************************************
  //*** RSS configuration
  //********************************************************
    $rss_blogdesc = $blogname;    // <description> field
    $rss_num_articles = 7;       // number of articles to show
    $rss_use_full_html = 1;	  // 1 = full, 0 = excerpt
    $rss_num_desc_words = 24;	  // if not full HTML, number of words to display before truncating
    $rss_truncate_more = 0; //1=truncate posts with "more" link. 0=show full post in rss.

    function config_rss_fixstr($str) // custom RSS filtering
    {
      return $str;
    }

  //********************************************************
  //*** Tags (no categories, but we can tag things)
  //********************************************************
    $tagstr = "#"; //designates tags     
    $tagStopStrs = " ,\n,.,;,/,\,:,<,>,'"; //characters that terminate tag (comma separated)
    $tagDelimiter = ",";  //for list above. comma hardcoded as terminator also
    $tagCloudSeparator = " // ";
    //array of the main tags used in your blog (like categories):
    $tagMainArray = array("life","foss","floss","random","code","site","jeep","news","linux");
    //////////////////////////
    
  //********************************************************
  //*** appearance configuration
  //********************************************************
    $jbs_bodyconfig='bgcolor="#6F6F6F" text="#000000" link="#030EA2" alink="#0C36FC" vlink="#0011E6"'; // any custom color settings for the <body> tag

    $center_article = 0;	// whether to <center> articles
    $jbs_article_date_column_width = "95"; // width of left "date" column, can be in pixels, or percentage i.e. 10%

  // form configuration for search form and comment form
    $jbs_formconfig =
       '<style type="text/css"> .myform { border-top:#c0c0c0 solid thin; border-bottom:#c0c0c0 solid thin; ' .
       'border-right:#c0c0c0 solid thin; border-left:#c0c0c0 solid thin; color:#f0f0f0; ' .
       'background:#303040; font-family:Courier; font-size:12px; border-width:1px; } </style>';
    //I'm going to add some more stuff for a flattr button:
    $jbs_formconfig .= "\n" . '<script type="text/javascript">
                                /* <![CDATA[ */
                                    (function() {
                                        var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
                                        s.type = 'text/javascript';
                                        s.async = true;
                                        s.src = "http://api.flattr.com/js/0.6/load.js?mode=auto";
                                        t.parentNode.insertBefore(s, t);
                                    })();
                                /* ]]> */
                                </script>';

  //browser icon:
    $faviconPath="http://jrobb.org/images/favicon.ico"; //default is 16x16
                                                        //set to "" to not use
    if (!($faviconPath=="")) {
    //you can leave this alone unless you need to customize it:      
      $favicon = '<link rel="shortcut icon" type="image/x-icon" href="' . $faviconPath .'" sizes="16x16" /> 
                <link rel="icon" type="image/x-icon" href="' . $faviconPath .'" sizes="16x16" />';
    } else {
      $favicon ="";
    }

  // outer (border) table configuration -- set to false to disable border
    $jbs_outer_table = 'border=0 CELLPADDING=4 CELLSPACING=0 HEIGHT=90% WIDTH=90% bgcolor="#A8A8A8" bordercolor="#000000"';

  // set our text that goes above the blog    
    $jbs_topline = "<center><a class='top' href=\"$blogurl\">$blogname</a></center>";
    $jbs_topline = '<div id="top"><b><font class="tophdr">' . $jbs_topline . '</font></b></div>';
    
  //this is a quote or tagline that goes under the main heading:
    $jbs_tagline = '<font size=2 color="#000066">jbs is a simple, fully hackable, and customizable blogging system</font>';
      
  // set our text that goes below the blog
      $jbs_bottomline = 'Copyright &copy; jon robbins : Powered by <a href="http://jbs.jrobb.org/">jamba blog script</a>';
      if ($article != "rc")  // show links to rss and recent comments, if not already in recent comments
        $jbs_bottomline =  '<a href="rss.php">rss</a> : <a href="recent_comments.php">recent comments</a> : ' . $jbs_bottomline;

      $jbs_bottomline = "<center><font size=-1>$jbs_bottomline</font></center>";
      
  //this is whatever you want to appear at the bottom of the page after $jbs_bottomline, can be blank;
    //$footer="\n<hr>\n"; //blank

  //apply footer
    if ($footer) 
      $jbs_bottomline_end = "<table width=90% align='center'>
                               <tr>
                                 <td>$footer</td>
                               </tr>
                             </table>";

  // comments appearances
      $comments_dispsep = "<hr>"; // separate comments
      $comments_datestr = "D d M Y \\a\\t H:i"; //see http://php.net/manual/en/function.date.php

  // navigation controls text and formatting
      $nav_maxviewpages=15; // max pages to show
 
      $nav_preprevlink = "<center><font size=\"-1\">[ ";
      $nav_postprevlink = " ]</font></center>";

      $nav_lastlink = "&lt;&lt;&lt; last (older) article"; // go to the next older article
      $nav_nextlink = "next (newer) article &gt;&gt;&gt;"; // go to the next most recent article
      $nav_inindexlink = "view in index"; // view current article in context
      $nav_preslink = "present"; // jump to the end of time
      $nav_pastlink = "past";   // jump to the beginning of time
      $nav_oldsearchresults = "older results...";
      $nav_newsearchresults = "newer results...";

  // search box settings
      $display_search="always"; // set to "always", "index", or anything else to disable
      $nav_searchfmt="<br><br><center>";
      $nav_searchfmt_end="</center>";


  //********************************************************
  //*** hit counter - only enabled on the main page default view
  //********************************************************
    $counter_enabled = 1;
    $counter_echo = " : Hits: "; // set to false to not echo, otherwise it will use this as a separator, following $jbs_bottomline
    $counter_disallowip = ""; // don't update counter for any IP containing this string


  //********************************************************
  //*** referer logging configuration, shows nifty 
  //*** Sites linking here" for pages
  //********************************************************
    $refer_label = "Sites linking here:";
    $refer_maxdispsize=60; // show this many chars of URL max
    $refer_maxitems=15;    // show this many URLs max (shows only the oldest to deter spam)

  // ignore links containing these values
  // note that the ignoring happens when the link happens, so it will not ignore items
  // already in the referer list (you will have to manually delete any)
    $refer_ignorelist = array("http://www.factorq.net",
							"http://jrob.org",
							"http://www.jrob.org",
							"http://factorq.net",
                           0); // 0 terminated list

  //********************************************************
  //*** add stuff to display at the bottom, below the normal footer (ie. google adsense ads)
  //********************************************************
    //just comment out if you don't want to use this! 
    //$postFooter = "<br><center></center>";
    
  //********************************************************
  //*** internal stuff - no need to edit unless you are a 
  //***                  real control freak
  //********************************************************

  // if request is from rss or palm, or user-agent has PalmOS or Nokia, disable border, search box
    $is_palm = strstr(($useragent = $_SERVER['HTTP_USER_AGENT']), "PalmOS") || strstr($useragent, "Nokia") || $_REQUEST['palm']=="y";
    if ($is_palm || $_REQUEST['fromrss'] == "y")
    {
      $jbs_outer_table=false;
      $display_search = "never";
    }

    if ($article != "rc") // normal RSS embed link
      $rss_embedlinkrel = "<LINK REL=\"alternate\" TITLE=\"$blogname RSS\" " . 
                          "HREF=\"$blogurl_rel" . "rss.php\" TYPE=\"application/rss+xml\">";
    else  // recent comments page embed link
      $rss_embedlinkrel = "<LINK REL=\"alternate\" TITLE=\"$blogname Recent Comments RSS\" " . 
                          "HREF=\"$blogurl_rel" . "recent_comments.php?rss=y\" TYPE=\"application/rss+xml\">";

  //some customization/format for the top
    $jbs_topline .= "<table width=90% align='center'><tr><td valign=bottom align=right>";
    $jbs_topline .= $jbs_tagline;
    $jbs_topline .= "</td></tr></table>";

    $jbs_pretext = "<head><title>$blogname</title>$jbs_formconfig $favicon\n";
    if ($styleSheet != "")
      $jbs_pretext .= '<link rel="stylesheet" type="text/css" href="' . $styleSheet . '" />' . "\n";
    $jbs_pretext .= "</head>\n <body $jbs_bodyconfig>\n$rss_embedlinkrel\n$jbs_topline\n";
    
    if (!($jbs_outer_table === false)){
      $jbs_pretext .= "<center><table $jbs_outer_table><tr>";
      if (!($menuFile===false)) {
        $jbs_pretext .= '<td valign="top" width=17% cellpadding=0><table width=100% cellpadding=0 valign="top">
                <tr valign="top">';
        $jbs_pretext .= '<td width=100% align="left" valign="top" bgcolor=#CCCCCC>' . "\n<h2>Menu:</h2>" . "\n";
        $jbs_pretext .= filegetcontents($menuFile); 
        $jbs_pretext .= '</td>
                </tr>
                <tr bgcolor="#6F6F6F"></tr>
               </table></td>';
      }
      $jbs_pretext .= "<td valign=top>\n";	  
    }

  // delimit comment files with this (only change if you have no comments in your comments dir)
    $comments_sep = "::!us-constitution-rocks!::"; // how to delimit the comments in file form

    if (!is_dir($config_path)) die("jbs configuration path not found.");

?>
