<?php

// Function to check authentication
function bs_auth_required( callable $callback ): Closure {
	return function ( $request ) use ( $callback ) {
		$blogstorm_auth_string = $request->get_header( 'blogstorm-auth' );

		if ( ! $blogstorm_auth_string ) {
			return new WP_Error( 'error', 'No authentication header provided' );
		}

		if ( $blogstorm_auth_string !== get_option( BS_TOKEN_NAME ) ) {
			return new WP_Error( 'error', 'Invalid authentication header provided' );
		}

		return $callback( $request );
	};
}