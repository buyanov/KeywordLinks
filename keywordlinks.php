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
		//echo $context;
		$app =& JFactory::getApplication(); if( $app->isAdmin() ) return true;
		
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
			list($keyword, $href) = explode('|', $match);
			
			$regex = array('#\s('.$keyword.')\s#', '#\b'.$keyword.'\b#');
			
			$class = $this->class !== '' ? ' class="'.$this->class.'" ' : '';
			$title = $this->title? ' title="'.$keyword.'" ' : '';
			
			if (strpos($href, $host) !== false)
			{
				//relative link
				$this->link = ' <a href="'.$href.'" '.$title.$class.'>'.$keyword.'</a> ';
			} else {
				//external link
				$this->link = ' <a href="'.$href.'" '.$args.$title.$class.'>'.$keyword.'</a> ';
			}
			$this->counter++;
			$this->_blocks[] = array($this->counter, $this->link);
			$article->text = preg_replace($regex, '<!-- keywordlink-excluded-block-'.$this->counter.' -->', $article->text, $this->limit);
		}
		
		if (is_array($this->_blocks) && !empty($this->_blocks))
		{
			foreach ($this->_blocks as $block)
			{
				list($n, $value) = $block;
				$regex = '#<!-- keywordlink-excluded-block-'.$n.' -->#';
				$article->text = preg_replace($regex, $value, $article->text);
			}
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
