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
 if (function_exists('curl_version') && function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec'))
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
   echo 'You have neither cUrl installed nor allow_url_fopen activated. Please setup one of those!';
  }
 return $content;
}


// import joomla controller library
jimport('joomla.application.component.controller');

// Setup Joomla Mailer
$mailer=JFactory::getMailer();
$config=JFactory::getConfig();
?>
<SCRIPT>
function selectbutton(btnid)
{
// alert(btnid);
 document.getElementById(btnid).checked=true;
// alert('done');
 return;
}
function setvalue(pulldown,radiobutton)
 {
//  alert('radiobutton');
  document.getElementById(radiobutton).value=-1*pulldown.value; // Normally button has nomination_id, but this will have user_id of nominee,
																// so we make it negative so we know to handle when put into database
//  alert(pulldown.value);
 }
function resetnomination(pulldown,radiobutton)
 {
  document.getElementById(pulldown).value=0;
  document.getElementById(radiobutton).value=0;
 }
function submitvote()
 {
  document.getElementById('dynamiccode').innerHTML="<input type=hidden name=act value='Vote!'>";
  alert(document.getElementById('dynamiccode').innerHTML);
  document.getElementById('voteform').submit();
 }
function checkvotes(btn,votes)
 {
//  alert(btn.name);
  var tvotes=0;
  var chkboxes=document.getElementsByName(btn.name);
  var checkboxes=chkboxes.length;
  for(i=0;i<checkboxes;i++)
   {
	if(chkboxes[i].checked)if(tvotes++==0)firstcheckbox=chkboxes[i];
   }
  if(tvotes>votes)
   {
	alert("You may choose "+votes+" candidates. You have attempted\nto select "+tvotes+" candidates.");
	firstcheckbox.checked=false;
   }
//  alert(votes+'/'+tvotes);
 }
 </SCRIPT>
<div style='text-align:center;margin-left:auto;margin-right:auto;width:100%;height:100%;display:inline-block;'><div style='border-color:red;border-style:groove;border-width:5px;text-align:center;position:relative;display:inline-block;background-color:white;font-color:black;'><table style='background-color:white;margin-left:auto;margin-right:auto;'><tr><td valign=top>
<?php
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

$tzvalue='America/New_York';$regkey='';	$pastresults=0;
$inifile=substr(__FILE__,0,strpos(__FILE__,'/components/')).'/administrator'.substr(__FILE__,strpos(__FILE__,'/components/'));
// echo '<br><br><br><br><br><br>'.$inifile.'<br>';
$inifile=substr($inifile,0,strrpos($inifile,'/'))."/onevote.ini";

$inifile='./administrator/components/com_onevote/onevote.ini';

// echo $inifile.'<br>';
$homedir=JURI::root().'components/com_onevote/';
// echo $homedir.'<br>';
if(file_exists($inifile))
if($fp=fopen($inifile,'r'))
 {
  while($inp=trim(fgets($fp)))
   {
//    echo $inp.'<hr>';
    $li=explode("=",$inp);
	if($li[0]=='tz')$tzvalue=$li[1];
	if($li[0]=='rk')$regkey=$li[1];
	if($li[0]=='pr')$pastresults=$li[1];
   }
  fclose($fp);
 }
// echo '&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;'.$regkey.'<hr>';
$isreg=xfile_get_contents("http://www.advcomsys.com/joomla/jonevote/adhead.php?act=checkreg&regkey=".$regkey);
// echo 'isreg='.$isreg;
date_default_timezone_set($tzvalue);
$workingtz=date_default_timezone_get();
// echo 'Timezone:'.$workingtz.'/'.$tzvalue.'/clock:'.time().'/localtime:'.strtotime(date('Y-m-d H:i:s')).'/'.date('Y-m-d H:i:s').'<hr>';
$localtime=strtotime(date('Y-m-d H:i:s'));

echo xfile_get_contents("http://www.advcomsys.com/joomla/jonevote/adhead.php?admin=0&isreg=".$isreg);
$yesno=array('No','Yes');
$votetype=array('Office','Question');
$uri=$_SERVER['SERVER_NAME'];
if(strpos($_SERVER['REQUEST_URI'],'&act')===false)$uri.=$_SERVER['REQUEST_URI'];
else $uri.=substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&act'));
$db = JFactory::getDbo();
$user = JFactory::getUser();
$jinput = JFactory::getApplication()->input;
$jsession = JFactory::getSession();
// print_r($jsession);
// print_r($jinput);


$nominations_open=0;$polls_open=0;

$act=$jinput->get('act','','');
$election_id=$jinput->get('election_id',0,'');
// if($election_id==0)if(!empty($_GET['election_id']))$election_id=$_GET['election_id'];
// echo 'act='.$act.'/election_id='.$election_id.'<hr>';

$description=$jinput->get('description','','');
$nominations_open=$jinput->get('nominations_open',null,'');
$nominations_close=$jinput->get('nominations_close',null,'');
$polls_open=$jinput->get('polls_open',null,'');
$polls_close=$jinput->get('polls_close',null,'');
$anonymous_nominations=$jinput->get('anonymous_nominations',0,'');
$anonymous_voting=$jinput->get('anonymous_voting',1,'');
$show_results=$jinput->get('show_results',0,'');
$show_results_min_votes=$jinput->get('show_results_min_votes',10,'');
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

$first_name=$jinput->get('first_name','','');
$last_name=$jinput->get('last_name','','');
$nominee_id=$jinput->get('nominee_id',0,'');
$nomination_id=$jinput->get('nomination_id',0,'');

$ip = $_SERVER['REMOTE_ADDR'];
$publicvote=0;

$skip_vote=0; // We'll set this if something in nominating went awry and they were nominating and voting.


if($election_id>0)
 {
  $nswap = array();
  $query=$db->getQuery(true);
  $query->select('election_id,title,description,polls_open,polls_close,nominations_open,nominations_close,anonymous_nominations*1 as anonymous_nominations,one_nomination*1 as one_nomination,notify_nominee*1 as notify_nominee,anonymous_voting*1 as anonymous_voting,show_results*1 as show_results,show_results_min_votes,show_total_votes_cast*1 as show_total_votes_cast,email_nominations_to,email_votes_to,active*1 as active');
  $query->from('#__onevote_elections');
  $query->where("election_id=".$election_id);
  $db->setQuery($query);
  $erow = $db->loadAssoc();
//  print_r($erow);

   // Make sure the person can play in the election
  $query=$db->getQuery(true);
  $query->select('group_id');
  $query->from('#__onevote_groups');
  $query->where("election_id=".$election_id);
  $db->setQuery($query);
  $grows = $db->loadAssocList();
   // Here we check to see if public vote
  foreach($grows as $grow)if($grow['group_id']==1)
   {
    $publicvote=1; // Assuming group ID = 1 is "Public"
//	echo $grow['group_id'].'/'.$publicvote.'<br>';
   }
 }

$msg="";
// echo 'act='.$act.'<hr>';
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
  $act='';
//  echo 'election_id='.$election_id.'<br>';
 }
// echo '1>act='.$act.'...';
if($act=='Vote!' || $act=='Nominate')
 {
  if($jsession->get('vcounter')=='')$jsession->set('vcounter',0);
  if($user->id==0 && $jsession->get('vcounter')>2)echo "<div style='font-face:Georgia;font-size:20px;color:red;margin:auto auto auto auto;float:middle;'>Sorry, we limit anonymous votes, you'll need to come back later to vote again. Thanks for participating.</div>";
  else
   {
    if($user->id==0)$jsession->set('vcounter',$jsession->get('vcounter')+1);
//  echo '<pre>' . print_r(get_defined_vars(), true) . '</pre>';

    $voteslist=explode(",",$jinput->get('voteslist','',''));
    $nominatelist=explode(",",$jinput->get('nominatelist','',''));
//  echo 'voteslist:';print_r($voteslist);echo '<br>';
//  echo 'nominatelist:';print_r($nominatelist);echo '<br>';
//  echo 'Nominations<br>';
	if($user->username==''){$user->name='Someone';$user->username=$user->name;}
    $msgtext="";
    foreach($nominatelist as $nomination)
     {
      $bid=substr($nomination,1); // Get the number portion of name, it will be the ballot_item_id;
	  $nid=$jinput->get($nomination,0,''); // This will be the id of the nominee_id
//	  echo 'ballot_item_id='.$bid.' and nominee_id='.$nid.'<br>';
      if($bid>0)
	   {
	    $nominate=1; // Default to doing the nomination, but setting to zero means something went wrong
	    $subquery='';
	    $last_name=$jinput->get('b'.$bid.'_last_name','','');
	    $first_name=$jinput->get('b'.$bid.'_first_name','','');
	    $writeinanswer=$jinput->get('b'.$bid.'_writeinanswer','','');
	    if($writeinanswer!='')
	     {
		  $first_name=substr($writeinanswer,0,24);
		  $last_name=substr($writeinanswer,24,24);
	     }
	    if($nid==0)
	     {
	      $subquery=" and (last_name='".$last_name."' and first_name='".$first_name."')";
	     }
		else $subquery='';
  	    if($nid>0 || $last_name!='' || $first_name!='') // Make sure they wrote something in or selected from pulldown
	     {
// Here we're checking to see if the person is already nominated for the specific office
	       $query=$db->getQuery(true);
           $query->select('count(*) as qty');
           $query->from('#__onevote_nominations');
           $query->where("ballot_item_id=".$bid." and (nominee_id=".$nid.$subquery.") and election_id=".$election_id);
           $db->setQuery($query);
           $row = $db->loadAssoc();
	       if($row['qty']!=0) // Found nomination for the person for the office
	        {
		     $msg.="Nominee has already been nominated for the office, so skipped.<br>";$skip_vote++;$nominate=0;$skip_vote++;
//			 echo $query.'<br>';
		    }
           else if($erow['one_nomination']!=0)
		    {
		     $query=$db->getQuery(true);
             $query->select('count(*) as qty');
             $query->from('#__onevote_nominations');
             $query->where("(nominee_id=".$nid.$subquery.") and election_id=".$election_id);
             $db->setQuery($query);
             $row = $db->loadAssoc();
	         if($row['qty']!=0) // Found a nomination for the election for this person and only one nomination allowed
	          {
	           $msg.="A person you nominated is already in nomination for another office, so nothing done.<br>";$skip_vote++;$nominate=0;
              }
			}
	     }
		else $nominate=0;
	    if($nominate>0)
	     {
          if($erow['anonymous_nominations']==0)$nominated_by=$user->id;
   	      else $nominated_by=0;
          $query=$db->getQuery(true);
          $columns=array('election_id','ballot_item_id','nominee_id','first_name','last_name','nominated_by','nominee_email','ip');
          $values=array($election_id,$bid,$nid,$db->quote($first_name),$db->quote($last_name),$nominated_by,$db->quote($nominee_email),$db->quote($ip));
          $query
           ->insert($db->quoteName('#__onevote_nominations'))
           ->columns($db->quoteName($columns))
           ->values(implode(',', $values));
          $db->setQuery($query);
// echo $query.'<br>';
          $db->query();
		// Check to see if there is a vote that has the negative value of the nomminee_id and change it to match the assigned nomination_id
		// for the record just created.
          $query=$db->getQuery(true);

          $query->select('nomination_id,nominee_id,ballot_title,name,username,first_name,last_name,email');
          $query->from($db->quoteName('#__onevote_nominations','n'));
          $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('n.nominee_id') . ')');
          $query->join('LEFT', $db->quoteName('#__onevote_ballot_items', 'b') . ' ON (' . $db->quoteName('b.ballot_item_id') . ' = ' . $db->quoteName('n.ballot_item_id') . ')');
          $query->where("n.ballot_item_id=".$bid." and n.nominee_id=".$nid." and n.election_id=".$election_id.$subquery);
// echo 'get nomination id query: '.$query.'<br>';
          $db->setQuery($query);
          $row = $db->loadAssoc();
          $nomination_id=$row['nomination_id'];
//		echo 'nswap[n'.$nid.']='.$nomination_id.'<br>';
          $nswap['n'.$nid]=$nomination_id; // this is to convert the passed nomination/vote to nomination_id.
	      if($row['nominee_id']==0)$row['name']=$row['first_name'].' '.$row['last_name'];
          $msgtext.=$row['ballot_title'].','.$row['name'].','.$row['username']."\r\n";

	      if($erow['notify_nominee']>0)
           {
			$mailer=JFactory::getMailer();
		    $nmsgtext=$user->name.' nominated you for '.$row['ballot_title'].' in '.$erow['title']." at http://".$_SERVER['SERVER_NAME'].".\n";
	        $headers = 'From: '.$user->email . "\r\n" .
                    'Reply-To: '.$user->email . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
//          echo 'user email of nominee='.$row['email'].'<br>';;
		    if($row['email']!='')$nominee_email=$row['email'];
//		    echo 'nominee_email='.$nominee_email.' user->email='.$user->email.'<br>';
//		    echo 'Sending nomination notification to '.$nominee_email;
//			$sender=array($user->email,$user->name);
			$sender = array($config->get( 'mailfrom' ),$config->get( 'fromname' ) );
			$mailer->setSender($sender);
			$mailer->addRecipient($nominee_email);
			$mailer->setSubject('You were nominated for '.$row['ballot_title'].'.');
			$mailer->setBody($nmsgtext);
			$send=$mailer->Send();
			if($send !== true)echo '<center>Error Sending Mail!</center>';
//	        mail($nominee_email,'You were nominated for '.$row['ballot_title'],$nmsgtext,$headers);
		   }
		  else
		   {
		//  echo 'no notify nominee<br>';
		   }
		 }
	   }
	 }
	   // $msgtext contains a summary of all nominations to send to the administrator
   if($msgtext!='') // If we had something generated
    {
	 $mailer=JFactory::getMailer();
     if($erow['anonymous_nominations']==0)$xmsgtext=$user->name.' ('.$user->username.')';
	 else $xmsgtext="A participant ";
	 $msgtext=$xmsgtext." made nomination(s)".' in '.$erow['title'].":\r\n".$msgtext;
     if($erow['email_nominations_to']!='')
      {
       $headers = 'From: '.$erow['email_nominations_to'] . "\r\n" .
                  'Reply-To: '.$erow['email_nominations_to'] . "\r\n" .
                 'X-Mailer: PHP/' . phpversion();
//		    echo 'Sending nomination notification to '.$erow['email_nominations_to'];
	   $sender = array($config->get( 'mailfrom' ),$config->get( 'fromname' ) );
	   $mailer->setSender($sender);
	   $mailer->addRecipient($erow['email_nominations_to']);
	   $mailer->setSubject($erow['title'].' Nomination(s) Made.');
	   $mailer->setBody($msgtext);
	   $send=$mailer->Send();
	   if($send !== true)echo '<center>Error Sending Mail!</center>';
//	     mail($erow['email_nominations_to'],$erow['title'].' Nomination(s) Made.',$msgtext,$headers);
     }
   }


// echo '2>act='.$act.'...';
// print_r($nswap);
    if($act=="Vote!")
     {
//    echo 'nswap:';print_r($nswap);
      if($skip_vote!=0)
	   {
	    $msg.='Because of a nomination issue that may have affected your vote, it was not submitted.<br>Please try again.<br>';
	   }
	  else
	   {
        $uid=0;
        $query=$db->getQuery(true);
        $query->select('count(*) as qty');
        $query->from('#__onevote_votes');
        $query->where("election_id=".$election_id." and user_id=".$user->id);
        $db->setQuery($query);
        $row = $db->loadAssoc();
// echo '3>row[qty]='.$row['qty'].'...';
        if($row['qty']==0 || $publicvote>0)
         {
// echo 'writing voted record ...';
          $query=$db->getQuery(true);
          $columns=array('election_id','user_id','ballot_item_id','nomination_id','ip');
          $values=array($election_id,$user->id,0,0,$db->quote($ip)); // Write an empty record, but put userid in there so we have way to know they already voted.
          $query
          ->insert($db->quoteName('#__onevote_votes'))
          ->columns($db->quoteName($columns))
          ->values(implode(',', $values));
          $db->setQuery($query);
          $db->query();
// echo 'writing actual vote records ... ';
	      $msgtext='';
  		  if(isset($voteslist))
          foreach($voteslist as $vote)
           {
            $bid=substr($vote,1); // Get the number portion of name, it will be the ballot_item_id;
			$nid=$jinput->get($vote,0,'');
			if(!is_array($nid))$nids=array($nid);
			else $nids=$nid;
			foreach($nids as $nid)
			 {
	          $nid*=1; // This will be the id of the nomination_id, forcing non-numeric to 0.
// echo "bid=".$bid."/nid=".$nid."/";
		      if($nid<0)
		       {
//		        echo "<br>".'$nid='.$nid.'/nswap[n'.(-1*$nid).']='.$nswap['n'.(-1*$nid)].'<br>';
		        if(!empty($nswap['n'.(-1*$nid)]))$nid=$nswap['n'.(-1*$nid)];
		       }
//         echo 'bid='.$bid.'/nid='.$nid.'/b_'.$bid.'_last_name='.$jinput->get('b'.$bid.'_last_name',0,'').'/b_'.$bid.'_first_name='.$jinput->get('b'.$bid.'_first_name',0,'');
              if($nid==0 && $first_name!='')
		       {
                $query=$db->getQuery(true);
                $query->select('nomination_id');
                $query->from($db->quoteName('#__onevote_nominations','n'));
                $query->where("n.ballot_item_id=".$bid." and n.election_id=".$election_id." and n.last_name='".$jinput->get('b'.$bid.'_last_name','','')."' and n.first_name='".$jinput->get('b'.$bid.'_first_name','','')."'");
                $db->setQuery($query);
                $xrow = $db->loadAssoc();
                $nid=$xrow['nomination_id'];
               }
// echo 'get nomination id query: '.$query.'<br>';
// print_r($xrow);
		      if($nid>0)
               {
		        $query=$db->getQuery(true);
                $columns=array('election_id','user_id','ballot_item_id','nomination_id','ip');
                $values=array($election_id,$uid,$bid,$nid,$db->quote($ip));
                $query
                ->insert($db->quoteName('#__onevote_votes'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                $db->setQuery($query);
//		echo $query.'<hr>';
                $db->query();

                $query=$db->getQuery(true);
                $query->select('nominee_id,ballot_title,name,username,first_name,last_name');
                $query->from($db->quoteName('#__onevote_nominations','n'));
                $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('n.nominee_id') . ')');
                $query->join('LEFT', $db->quoteName('#__onevote_ballot_items', 'b') . ' ON (' . $db->quoteName('b.ballot_item_id') . ' = ' . $db->quoteName('n.ballot_item_id') . ')');
                $query->where("n.nomination_id=".$nid);
                $db->setQuery($query);
                $row = $db->loadAssoc();
		        if($row['nominee_id']==0)$row['name']=$row['first_name'].' '.$row['last_name'];
  //            echo $row['ballot_title'].','.$row['name'].','.$row['username'].'<br>';
	  	        $msgtext.=$row['ballot_title'].','.$row['name'].','.$row['username']."\n";
		       }
		     }
           }
		  $msg.="Your vote(s) have been submitted!  Thank you for taking the time to participate!<br>";
		  if($erow['anonymous_voting']==0)$msgtext=$user->name.' ('.$user->username.") voted."."\r\n".$msgtext;
		  if($erow['email_votes_to']!='')
		   {
	        $headers = 'From: '.$erow['email_votes_to'] . "\r\n" .
                     'Reply-To: '.$erow['email_votes_to'] . "\r\n" .
                     'X-Mailer: PHP/' . phpversion();
// 		    echo 'Sending Vote Email to '.$erow['email_votes_to'];
			$sender = array($config->get( 'mailfrom' ),$config->get( 'fromname' ) );
			$mailer->setSender($sender);
			$mailer->addRecipient($erow['email_votes_to']);
			$mailer->setSubject($erow['title'].' Vote Cast.');
			$mailer->setBody($msgtext);
			$send=$mailer->Send();
			if($send !== true)echo '<center>Error Sending Mail!</center>';
//		    echo 'Send Complete.';
//		    mail($erow['email_votes_to'],$erow['title'].' Vote Cast.',$msgtext,$headers);
		   }
         }
        else
	     {
          $msg.="You have already voted. This ballot submission was not processed.<br>";$skip_vote++;
	     }
	   }
	 }
   }
  $act='';
 }
if($msg!="")echo "<div style='text-align:center;font-face:Arial;font-size:12pt;color:red;border:2px;borderColor:#0000ff'>".$msg."</div>";
if($groupids=='')$groupids = array();
// print_r($groupids);
if($election_id==0)
 {
  $curtim=date('Y-m-d H:i:n');
  $query=$db->getQuery(true);
  $query->select('election_id,title,description,polls_open,polls_close,nominations_open,nominations_close,anonymous_nominations*1 as anonymous_nominations,anonymous_voting*1 as anonymous_voting,show_results*1 as show_results,show_results_min_votes,email_nominations_to,email_votes_to,active*1 as active');
  $query->from('#__onevote_elections');
  $query->where("active=1 and ((polls_open<'".$curtim."' and polls_close>'".$curtim."') or (nominations_open<'".$curtim."' and nominations_close>'".$curtim."')) and ".
                'exists (select * from #__onevote_groups where election_id=#__onevote_elections.election_id'.
				' and (('.$user->id.'=0 and group_id=1) or exists (select * from #__user_usergroup_map where #__user_usergroup_map.group_id=#__onevote_groups.group_id and #__user_usergroup_map.user_id='.$user->id.')))');
//  echo 'Query:'.$query.'<br>';
  $db->setQuery($query);
  $rows = $db->loadAssocList();

//  print 'DEBUG: election_id='.$election_id.'... row count='.count($rows).' ... ';print_r($rows);exit;
  if(count($rows)==0)
   {
    echo '<center><div style="font-face:Georgia;font-size:18pt;color:red;">There are no ongoing elections available to you at this time.';
    echo "<center><a href='javascript:void(0);' onclick='window.location.href=\"".JURI::root()."/index.php?option=com_users&view=login\"'>Are you logged in?</a></center>";
    echo '<div></center>';
   }
  else if(count($rows)==1)
   {
	$runstr='location: '.$http.'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	if(strpos($runstr,"?")===false)$runstr.='?';else $runstr.="&";
	$runstr.='election_id='.$rows[0]['election_id'];
//	echo $runstr.'<br>';
    header($runstr);
    exit;
   }
  else
   {
    echo '<center><div style="font-face:helvetica;font-size:14pt;color:black;text-align:center;">'.count($rows).' Elections are available for your participation.<br>'.'</div>';
// If more than one election a list should be provided.
    echo "\n".'<table align=center style="width:100%;background-color:black;border-spacing:3px;">';
	echo "\n".'<tr style="text-align:center;background-color:black;color:white;font-face:helvetica;size:12pt;">'.
	     '<td style="text-align:center;"></td><td style="text-align:center;"></td><td colspan=2 style="text-align:center;">Nominations</td><td colspan=2 style="text-align:center;">Voting</td><td style="text-align:center;"></td></tr>'.
		 "\n".'<tr style="text-align:center;background-color:black;color:white;font-face:helvetica;size:12pt;">'.
	     '<td style="text-align:center;">Election</td><td style="text-align:center;">Description</td><td style="text-align:center;">Open</td><td style="text-align:center;">Close</td><td style="text-align:center;">Open</td><td style="text-align:center;">Close</td><td style="text-align:center;">Vote Options</td></tr>';

    foreach($rows as $row)
     {
      $query=$db->getQuery(true);
      $query->select('count(*) as qty');
      $query->from('#__onevote_votes');
      $query->where("election_id=".$row['election_id']." and user_id=".$user->id);
      $db->setQuery($query);
      $vrow = $db->loadAssoc();
	  echo "\n".'<tr style="background-color:white;color:blank;"><td>'.$row['title'].'</td><td>'.substr($row['description'],0,23).'...</td><td>'.substr($row['nominations_open'],0,10).'</td><td>'.substr($row['nominations_close'],0,10).'</td>'.
	           '<td>'.substr($row['polls_open'],0,10).'</td><td>'.substr($row['polls_close'],0,10).'</td><td>';
	  if($vrow['qty']==0 || $publicvote>0)echo "<a href=\"".$http."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'?election_id='.$row['election_id']."\">Vote</a>";
	  else echo 'Already Voted';
	  echo '</td></tr>';
//      print_r($row);
//      echo '<hr>';
     }
	echo '</table>';
   }
 }
else
 {
//  echo 'Election ID='.$election_id;exit;
   // Make sure the person can play in the election - Commented below code out because $grows is populated at beginning during publicvote detection
/*  $query=$db->getQuery(true);
  $query->select('group_id');
  $query->from('#__onevote_groups');
  $query->where("election_id=".$election_id);
  $db->setQuery($query);
  $grows = $db->loadAssocList(); */
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
    echo "<center>You are not authorized to participate in this election. <a href='javascript:void(0);' onclick='window.location.href=\"".JURI::root()."/index.php?option=com_users&view=login\"'>Are you logged in?</a></center>";
   }
  else
   {
//    echo 'jElection '.$election_id.' ... ';
/*
    $query=$db->getQuery(true);
    $query->select('election_id,title,description,polls_open,polls_close,nominations_open,nominations_close,anonymous_nominations,anonymous_voting,show_results,show_results_min_votes,email_nominations_to,email_votes_to,active');
    $query->from('#__onevote_elections');
    $query->where("active=1 and (polls_open<NOW() and polls_close>NOW()) or (nominations_open<NOW() and nominations_close>NOW()) and election_id=".$election_id);
    $db->setQuery($query);
    $erow = $db->loadAssoc();
*/
    echo "<div style='border-style:none;text-align:center;margin:0 auto;font-face:arial;font-size:18pt;'>".$erow['title']."</div>";
    echo "<div style='border-style:none;text-align:center;margin:0 auto;font-face:arial;font-size:10pt;margin-top:0px;'>".$erow['description']."</div>";
    echo "<div style='border-style:none;text-align:center;margin:0 auto;font-face:arial;font-size:10pt;color:red;'>";
    date_default_timezone_set($tzvalue);
//	echo 'workingtz='.$workingtz.'/'.$erow['nominations_open']."/".$erow['nominations_close']."/open:".strtotime($erow['nominations_open'])."/close:".strtotime($erow['nominations_close'])."/time:".$localtime."<hr>";
    if($erow['nominations_close']=='' || substr($erow['nominations_close'],0,10)=='0000-00-00');
    else if(strtotime($erow['nominations_open'])<$localtime && strtotime($erow['nominations_close'])>$localtime){echo 'Nominations Are Open Until '.$erow['nominations_close'].'';$nominations_open=1;}
    else if(strtotime($erow['nominations_open'])>$localtime && strtotime($erow['nominations_close'])>strtotime($erow['nominations_open']))echo "Nominations will be open from ".$erow['nominations_open']." until ".$erow['nominations_close']."";
	echo '</div>';
    echo "<div style='text-align:center;margin:0 auto;font-face:arial;font-size:10pt;color:red;'>";
    if($erow['polls_close']=='' || substr($erow['polls_close'],0,10)=='0000-00-00');
    else if(strtotime($erow['polls_open'])<$localtime && strtotime($erow['polls_close'])>$localtime){echo ' Voting is open until '.$erow['polls_close'].'';$polls_open=1;}
    else if(strtotime($erow['polls_open'])>$localtime && strtotime($erow['polls_close'])>strtotime($erow['polls_open']))echo "Voting will be open from ".$erow['polls_open']." until ".$erow['polls_close']."";
    echo "</div>";
//  echo '<br>'.time().'/'.strtotime($erow['polls_open']).'/'.strtotime($erow['polls_close']);
    if($act=='') // No task, so we just display the ballot
     {
      echo "<div style='width:550px;margin: auto auto;'><form id='voteform' style='width:500px;' action=\"".$http."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."\" method=POST>";
      $query=$db->getQuery(true);
      $query->select('position,election_id,ballot_item_id,ballot_title,ballot_description,is_ballot_question*1 as is_ballot_question,allow_nominations*1 as allow_nominations,nominate_group_members_only*1 as nominate_group_members_only,votes');
      $query->from('#__onevote_ballot_items');
  	  $query->order('position,ballot_item_id');
      $query->where("election_id=".$election_id);
      $db->setQuery($query);
      $brows = $db->loadAssocList();
  	  $rownum=0;
	  echo '<table align=center style="width:550px;">';
	  $voteslist="";$c="";
	  $nominatelist="";
      foreach($brows as $brow)
	   {
	    $votename="v".$brow['ballot_item_id'];$voteslist.=$c.$votename;$c=",";
	    $nominatename="n".$brow['ballot_item_id'];$nominatelist.=$c.$nominatename;$c=",";
	    echo "\n<tr style='background-color:black;color:white;'><td colspan=3 style='font-size:16pt;text-align:center;'>".$brow['ballot_title'];
		if($brow['votes']>1)echo "<div style='font-face:helvetica;color:yellow;font-size:9pt;text-align:center;'>Choose ".$brow['votes']."</div>";
	    if($brow['ballot_description']!='')echo "<div style='font-face:helvetica;color:yellow;font-size:9pt;text-align:center;'>".$brow['ballot_description'].'</div>';
	    echo "</td></tr>";
	    echo "\n<tr style='line-height:12pt;margin:0 0 0 0;spacing:0 0 0 0;padding:0 0 0 0;background-color:black;color:white;font-size:12pt;'><td style='text-align:center;width:35px;'><input type=radio value=0 name=".$votename." selected style='height:0;width:0;display:none;'></td>";
	    if($brow['is_ballot_question'])echo "<td style='text-align:center;width:464px;' colspan=2>Question Response</td>";
	    else echo "<td style='text-align:center;width:232px;'>Candidate Name</td><td style='width:232px;'>Username</td>";
	    echo "</tr>";
        $query=$db->getQuery(true);
        $query->select('nomination_id,nominee_id,first_name,last_name,nominated_by');
        $query->from('#__onevote_nominations');
        $query->where("ballot_item_id=".$brow['ballot_item_id']);
        $db->setQuery($query);
        $nrows = $db->loadAssocList();
        foreach($nrows as $nrow)
	     {
		  if($nrow['nominee_id']>0)
		   {
		    $query=$db->getQuery(true);
		    $query->select('username,name');
		    $query->from('#__users');
		    $query->where("id=".$nrow['nominee_id']);
		    $db->setQuery($query);
		    $urow = $db->loadAssoc();
			$nrow['first_name']=$urow['name'];
		   }
	    echo "\n<tr onclick=\"document.getElementById('".$votename.'_'.$nrow['nomination_id']."').click();\" style='line-height:12pt;padding:0 0 0 0;spacing:0 0 0 0;margin:0 0 0 0;background-color:white;color:black;font-size:12pt;'><td nowrap style='White-space:nowrap;margin-left:0;margin-right:0;'>";
		  if($polls_open)
		   {
			if($brow['votes']==1)
			 echo "<input type=radio id='".$votename.'_'.$nrow['nomination_id']."' name='".$votename."' value=".$nrow['nomination_id']." title='".addslashes($nrow['first_name']).' '.addslashes($nrow['last_name'])." for ".addslashes($brow['ballot_title'])."'>";
		    else
			 echo "<input type=checkbox id='".$votename.'_'.$nrow['nomination_id']."' name='".$votename."[]' value=".$nrow['nomination_id']." onclick=\"checkvotes(this,".$brow['votes'].");\" title='".addslashes($nrow['first_name']).' '.addslashes($nrow['last_name'])." for ".addslashes($brow['ballot_title'])."'>";
		   }
		  if($user->id!=$nrow['nominee_id'])echo "";
		  else if($user->id==$nrow['nominee_id'] && $publicvote==0 && $nominations_open!=0 && $polls_open==0)
           {
			$deluri=$http.'://'.$uri.'&amp;act=delnominee&amp;ballot_item_id='.$ballot_item_id.'&amp;election_id='.$election_id.'&amp;nomination_id='.$nrow['nomination_id'];
            echo "[<img src='".$homedir."images/del.gif' style='width:20px;height:20px;' title='Remove yourself from nomination.' onclick=\"if(confirm('Are you sure you want to remove yourself from nomination?'))window.location='".$deluri.";'\">]";

			  //            echo "<a href=\"javascript:void(0);\" onclick=\"window.open('".$http."://".$uri."&act=nomineeinfo&ballot_item_id=".$ballot_item_id."&election_id=".$election_id."&nomination_id=".$nrow['nomination_id']."','_blank','channelmode=0,fullscreen=0,location=0,menubar=0,resizable=0,scrollbars=0,status=1,titlebar=0,toolbar=0')\"><img src=".$homedir."images/edit.gif title=\"Edit your photo and campaign message.\"></a>";
           }
		  echo "</td>";
          if($nrow['nominee_id']>0)
           {
            echo '<td>'.$urow['name'].'</td><td>'.$urow['username'].'</td></tr>';
           }
	      else
	       {
		    if($brow['is_ballot_question'])echo '<td colspan=2>'.$nrow['first_name'].$nrow['last_name'].'</td></tr>';
		    else echo '<td>'.$nrow['first_name'].' '.$nrow['last_name'].'</td><td align=center>(write-in)</td></tr>';
	       }
//		  echo '<td>';print_r($brow);echo '</td>';
	//	  echo "</td></tr>";
         }
	    if($nominations_open!=0 && $brow['allow_nominations']!=0)
         {
		  if($brow['is_ballot_question']==0)
		   {
	        echo "\n<tr><td>";
			if($polls_open)echo "<input name='".$votename."' id='".$nominatename."_nominate'"." type=radio>";
			echo "</td><td colspan=2 nowrap>";
//	  	    echo "<form action=\"".$http."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."\" method=POST>";
		    echo "<Select name=".$nominatename." style=\"margin-top:2px;margin-bottom:0px;width:350px;\" onchange=\"setvalue(this,'".$nominatename."_nominate');\" onfocus=\"selectbutton('".$nominatename."_nominate')\" title=\"Nominate from this list of valid participants.\"><Option value=0 style=\"width:200px;\"></Option>";
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
	        echo '</Select> (optional) Select a nominee.';
//		    echo '</form></div>';
            echo '</td></tr>'; /* Formatting Fix Attempt */
           }
		  if($brow['nominate_group_members_only']==0) // So write-ins are allowed
		   {
			echo "\n".'<tr><td colspan=3>'; /* formatting fix attempt */
			if($brow['is_ballot_question']==1)
			 {
		      echo '<div style="text-align:left;color:red;">or write-in answer:';
		      echo '<input name=b'.$brow['ballot_item_id'].'_writeinanswer style="width:225px;font-size:14pt;" maxlength=40><input type=hidden name=b'.$brow['ballot_item_id'].'_last_name value=""><input type=hidden name=b'.$brow['ballot_item_id'].'_first_name value=""> if not in above list.</div>';
			 }
			else
			 {
		      echo '<div style="text-align:center;color:red;">or Write-in a candidate if not in the above list.</div>';
		      echo '<div>First Name:<input name=b'.$brow['ballot_item_id'].'_first_name style="width:100px;" maxlength=24> &nbsp; Last Name:<input name=b'.$brow['ballot_item_id'].'_last_name style="width:100px;" maxlength=24></div>';
			 }
			echo '</td></tr>'; /* formatting fix attempt */
		   }
		 //Removed as part of formatting fix attempt echo "</td></tr>";
		 }
	    echo "\n".'<tr height=2><td colspan=3><hr style="margin-top:0px;margin-bottom:1px;"></tr>';
	    $rownum+=1;
       }
	  echo '</table><input type=hidden name=voteslist value="'.$voteslist.'"><input type=hidden name=nominatelist value="'.$nominatelist.'">';

      $query=$db->getQuery(true);
      $query->select('count(*) as qty');
      $query->from('#__onevote_votes');
      $query->where("election_id=".$election_id." and user_id=".$user->id);
      $db->setQuery($query);
      $row = $db->loadAssoc();
	  if($row['qty']!=0)$already_voted=1;
	  else $already_voted=0;
// echo '3>row[qty]='.$row['qty'].'...';
	  echo '<div style="text-align:center;font-face:arial;font-size:12px;color:red;">';
      if($already_voted==0 || $publicvote!=0)
       {
	    if($polls_open>0 && $nominations_open>0)echo 'Clicking Vote will post your votes and any nominations together.';
		else if($polls_open)echo 'Clicking Vote will cast your ballot.';
		echo '<br>';
	   }
      else
       {
        echo 'You have already voted.<br>';
	   }
      if($nominations_open>0)echo ' Clicking nominate will post your nominations only';
	  if($already_voted==0 && $nominations_open>0)echo "; you may vote later";
	  echo '.<br>';
	  echo '</div>';
	  echo '<div style="text-align:center;font-face:ArialNarrow;font-size:8pt;color:red;text-align:center;">';
      if($already_voted==0 || $publicvote>0)
	   {
	    if($polls_open>0)
		 {
          if($erow['email_nominations_to']!='')
		   {
		    echo "Nominations will be sent to ".$erow['email_nominations_to'].".";
			if($erow['anonymous_nominations'])echo " However, the nominator remains anonymous.";
			else echo ' Voter identity is included.';
			echo '<br>';
		   }
          if($erow['email_votes_to']!='')
		   {
		    echo "Votes will be sent to ".$erow['email_votes_to'].".";
			if($erow['anonymous_voting'])echo " However, the voter remains anonymous.";
			else echo ' Voter identity is included.';
			echo '<br>';
		   }
//		  echo " <input type=button name=act value='Vote!' style='background-color:black;color:white;font-face:georgia;font-size:20px;' onclick=\"if(confirm('Are you sure you want to cast your vote?')){submitvote();}\">";
		  echo "<input type=submit name=act value='Vote!' style='background-color:#707070;color:blue;font-face:georgia;font-size:20px;'>";
		 }
	   }
	  else echo '<input type=button value="Already Voted"  style="background-color:#707070;color:blue;">';
      if($nominations_open>0)echo ' <input type=submit name=act value="Nominate" style="background-color:#707070;color:blue;font-face:georgia;font-size:20px;">';
	  echo '</div>';
	  echo '<div id=dynamiccode></div></form>';

	  $query=$db->getQuery(true);
	  $query->select('count(*) as qty');
      $query->from('#__onevote_votes');
      $query->where("ballot_item_id=0 and nomination_id=0 and election_id=".$election_id);
      $db->setQuery($query);
      $row = $db->loadAssoc();
      $totalvotes=$row['qty'];

	  echo '<center>';
	  if($erow['show_total_votes_cast']>0)
       {
        echo '<div style="font-face:helvetica;font-size:10pt;color:blue;">Total votes cast so far:'.$totalvotes.'</div>';
	   }
      if($polls_open)
	   if($erow['show_results'] && $totalvotes>=$erow['show_results_min_votes'])echo "<a href='javascript:void(0);' onclick=\"window.open('./components/com_onevote/results.php?election_id=".$election_id."','_blank','height=800,width=600,location=0,menubar=0,resizeable=1,scrollbars=yes,status=no,toolbar=no');\">View Running Results</a></td>";
	  echo '</center>';
     }
   }
 }
?>
</td></tr></table>
<?php
if($pastresults==1)echo "<a href='javascript:void(0);' onclick=\"window.open('./components/com_onevote/pastresults.php','_blank','height=800,width=600,location=0,menubar=0,resizeable=1,scrollbars=yes,status=no,toolbar=no');\">View Past Results</a></td>";
echo xfile_get_contents("http://www.advcomsys.com/joomla/jonevote/adhead.php?admin=0&act=foot&isreg=".$isreg);
?>
</div>
<div id=nomineeinfo style='width:0px;height:0px;border=0;background-color:#989898;margin-left:0;margin-right:auto;;position:absolute;display:none;'>CONTENT!!!</div>
</div>
<?
