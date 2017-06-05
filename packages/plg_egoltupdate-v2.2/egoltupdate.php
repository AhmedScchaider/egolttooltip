<?php
/**
 * @package   	Egolt Update
 * @link 		http://www.egolt.com
 * @copyright 	Copyright (C) Egolt Foundation - www.egolt.com
 * @author    	Soheil Novinfard
 * @license    	GNU/GPL 2
 */

defined('_JEXEC') or die();

class plgSystemEgoltUpdate extends JPlugin
{
    public function onAfterRoute()
    {
		$app = JFactory::getApplication();
		if(
			$app->input->get('option') == 'com_installer' 
			&& 
			$app->input->get('view') == 'update' 
			&&
			$app->isAdmin()
		)
		{
			if(version_compare(JVERSION, '3.0', 'ge'))
				require_once(dirname(__FILE__) . '/update-j3.php');
			else
				require_once(dirname(__FILE__) . '/update.php');
		}
    }
}