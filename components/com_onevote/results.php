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
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla'.DS.'database'.DS.'factory.php' );
$mainframe = JFactory::getApplication('site');
$db = JFactory::getDbo();
$jinput = JFactory::getApplication()->input;
$election_id=$jinput->get('election_id',0,'');
if(!is_numeric($election_id))$election_id=0;
$user = JFactory::getUser();
// echo 'user_id='.$user->id.'/election_id='.$jinput->get('election_id',0,'').'<br>';
echo "<div style='text-align:center;'>";
// echo "<Marquee height='100%' scrolldelay=1000 scrollamount=50 direction=up>";
echo "<div style='text-align:center;font-face:georgia;font-size:28pt;'>Election Results</div>";

$publicvote=0;

if($election_id>0)
 {
  $query=$db->getQuery(true);
  $query->select('group_id');
  $query->from('#__onevote_groups');
  $query->where("election_id=".$election_id);
  $db->setQuery($query);
  $grows = $db->loadAssocList();
  foreach($grows as $grow)if($grow['group_id']==1)$publicvote=1;

//  echo 'election_id='.$election_id.'/user->id='.$user->id.'/publicvote='.$publicvote.'<br>';  
 
  if($user->id>0 || $publicvote>0) // a valid user
   {
    $act=$jinput->get('act','','');
    $election_id=$jinput->get('election_id',0,'');
  
   // Make sure the person can play in the election
    $query=$db->getQuery(true);
    $query->select('group_id');
    $query->from('#__onevote_groups');
    $query->where("election_id=".$election_id);
    $db->setQuery($query);
    $grows = $db->loadAssocList();
    $groupwhere="(";$andor='';
    foreach($grows as $grow)
     {
      $groupwhere.=$andor."group_id=".$grow['group_id'];$andor=" or ";
     }
    $groupwhere.=")";
  // $groupwhere should not have the part of the query that checks if any of the group_ids match
   
    $query=$db->getQuery(true);
    $query->select('count(user_id) as qty');
    $query->from('#__user_usergroup_map');
    $query->where("user_id=".$user->id." and ".$groupwhere);
    $db->setQuery($query);
    $row = $db->loadAssoc();
//  echo $row['qty'].":".$query.'<br>';
    if($row['qty']==0 && $publicvote==0)
     {
      echo "<center>You are not authorized to participate in this election. <a href='javascript:void(0);' onclick='window.location.href=\"index.php/component/users/?view=login\"'>Are you logged in?</a></center>";
     }
    else
     {
//    echo 'jElection '.$election_id.' ... ';
      $query=$db->getQuery(true);
      $query->select('election_id,title,description,polls_open,polls_close,nominations_open,nominations_close,anonymous_nominations,anonymous_voting,show_results,show_results_min_votes,email_nominations_to,email_votes_to,active');
      $query->from('#__onevote_elections');
      $query->where("active=1 and (polls_open<NOW() and polls_close>NOW()) or (nominations_open<NOW() and nominations_close>NOW()) and election_id=".$election_id);
      $db->setQuery($query);
      $row = $db->loadAssoc();
      echo "<div style='text-align:center;margin:0 auto;font-face:arial;font-size:18pt;'>".$row['title']."</div>";
      echo "<div style='text-align:center;margin:0 auto;font-face:arial;font-size:12pt;'>".$row['description']."</div>";
      echo "<div style='text-align:center;margin:0 auto;font-face:arial;font-size:12pt;color:red;'>";
      if(strtotime($row['nominations_open'])<time() && strtotime($row['nominations_close'])>time()){echo 'Nominations Are Open Until '.$row['nominations_close'];$nominations_open=1;}
      else if(strtotime($row['nominations_open'])>time() && strtotime($row['nominations_close'])>strtotime($row['nominations_open']))echo "Nominations will be open from ".$row['nominations_open']." until ".$row['nominations_close'];
      echo '</div>';
      echo "<div style='text-align:center;margin:0 auto;font-face:arial;font-size:12pt;color:red;'>";
      if(strtotime($row['polls_open'])<time() && strtotime($row['polls_close'])>time()){echo ' The Polls Are Open until '.$row['polls_close'];$polls_open=1;}
      else if(strtotime($row['polls_open'])>time() && strtotime($row['polls_close'])>strtotime($row['polls_open']))echo "The polls (voting) will be open from ".$row['polls_open']." until ".$row['polls_close'];
      echo "</div>";
//  echo '<br>'.time().'/'.strtotime($row['polls_open']).'/'.strtotime($row['polls_close']);
      if($act=='') // No task, so we just display the ballot
       {
        echo "<div style='width:500px;margin: auto auto;'><form id='voteform' style='width:500px;' action=\"http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."\" method=POST>";
        $query=$db->getQuery(true);
        $query->select('position,election_id,ballot_item_id,ballot_title,ballot_description,is_ballot_question*1 as is_ballot_question,allow_nominations*1 as allow_nominations,nominate_group_members_only*1 as nominate_group_members_only');
        $query->from('#__onevote_ballot_items');
  	    $query->order('position,ballot_item_id');
        $query->where("election_id=".$election_id);
        $db->setQuery($query);
        $brows = $db->loadAssocList();
  	    $rownum=0;
  	    echo '<table align=center style="width:500px;">';
	    $voteslist="";$c="";
	    $nominatelist="";
        foreach($brows as $brow) 
	     {
	      $votename="v".$brow['ballot_item_id'];$voteslist.=$c.$votename;$c=",";
	      $nominatename="n".$brow['ballot_item_id'];$nominatelist.=$c.$nominatename;$c=",";
	      echo "<tr style='background-color:black;color:white;'><td colspan=4><div style='font-size:16pt;text-align:center;'>".$brow['ballot_title']."</div>";
	      if(strlen($brow['ballot_description'])<64)$talign="text-align:center";else $talign="";
	      if($brow['ballot_description']!='')echo "<div style='font-face:arial;font-size:12px;color:yellow;margin-top:-6px;".$talign."'>".$brow['ballot_description']."</div>";
	      echo "</td></tr>";
	      echo "<tr style='background-color:black;color:white;font-size:12pt;'>";
	      if($brow['is_ballot_question'])echo "<td style='text-align:center;width:485px;' colspan=2>Question Response</td><td style='text-align:center;width:80px;'>Votes</td><td style='text-align:center;width:100px;'>Pct</td>";
	      else echo "<td style='text-align:center;width:232px;'>Candidate Name</td><td style='width:232px;'>Username</td><td style='text-align:center;width:80px;'>Votes</td><td style='text-align:center;width:100px;'>Pct</td>";
	      echo "</tr>";

			// Here we get the total votes for the ballot item
          $query=$db->getQuery(true);
          $query->select('count(*) as qty');
          $query->from('#__onevote_votes');
          $query->where("ballot_item_id=".$brow['ballot_item_id']);
          $db->setQuery($query);
          $xrow = $db->loadAssoc();
		  $btotalvotes=$xrow['qty']; // Total votes cast on this ballot item
		  		  
          $query=$db->getQuery(true);
          $query->select('nomination_id,nominee_id,first_name,last_name,nominated_by');
          $query->from('#__onevote_nominations');
          $query->where("ballot_item_id=".$brow['ballot_item_id']);
          $db->setQuery($query);
          $nrows = $db->loadAssocList();
          foreach($nrows as $nrow) 
	       {
	        echo "<tr style='background-color:white;color:black;font-size:12pt;'>";
            if($nrow['nominee_id']>0)
             {
			  $query=$db->getQuery(true);
		      $query->select('username,name');
		      $query->from('#__users');
		      $query->where("id=".$nrow['nominee_id']);
		      $db->setQuery($query);
		      $urow = $db->loadAssoc();
              echo '<td>'.$urow['name'].'</td><td>'.$urow['username'].'</td>';
             }
	        else
	         {
		      if($brow['is_ballot_question'])echo '<td colspan=2>'.$nrow['first_name'].' '.$nrow['last_name'];
		      else echo '<td>'.$nrow['first_name'].' '.$nrow['last_name'].'</td><td>-</td>';
	         }
			// Here we get the total votes
			$query=$db->getQuery(true);
            $query->select('count(*) as qty');
            $query->from('#__onevote_votes');
            $query->where("ballot_item_id=".$brow['ballot_item_id']." and nomination_id=".$nrow['nomination_id']);
            $db->setQuery($query);
            $xrow = $db->loadAssoc();
		    $nvotes=$xrow['qty']; // Votes cast for this nominee on this ballot item
			if($nvotes>0 && $btotalvotes>0)$pct=($nvotes*100)/$btotalvotes;
			else $pct=0;
			echo '<td style="text-align:right">'.$nvotes.'</td><td style="text-align:right">'.number_format($pct,2).'</td></tr>';
           }
	      echo '<tr height=2><td colspan=4><hr style="margin-top:0px;margin-bottom:1px;"></tr>'; 
	      $rownum+=1;
         }   
			// Here we get the total votes
        $query=$db->getQuery(true);
        $query->select('count(*) as qty');
        $query->from('#__onevote_votes');
        $query->where("nomination_id=0 and election_id=".$brow['election_id']);
        $db->setQuery($query);
        $xrow = $db->loadAssoc();
		$totalvotes=$xrow['qty']; // Total votes cast on this ballot item		 
        echo '<tr style="background-color:white;color:black;font-size:12pt;"><td colspan=2 style="text-align:right">Total Votes Cast:</td><td style="text-align:right">'.$totalvotes.'</td><td style="text-align:right">'.number_format(100,2).'</td></tr>';
	    echo '</table>';
       }	 
     }   
   }
 }
// echo '</marquee>';
echo '</div>';
?>
