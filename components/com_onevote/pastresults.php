<?php
/**
 * @package Component OneVote! for Joomla! 2.5/3.0
 * @author Brian Keahl
 * @copyright (C) 2014- Advanced Computer Systems
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// No direct access to this file
// defined('_JEXEC') or die('Restricted access');
define( '_JEXEC', 1 );
define( 'JPATH_BASE', realpath(dirname(__FILE__)).'/../..');
define( 'DS', '/' );
// echo JPATH_BASE.'<br>';
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla'.DS.'database'.DS.'factory.php' );
$mainframe = JFactory::getApplication('site');
$user = JFactory::getUser();
// echo 'user_id='.$user->id.'<br>';
echo "<div style='text-align:center;'>";
echo "<div style='text-align:center;font-face:georgia;font-size:28pt;'>Election Results</div>";
if($user->id<=0) // a valid user
 {
  echo '<center><font size=5 color=red><B>You must be logged in to see past results.</B></font></center>';  
 }
else
 {
  echo '3';
  $db = JFactory::getDbo();
  // var_dump($user);
  $jinput = JFactory::getApplication()->input;
  echo '4';
  $act=$jinput->get('act','','');
  $election_id=$jinput->get('election_id',0,'');
/*
  if($election_id>0)
   {
    header('location:./components/com_onevote/results.php?election_id='.$election_id);
	exit;
   }
*/
  $query=$db->getQuery(true);
  $query->select('election_id,title,description,polls_open,polls_close,nominations_open,nominations_close,anonymous_nominations*1 as anonymous_nominations,anonymous_voting*1 as anonymous_voting,show_results*1 as show_results,show_results_min_votes,email_nominations_to,email_votes_to,active*1 as active');
  $query->from('#__onevote_elections');
  $query->where("polls_close<NOW() "." and ".
                "exists (select * from #__onevote_groups where election_id=#__onevote_elections.election_id".
				" and exists (select * from #__user_usergroup_map where #__user_usergroup_map.group_id=#__onevote_groups.group_id and #__user_usergroup_map.user_id=".$user->id."))");
  $db->setQuery($query);
//  echo $query.'<br>';
  $rows = $db->loadAssocList();
  if(count($rows)==0)echo '<center><font size=5 color=red><B>There are no past elections at this time.</B></font></center>';
/*  else if(count($rows)==1)
   {
    header('location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'?election_id='.$rows[0]['election_id']);
    exit;
   } */
  else
   {
    echo '<center><div style="font-face:helvetica;font-size:14pt;color:black;text-align:center;">'.count($rows).' Election(s) available for viewing.<br>'.'</div>';
// If more than one election a list should be provided.
    echo '<table align=center style="width:100%;background-color:black;border-spacing:3px;font-face:helvetica;font-size:12pt;">';
	echo '<tr style="text-align:center;background-color:black;color:white;font-face:helvetica;size:12pt;">'.
	     '<td style="text-align:center;width:350px;">Election</td><td style="text-align:center;width:70px;">Close</td><td style="text-align:center;width:70px;">Options</td></tr>';		 
    foreach($rows as $row)
     {
	  echo '<tr style="background-color:white;color:blank;"><td>'.$row['title'].'<div style="font-size:8pt;">'.$row['description'].'</div>'.'</td>'.
	           '<td style="text-align:center;">'.substr($row['polls_close'],0,10).'</td><td style="text-align:center;">';
	  echo "<a href=\"".'./results.php'.'?election_id='.$row['election_id']."\">View</a>";
	  echo '</td></tr>';
//      print_r($row);
//      echo '<hr>';
     }
	echo '</table>';
   }
 }
?>
