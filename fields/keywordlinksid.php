<?php

/*------------------------------------------------------------------------
# plg_keywordlinks
# ------------------------------------------------------------------------
# author &nbsp; &nbsp;Buyanov Danila - Saity74 Ltd.
# copyright Copyright (C) 2012 saity74.ru. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.saity74.ru
# Technical Support: &nbsp; http://saity74.ru/keywordlinks.html
# Admin E-mail: admin@saity74.ru
-------------------------------------------------------------------------*/

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('text');


class JFormFieldKeywordlinksId extends JFormFieldText
{

	protected $type = 'KeywordlinksId';


	protected function getInput()
	{
		$url = null;
		
		$doc = JFactory::getDocument();
		
		if ($this->value)
		{
			$app = JFactory::getApplication();
			if($app->isAdmin()){
				
				$doc->addScript('http://keywordlinks.ru/js/api.js');
			}
		}
		
		$juri = JFactory::getURI();
		$uri_data = parse_url($juri);
		$url_base64 = base64_encode($uri_data['host']);
	
		
		$html[] = '<div class="input-append">';
		$html[] = '<input id="appendedInputButton" type="text" name="' . $this->name . '" id="' . $this->id . '" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'">';
		if (!$this->value)
			$html[] = '<a href="http://keywordlinks.ru" target="_blank" class="btn btn-success">'.JText::_('PLG_KEYWORDLINKS_GETID').'</a>';
		else
			$html[] = '<a href="#" id="keywordlinks_getKeywords" data-keywordlinks-id="'.$this->value.'" class="btn btn-primary">'.JText::_('PLG_KEYWORDLINKS_EXPORT_KEYWORDS').'</a>';
		$html[] = '</div>';
  

		$html = implode("/n", $html);
		return $html;
	}
}
