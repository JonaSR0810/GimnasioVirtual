DROP TABLE IF EXISTS `#__onevote_elections`;
DROP TABLE IF EXISTS `#__onevote_groups`;
DROP TABLE IF EXISTS `#__onevote_ballot_items`;
DROP TABLE IF EXISTS `#__onevote_nominations`;
DROP TABLE IF EXISTS `#__onevote_votes`;
 
CREATE TABLE `#__onevote_elections` (
  `election_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,  
  `polls_open` datetime NULL,
  `polls_close` datetime NULL,
  `nominations_open` datetime NULL,
  `nominations_close` datetime NULL,
  `notify_nominee` bit default 1,
  `one_nomination` bit default 1,
  `anonymous_nominations` bit default 1,
  `anonymous_voting` bit default 1,
  `show_results` bit default 1,
  `show_results_min_votes` smallint default 10,
  `show_total_votes_cast` bit default 0,
  `email_nominations_to` varchar(64),
  `email_votes_to` varchar(64),
  `active` bit default 0,
  `creator_id` int(10) NULL,
  `ip` varchar(24) NULL,
  `election_log_time` Timestamp default CURRENT_TIMESTAMP,
   PRIMARY KEY  (`election_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `#__onevote_groups` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` smallint UNSIGNED NOT NULL,
  `group_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `election_id` (`election_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE `#__onevote_ballot_items` (
  `ballot_item_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` smallint UNSIGNED NOT NULL,
  `ballot_title` varchar(24) NOT NULL,
  `ballot_description` varchar(255) NOT NULL,
  `is_ballot_question` bit default 0,
  `allow_nominations` bit default 1,
  `nominate_group_members_only` bit default 1,
  `position` tinyint UNSIGNED,
  `votes` tinyint default 1,
  `ip` varchar(24) NULL,
  `ballot_items_log_time` Timestamp default CURRENT_TIMESTAMP,
   PRIMARY KEY  (`ballot_item_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

 
CREATE TABLE `#__onevote_nominations` (
  `nomination_id` int UNSIGNED NOT NULL AUTO_INCREMENT, 
  `election_id` smallint UNSIGNED NOT NULL,
  `ballot_item_id` int UNSIGNED NOT NULL,
  `nominee_id` int(10) NULL,
  `first_name` varchar(24) NULL,
  `last_name` varchar(24) NULL,
  `nominated_by` int(10) NULL,
  `nominee_email` varchar(64) NULL,
  `ip` varchar(24) NULL,
  `nomination_log_time` Timestamp default CURRENT_TIMESTAMP,
   PRIMARY KEY  (`nomination_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `#__onevote_votes` (
  `vote_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, 
  `election_id` smallint UNSIGNED NOT NULL,
  `user_id` int(10) NULL,
  `ballot_item_id` int UNSIGNED NOT NULL,
  `nomination_id` int UNSIGNED NOT NULL,  
  `ip` varchar(24),
  `vote_log_time` Timestamp default CURRENT_TIMESTAMP,
   PRIMARY KEY  (`vote_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
