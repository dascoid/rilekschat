<?php

	function sys_api( $access_module="" )
	{
		$err_msg = "";
		$class = "";

		$list_modules = array( 
			'API',
			'AUTH',
			'WA',
			'USERS',
			'CONTACTS',
			'IMPORT',
			'TEMPLATES',
			'RECEIVERS',
			'BROADCASTS',
			'DASHBOARD'
		);

		if ( in_array( $access_module, $list_modules ) )
		{
			$classname = "App\Http\Controllers\\{$access_module}\Helper";
			$class = new $classname();
		}
		else
		{
			$err_msg .= "Access for $access_module is denied, please contact your administrator";
		}

		echo $err_msg;
		return $class;
	}
