<?php
  /*
    This is the Language file for Dominion IT's Blog system. Note that it is 2 array's the one is the general array for the info in the blog system
    then there is a second array for the months in the language of present. This is months in short date format not long date format.
    dominion_blog_general => Is the general language array for system
    dominion_blog_months => is the months in short date format (NOTE the KEY is the month number) example :  $dominion_blog_months[3] = 'Mar';
  */
  
  $dominion_blog_months[1] = 'Jan';
  $dominion_blog_months[2] = 'Feb';
  $dominion_blog_months[3] = 'Mar';
  $dominion_blog_months[4] = 'Apr';
  $dominion_blog_months[5] = 'Mei';
  $dominion_blog_months[6] = 'Jun';
  $dominion_blog_months[7] = 'Jul';
  $dominion_blog_months[8] = 'Aug';
  $dominion_blog_months[9] = 'Sep';
  $dominion_blog_months[10] = 'Oct';
  $dominion_blog_months[11] = 'Nov';
  $dominion_blog_months[12] = 'Dec';
  
  $dominion_blog_general['BLOG_HEADER'] = "To use the blog you must create a blog group (what the blog is about). Then you can create blogs under the group. The group is going to be your ID that you used when in your page you tell it to post the blogs. The blogs will be posted as follows <b>(% blog:blog_group_id%)</b>. For example if the group id's name is myFamily then the entry in my pages will be (%blog:myFamily%)<br/> - Developed by ";
  $dominion_blog_general['BLOG_NAME'] = 'Name : ';
  $dominion_blog_general['BLOG_INFO_LINE1'] = ' to Page to display blog if Enabled.';
  $dominion_blog_general['BLOG_INFO_LINE2'] = ' to template or sidebar to display news items.';
  $dominion_blog_general['BLOG_INFO_COPY'] = 'Copy ';
  $dominion_blog_general['BLOG_INFO_TITLE'] = 'Title : ';
  $dominion_blog_general['BLOG_INFO_DATE'] = 'Date : ';
  $dominion_blog_general['BLOG_INFO_EXCERPT'] = 'Excerpt';
  $dominion_blog_general['BLOG_INFO_BLOG'] = 'Blog';
  
  $dominion_blog_general['SETTINGS_HEADER'] ="Here we will setup the settings for our blogs Settings";
  $dominion_blog_general['SETTINGS_SUB_HEADER'] = 'Settings';
  $dominion_blog_general['SETTINGS_GRAPHICS_DATES'] = 'Use graphical dates';
  $dominion_blog_general['SETTINGS_LANGUAGES'] = 'Language';
  $dominion_blog_general['SETTINGS_NUM_NEWS_ITEMS'] = 'Number News Items to Show';
  $dominion_blog_general['SETTINGS_NUM_BLOG_ITEMS'] = 'Number Blog Items to Show';
  $dominion_blog_general['SETTINGS_SHOW_SHORT_IN_NEWS'] = 'Show Excerpt in news list';
  $dominion_blog_general['SETTINGS_NEWS_TARGET_PAGE'] = 'News Target Page';
  $dominion_blog_general['SETTINGS_SHOW_BLOG_SUMMARY'] = 'Show blog summary page';
  
  
  
  $dominion_blog_general['PLUGIN_DISABLE'] = 'Disable Plugin ';
  $dominion_blog_general['PLUGIN_ENABLE'] = 'Enable Plugin';
  
  $dominion_blog_general['CSS_HEADER'] = "Here we will setup the CSS that we use to control the header and footers of all blogs and the body of the blogs. You can set anything you want here as the blogs are all inside DIV's.Please note the following DIV's can be accessed for each blog entry ";
  $dominion_blog_general['CSS_SUB_HEADER'] = "CSS Template";
  $dominion_blog_general['CSS_ID_BLOG_FOOTER'] = "This gets shown at the bottom of each blog entry";
  $dominion_blog_general['CSS_ID_BLOG_HEADER'] = "This gets shown at the top of each blog entry";
  $dominion_blog_general['CSS_ID_BLOG_BODY'] = "This is the blog entry block";
  $dominion_blog_general['CSS_ID_NEWS_HEADER'] = "This gets shown at the top of each news entry";
  $dominion_blog_general['CSS_ID_NEWS_FOOTER'] = "This gets shown at the bottom of each news entry";
  $dominion_blog_general['CSS_ID_NEWS_BODY'] = "This is the news entry";
  
  
  $dominion_blog_general['SYSTEM_ITEM_BLOGS'] = 'Blogs';
  $dominion_blog_general['SYSTEM_ITEM_CSS'] = 'CSS';
  $dominion_blog_general['SYSTEM_ITEM_SETTINGS'] = 'Settings';
  $dominion_blog_general['SYSTEM_ITEM_BUTTON_SAVE'] = 'Save';
  $dominion_blog_general['SYSTEM_ITEM_BUTTON_SAVE_GROUP'] = 'Save Group';
  $dominion_blog_general['SYSTEM_ITEM_LINK_DELETE_GROUP'] = 'Delete This Group';
  $dominion_blog_general['SYSTEM_ITEM_LINK_ADD_GROUP'] = 'Add Group';
  $dominion_blog_general['SYSTEM_ITEM_LINK_ADD_BLOG'] = 'Add New Blog';
  $dominion_blog_general['SYSTEM_ITEM_LINK_DELETE'] = 'Delete';
  $dominion_blog_general['SYSTEM_ITEM_LINK_SHOW_ALL_BLOGS'] = 'Show all Blog entries';
  $dominion_blog_general['SYSTEM_ITEM_LINK_SHOW_ALL_NEWS'] = 'Show all News entries';
  $dominion_blog_general['SYSTEM_ITEM_GRID_TITLE'] = 'Title';
  $dominion_blog_general['SYSTEM_ITEM_GRID_DATE'] = 'Date';
  $dominion_blog_general['SYSTEM_ITEM_CURRENT_GROUPS'] = 'Current Groups';
  
 ?>