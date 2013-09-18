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
			'com_content.article',
			'com_k2.item'
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
			$uri = JFactory::getURI()->toString();
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
		
		if ($this->htags)
		{
			$regex[] = '#<h(.*?)>(.*?)</h.{1}>#';
		}
		
		$article->text = preg_replace_callback($regex, array(&$this, '_exclude'), $article->text);
		
		
		foreach ($matches as $match)
		{
			list($keywords_str, $href, $title) = preg_split('~[=\|]~u', $match);
			$keywords = explode(',',$keywords_str);
			$title = trim($title);
			foreach ($keywords as $keyword)
			{
				$title_arg = '';
				$keyword = trim($keyword);
				$this->_normalize_url('http://saity74.ru/keywordlinks.html');
				
				$href = $this->_normalize_url($href);
				$uri = $this->_normalize_url($uri);
				
				if ($href !== $uri)
				{
					if ((strpos($keyword, '[') !== false) && (strpos($keyword, ']') !== false))
					{
						$keyword = str_replace(array('[',']',':'), array('(?:', ')', '|'), $keyword);
					}
					
					$first = mb_substr($keyword, 0, 1);
					
					$regex = '#(\s|[\>\'\"])((?:'.mb_strtoupper($first).'|'.mb_strtolower($first).')'.mb_substr($keyword, 1).')(\s|[\<\.,\'\"\;\:\!\?]){1}#u';
					$class = $this->class !== '' ? ' class="'.$this->class.'" ' : '';
					
					$title_arg = $this->title ? ' title="'.$title.'" ' : '';
					
					if (strpos($href, $host) !== false)
					{
						//relative link
						$this->link = '${1}<a href="'.$href.'" '.$title_arg.$class.'>${2}</a>${3}';
					} else {
						//external link
						$this->link = '${1}<a href="'.$href.'" '.$args.$title_arg.$class.'>${2}</a>${3}';
					}
					
					$article->text = preg_replace($regex, $this->link, $article->text, $this->limit);
				}
			}
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
	
	protected function _normalize_url($url)
	{
		$return = '';
		
		$parse_result = parse_url(trim($url));
		if (isset($parse_result['scheme']))
		{
			$return .= $parse_result['scheme'].'://';
		} else {
			return false;
		}
		if (isset($parse_result['host']))
		{
			$return .= $parse_result['host'];
		} else {
			return false;
		}
		if (isset($parse_result['path']) && $parse_result['path'] !== '/')
		{
			$return .= $parse_result['path'];
		}
		
		return $return;
	}
}
