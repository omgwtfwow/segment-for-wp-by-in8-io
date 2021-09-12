(function( $ ) {

	function get_cookie_names() {
		return document.cookie.split(/=[^;]*(?:;\s*|$)/);
	}

	function delete_segment_4_wp_cookies() {
		let pattern = new RegExp(/^segment_4_wp_/);
		let cookie_names = get_cookie_names();
		for (let i = 0; i < cookie_names.length; i++) {
			let cookie_name = cookie_names[i];
			if (pattern.test(cookie_name)) {
				Cookies.set(cookie_name, '');
				Cookies.remove(cookie_name);

			}
		}

	}

	function delete_cookie(event) {


		let reg_1 = new RegExp(/^segment_4_wp_/);
		let reg_2 = event;
		let reg_3 = '_';

		let pattern = new RegExp(reg_1.source + reg_2 + reg_3);

		let cookie_names = get_cookie_names();
		for (let i = 0; i < cookie_names.length; i++) {
			if (pattern.test(cookie_names[i])) {
				let cookie_name = cookie_names[i];
				Cookies.set(cookie_name, '');
				Cookies.remove(cookie_name);
			}
		}
	}

	$(function () {

		analytics.ready(function () {

			// analytics.on('track', function (event, properties, options) {
			//
			// 	if (wp_ajax.custom_js_events.includes(event)) {
			//
			// 		$.ajax({
			// 			cache: false,
			// 			type: "POST",
			// 			url: wp_ajax.ajax_url,
			// 			data: {
			// 				'action': 'public_ajax_track',
			// 				'nonce': wp_ajax._nonce,
			// 				'event': event
			// 			},
			// 			success: function (response) {
			// 				if(response) {
			// 					if ('user_id' in response.data) {
			// 						if (response.data.event==='identify'){
			// 							let user_id = response.data.user_id;
			// 							let traits = response.data.traits;
			// 							analytics.identify(user_id, traits);
			// 						}
			// 					}
			// 				}
			//
			// 			},
			// 			error: function (xhr, status, error) {
			// 				console.log('Status: ' + xhr.status);
			// 				console.log('Error: ' + xhr.responseText);
			// 			}
			// 		});
			//
			// 	}
			//
			// 	delete_cookie(event);
			//
			// });

			// analytics.on('identify', function (event, properties, options) {
			//
			// 	console.log('identified')
			// 	console.log(event)
			// 	// delete_cookie(event);
			//
			// });

			// setTimeout(function(){
			//     //delete all cookies regardless, minimise double counting if there are bugs or user errors
			//     delete_segment_4_wp_cookies();
			// }, 1000);

		});

	});

})( jQuery );
