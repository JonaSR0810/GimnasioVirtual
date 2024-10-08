<?php
/**
* @title				Minitek FAQ Book
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

$questionsData = array();
$questionsData['sectionId'] = $this->sectionId;
$questionsData['topic'] = $this->topic;
$questionsData['questions_params'] = $this->questions_params;
$questionsData['utilities'] = $this->utilities;
$questionsData['final_page'] = false;

if (isset($this->topic->questions) && $this->topic->questions)
{
  $params = $this->utilities->getParams('com_faqbookpro');
  $pagination_limit = $params->get('pagination_limit', 20);
  $questions_found = count($this->topic->questions);
  if ($questions_found > $pagination_limit)
  {
    array_pop($this->topic->questions); // remove extra item from end of array
  }
  else
  {
    $questionsData['final_page'] = true;
  }

  $layout = new JLayoutFile('fb_questions_'.$this->questions_params->questions_theme);
  echo $layout->render($questionsData);
}
