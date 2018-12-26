(function( $ ) {
	'use strict';

	window.addEventListener("message", receiveMessage, false);

	function receiveMessage(event) {
		if (event.data === "CloseWidget") {
			var urlStr = $(".kyber-widget-button").attr("href");
			var url = new URL(urlStr)
			var orderNumber = url.searchParams.get("order_id");
			// window.location.href = "/my-account/view-order/"+orderNumber;
			window.location.reload();
		}
	}

	$(document).ready(function() {
		setTimeout(function() {
			window.kyberWidgetOptions.onCloseCallBack = function() {
				var broadcasted = window.kyberWidgetOptions.isBroadcasted;
				if (broadcasted) {
					location.reload();
				}
			}
		}, 0)
	})

})( jQuery );
