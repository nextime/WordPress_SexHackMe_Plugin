<?php

namespace wp_SexHackMe;


class SexHackPMSHelper
{

	public function __construct()
	{
		$this->plans = $this->get_pms_plantype();
	}

	public function get_pms_plantype()
	{
    	$plans = array(
       	'member' => array(),
       	'premium'=> array()
    	);
    	$splans=pms_get_subscription_plans(true);
    	foreach($splans as $splan)
    	{
       	if(intval($splan->price)==0) $plans['member'][] = $splan->id;
       	else $plans['premium'][] = $splan->id;
    	}
	 	return $plans;
	}

	public function is_member($uid='')
	{
		return pms_is_member( $uid, $this->plans['member'] );
	}

	public function is_premium($uid='')
	{
		return pms_is_member( $uid, $this->plans['premium'] );
	}
}


function instancePMSHelper() {
	$GLOBALS['sexhack_pms'] = new SexHackPMSHelper();
}


add_action('wp', 'wp_SexHackMe\instancePMSHelper');

?>
