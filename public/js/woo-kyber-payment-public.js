(function( $ ) {
	'use strict';

	window.kyberWidgetOptions = {
		onClose: function() {
			window.location.href = "/my-account/orders/";
		}
	}

	window.addEventListener("message", receiveMessage, false);

	function receiveMessage(event) {
		if (event.data === "CloseWidget") {
			var urlStr = $(".kyber-widget-button").attr("href");
			var url = new URL(urlStr)
			var orderNumber = url.searchParams.get("order_id");
			window.location.href = "/my-account/view-order/"+orderNumber;
		}
	}

})( jQuery );
