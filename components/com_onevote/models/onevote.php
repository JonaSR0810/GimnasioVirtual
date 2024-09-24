<?php
/**
 * @package Component OneVote! for Joomla! 2.5/3.0
 * @author Brian Keahl
 * @copyright (C) 2014- Advanced Computer Systems
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
/**
 * OneVote Model
 */
class OneVoteModelOneVote extends JModelItem
{
        /**
         * @var array messages
         */
        protected $messages;
 
        /**
         * Returns a reference to the a Table object, always creating it.
         *
         * @param       type    The table type to instantiate
         * @param       string  A prefix for the table class name. Optional.
         * @param       array   Configuration array for model. Optional.
         * @return      JTable  A database object
         * @since       2.5
         */
        public function getTable($type = 'OneVote', $prefix = 'OneVoteTable', $config = array()) 
        {
                return JTable::getInstance($type, $prefix, $config);
        }
        /**
         * Get the message
         * @param  int    The corresponding id of the message to be retrieved
         * @return string The message to be displayed to the user
         */
        public function getMsg($id = 1) 
        {
                if (!is_array($this->messages))
                {
                        $this->messages = array();
                }
 
                if (!isset($this->messages[$id])) 
                {
                        //request the selected id
                        $jinput = JFactory::getApplication()->input;
                        $id = $jinput->get('id', 1, 'INT' );
 
                        // Get a TableOneVote instance
                        $table = $this->getTable();
 
                        // Load the message
                        $table->load($id);
 
                        // Assign the message
                        $this->messages[$id] = $table->greeting;
                }
 
                return $this->messages[$id];
        }
}