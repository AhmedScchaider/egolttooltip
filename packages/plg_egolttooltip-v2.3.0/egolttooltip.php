<?php
/**
 * @package   	Egolt Tooltip
 * @link 		http://www.egolt.com
 * @copyright 	Copyright (C) Egolt - www.egolt.com
 * @author    	Soheil Novinfard
 * @license    	GNU/GPL 2
 *
 * Name:		Egolt Tooltip
 * License:		GNU/GPL 2
 * Product:		http://www.egolt.com/products/egolttooltip
 */
 
// Check Joomla! Library and direct access
defined('_JEXEC') or die('Direct access denied!');

jimport( 'joomla.html.parameter' );

class plgSystemEgoltTooltip extends JPlugin
{

	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		
		// load plugin parameters
        $this->_plugin = JPluginHelper::getPlugin( 'system', 'egolttooltip' );
        $this->_params = new JRegistry( $this->_plugin->params );
	}
	
	public function isActive()
	{
		$app 		= JFactory::getApplication();
		$juri		= JFactory::getURI();
		$menu		= $app->getMenu();
		$mymenuitem	= $this->_params->get('egmenuitem');
		$page		= $this->_params->get('egpage', 0);
		
		// Admin Pages
		if(strpos($juri->getPath(),'/administrator/')!==false) 
			return;
		
		// Frontend Pages
		$enableit	= false;
		switch($page){
			case 0 :
				$enableit = true;
			break;
					
			case 1 :
				if (in_array($menu->getActive()->id, $mymenuitem)) 
					$enableit = true;
				break;
					
			case 2 :
				if (!in_array($menu->getActive()->id, $mymenuitem))
					$enableit = true;
				break;	
		}
		return $enableit;
	}
	
	public function onAfterRoute() {
		// Check if tooltip is active
		if(!$this->isActive()) 
			return;
			
		$app 		= JFactory::getApplication();
		$menu		= $app->getMenu();
		$document	= JFactory::getDocument();		
		$juri		= JFactory::getURI();
		$mymenuitem	= $this->_params->get('egmenuitem');
		$page		= $this->_params->get('egpage', 0);
		$adminpages	= $this->_params->get('adminpages', 0);
		
		if (!version_compare(JVERSION, '3.0', 'ge')) 
		{
			$needjquery = true;
			$header = $document->getHeadData();
			foreach($header['scripts'] as $scriptName => $scriptData)
			{
				if(substr_count($scriptName,'/jquery'))
				{
					die();
					$needJquery = false;
				}
			}
			// if(EGOHtmlJs::needJquery()) {
			if($needjquery) {
				$document->addScript(JUri::root( true ).'/media/egolttooltip/js/jquery-2.0.0.min.js');
			}
		}
		else
		{
			JHtml::_('jquery.framework');
		}
		
		$document->addScript(JUri::root( true ).'/media/egolttooltip/js/egolttooltip.js');
		$document->addStyleSheet(JUri::root( true ).'/media/egolttooltip/css/egolttooltip.css');
		
		// $styled = 
		// '
		// ';
		// $document->addStyleDeclaration($styled);
		
		// $param['theme'] = 'noir';
		// $this->setJS($param);
		$document->addScriptDeclaration('jQuery.noConflict();');
		if($this->_params->get('htmlselector', '.egtip'))
		{
			$document->addScriptDeclaration($this->setJS());
		}
	}
	
	// function onAfterDispatch()
	// {
		// Future method ...
	// }

	function onAfterRender()
	{
		// Check if tooltip is active
		if(!$this->isActive()) 
			return;
		
		// HTML Output Filter
		if (JFactory::getDocument()->getType() !== 'html' && JFactory::getDocument()->getType() !== 'feed') {
			return;
		}

		$html = JResponse::getBody();
		if ($html == '') {
			return;
		}
				
		if (strpos($html, '{egtip') !== false) {
			$regex = '#\{egtip((?: |&nbsp;|&\#160;|<)(?:[^\}]*\{[^\}]*\})*[^\}]*)\}(.*?)\{/egtip\}#s';
			if (preg_match_all($regex, $html, $matches, PREG_SET_ORDER) > 0) {
				// die(var_dump($matches));
				foreach ($matches as $i => $match) {
					$params = array();
					$ptext = $match['1'];
					$text = $match['2'];
					
					// $tparams = explode(' ', $tparams);
					if(preg_match_all('/(tip|type|delay|animation|arrow|maxwidth|offsetx|offsety|trigger|position|speed)=("[^"]*")/i', $ptext, $tparams, PREG_SET_ORDER) > 0) {
						// die(var_dump($tparams));
						foreach($tparams as $tparam)
						{
							$param_name = $tparam[1];
							$param_value = trim($tparam[2], '"');
							$params[$param_name] = $param_value;
						}
					}
					if( !(array_key_exists('tip', $params) and @!empty($params['tip'])) )
						return;
								
					$content = strip_tags($params['tip']);
					// die($content);

					$randnum = uniqid();
					// $randnum = $i;
					$selector = 'egolttooltip-' . $randnum;
					$params['htmlselector'] = '#' . $selector;
					
					// if(strpos($params['title'], 'img') !== false) {
					// }
					
					if(array_key_exists('type', $params))
					{
						if($params['type']=='snap')
						{
							$snap = '';
							$snap_width = $this->_params->get('screenshot_width', '200');
							$snap_height = $this->_params->get('screenshot_height', '150');
							if($this->_params->get('screenshot_service', 'page2images') == 'page2images')
							{
								$key = $this->_params->get('api_t1');
								$snap = 'http://api.page2images.com/directlink?p2i_key='. $key .'&p2i_device=6&p2i_size='.$snap_width.'x'.$snap_height.'&p2i_url=' . $content;
							}
							elseif($this->_params->get('screenshot_service', 'page2images') == 'shrinktheweb')
							{
								$key = $this->_params->get('api_t2');
								$snap = 'http://images.shrinktheweb.com/xino.php?stwembed=1&stwxmax='.$snap_width.'&stwymax='.$snap_height.'&stwaccesskeyid='.$key.'&stwurl=' . $content;
							}
							elseif($this->_params->get('screenshot_service', 'page2images') == 'immediatenet')
							{
								$snap = 'http://immediatenet.com/t/m?Size=1024x768&URL=' . $content;
							}
							$content = '<img src=\''. $snap .'\' >';
						}
						elseif($params['type']=='image')
						{
							$content = '<img src=\''. $content .'\' >';	
						}
					}

					$js = $this->setJS($params);
					$jsdec =
					'<script type="text/javascript">' . $js . '</script>';
					
					$html = str_replace( $match['0'], $jsdec .'<span title="'.$content.'" id="'. $selector .'" >'.$text.'</span>', $html );
					// $html = str_replace( $match['0'], $jsdec .'<span id="'. $selector .'" >'.$text.'</span>', $html );
				}

			}
		}
		JResponse::setBody($html);
	}
	
	function setJS($params = null)
	{
		$document	= JFactory::getDocument();	
		if(isset($params) and is_array($params))
		{
			foreach($params as $name => $param)
			{
				$this->_params->set($name, $param);
			}
		}
		
		$jsdparams	= array();
		
		$jsdparams[]= "onlyone: 'false'";
		
		// if($content = $this->_params->get('content'))
		// {
			// $jsdparams[]= "content: '{$content}'";
		// }
		
		$animation	= $this->_params->get('animation', 'fade');
		$jsdparams[]= "animation: '{$animation}'";
		
		$arrow		= $this->_params->get('arrow', 1);
		$arrow		= $arrow ? true : false;
		$jsdparams[]= "arrow: '{$arrow}'";
		
		$arrowColor	= $this->_params->get('arrowcolor');
		if($arrowColor)
			$jsdparams[]= "arrowColor: '{$arrowColor}'";
		
		$delay		= (int) $this->_params->get('delay', 200);
		$jsdparams[]= "delay: '{$delay}'";
		
		$fixedwidth	= $this->_params->get('fixedwidth', 0);
		$jsdparams[]= "fixedWidth: '{$fixedwidth}'";
		
		$maxWidth	= $this->_params->get('maxwidth', 0);
		$jsdparams[]= "maxWidth: '{$maxWidth}'";
		
		$interactive	= $this->_params->get('interactive', 0);
		$interactive	= $interactive ? true : false;
		$jsdparams[]= "interactive: '{$interactive}'";
		
		$interactive2l	= $this->_params->get('interactive2l', 350);
		$jsdparams[]= "interactiveTolerance: '{$interactive2l}'";
		
		$interactiveac	= $this->_params->get('interactiveac', 1);
		$interactiveac	= $interactiveac ? true : false;
		$jsdparams[]= "interactiveAutoClose: '{$interactiveac}'";
		
		$offsetx	= $this->_params->get('offsetx', 0);
		$jsdparams[]= "offsetX: '{$offsetx}'";
		
		$offsety	= $this->_params->get('offsety', 0);
		$jsdparams[]= "offsetY: '{$offsety}'";
		
		$position	= $this->_params->get('position', 'top');
		$jsdparams[]= "position: '{$position}'";
		
		$speed		= $this->_params->get('speed', 350);
		$jsdparams[]= "speed: '{$speed}'";
		
		$timer		= $this->_params->get('timer', 0);
		$jsdparams[]= "timer: '{$timer}'";
		
		$theme		= $this->_params->get('theme', 'default');
		$jsdparams[]= "theme: '.egolttooltip-{$theme}'";
		if($theme != 'default') {
			$document->addStyleSheet(JUri::root(true) . '/media/egolttooltip/css/themes/'. $theme .'.css');
		}
		
		$touchdevices	= $this->_params->get('touchdevices', 1);
		$touchdevices	= $touchdevices ? true : false;
		$jsdparams[]= "touchDevices: {$touchdevices}";
		
		$trigger	= $this->_params->get('trigger', 'hover');
		$jsdparams[]= "trigger: '{$trigger}'";
		
		$updateanimation= $this->_params->get('updateanimation', 1);
		$updateanimation= $updateanimation ? true : false;
		$jsdparams[]= "updateAnimation: '{$updateanimation}'";
		
		$selector	= $this->_params->get('htmlselector', '.egolttooltip');
		$jsd = 
		"jQuery(document).ready(function() {
			jQuery('". $selector ."').not('.notooltip').egolttooltip({
				". implode(',', $jsdparams) ."
			});
		});	
		";
		return $jsd;	
	}
	
}
