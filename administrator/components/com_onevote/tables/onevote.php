<?php
/**
 * @package Component OneVote! for Joomla! 2.5/3.0
 * @author Brian Keahl
 * @copyright (C) 2014- Advanced Computer Systems
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// No direct access
defined('_JEXEC') or die('Restricted access');
 
// import Joomla table library
jimport('joomla.database.table');
 
/**
 * OneVote Table class
 */
class OneVoteTableOneVote extends JTable
{
        /**
         * Constructor
         *
         * @param object Database connector object
         */
        function __construct(&$db) 
        {
                parent::__construct('#__onevote', 'id', $db);
        }
}