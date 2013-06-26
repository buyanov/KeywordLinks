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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
//setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.CP1251', 'Russian_Russia.1251', 'ru_RU.UTF-8'); 

jimport('joomla.plugin.plugin');



class plgContentKeyWordLinks extends JPlugin 
{

	protected $keywords;
	protected $nofollow;
	protected $target;
	protected $htags;
	protected $limit;
	protected $relative;
	protected $class;
	protected $args;
	protected $_blocks;
	protected $counter;
	
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		$contexts = array(
			'com_content.featured',
			'com_content.article'
		);
		
		if (!in_array($context, $contexts)) return true;
				
		$this->counter = 0;
		$this->link = '';
		$this->_blocks[] = array();
		
		$this->keywords = trim($this->params->get('keywords'));
		
		if (!$this->keywords)
			return true;
			
		$this->nofollow 	= $this->params->get('nofollow');
		$this->target 		= $this->params->get('target');
		$this->htags 		= $this->params->get('htags', 1);
		$this->limit		= $this->params->get('limit', 1);
		$this->title		= $this->params->get('title', 1);
		$this->relative		= $this->params->get('relative', 1);
		$this->class		= $this->params->get('class', '');
		
		if ($this->relative)
		{
			$host = JFactory::getURI()->getHost();
		}
	
		
		$matches = explode("\n", $this->keywords);
		
		$this->args = array();
		$this->args['rel'] = !$this->nofollow  ? 0 : 'nofollow';
		$this->args['target'] = !$this->target ? '_parent' : '_blank';
		
		
		$args = '';
		foreach ($this->args as $key => $value)
		{
			if ($value)
				$args .= $key.'="'.$value.'" ';
		}
		
		//save links and images
		$regex = array('#<a(.*?)>(.*?)</a>#', '#<img(.*?)/>#');
		$article->text = preg_replace_callback($regex, array(&$this, '_exclude'), $article->text);
		
	
		if ($this->htags)
		{
			$regex = '#<h(.*?)>(.*?)</h.{1}>#';
			$article->text = preg_replace_callback($regex, array(&$this, '_exclude'), $article->text);
		}
		
		foreach ($matches as $match)
		{
			list($keyword, $href, $title) = explode('|', $match);
			if ((strpos($keyword, '[') !== false) && (strpos($keyword, ']') !== false))
			{
				$keyword = str_replace(array('[',']',':'), array('(?:', ')', '|'), $keyword);
			}
			
			$first = mb_substr($keyword, 0, 1);
			
			$regex = '#(\s|[\>\'\"])((?:'.mb_strtoupper($first).'|'.mb_strtolower($first).')'.mb_substr($keyword, 1).')(\s|[\<\.,\'\"\;\:]){1}#u';
			$class = $this->class !== '' ? ' class="'.$this->class.'" ' : '';
			
			$title = $this->title? ' title="'.$title.'" ' : '';
			
			if (strpos($href, $host) !== false)
			{
				//relative link
				$this->link = '${1}<a href="'.$href.'" '.$title.$class.'>${2}</a>${3}';
			} else {
				//external link
				$this->link = '${1}<a href="'.$href.'" '.$args.$title.$class.'>${2}</a>${3}';
			}
			
			$article->text = preg_replace($regex, $this->link, $article->text, $this->limit);
		}
		
		if (is_array($this->_blocks) && !empty($this->_blocks))
		{
			foreach ($this->_blocks as $block)
			{
				list($n, $value) = $block;
				$patterns[$n] = '#<!-- keywordlink-excluded-block-'.$n.' -->#';
				$replacement[$n] = $value;
			}
			
			$article->text = preg_replace($patterns, $replacement, $article->text);
		}
			
		return true;

	}
	
	protected function _exclude($matches)
	{
		
		$this->counter++;
		$this->_blocks[] = array($this->counter, $matches[0]);
		return '<!-- keywordlink-excluded-block-'.$this->counter.' -->';
	}
}
