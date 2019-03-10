(function( $ ) {
	'use strict';

	/**
	 * Checks if the given string is an address
	 *
	 * @method isAddress
	 * @param {String} address the given HEX adress
	 * @return {Boolean}
	*/
	var isAddress = function (address) {
		if (!/^(0x)?[0-9a-f]{40}$/i.test(address)) {
			// check if it has the basic requirements of an address
			return false;
		} else if (/^(0x)?[0-9a-f]{40}$/.test(address) || /^(0x)?[0-9A-F]{40}$/.test(address)) {
			// If it's all small caps or all all caps, return true
			return true;
		} else {
			// Otherwise check each case
			return isChecksumAddress(address);
		}
	};

	/**
	 * Checks if the given string is a checksummed address
	 *
	 * @method isChecksumAddress
	 * @param {String} address the given HEX adress
	 * @return {Boolean}
	*/
	var isChecksumAddress = function (address) {
		// Check each case
		address = address.replace('0x','');
		var hash = keccak256.create();
		var addressHash = hash.update(address.toLowerCase()).hex();
		console.log("address hash: ", addressHash);
		for (var i = 0; i < 40; i++ ) {
			// the nth letter should be uppercase if the nth digit of casemap is 1
			if ((parseInt(addressHash[i], 16) > 7 && address[i].toUpperCase() !== address[i]) || (parseInt(addressHash[i], 16) <= 7 && address[i].toLowerCase() !== address[i])) {
				return false;
			}
		}
		return true;
	};

	var validBlockConfirmation = function (blockconfirmation) {
		return blockconfirmation > 0
	}
	
	$(document).ready(function() {
		 $(".supported-tokens").select2(); 

		//  $(".woocommerce-save-button").click(function(e) {
		// 	// check if current url is section kyber
		// 	// this part only run if it is in kyber section
		// 	var url = new URL(window.location.href);

		// 	if (url.searchParams.get("section") == "kyber") {
		// 		e.preventDefault();
		// 		// remove old error message
		// 		$(".receive_address_empty").remove();
		// 		$(".receive_address_invalid").remove();
		// 		$(".block_confirmation_invalid").remove();

		// 		// do validation
		// 		var formValid = true
		// 		var receiveAddr = $("#woocommerce_kyber_receive_addr").val()
		// 		console.log(receiveAddr);

		// 		if ( !isAddress(receiveAddr) ) {
		// 			formValid = false;
		// 			$("#woocommerce_kyber_receive_addr").css("border-color", "red");
					
		// 			if (receiveAddr == "") {
		// 				console.log("receive address is empty")
		// 				$("#woocommerce_kyber_receive_addr").after("<p class='receive_address_empty' style='color: red; font-style: italic;'>Receive Address should not be empty.</p>");
		// 			} else {
		// 				console.log("invalid ethereum address");
		// 				$("#woocommerce_kyber_receive_addr").after("<p class='receive_address_invalid' style='color: red; font-style: italic;'>Receive Address is not valid.</p>");
		// 			}
		// 		}

		// 		var blockconfirmation = $("#woocommerce_kyber_block_confirmation").val()
		// 		console.log(blockconfirmation)
		// 		if ( !validBlockConfirmation(blockconfirmation) ) {
		// 			formValid = false;
		// 			$("#woocommerce_kyber_block_confirmation").css("border-color", "red");
		// 			$("#woocommerce_kyber_block_confirmation").after("<p class='block_confirmation_invalid' style='color:red; font-style: italic;'>Block confirmation must greater than 0</p>");
		// 		}
				
		// 		if (formValid) {
		// 			$("#mainform").submit();
		// 		}
	// 		}
	// 	 })
	});
})( jQuery );
