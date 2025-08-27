[1mdiff --git a/modules/membership/includes/Admin/Services/Paypal/PaypalService.php b/modules/membership/includes/Admin/Services/Paypal/PaypalService.php[m
[1mindex 78b0ea56..04761032 100644[m
[1m--- a/modules/membership/includes/Admin/Services/Paypal/PaypalService.php[m
[1m+++ b/modules/membership/includes/Admin/Services/Paypal/PaypalService.php[m
[36m@@ -575,6 +575,75 @@[m [mclass PaypalService {[m
 			'message' => $message,[m
 		);[m
 	}[m
[32m+[m	[32m/**[m
[32m+[m	[32m * Reactivates already cancelled subscription.[m
[32m+[m	[32m * @param $subscription_id Subscription Id.[m
[32m+[m	[32m */[m
[32m+[m	[32mpublic function reactivate_subscription( $subscription_id ) {[m
[32m+[m		[32m$paypal_options['mode']          = get_option( 'user_registration_global_paypal_mode', 'test' );[m
[32m+[m		[32m$paypal_options['client_id']     = get_option( 'user_registration_global_paypal_client_id', '' );[m
[32m+[m		[32m$paypal_options['client_secret'] = get_option( 'user_registration_global_paypal_client_secret', '' );[m
[32m+[m		[32m$client_id     = $paypal_options['client_id'];[m
[32m+[m		[32m$client_secret = $paypal_options['client_secret'];[m
[32m+[m		[32m$url           = ( 'production' === $paypal_options['mode'] ) ? 'https://api-m.paypal.com/' : 'https://api-m.sandbox.paypal.com/';[m
[32m+[m
[32m+[m		[32m$login_request = self::login_paypal( $url, $client_id, $client_secret );[m
[32m+[m
[32m+[m		[32mif( 200 !== $login_request[ 'status_code' ] ) {[m
[32m+[m			[32m$message = esc_html__( 'Invalid response from paypal, check Client ID or Secret.', 'user-registration' );[m
[32m+[m			[32mur_get_logger()->notice( $message, array( 'source' => 'ur-membership-paypal' ) );[m
[32m+[m
[32m+[m			[32mreturn array([m
[32m+[m				[32m'status'  => false,[m
[32m+[m				[32m'message' => $message,[m
[32m+[m			[32m);[m
[32m+[m		[32m}[m
[32m+[m		[32m$url .= sprintf( 'v1/billing/subscriptions/%s/activate', $subscription_id );[m
[32m+[m
[32m+[m		[32m$bearerToken = $login_request['access_token'];[m
[32m+[m
[32m+[m		[32m$headers = array([m
[32m+[m			[32m'Content-Type: application/json',[m
[32m+[m			[32m'Accept: application/json',[m
[32m+[m			[32m'Authorization: Bearer ' . $bearerToken,[m
[32m+[m		[32m);[m
[32m+[m		[32m$data = json_encode( array([m
[32m+[m			[32m'reason' => 'User initiated reactivation'[m
[32m+[m		[32m) );[m
[32m+[m		[32m$ch      = curl_init();[m
[32m+[m		[32mcurl_setopt( $ch, CURLOPT_URL, $url );[m
[32m+[m		[32mcurl_setopt( $ch, CURLOPT_POST, true );[m
[32m+[m		[32mcurl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );[m
[32m+[m		[32mcurl_setopt( $ch, CURLOPT_POSTFIELDS, $data );[m
[32m+[m		[32mcurl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );[m
[32m+[m
[32m+[m		[32m$response = curl_exec( $ch );[m
[32m+[m		[32m$result   = json_decode( $response );[m
[32m+[m
[32m+[m		[32m$status_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );[m
[32m+[m
[32m+[m		[32mif ( curl_errno( $ch ) ) {[m
[32m+[m			[32mur_get_logger()->notice( curl_error( $ch ), array( 'source' => 'ur-membership-paypal' ) );[m
[32m+[m		[32m}[m
[32m+[m		[32mcurl_close( $ch );[m
[32m+[m		[32mur_get_logger()->notice( 'Paypal Response Status Code: ' . $status_code, array( 'source' => 'ur-membership-paypal' ) );[m
[32m+[m
[32m+[m		[32mif ( 204 === $status_code ) {[m
[32m+[m			[32m$message = esc_html__( 'Subscription successfully reactivated from paypal.', 'user-registration' );[m
[32m+[m			[32mur_get_logger()->notice( $message, array( 'source' => 'ur-membership-paypal' ) );[m
[32m+[m
[32m+[m			[32mreturn array([m
[32m+[m				[32m'status' => true,[m
[32m+[m			[32m);[m
[32m+[m		[32m}[m
[32m+[m		[32m$message = esc_html__( 'Subscription reactivation failed from Paypal.', 'user-registration' );[m
[32m+[m		[32mur_get_logger()->notice( $message, array( 'source' => 'ur-membership-paypal' ) );[m
[32m+[m
[32m+[m		[32mreturn array([m
[32m+[m			[32m'status'  => false,[m
[32m+[m			[32m'message' => $message,[m
[32m+[m		[32m);[m
[32m+[m	[32m}[m
 [m
 	/**[m
 	 * validate_ipn[m
[1mdiff --git a/modules/membership/includes/Admin/Services/Stripe/StripeService.php b/modules/membership/includes/Admin/Services/Stripe/StripeService.php[m
[1mindex 406098f3..821c72a7 100644[m
[1m--- a/modules/membership/includes/Admin/Services/Stripe/StripeService.php[m
[1m+++ b/modules/membership/includes/Admin/Services/Stripe/StripeService.php[m
[36m@@ -580,7 +580,10 @@[m [mclass StripeService {[m
 		}[m
 [m
 		$subscription = \Stripe\Subscription::retrieve( $subscription['subscription_id'] );[m
[31m-		$deleted_sub  = $subscription->cancel();[m
[32m+[m		[32m$deleted_sub = \Stripe\Subscription::update([m
[32m+[m			[32m$subscription['subscription_id'],[m
[32m+[m			[32m[ 'cancel_at_period_end' => true ],[m
[32m+[m		[32m);[m
 		if ( '' !== $deleted_sub['canceled_at'] ) {[m
 			$response['status'] = true;[m
 		}[m
[36m@@ -589,6 +592,49 @@[m [mclass StripeService {[m
 [m
 		return $response;[m
 	}[m
[32m+[m	[32m/**[m
[32m+[m	[32m * Reactivates stripe subscription if it has been soft cancelled.[m
[32m+[m	[32m *[m
[32m+[m	[32m * @param $subscription_id Stripe's Subscription Id.[m
[32m+[m	[32m *[m
[32m+[m	[32m * @return $response array Response with status flag and message.[m
[32m+[m	[32m */[m
[32m+[m	[32mpublic function reactivate_subscription( $subscription_id ) {[m
[32m+[m		[32m$response = array([m
[32m+[m			[32m'status' => false,[m
[32m+[m		[32m);[m
[32m+[m		[32m$subscription = \Stripe\Subscription::retrieve( $subscription_id );[m
[32m+[m		[32mif( isset( $subscription->id ) ) {[m
[32m+[m			[32mif( 'active' === $subscription->status ) {[m
[32m+[m				[32mreturn array([m
[32m+[m					[32m'status' => true,[m
[32m+[m					[32m'message' => __( 'Subscription reactivated successfully.', 'user-registration' ),[m
[32m+[m				[32m);[m
[32m+[m			[32m}[m
[32m+[m			[32melseif( 'canceled' !== $subscription->status && true === $subscription->cancel_at_period_end ) {[m
[32m+[m				[32m$subscription = \Stripe\Subscription::update([m
[32m+[m					[32m$subscription_id,[m
[32m+[m					[32m[ 'cancel_at_period_end' => false, ][m
[32m+[m				[32m);[m
[32m+[m				[32mreturn array([m
[32m+[m					[32m'status' => true,[m
[32m+[m					[32m'message' => __( 'Subscription reactivated successfully.', 'user-registration' ),[m
[32m+[m				[32m);[m
[32m+[m			[32m}[m
[32m+[m			[32melse {[m
[32m+[m				[32mur_get_logger()->info( sprintf( 'Subscription %s with status %s is not reactivable in stripe end.', $subscription->status , $subscription_id ),  );[m
[32m+[m			[32m}[m
[32m+[m		[32m} else {[m
[32m+[m			[32mur_get_logger()->info( sprintf( 'Subscription %s not found in stripe end.', $subscription_id ), 'ur-membership-stripe' );[m
[32m+[m			[32mwp_send_json_error([m
[32m+[m				[32marray([m
[32m+[m					[32m'message' => __( 'Error reactivating the stripe subscription.', 'user-registration' ),[m
[32m+[m				[32m)[m
[32m+[m			[32m);[m
[32m+[m		[32m}[m
[32m+[m
[32m+[m		[32mreturn $response;[m
[32m+[m	[32m}[m
 [m
 	public function handle_webhook( $event, $subscription_id ) {[m
 		switch ( $event['type'] ) {[m
[1mdiff --git a/reactivate.patch b/reactivate.patch[m
[1mdeleted file mode 100644[m
[1mindex cf08be12..00000000[m
[1m--- a/reactivate.patch[m
[1m+++ /dev/null[m
[36m@@ -1,147 +0,0 @@[m
[31m-diff --git a/modules/membership/includes/Admin/Services/Paypal/PaypalService.php b/modules/membership/includes/Admin/Services/Paypal/PaypalService.php[m
[31m-index 97e956a2..bdf19b2b 100644[m
[31m---- a/modules/membership/includes/Admin/Services/Paypal/PaypalService.php[m
[31m-+++ b/modules/membership/includes/Admin/Services/Paypal/PaypalService.php[m
[31m-@@ -576,6 +576,75 @@ class PaypalService {[m
[31m- 			'message' => $message,[m
[31m- 		);[m
[31m- 	}[m
[31m-+	/**[m
[31m-+	 * Reactivates already cancelled subscription.[m
[31m-+	 * @param $subscription_id Subscription Id.[m
[31m-+	 */[m
[31m-+	public function reactivate_subscription( $subscription_id ) {[m
[31m-+		$paypal_options['mode']          = get_option( 'user_registration_global_paypal_mode', 'test' );[m
[31m-+		$paypal_options['client_id']     = get_option( 'user_registration_global_paypal_client_id', '' );[m
[31m-+		$paypal_options['client_secret'] = get_option( 'user_registration_global_paypal_client_secret', '' );[m
[31m-+		$client_id     = $paypal_options['client_id'];[m
[31m-+		$client_secret = $paypal_options['client_secret'];[m
[31m-+		$url           = ( 'production' === $paypal_options['mode'] ) ? 'https://api-m.paypal.com/' : 'https://api-m.sandbox.paypal.com/';[m
[31m-+[m
[31m-+		$login_request = self::login_paypal( $url, $client_id, $client_secret );[m
[31m-+[m
[31m-+		if( 200 !== $login_request[ 'status_code' ] ) {[m
[31m-+			$message = esc_html__( 'Invalid response from paypal, check Client ID or Secret.', 'user-registration' );[m
[31m-+			ur_get_logger()->notice( $message, array( 'source' => 'ur-membership-paypal' ) );[m
[31m-+[m
[31m-+			return array([m
[31m-+				'status'  => false,[m
[31m-+				'message' => $message,[m
[31m-+			);[m
[31m-+		}[m
[31m-+		$url .= sprintf( 'v1/billing/subscriptions/%s/activate', $subscription_id );[m
[31m-+[m
[31m-+		$bearerToken = $login_request['access_token'];[m
[31m-+[m
[31m-+		$headers = array([m
[31m-+			'Content-Type: application/json',[m
[31m-+			'Accept: application/json',[m
[31m-+			'Authorization: Bearer ' . $bearerToken,[m
[31m-+		);[m
[31m-+		$data = json_encode( array([m
[31m-+			'reason' => 'User initiated reactivation'[m
[31m-+		) );[m
[31m-+		$ch      = curl_init();[m
[31m-+		curl_setopt( $ch, CURLOPT_URL, $url );[m
[31m-+		curl_setopt( $ch, CURLOPT_POST, true );[m
[31m-+		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );[m
[31m-+		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );[m
[31m-+		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );[m
[31m-+[m
[31m-+		$response = curl_exec( $ch );[m
[31m-+		$result   = json_decode( $response );[m
[31m-+[m
[31m-+		$status_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );[m
[31m-+[m
[31m-+		if ( curl_errno( $ch ) ) {[m
[31m-+			ur_get_logger()->notice( curl_error( $ch ), array( 'source' => 'ur-membership-paypal' ) );[m
[31m-+		}[m
[31m-+		curl_close( $ch );[m
[31m-+		ur_get_logger()->notice( 'Paypal Response Status Code: ' . $status_code, array( 'source' => 'ur-membership-paypal' ) );[m
[31m-+[m
[31m-+		if ( 204 === $status_code ) {[m
[31m-+			$message = esc_html__( 'Subscription successfully reactivated from paypal.', 'user-registration' );[m
[31m-+			ur_get_logger()->notice( $message, array( 'source' => 'ur-membership-paypal' ) );[m
[31m-+[m
[31m-+			return array([m
[31m-+				'status' => true,[m
[31m-+			);[m
[31m-+		}[m
[31m-+		$message = esc_html__( 'Subscription reactivation failed from Paypal.', 'user-registration' );[m
[31m-+		ur_get_logger()->notice( $message, array( 'source' => 'ur-membership-paypal' ) );[m
[31m-+[m
[31m-+		return array([m
[31m-+			'status'  => false,[m
[31m-+			'message' => $message,[m
[31m-+		);[m
[31m-+	}[m
[31m- [m
[31m- 	/**[m
[31m- 	 * validate_ipn[m
[31m-diff --git a/modules/membership/includes/Admin/Services/Stripe/StripeService.php b/modules/membership/includes/Admin/Services/Stripe/StripeService.php[m
[31m-index e3b5ef6b..bcc213cc 100644[m
[31m---- a/modules/membership/includes/Admin/Services/Stripe/StripeService.php[m
[31m-+++ b/modules/membership/includes/Admin/Services/Stripe/StripeService.php[m
[31m-@@ -580,7 +580,11 @@ class StripeService {[m
[31m- 		}[m
[31m- [m
[31m- 		$subscription = \Stripe\Subscription::retrieve( $subscription['subscription_id'] );[m
[31m--		$deleted_sub  = $subscription->cancel();[m
[31m-+		// $deleted_sub  = $subscription->cancel();[m
[31m-+		$deleted_sub = \Stripe\Subscription::update([m
[31m-+			$subscription['subscription_id'],[m
[31m-+			[ 'cancel_at_period_end' => true ],[m
[31m-+		);[m
[31m- 		if ( '' !== $deleted_sub['canceled_at'] ) {[m
[31m- 			$response['status'] = true;[m
[31m- 		}[m
[31m-@@ -589,6 +593,49 @@ class StripeService {[m
[31m- [m
[31m- 		return $response;[m
[31m- 	}[m
[31m-+	/**[m
[31m-+	 * Reactivates stripe subscription if it has been soft cancelled.[m
[31m-+	 * [m
[31m-+	 * @param $subscription_id Stripe's Subscription Id.[m
[31m-+	 * [m
[31m-+	 * @return $response array Response with status flag and message.[m
[31m-+	 */[m
[31m-+	public function reactivate_subscription( $subscription_id ) {[m
[31m-+		$response = array([m
[31m-+			'status' => false,[m
[31m-+		);[m
[31m-+		$subscription = \Stripe\Subscription::retrieve( $subscription_id );[m
[31m-+		if( isset( $subscription->id ) ) {[m
[31m-+			if( 'active' === $subscription->status ) {[m
[31m-+				return array([m
[31m-+					'status' => true,[m
[31m-+					'message' => __( 'Subscription reactivated successfully.', 'user-registration' ),[m
[31m-+				);[m
[31m-+			}[m
[31m-+			elseif( 'canceled' !== $subscription->status && true === $subscription->cancel_at_period_end ) {[m
[31m-+				$subscription = \Stripe\Subscription::update([m
[31m-+					$subscription_id, [m
[31m-+					[ 'cancel_at_period_end' => false, ][m
[31m-+				);[m
[31m-+				return array([m
[31m-+					'status' => true,[m
[31m-+					'message' => __( 'Subscription reactivated successfully.', 'user-registration' ),[m
[31m-+				);[m
[31m-+			}[m
[31m-+			else {[m
[31m-+				ur_get_logger()->info( sprintf( 'Subscription %s with status %s is not reactivable in stripe end.', $subscription->status , $subscription_id ),  );[m
[31m-+			}[m
[31m-+		} else {[m
[31m-+			ur_get_logger()->info( sprintf( 'Subscription %s not found in stripe end.', $subscription_id ), 'ur-membership-stripe' );[m
[31m-+			wp_send_json_error([m
[31m-+				array([m
[31m-+					'message' => __( 'Error reactivating the stripe subscription.', 'user-registration' ),[m
[31m-+				)[m
[31m-+			);[m
[31m-+		}[m
[31m-+[m
[31m-+		return $response;[m
[31m-+	}[m
[31m- [m
[31m- 	public function handle_webhook( $event, $subscription_id ) {[m
[31m- 		switch ( $event['type'] ) {[m
