<?php
/*
    Copyright (C) 2004-2022 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @package Base
 */


require_once('header.php');
require_once('objects/group.php');
require_once('objects/groupUser.php');
require_once('lib/message.php');
require_once('objects/notice.php');

//createGroup=on&type=Family&joinSelf=on

$groupID = -1;
if( $User->type['User']) 
{
	if( isset($_REQUEST['gameID']) && isset($_REQUEST['explanation']) && isset($_REQUEST['userWeighting']) )
	{
		$gameID = (int)$_REQUEST['gameID'];
		// We are submitting a cheating accusation from a game
		$Variant = libVariant::loadFromGameID($gameID);
		$Game = $Variant->Game($gameID);
		libAuth::formToken_Valid();
		$suspectedCountries = array();
		$suspectingCountryID = null;
		foreach($Game->Members->ByCountryID as $countryID=>$Member)
		{
			if( isset($_REQUEST['countryIsSuspected'.$countryID]) && $_REQUEST['countryIsSuspected'.$countryID] )
				$suspectedCountries[] = $countryID;

			if( $Member->userID == $User->id )
				$suspectingCountryID = $countryID;
		}
		try 
		{
			if( $suspectingCountryID == null && !$User->type['Moderator']) 
			{
				throw new Exception("Cannot create a suspicion for this game; you are not a member of the game, and are not a moderator.");
			}
			if( count($suspectedCountries) <= 1 )
			{
				throw new Exception("Please select at least two countries that you suspect.");
			}
			$groupID = Group::createSuspicionFromGame($gameID, $suspectedCountries, $_REQUEST['userWeighting'], $_REQUEST['explanation'], $suspectingCountryID);
		}
		catch(Exception $e)
		{
			libHTML::error(l_t("Could not lodge new suspicion: ". $e->getMessage()));
		}
	}
	// Check for create group commands:
	elseif( isset($_REQUEST['createGroup']) && isset($_REQUEST['groupType']) && isset($_REQUEST['groupName']) && isset($_REQUEST['groupDescription']) && (!isset($_REQUEST['groupID']) || strlen($_REQUEST['groupID'])==0) )
	{
		libAuth::formToken_Valid();
		try
		{
			$groupID = Group::create($_REQUEST['groupType'], $_REQUEST['groupName'], $_REQUEST['groupDescription'], isset($_REQUEST['groupGameReference']) ? $_REQUEST['groupGameReference'] : '');
		}
		catch (Exception $e)
		{
			libHTML::error(l_t("Could not create new relationship group: ". $e->getMessage()));
		}
	}
}

if ( isset($_REQUEST['groupID']) && intval($_REQUEST['groupID'])>0 )
{
	$groupID = (int)$_REQUEST['groupID'];
}

if( $groupID === -1 )
{
	// No group specified; show an overview page for this user.

	// Ensure user records don't get locked by this query;
	$DB->sql_put("COMMIT");
	$DB->sql_put("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");  // https://stackoverflow.com/a/918092
	$groupUsers = Group::getUsers("gr.isActive = 1 AND (gr.ownerUserID = ". $User->id ." OR g.userID = ".$User->id.")");
	$DB->sql_put("COMMIT"); // This will revert back to READ COMMITTED.

	$groupUsersSorted = array(
		'Declared' => array('Verified'=>array(), 'Unverified'=>array(), 'Denied'=>array()),
		'Suspicions' => array('Verified'=>array(), 'Unverified'=>array(), 'Denied'=>array()),
		'MySuspicions' => array('Verified'=>array(), 'Unverified'=>array(), 'Denied'=>array()),
	);
	foreach($groupUsers as $groupUser)
	{
		if( $groupUser->Group->type != 'Unknown' )
		{
			$relationType = 'Declared';
		}
		else if( $groupUser->userID == $User->id )
		{
			$relationType = 'Suspicions';
		}
		else
		{
			$relationType = 'MySuspicions';
		}

		if( $groupUser->isVerified() )
		{
			$verifiedType = 'Verified';
		}
		else if( $groupUser->isDenied() )
		{
			$verifiedType = 'Denied';
		}
		else
		{
			$verifiedType = 'Unverified';
		}
		$groupUsersSorted[$relationType][$verifiedType][] = $groupUser;
	}
	
	libHTML::starthtml();

	print libHTML::pageTitle('Your User Relationships',l_t('View and manage the links created between accounts that disclose outside relationships to players.'));

	print '<div>';
	print '<p>This page lets you view and manage your user relationships; confirm or deny relationships other users have created, and view the '.
		'relationships you have created for yourself and others.</p>';

	print '<div class = "profile_title">Terminology</div>';
	print '<div class = "profile_content">';
		print '<p><ul>
		<li><strong>Relationship:</strong> A connection between two users of the site that exists outside of the site, or otherwise causes an account to potentially be biased towards a certain player for reasons outside of the game.</li>
		<li><strong>Declared:</strong> A relationship that the user has acknowledged by verifying themselves.</li>
		<li><strong>Verified:</strong> A relationship that has been verified either by being declared, or through moderators investigations.</li>
		<li><strong>Unverified:</strong> A relationship that may or may not be true; currently not enough information by itself.</li>
		<li><strong>Denied relationship:</strong> A relationship that has been established that it does not exist. Either a false suspicion a moderator has investigated,
			or a mistaken link that has been denied.</li>
		<li><strong>Suspicion:</strong> A relationship created by someone not in the relationship, including others suspected of having a relationship, 
			based on the behavior of the players in a game. Can be assigned a strength based on the strength of the suspicion.</li>
		<li><strong>User/Creator/Moderator Rating:</strong> A strength assigned to a relationship: Ranges from -100 for completely deny to 100 for very strong/suspect. [Denied=-100, Doubt=-50, None=0, Weak=33, Mid=66, Strong=100]</li>
		<li><strong>Active/Inactive:</strong> A relationship can be made inactive by the creator or by a moderator, for if the relationship is not worth considering further, or has ceased.</li>
		<li><strong>Type:</strong> The nature of the relationship; ranging from accounts being run by the same person to a distant community / organizational relationship.</li>
		<li><strong>Type: Person/Family/Work/School/Other:</strong> Types of declared relationships where the relation type is known. School indicates the relationship 
			revolves around school, Person means the users involved are the same person, Other means known but not listed, etc..</li>
		<li><strong>Type: Unknown:</strong> An unknown relationship, where there is a suspicion but the actual nature of the relationship is unknown. All suspicions start
			as Unknown relationships, but moderators can change the type if the related users acknowledge and declare it.</li>
		</ul>
		</p>';
	print '</div>';
		
	print '<div class="hr"></div>';
	
	print '<h3>Declared relationships:</h3>';
	print '<div class = "profile_title">Verified - '.count($groupUsersSorted['Declared']['Verified']).' - <em>Relationships which have been verified/acknowledged.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Declared']['Verified'],null,null);
	print '</div>';

	print '<div class = "profile_title">Unverified - '.count($groupUsersSorted['Declared']['Unverified']).' - <em>Relationships which are not acknowledged/verified and are unresolved.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Declared']['Unverified'],null,null);
	print '</div>';
	print '<div class = "profile_title">Denied - '.count($groupUsersSorted['Declared']['Denied']).' - <em>Relationships which have been determined invalid.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Declared']['Denied'],null,null);
	print '</div>';

	print '<h3>Suspicions of a relationship between you and others:</h3>';
	print '<div class = "profile_title"><li>Verified - '.count($groupUsersSorted['Suspicions']['Verified']).' - <em>Suspicions which have been verified/acknowledged.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Suspicions']['Verified'],null,null);
	print '</div>';
	print '<div class = "profile_title">Unverified - '.count($groupUsersSorted['Suspicions']['Unverified']).' - <em>Suspicions which you have not verified/acknowledged.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Suspicions']['Unverified'],null,null);
	print '</div>';
	print '<div class = "profile_title">Denied - '.count($groupUsersSorted['Suspicions']['Denied']).' - <em>Relationships which have been determined invalid.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['Suspicions']['Denied'],null,null);
	print '</div>';
	print '</ul>';

	print '<h3>Suspicions of a relationship between others, created by you:</h3>';
	print '<div class = "profile_title">Verified - '.count($groupUsersSorted['MySuspicions']['Verified']).' - <em>Your suspicions which have been verified/acknowledged.</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['MySuspicions']['Verified'],null,null);
	print '</div>';
	print '<div class = "profile_title">Unverified - '.count($groupUsersSorted['MySuspicions']['Unverified']).' - <em>Your suspicions which have not been verified/acknowledged</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['MySuspicions']['Unverified'],null,null);
	print '</div>';
	print '<div class = "profile_title">Denied - '.count($groupUsersSorted['MySuspicions']['Denied']).' - <em>Your suspicions which have been determined invalid</em></div>';
	print '<div class = "profile_content">';
	print Group::outputUserTable_static($groupUsersSorted['MySuspicions']['Denied'],null,null);
	print '</div>';

	print '</div>';
	print '</div>';
	?>

	<script type="text/javascript">
	   var coll = document.getElementsByClassName("profile_title");
	   var searchCounter;
	
	   for (searchCounter = 0; searchCounter < coll.length; searchCounter++) {
		 coll[searchCounter].addEventListener("click", function() {
		   this.classList.toggle("active");
		   var content = this.nextElementSibling;
			   if (content.style.display === "block") { content.style.display = "none"; }
			   else { content.style.display = "block"; }
		 });
	   }
	</script>
	
	<?php
	libHTML::footer();
}

function TryPostReply($groupID)
{
	global $User, $DB;

	if( !isset($_REQUEST['newmessage']) ) $_REQUEST['newmessage']  = '';
	if( !isset($_REQUEST['newsubject']) ) $_REQUEST['newsubject'] = '';

	$new = array('message' => "", 'subject' => "", 'id' => -1);
	if(isset($_REQUEST['newmessage']) AND $User->type['User']
		AND ($_REQUEST['newmessage'] != "") ) {
		// We're being asked to send a message.

		$new['message'] = $DB->msg_escape($_REQUEST['newmessage']);

		$new['sendtothread'] = $groupID;

		try
		{
			libAuth::formToken_Valid();
			
			// If a mod was waiting for this message let them know we have replied
			$DB->sql_put("UPDATE wD_GroupUsers SET isMessageWaiting = greatest(isMessageWaiting,isMessageNeeded), isMessageNeeded = 0, ".
				"timeLastMessageSent = ".time().", messageCount = messageCount + 1 WHERE groupID = ".$groupID." AND userID = ".$User->id);
			
			$DB->sql_put("UPDATE wD_Groups SET isMessageWaiting = greatest(isMessageWaiting,isMessageNeeded), isMessageNeeded = 0 ".
				"WHERE id = ".$groupID." AND ownerUserID = ".$User->id);

			$new['id'] = Message::send( $new['sendtothread'],
				$User->id,
				$new['message'],
					'',
					'GroupDiscussion');
			
			header("Location: " . $_SERVER['REQUEST_URI'] . '&reply=success');
		}
		catch(Exception $e)
		{
			$new['messageproblem']=$e->getMessage();
		}

		if ( isset($new['messageproblem']) and $new['id'] != -1 )
		{
			$_REQUEST['newmessage'] = '';
			$_REQUEST['newsubject'] = '';
		}
	}
	else
	{
		/*
		* This isn't very secure, it could potentially lead to XSS attacks, but it
		* is the easiest way to un-escape a failed post without having to use a
		* UTF-8 library to replace strings
		*/
		$_REQUEST['newmessage'] = '';
		$_REQUEST['newsubject'] = '';
	}

	return $new;
}

function OutputDiscussionThread($groupID, $new)
{
	global $DB, $User, $GroupProfile;

	// If we are in gunboat mode we need to ensure players don't see each others' messages so that no-press is preserved
	$gunboatMode = false;
	if( $GroupProfile->gameID )
	{
		list($pressType) = $DB->sql_row("SELECT pressType FROM wD_Games WHERE id = ". $GroupProfile->gameID);
		if( $pressType == 'NoPress' && !$User->type['Moderator'] )
		{
			$gunboatMode = true;
		}
	}

	if( isset($new['messageproblem']) ) $messageproblem = $new['messageproblem'];

?>
<div class="thread threadID1538794 threadborder1 threadalternate1 userID23277">
	<!--<div class="leftRule message-head threadalternate1">
	<a href="profile.php?userID=23277">Octavious  (1712 <img src="images/icons/points.png" alt="D" title="webDiplomacy points">)</a><br>
		<strong><em><span class="timestamp" unixtime="1517330928">31 Jan 2018</span></em></strong> <br><a title="Mute this thread, hiding it from your forum and home page" class="light likeMessageToggleLink" href="forum.php?toggleMuteThreadID=1538794&amp;rand=88241#1538794">Mute thread</a><br>
		<a id="likeMessageToggleLink1538794" href="#" title="Give a mark of approval for this post" class="light likeMessageToggleLink" onclick="likeMessageToggle(25560,1538794,'25560_1538794_be8904232222db72a7628bef0ead3f3b'); return false;">+1</a></div><div class="message-subject"><a style="display:none;" class="messageIconForum" threadid="1538794" messageid="1539514" href="forum.php?threadID=1538794#1539514"><img src="images/icons/mail.png" alt="New" title="Unread messages!"></a> <a style="display:none;" class="participatedIconForum" threadid="1538794" href="forum.php?threadID=1538794#1538794"><img src="images/icons/star.png" alt="Participated" title="You have participated in this thread."></a> <strong>Occasionally at the top thread</strong>
	</div>-->

<div class="message-body threadalternate1">
	<div class="message-contents">
		Use this thread to clarify any details regarding this relationship, discuss the reasoning / validity behind the relationship, 
		and to discuss with the mod team.<br /><br />
	</div>
</div>
<div style="clear:both;"></div>
<?php
	// We are viewing the thread; print replies
	$replytabl = $DB->sql_tabl(
		"SELECT f.id, fromUserID, f.timeSent, f.message, u.points as points, 
				u.username as fromusername, f.toID, u.type as userType
			FROM wD_ForumMessages f
			INNER JOIN wD_Users u ON f.fromUserID = u.id
			WHERE f.toID=".$groupID." AND f.type='GroupDiscussion'
			order BY f.timeSent ASC");
	$replyswitch = 2;
	$replyNumber = 0;
	list($maxReplyID) = $DB->sql_row("SELECT MAX(id) FROM wD_ForumMessages WHERE toID=".$groupID." AND type='GroupDiscussion'");
	while($reply = $DB->tabl_hash($replytabl) )
	{
		$replyFromModerator = ( strstr($reply['userType'], 'Moderator') !== false );

		$userLink = null;
		if( $reply['fromUserID'] == $GroupProfile->ownerUserID )
		{
			$userLink = $GroupProfile->ownerLink($reply['userType'], $reply['points']);
		}
		else
		{
			foreach($GroupProfile->GroupUsers as $groupUser)
			{
				if( $reply['fromUserID'] == $groupUser->userID )
				{
					$userLink = $groupUser->userLink($reply['userType'], $reply['points']);
				}
			}
		}
		if( $userLink == null )
		{
			$userLink = User::profile_link_static($reply['fromusername'], $reply['fromUserID'], $reply['userType'], $reply['points']);
		}
		
		$replyswitch = 3-$replyswitch;//1,2,1,2,1...
		
		print '<div class="reply replyborder'.$replyswitch.' replyalternate'.$replyswitch.'
			'.($replyNumber ? '' : 'reply-top').' userID'.$reply['fromUserID'].'">';
		$replyNumber++;

		print '<a name="'.$reply['id'].'"></a>';

		if ( $new['id'] == $reply['id'] )
		{
			print '<a name="postbox"></a>';
		}

		print '<div class="message-head replyalternate'.$replyswitch.' leftRule">';

		print $userLink.'<br />';

		print libHTML::forumMessage($groupID,$reply['id']);

		print '<em>'.libTime::text($reply['timeSent']).'</em>';
		
		print '</div>';

		print '
			<div class="message-body replyalternate'.$replyswitch.'">
				<div class="message-contents" fromUserID="'.$reply['fromUserID'].'">
					'.(($gunboatMode && !$replyFromModerator) ? '<i>Message only visible to moderators as this is a no-press game</i>' : $reply['message']).'
				</div>
			</div>

			<div style="clear:both"></div>
			</div>';
	}
	unset($replytabl, $replyfirst, $replyswitch);

	
	// Replies done, now print the footer
	print '<div class="message-foot threadalternate1">';

	if($User->type['User'] )
	{
		print '<div class="postbox">'.
			( $new['id'] != (-1) ? '' : '<a name="postbox"></a>').
			'<form class="safeForm" action="./group.php?groupID='.$groupID.'#postbox" method="post">
			<p>';
		print '<div class="hrthin"></div>';
		if ( isset($messageproblem) and $new['sendtothread'] )
		{
			print '<p class="notice">'.$messageproblem.'</p>';
		}
		print '<TEXTAREA NAME="newmessage" style="margin-bottom:5px;" ROWS="4">'.$_REQUEST['newmessage'].'</TEXTAREA><br />
				'.libAuth::formTokenHTML().'
				<input type="submit" class="form-submit" value="'.l_t('Post message').'" name="'.l_t('Post').'"></p></form>
				</div>';
	} else {
		print '<br />';
	}
	print '</div>';
	print '</div>';
}

if( $User->type['Moderator'] )
{
	// Reset any flags that directed us here
	$DB->sql_put("UPDATE wD_GroupUsers SET isWeightingWaiting = 0, isMessageWaiting = 0 WHERE groupID = ".$groupID." AND modUserID=".$User->id);
	$DB->sql_put("UPDATE wD_Groups SET isMessageWaiting = 0 WHERE id = ".$groupID." AND modUserID=".$User->id);

	// If we are requesting a response or weighting from a user set that flag now
	if( isset($_REQUEST['messageNeeded']) && isset($_REQUEST['messageNeededUserID']) )
	{
		$messageNeeded = (int)$_REQUEST['messageNeeded'];
		$messageNeededUserID = (int)$_REQUEST['messageNeededUserID'];
		$DB->sql_put("UPDATE wD_GroupUsers SET isMessageNeeded = ".$messageNeeded.", modUserID = ".$User->id." WHERE groupID = ".$groupID." AND userID = ".$messageNeededUserID);
	}
	if( isset($_REQUEST['weightingNeeded']) && isset($_REQUEST['weightingNeededUserID']) )
	{
		$weightingNeeded = (int)$_REQUEST['weightingNeeded'];
		$weightingNeededUserID = (int)$_REQUEST['weightingNeededUserID'];
		$DB->sql_put("UPDATE wD_GroupUsers SET isWeightingNeeded = ".$weightingNeeded.", modUserID = ".$User->id." WHERE groupID = ".$groupID." AND userID = ".$weightingNeededUserID);
	}
	if( isset($_REQUEST['ownerMessageNeeded']) )
	{
		$ownerMessageNeeded = (int)$_REQUEST['ownerMessageNeeded'];
		$DB->sql_put("UPDATE wD_Groups SET isMessageNeeded = ".$ownerMessageNeeded.", modUserID = ".$User->id." WHERE id = ".$groupID);
	}
	// If we are requesting a response or weighting from a user set that flag now
	if( isset($_REQUEST['modSetGroupType']) )
	{
		if ( in_array($_REQUEST['modSetGroupType'], Group::$validTypes, true) )
		{
			$DB->sql_put("UPDATE wD_Groups SET type = '".$_REQUEST['modSetGroupType']."', modUserID = ".$User->id." WHERE id = ".$groupID);
		}
		
	}
	if( isset($_REQUEST['modAddUserID']) )
	{
		// Mod wants to add a user to the group
		$GroupProfile = Group::loadFromID($groupID);
		$ownerUser = new User($GroupProfile->ownerUserID);
		$newUser = new User((int)$_REQUEST['modAddUserID']);
		$GroupProfile->userAdd($ownerUser, $newUser, 0);
	}
}

try
{
	$GroupProfile = Group::loadFromID($groupID);
}
catch (Exception $e)
{
	libHTML::error(l_t("Invalid group ID given."));
}

if( $User->type['User'] )
{
	try
	{
		
		// Check for modify group commands
		if( isset($_REQUEST['addSelf']) )
		{
			// User wants to join the group themselves
			$GroupProfile->userAdd($User, $User, isset($_REQUEST['groupUserStrength']) ? $_REQUEST['groupUserStrength'] : 100);

			$GroupProfile = Group::loadFromID($groupID);
		}
		if( isset($_REQUEST['addUserID']) )
		{
			// User wants to add a user to the group
			$addingUser = new User($_REQUEST['addUserID']);
			$GroupProfile->userAdd($User, $addingUser, isset($_REQUEST['groupUserStrength']) ? $_REQUEST['groupUserStrength'] : 66);

			$GroupProfile = Group::loadFromID($groupID);
		}
		/*
		This is a mandatory field on creation
		if( isset($_REQUEST['groupDescription']) && strlen($_REQUEST['groupDescription']) > 0 ) // Prevent overwriting description with blank when adding user from usercp
		{
			$GroupProfile->userSetDescription($User, $_REQUEST['groupDescription']);
			
			$GroupProfile = Group::loadFromID($groupID);
		}*/
		if( isset($_REQUEST['moderatorNotes']) )
		{
			$GroupProfile->userSetModNotes($User, $_REQUEST['moderatorNotes']);
			
			$GroupProfile = Group::loadFromID($groupID);
		}
		if( isset($_REQUEST['deactivate']) )
		{
			$GroupProfile->userSetActive($User, 0);

			$GroupProfile = Group::loadFromID($groupID);
		}
		if( isset($_REQUEST['activate']) )
		{
			$GroupProfile->userSetActive($User, 1);

			$GroupProfile = Group::loadFromID($groupID);
		}

		$weightsUpdated = false;
		foreach($GroupProfile->GroupUsers as $groupUser)
		{
			if( isset($_REQUEST['userWeighting'.$groupUser->userID]) )
			{
				$GroupProfile->userUpdateUserWeighting($User, $groupUser, $_REQUEST['userWeighting'.$groupUser->userID]);
				$weightsUpdated = true;
			}
			if( isset($_REQUEST['modWeighting'.$groupUser->userID]) )
			{
				$GroupProfile->userUpdateModWeighting($User, $groupUser, $_REQUEST['modWeighting'.$groupUser->userID]);
				$weightsUpdated = true;
			}
			if( isset($_REQUEST['ownerWeighting'.$groupUser->userID]) )
			{
				$GroupProfile->userUpdateOwnerWeighting($User, $groupUser, $_REQUEST['ownerWeighting'.$groupUser->userID]);
				$weightsUpdated = true;
			}
		}
		if( $weightsUpdated )
		{
			$GroupProfile = Group::loadFromID($groupID);
		}
	}
	catch(Exception $ex)
	{
		libHTML::error("<strong>Failed to apply changes to group:</strong> " . $ex->getMessage() . "<br />Please ensure you have permissions to do this operation.");
	}
}

// This will check $User, perms etc:, call before starting output:	
if ( $GroupProfile->canUserComment($User) )
{
	$new = TryPostReply($GroupProfile->id);
}

libHTML::starthtml();

print libHTML::pageTitle('Group Panel: #'.$GroupProfile->id.' '.$GroupProfile->name,l_t('A space for the community and mod team to discuss, decide and resolve problems.'));

print '<div>';
print '<div class = "profile-show-floating" style="margin-left:2.5%">';

// Profile Information
print '<div class = "profile-show-inside-left" style="width:45%">';
print '<div class = "comment_title" style="width:90%">';
print '<strong>Group Information:</strong> </div>';
	print '<p><ul class="profile">';

	if( $GroupProfile->gameID )
	{
		print '<p><strong>Open game:</strong> <a href="board.php?gameID='.$GroupProfile->gameID.'">Game board</a></p>';
	}
	print '<p><strong>Group Type:</strong> '.$GroupProfile->type.'</p>';
	print '<p><strong>Status:</strong> '.($GroupProfile->isActive ? 'Active' : 'Inactive').'</p>';
	
	if( $GroupProfile->ownerUserID == $User->id || ( $User->type['Moderator'] && $GroupProfile->isViewerInGame !== 1 ) )
	{
		print '<form>'.
		'<input type="hidden" name="groupID" value="'.$GroupProfile->id.'" />';
		if( $GroupProfile->isActive )
		{
			print '<input type="hidden" name="deactivate" value="on" />'.
				'<input type="Submit" class="form-submit" value="Deactivate group" />';
		}
		else
		{
			print '<input type="hidden" name="activate" value="on" />'.
				'<input type="Submit" class="form-submit" value="Reactivate group" />';
		}
		print libAuth::formTokenHTML().
		'</form>';
	}

	print '</li></ul></p>';

print '</div>';

print '<div class="profile-show-inside" style="width:45%">';
print '<div class = "comment_title" style="width:90%">';
print '<strong>Creator Info / Explanation:</strong> </div>';

print '<p><ul>';
print '<li><strong>Creator:</strong> '.$GroupProfile->ownerLink().'</li></br>';
print '<li><strong>Created:</strong> '.libTime::text($GroupProfile->timeCreated).'</li>';
if( $User->id == $GroupProfile->ownerUserID )
{
	if( $GroupProfile->isMessageNeeded )
	{
		print '<li><strong style="color:darkred">A moderator has requested a message response in the discussion below. Thank you!</strong></li>';
	}

}
print '</ul></p>';

print '<p class="profileComment">"'.$GroupProfile->description.'"</p>';



print '</div></br>';
print '</div>';

print '</div>';

print '<div class="hr"></div>';

print '<div>';
print GroupUserToUserLinks::loadFromGroup($GroupProfile)->outputTable();
print '</div>';

print '<div class="hr"></div>';
// Show moderator information
if ( $User->type['Moderator'] )
{	
	print '<div class = "profile-show">';

	print '<div class = "profile_title"> Moderator Info</div>';
	print '<div class = "profile_content_show">';

			$modActions=array();

			//$modActions[] = libHTML::admincpType('Group',$GroupProfile->id);
			//$modActions[] = libHTML::admincp('groupChangeOwner',array('groupID'=>$GroupProfile->id), 'Change group owner');
			//$modActions[] = libHTML::admincp('groupChangeOwner',array('groupID'=>$GroupProfile->id), 'Change group type');

			if( $GroupProfile->isMessageNeeded )
				$modActions[] = '<a class="light" href="group.php?groupID='.$groupUser->groupID.'&ownerMessageNeeded=0">Cancel Message</a>';
			else
				$modActions[] = '<a class="light" href="group.php?groupID='.$groupUser->groupID.'&ownerMessageNeeded=1">Get Message</a>';
			
			$multiAccountParams = '';
			foreach($GroupProfile->GroupUsers as $groupUser)
			{
				if( $multiAccountParams == '' )
				{
					$multiAccountParams = 'aUserID=' . $groupUser->userID . '&bUserIDs=';
				}
				else
				{
					$multiAccountParams .= $groupUser->userID . '%2C';
				}
			}
			$modActions[] = '<a href="admincp.php?tab=Multi-accounts&'.$multiAccountParams.'" class="light">Enter multi-account finder</a>';
			
			$modActions[] = 'Set type:';
			$modActions[] = '<a href="group.php?groupID='.$groupUser->groupID.'&modSetGroupType=Suspicion" class="light">Suspicion</a>';
			$modActions[] = '<a href="group.php?groupID='.$groupUser->groupID.'&modSetGroupType=Person" class="light">Person</a>';
			$modActions[] = '<a href="group.php?groupID='.$groupUser->groupID.'&modSetGroupType=Other" class="light">Other</a>';
			$modActions[] = '<a href="group.php?groupID='.$groupUser->groupID.'&modSetGroupType=Unknown" class="light">Unknown</a>';
			$modActions[] = '<a href="group.php?groupID='.$groupUser->groupID.'&modSetGroupType=Family" class="light">Family</a>';
			$modActions[] = '<a href="group.php?groupID='.$groupUser->groupID.'&modSetGroupType=School" class="light">School</a>';
			$modActions[] = '<a href="group.php?groupID='.$groupUser->groupID.'&modSetGroupType=Work" class="light">Work</a>';

			if($modActions)
			{
				print '<p class="notice">'.implode(' - ', $modActions).'</p>';
			}
			
			print '<br /><form><input type="hidden" name="groupID" value="'.$GroupProfile->id.'" /><input type="text" name="modAddUserID" /><input type="Submit" value="Add user ID" /></form>';

			print '</div>';
			
	print '<div class = "profile_content_show">';
	print '<div class = "comment_title" style="width:90%">';
	
	print 'Notes: </div>';
	print '<div class = "comment_content">';
	print '<form><input type="hidden" name="groupID" value="'.$GroupProfile->id.'" />';
	print '<textarea name="moderatorNotes" ROWS=4>'.$GroupProfile->moderatorNotes.'</textarea>';
	print '<input type="Submit" class="form-submit" value="Update notes" />';
	print libAuth::formTokenHTML();
	print '</form>';
	print '</div>';
	
	print '</div></div>';
	print '<div class="hr"></div>';
}

print '<form>';
print '<input type="hidden" name="groupID" value="'.$GroupProfile->id.'" />';
print '<table class="rrInfo">';
print $GroupProfile->outputUserTable($User);
print '</table>';
print '<input id="submitRatingUpdates" type="Submit" class="form-submit" value="Submit rating updates" />';
print '</form>';
print '</div>';

if ( $GroupProfile->canUserComment($User) )
{
	print '<div class="content"><a name="discussion"></a>';
	print '<h2>Discussion</h2>';
	foreach($GroupProfile->GroupUsers as $groupUser)
	{
		if( $groupUser->userID == $User->id && $groupUser->isMessageNeeded )
		{
			print '<p style="color:darkred">A moderator has requested you please respond to this group panel below before you continue to use the site. Thank you.</p>';
		}
	}
	OutputDiscussionThread($GroupProfile->id, $new);
}
print '</div>';
/*
print '<h3>Members</h3>';
print '<ul>';
// User group members
foreach($GroupProfile->Members as $member)
{
	print $member->showLink();
}
print '</ul>';

// User group comments

if( $GroupProfile->canViewMessages($User) )
{
	require_once('lib/message.php');
	print '<h3>Messages</h3>';
	if( $GroupProfile->canSendMessages($User) )
	{
		try
		{
			$message=notice::sendPMs();
		}
		catch(Exception $e)
		{
			$message=$e->getMessage();
		}	
	}

	if ( $message )
		print '<p class="notice">'.$message.'</p>';

	$tabl=$DB->sql_tabl("SELECT n.*
		FROM wD_Notices n
		WHERE n.fromID=".$GroupProfile->id." AND n.type='Group'
		ORDER BY n.timeSent DESC ");
	while($hash=$DB->tabl_hash($tabl))
	{
		$notices[] = new notice($hash);
	}
	if(!count($notices))
	{
		print '<div class="hr"></div>';
		print '<p class="notice">'.l_t('No group messages yet.').'</p>';
		return;
	}

	print '<div class="hr"></div>';

	foreach($notices as $notice)
	{
		print $notice->viewedSplitter();

		print $notice->html();
	}
}
*/
libHTML::footer();
