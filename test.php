<?php

//删除默认端点
	remove_action( 'rest_api_init', 'create_initial_rest_routes', 0 );