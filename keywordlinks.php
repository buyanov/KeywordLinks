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
setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.CP1251', 'Russian_Russia.1251'); 

jimport('joomla.plugin.plugin');

class plgContentKeyWordLinks extends JPlugin 
{

	protected $keywords;
	protected $nofollow;
	protected $target;
	protected $htags;
	protected $limit;
	protected $args;
	protected $_blocks;
	
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		$this->keywords = trim($this->params->get('keywords'));
		
		if (!$this->keywords)
			return true;
			
		$this->nofollow 	= $this->params->get('nofollow');
		$this->target 		= $this->params->get('target');
		$this->htags 		= $this->params->get('htags');
		$this->limit		= $this->params->get('limit');
	
		
		$matches = explode("\n", $this->keywords);
		
		$this->args = array();
		$this->args['rel'] = $this->nofollow  ? 0 : 'nofollow';
		$this->args['target'] = $this->target ? '_parent' : '_blank';
		
		$args = '';
		foreach ($this->args as $key => $value)
		{
			if ($value)
				$args .= $key.'="'.$value.'" ';
		}
		
		//save links
		$regex = '#<a(.*?)>(.*?)</a>#s';
		$article->text = preg_replace_callback($regex, array(&$this, '_excludeBlocks'), $article->text);
			
		if ($this->htags)
		{
			$regex = '#<h(.*?)>(.*?)</h.{1}>#s';
			$article->text = preg_replace_callback($regex, array(&$this, '_excludeBlocks'), $article->text);
		}
		
		foreach ($matches as $match)
		{
			list($keyword, $href) = explode('|', $match);
			
			$regex = '#\b'.$keyword.'\b#s';
			$link = '<a href="'.$href.'" '.$args.'>'.$keyword.'</a>';
			
			$article->text = preg_replace($regex, $link, $article->text, $this->limit);
		}
	
		
		if (is_array($this->_blocks) && !empty($this->_blocks))
		{
			$this->_blocks = array_reverse($this->_blocks);
			$regex = '#<!-- keywordlink-excluded-block -->#s';
			$article->text = preg_replace_callback($regex, array(&$this, '_includeBlocks'), $article->text);
		}
			
		return true;

	}
	
	protected function _excludeBlocks($matches)
	{
		$this->_blocks[] = $matches[0];
		return '<!-- keywordlink-excluded-block -->';
	}
	
	protected function _includeBlocks($matches)
	{
		$block = array_pop($this->_blocks);
		return $block;
	}
}
?>