###
MyAlerts core class
###

class MyAlerts
	pollTime : 5
	dropdownAnchor : "#myalerts_dropdown"
	numAlertsInDropdown : 5
	myalertsPath : "xmlhttp.php"

	constructor : ->
		# Run when instantiated
		alert "Myalerts.js is ready"

	getAlerts : (callback) =>
		# Make sure callback is a callback function
		callback = new Function(callback) if typeof callback isnt "function"

		$.ajax(
			@xmlhttp
			{
				cache: false
				dataType : "json"
				type : "POST"
				data : {
					action : "getAlerts"
					limit : @numAlertsInDropdown
				}
				success : callback
				error : @ajaxErrorHandler
			}
		)
		return

	updateDropdown : (data) =>
		$(document).trigger("onUpdateDropdown", [data, this]);
		#! Update the dropdown's alert listing
		return

	updateListing : (data) =>
		$(document).trigger("onUpdateListing", [data, this]);
		$('#latestAlertsListing').prepend(data.template);
		return

	deleteAlert : (alertId = 0, callback) ->
		# Make sure alertId is an integer greater than 0
		alertId = parseInt(alertId, 10)
		if alertId < 1
			return

		# Make sure callback is a callback function
		callback = new Function(callback) if typeof callback isnt "function"

		$.ajax(
			@xmlhttp
			{
				cache : false
				dataType : "json"
				type : "POST"
				data : {
					id : alertId
				}
				success : callback
				error : @ajaxErroHandler
			}
		)
		return

	markRead : (alertIds = [], callback) =>
		# Make sure that alertIds is either an array of at least 1 entry or a single integer
		if not alertIds instanceof Array
			alertIds = parseInt(alertIds, 10)
			if alertIds < 1
				return
		else if alertIds.length < 1
			return

		# Make sure callback is a callback function
		callback = new Function(callback) if typeof callback isnt "function"

		$.ajax(
			@xmlhttp
			{
				cache : false
				dataType : "json"
				type : "POST"
				data : {
					toMarkRead : alertIds
				}
				success : callback
				error : @ajaxErrorHandler
			}
		)

		return

	ajaxErrorHandler : (jqXHR, textStatus, errorThrown) ->
		console.error textStatus
		console.error errorThrown
		return

	updateTitle : (alertCount = 0) ->
		alertCount = 0 if alertCount < 0

		if alertCount isnt 0
			document.title = "(#{alertCount}) #{document.title}"

		return
