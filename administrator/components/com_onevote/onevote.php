<?php
/**
 * @package Component OneVote! for Joomla! 2.5/3.0
 * @author Brian Keahl
 * @copyright (C) 2014- Advanced Computer Systems
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

function xfile_get_contents($file)
{
 if (function_exists('curl_version'))
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $file.'&curl=1');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);
  }
 else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
  {
    $content = file_get_contents($file.'&allow_url_fopen=1');
  }
 else
  {
   echo '<div style="margin:auto auto;font-face:helvetica;font-size:16pt;color:red">You have neither cUrl installed nor allow_url_fopen activated. Please setup one of those!</div>';
  }
 return $content;
}

$http='http';$port="";
if(!empty($_SERVER['HTTPS']))
 {
  if($_SERVER['HTTPS']!== 'off')
   $http='https';
 }
else if(!empty($_SERVER['SERVER_PORT']))
 {
  if($_SERVER['SERVER_PORT']==443)
   $http='https';
  else if(is_numeric($_SERVER['SERVER_PORT']))$port=$_SERVER['SERVER_PORT'];
 }
?>
<div style='position:absolute;display:block;'><img width=100 height=100 src=../components/com_onevote/images/onevote200x200.png></div>
<table align=center style='width:1000px;'><tr>
<SCRIPT>
function setresults(block)
 {
  document.getElementById('electionresults').style.display='none';
  document.getElementById('electionrecords').style.display='none';
  document.getElementById('electionparticipants').style.display='none';
  document.getElementById('electionrecordstab').style.backgroundColor='#d0d0d0';
  document.getElementById('electionresultstab').style.backgroundColor='#d0d0d0';
  document.getElementById('electionparticipantstab').style.backgroundColor='#d0d0d0';
  document.getElementById(block).style.display='inline';
  document.getElementById(block+'tab').style.backgroundColor='#c0c0c0';
 }
setresults('electionresults');
</SCRIPT>
<?php
$msg='';
// $tzlist = DateTimeZone::listAbbreviations();
$inifile=substr(__FILE__,0,strrpos(__FILE__,'.php')).'.ini';
$tmpinifile=substr(__FILE__,0,strrpos(__FILE__,'.php')).'.new';
$registrationcomplete=0; // Only used during actual registartion process.  isreg is runtime determiner.
// echo '<br><br><br><br><br><br><br><br>'.$inifile.'<br>'.$tmpinifile.'<br><br>';
$tzvalue='America/New_York';$isreg=0;$regkey='';$pastresults=0;
if(file_exists($inifile))
if($fp=fopen($inifile,'rt'))
 {
  while($inp=trim(fgets($fp)))
   {
//    echo $inp.'<br>';
    $li=explode("=",$inp);
	//echo $inp.'<br>';
	if($li[0]=='tz')$tzvalue=$li[1];
	if($li[0]=='rk')$regkey=$li[1];
	if($li[0]=='pr')$pastresults=$li[1];
   }
  fclose($fp);
 }
if($isreg==0)
 {
  $isreg=xfile_get_contents('http://www.advcomsys.com/joomla/jonevote/adhead.php?act=checkreg&regkey='.$regkey);
//  if($isreg==2)$isreg=0; // DEBUGGING USE
 }
echo xfile_get_contents("http://www.advcomsys.com/joomla/jonevote/adhead.php?admin=1&isreg=".$isreg);
// echo '<br><br><br><br><br>'.$isreg.'<br>';
// echo $tzvalue.'<br>';
$tzlist = DateTimeZone::listIdentifiers();
// print_r($list);echo '<hr>';print_r($idents);echo '<hr>';
date_default_timezone_set($tzvalue);

$uri=$_SERVER['SERVER_NAME'];if($port!='')$uri.=":".$port;
if(strpos($_SERVER['REQUEST_URI'],'?')===false)$uri.=$_SERVER['REQUEST_URI'].'?canard=1';
// else $url.=substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'?'));
if(strpos($_SERVER['REQUEST_URI'],'&act')===false)$uri.=$_SERVER['REQUEST_URI'];
else $uri.=substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&act'));
// echo $_SERVER['SERVER_NAME']."|".$_SERVER['REQUEST_URI']."|".$uri.'<br>';
?>
<td valign=top style='width:450px;'>
<?php
$yesno=array('No','Yes','Y','N');
$votetype=array('Office','Question');

$db = JFactory::getDbo();
$user = JFactory::getUser();
$jinput = JFactory::getApplication()->input;

// print_r($jinput);

$act=$jinput->get('act','','');
$election_id=$jinput->get('election_id',0,'');
// if($election_id==0)if(!empty($_GET['election_id']))$election_id=$_GET['election_id'];
$description=$jinput->get('description','','');
$title=$jinput->get('title','','');
$nominations_open=$jinput->get('nominations_open',null,'');
$nominations_close=$jinput->get('nominations_close',null,'');
$notify_nominee=$jinput->get('notify_nominee',0,'');
$one_nomination=$jinput->get('one_nomination',0,'');
$polls_open=$jinput->get('polls_open',null,'');
$polls_close=$jinput->get('polls_close',null,'');
$anonymous_nominations=$jinput->get('anonymous_nominations',0,'');
$anonymous_voting=$jinput->get('anonymous_voting',1,'');
$show_results=$jinput->get('show_results',0,'');
$show_results_min_votes=$jinput->get('show_results_min_votes',10,'');
$show_total_votes_cast=$jinput->get('show_total_votes_cast',0,'');
$email_nominations_to=$jinput->get('email_nominations_to','','');
$email_votes_to=$jinput->get('email_votes_to','','');
$groupids=$jinput->get('groupids','','');
$active=$jinput->get('active',0,'');

$ballot_item_id=$jinput->get('ballot_item_id','','');
$ballot_title=$jinput->get('ballot_title','','');
$ballot_description=$jinput->get('ballot_description','','');
$is_ballot_question=$jinput->get('is_ballot_question',0,'');
$allow_nominations=$jinput->get('allow_nominations',0,'');
$nominate_group_members_only=$jinput->get('nominate_group_members_only',1,'');
$position=$jinput->get('position',0,'');
$votes=$jinput->get('votes',1,'');

$first_name=$jinput->get('first_name','','');
$last_name=$jinput->get('last_name','','');
$nominee_email=$jinput->get('nominee_email','','');
$nominee_id=$jinput->get('nominee_id',0,'');
$nomination_id=$jinput->get('nomination_id',0,'');

$nomination_id=$jinput->get('nomination_id',0,'');

$regemail=$jinput->get('regemail','','');
$regdomain=$jinput->get('regdomain','','');

$log_time=$jinput->get('log_time','','');

$ip = $_SERVER['REMOTE_ADDR'];


$question_response=$jinput->get('question_response','','');
if($question_response!='')
{
 $first_name=substr($question_response,0,24);
 $last_name=substr($question_response,24,24);
}


if($groupids=='')$groupids = array();
// print_r($groupids);

$tablenames = array('#__onevote_elections','#__onevote_groups','#__onevote_ballot_items','#__onevote_nominations','#__onevote_votes');

?>
<SCRIPT type='text/javascript'>
var showingregistration=0;
var optionscode;
function setup_registration() {
  if(showingregistration==0)
   {
	optionscode=document.getElementById('regdiv').innerHTML;
	document.getElementById('regdiv').innerHTML='<table style="width:100%"><tr><td colspan=3 style="text-align:center;font-face=Georgia;font-size:18pt;text-decoration:underline;">Finish Registration</td></tr><tr><td style="float:right;">Email:</td><td><input name=regemail style="width:195px;" value="<?php echo $regemail;?>"></td><td><input type=submit name=act value="Register!"></td></tr><tr><td style="text-algin:right;">Domain:</td><td><input name=regdomain style="width:195px;" value="<?php echo $regdomain;?>"></td><td><input type=button value="Cancel" onclick="setup_registration();"></td></tr></table>';
	showingregistration=1;
   }
  else
   {
	document.getElementById('regdiv').innerHTML=optionscode;
	showingregistration=0;
   }
 }
</SCRIPT>
<?PHP
if($act=='report')
 {
  $query=$db->getQuery(true);
  $query->select('election_id,title,description,nominations_open,nominations_close,one_nomination*1 as one_nomination,polls_open,polls_close,anonymous_nominations*1 as anonymous_nominations,anonymous_voting*1 as anonymous_voting,show_results*1 as show_results,show_results_min_votes,email_nominations_to,email_votes_to,notify_nominee*1 as notify_nominee,show_total_votes_cast*1 as show_total_votes_cast,active*1 as active,ip');
  $query->from('#__onevote_elections');
  $query->where("election_id='".$election_id."'");
  $db->setQuery($query);
  $row = $db->loadAssoc();

  echo '<div style="float:top;"><Center><font size=5><B><U>'.$row['title'].'</U></B>'.
       "&nbsp;&nbsp;<a id=printlink href='javascript:void(0);' onclick=\"this.style.visibility='hidden';window.print();this.style.visibility='visible';\"><span style=\"font-size:8pt;\">Print</span></a>".
       '</font><br><table style="width:500px;"><tr><td style="color:#444444;font-face:helvetica;font-size:8pt;">'.$row['description'].'</td></tr></table></Center>';
  $query=$db->getQuery(true);
  $query->select('position,election_id,ballot_item_id,ballot_title,ballot_description,is_ballot_question*1 as is_ballot_question,allow_nominations*1 as allow_nominations,nominate_group_members_only*1 as nominate_group_members_only');
  $query->from('#__onevote_ballot_items');
  $query->order('position,ballot_item_id');
  $query->where("election_id=".$election_id);
  $db->setQuery($query);
  $brows = $db->loadAssocList();
  $rownum=0;
  echo '<div id=report_electionresults style="display:inline;"><table align=center style="width:500px;">';
  $voteslist="";$c="";
  $nominatelist="";
  foreach($brows as $brow)
   {
    $votename="v".$brow['ballot_item_id'];$voteslist.=$c.$votename;$c=",";
    $nominatename="n".$brow['ballot_item_id'];$nominatelist.=$c.$nominatename;$c=",";
    echo "<tr style='background-color:black;color:white;'><td colspan=4><div style='font-size:16pt;text-align:center;'>".$brow['ballot_title']."</div>";
    if(strlen($brow['ballot_description'])<64)$talign="text-align:center";else $talign="";
    if($brow['ballot_description']!='')echo "<div style='font-face:arial;font-size:12px;color:yellow;margin-top:-5px;".$talign."'>".$brow['ballot_description']."</div>";
    echo "</td></tr>";
    echo "<tr style='background-color:black;color:white;font-size:12pt;'>";
    if($brow['is_ballot_question'])echo "<td style='text-align:center;width:485px;' colspan=2>Question Response</td><td style='text-align:right;width:80px;'>Votes</td><td style='text-align:right;width:100px;'>Pct. </td>";
    else echo "<td style='text-align:center;width:232px;'>Candidate Name</td><td style='width:232px;'>Username</td><td style='text-align:right;width:80px;'>Votes</td><td style='text-align:right;width:100px;'>Pct. </td>";
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
	echo '<tr height=2><td colspan=4><hr style="margin-top:0px;margin-bottom:1px;"></td></tr>';
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
  echo '<tr style="background-color:black;color:white;font-size:12pt;"><td colspan=2 style="text-align:right">Total Votes Cast:</td><td style="text-align:right">'.$totalvotes.'</td><td style="text-align:right">'.number_format(100,2).'</td></tr>';
  echo '</table></div>';
			// Here we get the voters
  $query=$db->getQuery(true);
  $query->select('name,username,vote_log_time,ip');
  $query->from('#__onevote_votes');
  $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('user_id') . ')');
  $query->where("ballot_item_id=0 and nomination_id=0 and election_id=".$election_id);
  $query->order('username');
  $db->setQuery($query);
  $xrows = $db->loadAssocList();
//  echo $query.'<hr>';
  $lc=0;$lastusername='';
  foreach($xrows as $xrow)
   {
    if($lc++%40==0){
	  if($lc>0)echo '</table></div>';
      echo "<P style='page-break-after:always;'></P><div style='position:absolute;display:block;'></div>";
      echo '<div style="float:top;margin-left:250px;width:500px;]"><Center><font size=5><B><U>'.$row['title'].'</U></B></font><br><table><tr><td style="color:#444444;font-face:helvetica;font-size:8pt;">'.$row['description'].'</td></tr></table></Center></div>';
	  echo '<div id=report_electionparticipants style="display:inline;"><table align=center style="margin-left:250px;width:500px;"><tr style="background-color:black;color:white;"><td colspan=5 align=center>Election Participants</td></tr><tr style="background-color:black;color:white;"><td align=center>User</td><td align=center>Name</td><td align=center>Vote Date/Time</td><td align=center>IP Address</td></tr>';
	 }
    if($xrow['username']=='' || $xrow['username']!=$lastusername)echo '<tr><td>'.$xrow['username'].'</td><td>'.$xrow['name'].'</td><td>'.$xrow['vote_log_time'].'</td><td>'.$xrow['ip'].'</td></tr>';
	$lastusername=$xrow['username'];
   }
  if($lc)echo '</table></div>';
//  echo "<Script type='text/javascript'>document.getElementById('printlink').style.visibility='hidden';document.getElementById('printlink').click();</Script>";

/*
	 // Here we get the INDIVIDUAL votes!
  $query=$db->getQuery(true);
  $query->select('name,username,vote_log_time,ip,ballot_item_id,nomination_id');
  $query->from('#__onevote_votes');
  $query->join('left',$db->quoteName('#__users','b'). 'ON' . $db->quoteName('id') . '=' . $db->quoteName('b.nomination_id'));
  $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('user_id') . ')');
  $query->where("ballot_item_id!=0 and nomination_id!=0 and election_id=".$election_id);
  $query->order('username');
  $db->setQuery($query);
  $xrows = $db->loadAssocList();
//  echo $query.'<hr>';;
  $lc=0;$lastusername='';
  foreach($xrows as $xrow)
   {
    if($lc++%40==0){
	  if($lc>0)echo '</table></div>';
      echo "<P style='page-break-after:always;'></P><div style='position:absolute;display:block;'></div>";
      echo '<div style="float:top;margin-left:250px;width:500px;]"><Center><font size=5><B><U>'.$row['title'].'</U></B></font><br><table><tr><td style="color:#444444;font-face:helvetica;font-size:8pt;">'.$row['description'].'</td></tr></table></Center></div>';
	  echo '<div id=report_electionrecords style="display:inline;"><table align=center style="margin-left:250px;width:500px;"><tr style="background-color:black;color:white;"><td colspan=5 align=center>Election Records</td></tr><tr style="background-color:black;color:white;"><td align=center>User</td><td align=center>Name</td><td align=center>Vote Date/Time</td><td align=center>IP Address</td><td>Ballot_Item</td><td>Ballot Answer</td></tr>';
	 }
    echo '<tr><td>'.$xrow['username'].'</td><td>'.$xrow['name'].'</td><td>'.$xrow['vote_log_time'].'</td><td>'.$xrow['ballot_item_id'].'</td><td>'.$xrow['b.username'].'</td></tr>';
	$lastusername=$xrow['username'];
   }
*/
  if($lc)echo '</table></div>';

  exit;
 }

// echo 'act='.$act.'<br>';
if($act=='Set!' || $act=='Register!')
 {
  $tzwritten=0;$rkwritten=0;$prwritten=0;
//  echo 'Set:';
  $tzvalue=$jinput->get('tzvalue',$tzvalue,'');
  $regkey=$jinput->get('regkey',$regkey,'');
  $pastresults=$jinput->get('pr',$pastresults,'');
//  echo 'regemail='.$regemail.'/regdomain='.$regdomain.'<br>';

  if($regemail!='' && $regdomain!='' && $act=='Register!')
   {
//	echo 'Getting Registration Key ... ';
    $regkey=xfile_get_contents('http://www.advcomsys.com/joomla/jonevote/adhead.php?act=getreg&email='.$regemail."&domain=".$regdomain);
//	echo 'Have Key:['.$regkey.']<br>';
    if(strlen($regkey)>6)$registrationcomplete=1;$isreg=$registrationcomplete;
   }
// echo $tmpinifile.'|'.$inifile.'|'.$regkey.'<hr>';
  if($fpo=fopen($tmpinifile,'wt'))
   {
//    echo 'fpo ...';
    if($fp=fopen($inifile,'rt'))
     {
//	  echo 'fp ... ';
      while($inp=trim(fgets($fp)))
       {
//	    echo '<br>'.$inp;
        $li=explode("=",$inp);
	    if($li[0]=='tz'){fputs($fpo,'tz='.$tzvalue."\r\n");$tzwritten=1;}
	    else if($li[0]=='rk'){fputs($fpo,'rk='.$regkey."\r\n");$rkwritten=1;}
	    else if($li[0]=='pr'){fputs($fpo,'pr='.$pastresults."\r\n");$prwritten=1;}
	    else fputs($fpo,$inp);
       }
      fclose($fp);
	 }
    if($tzwritten==0)fputs($fpo,'tz='.$tzvalue)."\r\n";
 	if($rkwritten==0)fputs($fpo,'rk='.$regkey)."\r\n";
 	if($prwritten==0)fputs($fpo,'pr='.$pastresults)."\r\n";
	fclose($fpo);
	unlink($inifile);
	rename($tmpinifile,$inifile);
   }
//  echo 'done';
  $act='';
 }

date_default_timezone_set($tzvalue); // This is actually setting the zone for the script.  We put it here in case the zone was changed via Set!
// echo 'tzvalue='.$tzvalue;
if($act=='delvote')
 {
  $query = $db->getQuery(true);
  $wdate=substr($log_time,0,4).'-'.substr($log_time,4,2).'-'.substr($log_time,6,2).' '.substr($log_time,9,2).':'.substr($log_time,11,2).':'.substr($log_time,13,2);
  $conditions = array(
    $db->quoteName('vote_log_time') . " = '".$wdate."'"
   );
  $query->delete($db->quoteName('#__onevote_votes'));
  $query->where($conditions);
  $db->setQuery($query);
//  echo '<hr>'.'('.$log_time.'):'.$query.'<hr>';
  $result = $db->query();
  $act='results';
 }

if($act=='deleteballotitem')
 {
  $ixx=0;
  foreach ($tablenames as $tablename)
   {
    if($ixx++>=2)
	 {
      $query = $db->getQuery(true);
      $conditions = array(
        $db->quoteName('ballot_item_id') . ' = '.$ballot_item_id
       );
      $query->delete($db->quoteName($tablename));
      $query->where($conditions);
      $db->setQuery($query);
      $result = $db->execute();
     }
   }
  $act='edit';$ballot_item_id=0;
 }

if($act=='electiondelete')
 {
  foreach ($tablenames as $tablename)
   {
    $query = $db->getQuery(true);
    $conditions = array(
      $db->quoteName('election_id') . ' = '.$election_id
     );
    $query->delete($db->quoteName($tablename));
    $query->where($conditions);
    $db->setQuery($query);
    $result = $db->execute();
   }
  $act='';$ballot_item_id=0;
 }

if($act=='Add Nominee' || $act=='Add Response')
 {
  $query=$db->getQuery(true);
  $query->select('count(*) as qty');
  $query->from('#__onevote_nominations');
  $query->where("ballot_item_id=".$ballot_item_id." and nominee_id=".$nominee_id." and election_id=".$election_id);
  $db->setQuery($query);
  $row = $db->loadAssoc();
  if($row['qty']==0 || $nominee_id==0) // If nominee ID is zero then we assume a write-in.
   {
    $nominated_by=$user->id;
    $query=$db->getQuery(true);
	if($erow['anonymous_nominations']>0)$nominated_by=0;
    $columns=array('election_id','ballot_item_id','nominee_id','first_name','last_name','nominated_by','nominee_email','ip');
    $values=array($election_id,$ballot_item_id,$nominee_id,$db->quote($first_name),$db->quote($last_name),$nominated_by,$db->quote($nominee_email),$db->quote($ip));
    $query
      ->insert($db->quoteName('#__onevote_nominations'))
      ->columns($db->quoteName($columns))
      ->values(implode(',', $values));
// Set the query using our newly populated query object and execute it.
//  echo 'prepping ... ';
    $db->setQuery($query);
//  echo 'executing ... ';
    $db->query();
   }
  $act='edit';
 }
if($act=='delnominee')
 {
  $query = $db->getQuery(true);

// delete all custom keys for user 1001.
  $conditions = array(
    $db->quoteName('nomination_id') . ' = '.$nomination_id
   );
  $query->delete($db->quoteName('#__onevote_nominations'));
  $query->where($conditions);
  $db->setQuery($query);
  $result = $db->query();
  $act='edit';
 }

if($act=='Add Ballot Item')
 {
  $query=$db->getQuery(true);
  $columns=array('election_id','ballot_title','ballot_description','is_ballot_question','allow_nominations','nominate_group_members_only','votes','position','ip');
  $values=array($election_id,$db->quote($ballot_title),$db->quote($ballot_description),$is_ballot_question,$allow_nominations,$nominate_group_members_only,
				$votes,$position,$db->quote($ip));
  $query
    ->insert($db->quoteName('#__onevote_ballot_items'))
    ->columns($db->quoteName($columns))
    ->values(implode(',', $values));
// Set the query using our newly populated query object and execute it.
//  echo 'prepping ... ';
  $db->setQuery($query);
//  echo 'executing ... ';
  $db->query();
  $query->select('ballot_item_id');
  $query->from('#__onevote_ballot_items');
  $query->where("election_id=".$election_id." and ballot_title=".$db->quote($ballot_title)."");
  $db->setQuery($query);
  $row = $db->loadAssoc();
// echo $query.'<br>';
  $ballot_item_id=$row['ballot_item_id'];

  // $ballot_item_id=0;$ballot_title='';$ballot_description='';$position=0;
  $act='edit'; // go back to edit screen
 }

if($act=='Update Ballot Item')
 {
  $query = $db->getQuery(true);
// Fields to update.
  $fields = array(
    $db->quoteName('ballot_title') . ' = ' . $db->quote($ballot_title),
    $db->quoteName('ballot_description') . ' = ' . $db->quote($ballot_description),
	$db->quoteName('is_ballot_question') . ' = ' . $is_ballot_question,
    $db->quoteName('allow_nominations') . ' = ' . $allow_nominations,
    $db->quoteName('nominate_group_members_only') . ' = ' . $nominate_group_members_only,
    $db->quoteName('position') . ' = ' . $position,
    $db->quoteName('votes') . ' = ' . $votes
   );
  $conditions = array(
    $db->quoteName('ballot_item_id') . ' = '.$ballot_item_id
   );
  $query->update($db->quoteName('#__onevote_ballot_items'))->set($fields)->where($conditions);
  $db->setQuery($query);
  $result = $db->query();

  $ballot_item_id=0;$ballot_title='';$ballot_description='';$votes=0;$position=0; $act='edit'; // go back to edit screen
 }


if($act=='Add' || $act=='Update')
 {
  date_default_timezone_set($tzvalue);
  if($polls_open=='')$polls_open=date('Y-m-d h:i:s');if($polls_close=='')$polls_close=date('Y-m-d h:i:s');
  if($nominations_open=='')$nominations_open=date('Y-m-d h:i:s');if($nominations_close=='')$nominations_close=date('Y-m-d h:i:s');
//  echo $polls_open.'/'.$polls_close.'/'.$nominations_open.'/'.$nominations_close.'/'.
//       strtotime($polls_open).'/'.strtotime($polls_close).'/'.strtotime($nominations_open).'/'.strtotime($nominations_close).'<br>';
  if(strtotime($nominations_close)<strtotime($nominations_open))
   {
	$nominations_close=$nominations_open;
//	echo '1';
	$msg.="Nominations Close date must be greater than Nominations Open. Forced Nominations Close to valid value.";
   }
  if(strtotime($polls_close)<strtotime($polls_open))
   {
//	echo '2';
	$polls_close=$polls_open;
	$msg.="Voting Ends date must be greater than Voting Starts. Forced voting closed to valid value.";
   }
  if(strtotime($nominations_open)>strtotime($polls_close) || strtotime($nominations_close)>strtotime($polls_close))
   {
//	echo '3';
	$msg.="Nominations Open and Close must be before or during the Polling period.";
    $nominations_open=$polls_open;$nominations_close=$polls_open;
   }
 }

if($act=='Add')
 {
 // Search and verify no duplicate election!
 //  echo 'setting up ... ';
  $query=$db->getQuery(true);
  $columns=array('title','description','nominations_open','nominations_close','polls_open','polls_close','anonymous_nominations','anonymous_voting','show_results',
                 'show_results_min_votes','show_total_votes_cast','email_nominations_to','email_votes_to','active','creator_id','one_nomination','notify_nominee','ip');
  $values=array($db->quote($title),$db->quote($description),$db->quote($nominations_open),$db->quote($nominations_close),$db->quote($polls_open),$db->quote($polls_close),
				$anonymous_nominations,$anonymous_voting,$show_results,$show_results_min_votes,$show_total_votes_cast,$db->quote($email_nominations_to),
				$db->quote($email_votes_to),$active,$user->id,$one_nomination,$notify_nominee,$db->quote($ip));
  $query
    ->insert($db->quoteName('#__onevote_elections'))
    ->columns($db->quoteName($columns))
    ->values(implode(',', $values));
// Set the query using our newly populated query object and execute it.
//  echo 'prepping ... ';
//  echo $query.'<br>';
  $db->setQuery($query);
//  echo 'executing ... ';
  $db->query();
//  echo 'done.<br>';
// Search for election and get $election_id.
  $query->select('election_id');
  $query->from('#__onevote_elections');
  $query->where("title='".$title."'");
  $db->setQuery($query);
  $row = $db->loadAssoc();
  $election_id=0;
// echo $query.'<br>';
  $election_id=$row['election_id'];
// Okay, got election_id
//
  foreach($groupids as $groupid)
   {
    $query=$db->getQuery(true);
    $columns=array('election_id','group_id');
	$values=array($election_id,$db->quote($groupid));
    $query
      ->insert($db->quoteName('#__onevote_groups'))
      ->columns($db->quoteName($columns))
      ->values(implode(',', $values));
    $db->setQuery($query);
//    echo 'executing ... ';
    $db->query();
   }
  $act='edit';
 }

if($act=='Update')
 {
 // Update the election record first
  $query = $db->getQuery(true);
// Fields to update.
  $fields = array(
    $db->quoteName('title') . ' = ' . $db->quote($title),
    $db->quoteName('description') . ' = ' . $db->quote($description),
    $db->quoteName('nominations_open') . ' = ' . $db->quote($nominations_open),
	$db->quoteName('nominations_close') . ' = ' . $db->quote($nominations_close),
	$db->quoteName('notify_nominee') . ' = ' . $notify_nominee,
    $db->quoteName('polls_open') . ' = ' . $db->quote($polls_open),
    $db->quoteName('polls_close') . ' = ' . $db->quote($polls_close),
    $db->quoteName('anonymous_nominations') . ' = ' . $anonymous_nominations,
    $db->quoteName('one_nomination') . ' = ' . $one_nomination,
    $db->quoteName('anonymous_voting') . ' = ' . $anonymous_voting,
    $db->quoteName('show_results') . ' = ' . $show_results,
    $db->quoteName('show_results_min_votes') . ' = ' . $show_results_min_votes,
    $db->quoteName('show_total_votes_cast') . ' = ' . $show_total_votes_cast,
    $db->quoteName('email_nominations_to') . ' = ' . $db->quote($email_nominations_to),
    $db->quoteName('email_votes_to') . ' = ' . $db->quote($email_votes_to),
    $db->quoteName('active') . ' = ' . $active
   );
  $conditions = array(
    $db->quoteName('election_id') . ' = '.$election_id
   );
  $query->update($db->quoteName('#__onevote_elections'))->set($fields)->where($conditions);
  $db->setQuery($query);
  $result = $db->query();
// Done updating the election record
//  echo $query.'<hr>';
// Now we delete the old group fields and insert the new ones
  $query = $db->getQuery(true);
  $conditions = array(
    $db->quoteName('election_id') . ' = '.$election_id
   );
  $query->delete($db->quoteName('#__onevote_groups'));
  $query->where($conditions);
  $db->setQuery($query);
  $result = $db->query();
// Okay, old groups should be gone now
// Plug the new ones in
  foreach($groupids as $groupid)
   {
    $query=$db->getQuery(true);
    $columns=array('election_id','group_id');
	$values=array($election_id,$db->quote($groupid));
    $query
      ->insert($db->quoteName('#__onevote_groups'))
      ->columns($db->quoteName($columns))
      ->values(implode(',', $values));
    $db->setQuery($query);
//    echo 'executing ... ';
    $db->query();
   }

  $act='edit'; // Force us to stay in edit mode
 }

if($act=='edit' || $act=='results') // If we're editing we need to recall the info to populate the page
 {
// Search for election and get $election_id.
  $query=$db->getQuery(true);
  $query->select('election_id,title,description,nominations_open,nominations_close,one_nomination*1 as one_nomination,polls_open,polls_close,anonymous_nominations*1 as anonymous_nominations,anonymous_voting*1 as anonymous_voting,show_results*1 as show_results,show_results_min_votes,email_nominations_to,email_votes_to,notify_nominee*1 as notify_nominee,show_total_votes_cast*1 as show_total_votes_cast,active*1 as active,ip');
  $query->from('#__onevote_elections');
  $query->where("election_id='".$election_id."'");
  $db->setQuery($query);
  $row = $db->loadAssoc();
// echo $query.'<br>';
  if(!$row)echo '<center>Unexpected Error.  Election ['.$election_id.'] could not be found!</center>';
  else
   {
    $election_id=$row['election_id'];
    $title=$row['title'];
    $description=$row['description'];
    $nominations_open=$row['nominations_open'];
    $nominations_close=$row['nominations_close'];
    $one_nomination=$row['one_nomination'];
    $notify_nominee=$row['notify_nominee'];
    $polls_open=$row['polls_open'];
    $polls_close=$row['polls_close'];
    $anonymous_nominations=$row['anonymous_nominations'];
    $anonymous_voting=$row['anonymous_voting'];
    $show_results=$row['show_results'];
    $show_results_min_votes=$row['show_results_min_votes'];
    $show_total_votes_cast=$row['show_total_votes_cast'];
    $email_nominations_to=$row['email_nominations_to'];
    $email_votes_to=$row['email_votes_to'];
//    $groupids=$row['groupids'];
    $active=$row['active'];
    $ip = $row['ip'];

    $query=$db->getQuery(true);
    $query->select('election_id,group_id');
    $query->from('#__onevote_groups');
    $query->where("election_id=".$election_id);
    $db->setQuery($query);
    $rows = $db->loadAssocList();
    $groupids = array();
    foreach($rows as $row)
	 {
	  $groupids[]=$row['group_id'];
	 }
   }
  if($ballot_item_id>0)
   {
    $query=$db->getQuery(true);
    $query->select('position,election_id,ballot_item_id,ballot_title,ballot_description,is_ballot_question*1 as is_ballot_question,allow_nominations*1 as allow_nominations,nominate_group_members_only*1 as nominate_group_members_only,votes');
    $query->from('#__onevote_ballot_items');
    $query->where("ballot_item_id=".$ballot_item_id);
    $db->setQuery($query);
    $row = $db->loadAssoc();
	if($row){
      $ballot_title=$row['ballot_title'];
	  $ballot_description=$row['ballot_description'];
	  $is_ballot_question=$row['is_ballot_question'];
	  $allow_nominations=$row['allow_nominations'];
	  $nominate_group_members_only=$row['nominate_group_members_only'];
	  $position=$row['position'];
	  $votes=$row['votes'];
	 }
	else
	 {
	  echo 'Unexpected error retrieving ballot item!<br>';
	 }
   }
 }

if($act!='report')echo "";
if($msg!='')echo '<div style="width:100%;font-face:Georgia;color:red;font-size:12pt;">'.$msg.'</div>';
if($act=='' || $act=='edit' || $act=='results' || $act=='nominations')
 {
  echo "<form action=\"".$http.'://'.$uri."\" method=POST>";
  echo '<input type=hidden name=election_id value='.$election_id.'>';
  if($act=='')echo "<center><font size=6><B><U>Create New Election</U></B></font></center><br>";
  else if($act=='nominations' || $act=='edit')echo "<center><font size=6><B><U>Edit Election</U></B></font></center><br>";
  else echo "<center><font size=6><B><U>Election Results</U></B></font></center><br>";
echo "<table align=center>
<tr>
 <td align=right>Election Name:</td>
 <td colspan=3><input type=text name=title style='width:240px;' size=64 maxlength=64 value='".$title."'></td>
</tr>
<tr>
 <td align=right>Description/Details:</td>
 <td colspan=3><textarea name=description cols=63 rows=4>".$description."</textarea></td>
</tr>";
if($isreg)
 {
  echo "<tr>
   <td align=right>Nominations Open:</td><td>";
   echo JHTML::calendar($nominations_open,'nominations_open','nominations_open','%Y-%m-%d %H:%M:%S');
   echo "</td>
  </tr>
  <tr>
   <td align=right>Nominations Close:</td><td>";
   echo JHTML::calendar($nominations_close,'nominations_close','nominations_close','%Y-%m-%d %H:%M:%S');
   echo "</td>
  </tr>
  <tr><td align=right>Notify new nominees:</td><td><input type=checkbox value=1 name=notify_nominee title='If checked, the system will attempt to notify new nominees of their nomination by email.'";
  if($notify_nominee>0)echo "checked";
  echo "></td></tr>
  <tr><td align=right>One Office Per Nominee:</td><td><input type=checkbox name=one_nomination value=1 title='If checked, person can only be nominated for one office.' ";
  if($one_nomination>0)echo 'checked';
  echo "></td></tr>
  <tr>
   <td align=right nowrap>Anonymous Nominations?</td>
   <td><select name=anonymous_nominations style='width:60px;' title='If Yes then people making the nominations are kept secret. If No then the User making the nomination is identified.'><option value=0 ";
   if($anonymous_nominations==0)echo 'selected';
   echo ">No</option><option value=1 ";
   if($anonymous_nominations==1)echo 'selected';
   echo ">Yes</option></select></td>
  </tr> ";
 }
else
 {
  echo '<tr><td colspan=2>The PRO version allows you to set date and time to open and close nominations by participants and whether to notify the nominee of their nomination via email. It also allows for nominations to be either anonymous or not.</td></tr>';
 }
if ($isreg)
 {
  echo "<tr> <td align=right>Voting Starts:</td><td>";
  echo JHTML::calendar($polls_open,'polls_open','polls_open','%Y-%m-%d %H:%M:%S');
  echo "</td></tr>";
 }
else
 {
  echo "<tr><td colspan=2>The PRO version allows you to set both poll open (start voting) and poll close (stop voting) times.</td></tr>";
 }
echo "<tr>
 <td align=right>Voting Ends:</td><td>";
 echo JHTML::calendar($polls_close,'polls_close','polls_close','%Y-%m-%d %H:%M:%S');
 echo "</td>
</tr>
<tr>
 <td align=right>Anonymous Voting?</td>
 <td><select name=anonymous_voting style='width:60px;' title='If Yes then votes are secret.  If No, then the votes include the ID of the user.'><option value=0 ";
 if($anonymous_voting==0)echo 'selected';
 echo ">No</option><option value=1 ";
 if($anonymous_voting==1)echo 'selected';
 echo ">Yes</option></select></td>
</tr>
<tr><td align=right>Allowed Groups:<br><div style='font-size:10px;font-face=helvetica'>Members of checked groups can participate in the election.</div></td><td>
<table align=left>";

$query = $db->getQuery(true);
$query->select('id,title');
$query->from('#__usergroups');
$query->order($db->quoteName('title'));
$db->setQuery($query);
$rows = $db->loadAssocList();
$rownum=0;
// echo $query.'<br>';
foreach ($rows as $row)
 {
// print_r($row);
  if(($rownum%2)==0)
   if($rownum>0)echo '</tr><tr>';
   else echo '<tr>';
  $checked='';foreach($groupids as $groupid)if($groupid==$row['id'])$checked=' checked';
  echo '<td style="width:220px;"><input type=checkbox name=groupids[] value='.$row['id'].$checked." title='If checked, members of the ".$row['title']." group may participate.'>".substr($row['title'],0,20).'</td>';
  $rownum+=1;
 }
if($rownum>0)echo '</tr>';
echo "
</table>
</td>
</tr>
<tr><td align=right>Show Results:</td><td><input type=checkbox name=show_results value=1 ";
if($show_results>0)echo 'checked';
echo " title='Allows results to be viewed DURING the voting process.'> after <input type=number size=2 maxlength=3 name=show_results_min_votes style='width:40px;' value='".$show_results_min_votes."'>votes cast.</td></tr>
<tr><td align=right>Show Total Votes:</td><td><input type=checkbox name=show_total_votes_cast value=1 title='If checked, will display total ballots cast so far at bottom of the ballot, near the vote button.' ";
if($show_total_votes_cast>0)echo 'checked';
echo "></td></tr>
<tr><td align=right>Email Nominations To:</td><td><input type=text name=email_nominations_to value='".$email_nominations_to."' style='width:240px;' title='Every time a nomination is made an email is sent to this address. If anonymous nominations is set to YES then the identity of the person nominating is not included.'></td></tr>
<tr><td align=right>Email Votes To:</td><td><input type=text name=email_votes_to value='".$email_votes_to."' style='width:240px;'  title='Every time a vote is cast an email is sent to this address. If anonymous voting is set to YES then the identity of the person voting is not included.'></td></tr>";
echo "<tr><td align=right>Active?:</td><td><input type=checkbox name=active value=1 ";
if($active>0)echo 'checked';
echo " title='This enables the election. If not checked no voting can take place.'></td></tr>
</table>";
if($act=='')echo "<center><input type=submit name=act value='Add'></center>";
else if($act=='edit')echo "<center><input type=submit name=act value='Update'> <input type=button onclick=\"window.location='".$http."://".$uri."';\" value='Back to Main'></center>";
// echo '<br>'.$_SERVER['SCRIPT_URI'];
echo "</form></td>";
}
// if($act=='report')echo '<table><tr>';
?>
<td width=50><comment> </comment></td>
<?php
if($act=='RESET')
 {
  $query = $db->getQuery(true);
  $conditions = array(
    $db->quoteName('election_id') . " = '".$election_id."'"
   );
  $query->delete($db->quoteName('#__onevote_votes'));
  $query->where($conditions);
  $db->setQuery($query);
//  echo '<hr>'.'('.$log_time.'):'.$query.'<hr>';
  $result = $db->query();
  $act='results';
 }
if($act=='results')
 {
  echo '<td valign=top style="width:560px;">';
  echo '<Center><font size=5><B><U>'.$title.'</U></B></font><br><table><tr><td style="color:#444444;font-face:helvetica;font-size:8pt;">'.$description.'</td></tr></table></Center>';
  $query=$db->getQuery(true);
  $query->select('position,election_id,ballot_item_id,ballot_title,ballot_description,is_ballot_question*1 as is_ballot_question,allow_nominations*1 as allow_nominations,nominate_group_members_only*1 as nominate_group_members_only');
  $query->from('#__onevote_ballot_items');
  $query->order('position,ballot_item_id');
  $query->where("election_id=".$election_id);
  $db->setQuery($query);
  $brows = $db->loadAssocList();
  $rownum=0;

  echo "<table style='border:1px solid black;margin-left:auto;margin-right:auto;'><tr style=''><td id=electionresultstab style='border:1px solid black;background-color:#c0c0c0;'><a href='javascript:void(0);' onclick=\"setresults('electionresults');\"> View Results</a></td><td style='border:1px solid black;background-color:#d0d0d0;' id=electionparticipantstab><a href='javascript:void(0);' onclick=\"setresults('electionparticipants');\">View Participants</a></td><td style='border:1px solid black;background-color:#d0d0d0;' id=electionrecordstab><a href='javascript:void(0);' onclick=\"setresults('electionrecords');\">View Votes</a></td></tr></table>";
  echo '<div id=electionresults style="display:inline;"><table align=center style="width:500px;">';
  $voteslist="";$c="";
  $nominatelist="";
  foreach($brows as $brow)
   {
    $votename="v".$brow['ballot_item_id'];$voteslist.=$c.$votename;$c=",";
    $nominatename="n".$brow['ballot_item_id'];$nominatelist.=$c.$nominatename;$c=",";
    echo "<tr style='background-color:black;color:white;'><td colspan=4><div style='font-size:16pt;text-align:center;'>".$brow['ballot_title']."</div>";
    if(strlen($brow['ballot_description'])<64)$talign="text-align:center";else $talign="";
    if($brow['ballot_description']!='')echo "<div style='font-face:arial;font-size:12px;color:yellow;margin-top:-5px;".$talign."'>".$brow['ballot_description']."</div>";
    echo "</td></tr>";
    echo "<tr style='background-color:black;color:white;font-size:12pt;'>";
    if($brow['is_ballot_question'])echo "<td style='text-align:center;width:485px;' colspan=2>Question Response</td><td style='text-align:right;width:80px;'>Votes</td><td style='text-align:right;width:100px;'>Pct. </td>";
    else echo "<td style='text-align:center;width:232px;'>Candidate Name</td><td style='width:232px;'>Username</td><td style='text-align:right;width:80px;'>Votes</td><td style='text-align:right;width:100px;'>Pct. </td>";
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
	echo '<tr height=2><td colspan=4><hr style="margin-top:0px;margin-bottom:1px;"></td></tr>';
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
  echo '<tr style="background-color:black;color:white;font-size:12pt;"><td colspan=2 style="text-align:right">Total Votes Cast:</td><td style="text-align:right">'.$totalvotes.'</td><td style="text-align:right">'.number_format(100,2).'</td></tr>';
  echo '</table></div>';

			// Here we get the voters
  $query=$db->getQuery(true);
  $query->select('name,username,vote_log_time,ip,vote_id');
  $query->from('#__onevote_votes');
  $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('user_id') . ')');
  $query->where("ballot_item_id=0 and nomination_id=0 and election_id=".$election_id);
  $query->order('username,vote_log_time');
  $db->setQuery($query);
  $xrows = $db->loadAssocList();
//  echo $query.'<hr>';
  echo '<div id=electionparticipants style="display:none;"><table align=center style="width:500px;"><tr style="background-color:black;color:white;"><td colspan=4 align=center>Election Participants</td></tr><tr style="background-color:black;color:white;"><td align=center>User</td><td align=center>Name</td><td align=center>Vote Date/Time</td><td align=center>IP Address</td></tr>';
  foreach($xrows as $xrow)
   {
    echo '<tr><td>'.$xrow['username'].
		 "<a onclick=\"return confirm('Are you sure you want to remove this vote and alter the election?')\" href=".$http."://".$uri.'&act=delvote&election_id='.$election_id."&log_time=".date('Ymd.His',strtotime($xrow['vote_log_time']))."><img src='../components/com_onevote/images/del.gif'></a>".
		 '</td><td>'.$xrow['name'].'</td><td>'.$xrow['vote_log_time'].'</td><td>'.$xrow['ip'].'</td></tr>';
   }
  echo '</table></div>';
// }

			// Here we get the INDIVIDUAL votes
  $query=$db->getQuery(true);
  $query->select('name,username,username,vote_log_time,a.ip,a.ballot_item_id,nomination_id,user_id');
  $query->from($db->quoteName('#__onevote_votes', 'a'));
  $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.user_id') . ')');
//  $query->join('left',$db->quoteName('#__onevote_nominations','n'). 'ON' . $db->quoteName('n.nomination_id') . '=' . $db->quoteName('a.nomination_id'));
//  $query->join('left',$db->quoteName('#__users','b'). 'ON' . $db->quoteName('b.id') . '=' . $db->quoteName('n.nominee_id'));
  $query->where("a.election_id=".$election_id);
  $query->order('vote_log_time,u.username desc');
  $db->setQuery($query);
  $xrows = $db->loadAssocList();
//  echo $query.'<hr>';
  echo '<div id=electionrecords style="display:none;"><table align=center style="width:500px;"><tr style="background-color:black;color:white;"><td colspan=5 align=center>Election Participants</td></tr><tr style="background-color:black;color:white;"><td align=center>User</td><td align=center>Name</td><td align=center>Vote Date/Time</td><td>Ballot ID</td><td>Nom.ID</td></tr>';
  foreach($xrows as $xrow)
   {
    echo '<tr><td>'.$xrow['user_id'].':'.$xrow['username'].'</td><td>'.$xrow['name'].'</td><td>'.$xrow['vote_log_time'].'</td><td>'.$xrow['ballot_item_id'].'</td><td>'.$xrow['nomination_id'].'</td></tr>';
   }
  echo '</table></div>';

  echo '<center>';
  echo "<br><input type=button value=\"Back to Main\" onclick=\"window.location.href='".$http."://".$uri."';\">";
  echo "&nbsp;&nbsp;&nbsp;<input type=button value=\"Reset\" onclick=\"if(confirm('This will erase existing votes and results! Are you sure?'))if(confirm('Are you really sure you wish to erase existing votes and results?'))window.location.href='".$http."://".$uri."&act=RESET&election_id=".$election_id."';\">";
  echo '</center>';
  echo '</td>';
 }


if($act=='edit')
 {
  echo '<td valign=top style="width:550px;">';
  echo '<Center><font size=5><B><U>Ballot Item Management</U></B></font></Center><br>';
  if($ballot_item_id>0)
   {
    echo "<form action=\"".$http."://".$uri."&act=Add Nominee\" method=POST>";
	echo "<input type=hidden name=ballot_item_id value=".$ballot_item_id.">";
	echo "<input type=hidden name=election_id value=".$election_id.">";
    $query=$db->getQuery(true);
    $query->select('position,election_id,ballot_item_id,ballot_title,ballot_description,is_ballot_question*1 as is_ballot_question,allow_nominations*1 as allow_nominations,nominate_group_members_only*1 as nominate_group_members_only');
    $query->from('#__onevote_ballot_items');
    $query->where("ballot_item_id=".$ballot_item_id);
    $db->setQuery($query);
    $brow = $db->loadAssoc();
    echo '<Center><font size=4><B>';
	if($brow['is_ballot_question'])echo 'Answers';else echo 'Nominees';
	echo ' for '.$brow['ballot_title'].'</B></font></Center>';

    $query=$db->getQuery(true);
    $query->select('nomination_id,nominee_id,first_name,last_name,nominated_by');
    $query->from('#__onevote_nominations');
    $query->where("ballot_item_id=".$ballot_item_id);
    $db->setQuery($query);
    $nrows = $db->loadAssocList();
	$rownum=0;$bgcolor='';
    foreach($nrows as $nrow)
	 {
	  if($rownum==0)
	   {
	    echo "<table align=center>";
	    if($brow['is_ballot_question'])
		 echo "<tr style='background-color:black;color:white;'><td style='width:40px;'>Tasks</td><td align=center style='width:140px;' colspan=2>Question Response</td></tr>";
		else
		 echo "<tr style='background-color:black;color:white;'><td style='width:40px;'>Tasks</td><td align=center style='width:140px;'>Nominee's Name</td><td style='width:100px;'>Username</td></tr>";
	   }
	  echo "<tr><td nowrap><a onclick=\"return confirm('Are you sure you want to remove this person from nomination?');\"".' href="'.$http.'://'.$uri.'&act=delnominee&ballot_item_id='.$ballot_item_id.'&election_id='.$election_id.'&nomination_id='.$nrow['nomination_id']."\"><img src=../components/com_onevote/images/del.gif title=\"Remove\"></a></td>";
      if($nrow['nominee_id']>0)
       {
		$query=$db->getQuery(true);
		$query->select('username,name');
		$query->from('#__users');
		$query->where("id=".$nrow['nominee_id']);
		$db->setQuery($query);
		$urow = $db->loadAssoc();
        echo '<td>'.$urow['name'].'</td><td>'.$urow['username'].'</td></tr>';
       }
	  else
	   {
	    echo '<td>'.$nrow['first_name'].' '.$nrow['last_name'].'</td><td align=center>-</td></tr>';
	   }
	  $rownum+=1;
     }
	if($rownum>0)echo '</table>';

	if($brow['is_ballot_question']==0)
	 {
       // Build the part of the query that allows check person(s) are part of the valid group
      $query=$db->getQuery(true);
      $query->select('group_id');
      $query->from('#__onevote_groups');
      $query->where("election_id=".$election_id);
      $db->setQuery($query);
      $grows = $db->loadAssocList();
      $groupwhere="";$andor='';
      foreach($grows as $grow)
       {
        $groupwhere.=$andor."group_id=".$grow['group_id'];$andor=" or ";
       }
      if($groupwhere!='')$groupwhere="(".$groupwhere.")";
       // groupwhere has the search (in parenthesis) of groups valid for the election

	  echo '<B>Select nominee from list:</B><Select name=nominee_id><Option value=0>';
	  if($groupwhere=='')
	   {
	    echo 'No usergroup(s) selected.</option>';
	   }
	  else
	   {
	    echo '</Option>';
        $query=$db->getQuery(true);
        $query->select('id,username,name');
        $query->from('#__users');
        $query->where("exists(select user_id from #__user_usergroup_map where #__user_usergroup_map.user_id=#__users.id and ".$groupwhere.")");
        $query->order('name');
        $db->setQuery($query);
        $urows = $db->loadAssocList();
	    $rownum=0;
        foreach($urows as $urow)
	     {
	      echo '<Option value='.$urow['id'].'>'.$urow['name'].' ('.$urow['username'].')</Option>';
	     }
	   }
	  echo '</Select>';

//	  if($brow['nominate_group_members_only']==0) // Not requiring membership, so allow manual creation of nominee
	   {
	    echo '<div style="text-align:center;">or manually enter a nominee.</div>';
	    echo '<div style="text-align:center;">First Name: <input name=first_name style="width:100px;" maxlength=24 value="'.$first_name.'">   Last Name:<input name=last_name  style="width:100px;"  maxlength=24 value="'.$last_name.'"></div>';
		echo '<div style="text-align:center;">Email Address: <input name=nominee_email style="width:230px;" maxlength=64 value="'.$nominee_email.'" title="Please provide email so we can notify this person they have been nominated."></div>';
       }
	 }
	else
     {
	  if($question_response=='')$question_response=$nrow['first_name'].$nrow['last_name'];
	  echo '<div style="margin-left:55px;">Question Response: <input name=question_response style="width:240px;" maxlength=40 value="'.$question_response.'"></div>';
     }
    if($brow['is_ballot_question'])echo "<center><input type=submit name=act value='Add Response'>";
	else echo "<center><input type=submit name=act value='Add Nominee'>";
    echo "<br><br>or edit ballot information below.</center></form>";
   }
  else if($ballot_item_id==0)
   {
    $query=$db->getQuery(true);
    $query->select('position,election_id,ballot_item_id,ballot_title,ballot_description,is_ballot_question*1 as is_ballot_question,allow_nominations*1 as allow_nominations,nominate_group_members_only*1 as nominate_group_members_only');
    $query->from('#__onevote_ballot_items');
	$query->order('position,ballot_item_id');
    $query->where("election_id=".$election_id);
    $db->setQuery($query);
    $rows = $db->loadAssocList();
	$rownum=0;$bgcolor='';
    foreach($rows as $row)
	 {
	  if($bgcolor!='#e0e0e0')$bgcolor='#e0e0e0';else $bgcolor='#d8d8d8';
      if($rownum==0)echo '<table align=center style="width:340px;"><tr style="background-color:black;color:white;"><td colspan=3></td><td colspan=2 align=center>Nominations</td></tr><tr style="background-color:black;color:white;"><td>Tasks</td><td>Title</td><td>Type</td><td align=center>Allowed</td><td align=center>Members?</td></tr>';
      echo '<tr bgcolor="'.$bgcolor.'"><td nowrap>';
	  echo '<a href="'.$http.'://'.$uri.'&act=edit&election_id='.$row['election_id'].'&ballot_item_id='.$row['ballot_item_id'].'"><img src=../components/com_onevote/images/edit.gif title="Edit"></a>';
	  echo '<a href="'.$http.'://'.$uri.'&act=deleteballotitem&election_id='.$row['election_id'].'&ballot_item_id='.$row['ballot_item_id']."\" onclick=\"return confirm('Are you sure you want to remove this ballot item?');\" ><img src=../components/com_onevote/images/del.gif title=\"Delete\"></a>";
	  echo '</td>';
	  echo '<td style="width:150px;">'.$row['ballot_title'].'</td><td align=center>'.$votetype[$row['is_ballot_question']].'</td><td align=center>'.$yesno[$row['allow_nominations']].'</td><td align=center>'.$yesno[$row['allow_nominations']].'</td></tr>';
	  $rownum+=1;}
	if($rownum>0)echo '</table><br>';else echo '<center><font size=3 color=red>No ballot items found for this election</font></center>';
   }
  echo "<form action=\"".$http.'://'.$uri."\" method=POST>";
  echo '<table align=center>';
  echo "<tr><td align=right>Ballot Title:</td><td><input type=text name=ballot_title size=24 maxlength=24 style='width:150px;' title='Office or topic (President, Treasurer, Referendum, or Rules Change)' value='".$ballot_title."'></td></tr>";
  echo "<tr><td align=right>Description:</td><td><textarea name=ballot_description cols=64 rows=2 style='font-size:10;width:250px;' title='Details of rules change or office description)'>".$ballot_description."</textarea></td></tr>";
  echo "<tr><td align=right>Type:</td><td><select name=is_ballot_question style='width:90px;' title='Election is for an office, a Question may be rules change or referendum item.'><option value=0>Election</option><option value=1";
  if($is_ballot_question>0)echo ' selected';
  echo ">Question</option></select></td></tr>";
  echo "<tr><td style='text-align:right;'>Votes Allowed:</td><td><Select name=votes style='width:50px;'>";
  for($ixx=1;$ixx<15;$ixx++)
   {
	echo "<Option value=".$ixx;
	if($ixx==$votes)echo ' selected';
	echo ">".$ixx."</option>";
	if($isreg==0)break; // Only allow vote for one person (no board of directors style election)
   }
  echo "</select></td></tr>";
  if($isreg)
   {
    echo "<tr><td align=right>Allow Nominations:</td><td><select name=allow_nominations style='width:60px;' title='If checked then any participant may nominate for this position or suggest an answer to the question.'><option value=1>Yes</option><option value=0";
    if($allow_nominations==0)echo ' selected';
    echo ">No</option></select></td></tr>";
    echo "<tr><td align=right>Nominations:</td><td><select name=nominate_group_members_only style='width:260px;' title='Only applies if nominations are allowed. Forces nominations from pulldown list of participants only (no write-ins).'><option value=0>Members may \"write in\" nominations</option>";
    echo "<option value=1";
    if($nominate_group_members_only>0)echo ' selected';
    echo ">Only eligible voters may be nominated.</option>";
    echo "/<select></td></tr>";
   }
  else
   {
    echo '<tr><td colspan=2>The PRO version allows each ballot item to individually allow (or disallow) write-in candidates by participants. The admin panel allows "write-in" candidates however.</td></tr>';
   }
  echo '</table>';
  echo '<input type=hidden name=election_id value='.$election_id.'>';
  echo '<input type=hidden name=ballot_item_id value='.$ballot_item_id.'>';
  if($ballot_item_id>0)echo "<center><input type=submit name=act value='Update Ballot Item'> <input type=button onclick=\"window.location='".$http."://".$uri.'&act=edit&election_id='.$election_id."';\" value='Back to Ballot Items'></center>";
  else echo '<center><input type=submit name=act value="Add Ballot Item"></center>';
  echo '</form>';
  echo '</td>';
 }
// echo "act=".$act.'<hr>';
if($act=='')
 {
  echo '<td valign=top>';
  echo "<form method=post action=\"".$http."://".$uri."\" style='width:100%'><table><tr>";
  echo "<td style='text-align:right;'>Global Timezone:</td><td><Select name=tzvalue>";
  foreach($tzlist as $tzitem)
   {
    echo "<Option value='".$tzitem."'";
	if($tzvalue==$tzitem)echo ' selected ';
	echo '>'.$tzitem.'</option>';
   }
//  echo "<div align=right style='text-align:right;'>";
  echo '</select></td>';
  echo '<td rowspan=2 style="vertical-align:middle;"><input type=submit name=act value="Set!" style="margin-top:-10px;"><br><a target=new href="http://www.advcomsys.com/index.php/onevotedocs">Onevote Help</a></td></tr>';
  $prdisabled=" ";$prenabled=" ";if($pastresults==0)$prdisabled=' selected ';else $prenabled=' selected ';
  echo '<tr><td style="text-align:right;">Past Results:</td>'."<td><select onchange=\"alert('Don't forget to click Set!')\" name=pr><option value=0 ".$prdisabled.">Disabled</option><option value=1 ".$prenabled.">Enabled</option></select>";
  echo '</td></tr>';
  if($isreg>0 && $regkey!='')
   echo '<tr><td colspan=3 style="text-align:center;">Thanks for registering OneVotePRO!<input type=hidden name=regkey value="'.$regkey.'"></td>';
  else
   {
    echo '<td><div id=regdiv style="float:left;">Purchased OneVotePRO? <input type=button value="Finish Registration" onclick="setup_registration();"> or <a href="http://www.advcomsys.com/index.php/purchaseonevote" target=new>Purchase Upgrade</a></div></td>';
   }
  echo "</tr></table></form>";
  // if($db)echo 'db!';else echo 'nodb!';
  $query = $db->getQuery(true);
  $query->select('election_id,title,description,nominations_open,nominations_close,polls_open,polls_close,anonymous_nominations*1 as anonymous_nominations, anonymous_voting*1 as anonymous_voting, show_results*1 as show_results, show_results_min_votes,active*1 as active');
  $query->from('#__onevote_elections');
  $query->order($db->quoteName('polls_open'));
  $db->setQuery($query);
  $rows = $db->loadAssocList();
  $rownum=0;
// echo $query.'<br>';
  foreach ($rows as $row)
   {
//  print_r($row);
    if($rownum==0)
     {
      echo '<center><font size=6><B><U>Existing Elections</U></B></font>';
	  echo '<table border=1 bordercolor=black cellpadding=3 cellspacing=3 style="width:500px;font-face:Helvetica;font-size:7pt;">';
      echo '<tr style="background-color:black;color:white;"><td colspan=2></td><td colspan=2 align=center>Nominations</td><td colspan=2 align=center>Polls/Voting</td><td></td></tr>';
	  echo '<tr style="background-color:black;color:white;text-align:center;"><td>Task</td><td>Election Name</td><td>Open</td><td>Close</td><td>Open</td><td>Close</td><td>Active</td></tr>';
     }
    echo '<tr>';
    echo '<td nowrap style="width:85px;">'.'<a href="'.$http.'://'.$uri.'&act=edit&election_id='.$row['election_id'].'"><img src=../components/com_onevote/images/edit.gif title="Edit"></a> ';
	echo '<a href="'.$http.'://'.$uri.'&act=electiondelete&election_id='.$row['election_id']."\" onclick=\"javascript: return confirm('Are you sure you want to remove the entire election?');\" ><img src=../components/com_onevote/images/del.gif title=\"Delete\"></a> ";
//    echo "<a href='javascript:void(0);' onclick=\"window.open('../components/com_onevote/results.php?election_id=".$row['election_id']."','_blank','location=0,menubar=0,resizeable=1,scrollbars=yes,status=no,toolbar=no');\"><img src=../components/com_onevote/images/view.png title=\"Results\"></a></td>";
    echo "<a href='".$http."://".$uri."&act=results&election_id=".$row['election_id']."'><img src=../components/com_onevote/images/view.png title=\"Results\"></a> ";
    echo "<a href='javascript:void(0);' onclick=\"window.open('".$http."://".$uri."&act=report&election_id=".$row['election_id']."','','width=1000,height=700,location=no,menubar=yes,status=no,toolbar=yes,scrollbars=yes,titlebar=no')\"><img src=../components/com_onevote/images/report.png width=12 height=12 title=\"Report\"></a></td>";
    echo '<td style="width:150px;">'.$row['title'].'</td><td style="width:47px;">'.substr($row['nominations_open'],0,10).'</td><td style="width:47px;">'.substr($row['nominations_close'],0,10).'</td><td style="width:47px;">'.
	     substr($row['polls_open'],0,10).'</td><td style="width:47px;">'.substr($row['polls_close'],0,10).'</td>';
    echo '<td style="width:22px;">';if($row['active']>0)echo 'Yes';else echo 'No';
    echo '</td></tr>';
    $rownum+=1;
   }
  if($rownum)echo '</table></center>';
  else echo '<center><font size=4 color=red>No elections found.</font></center>';
  echo '</td>';
 }
?>
</tr></table><center>
<?php
 echo xfile_get_contents("http://www.advcomsys.com/joomla/jonevote/adhead.php?admin=1&act=foot&isreg=".$isreg);
?>
</center>
