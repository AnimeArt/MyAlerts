###
MyAlerts core class
###

class MyAlerts
	pollTime : 5
	dropdownAnchor : "#myalerts_dropdown"
	numAlertsInDropdown : 5
	xmlhttp : "xmlhttp.php"

	constructor : ->
		# Run when instantiated

		# Get unread alerts when clicking on the link to do so
		$('#getUnreadAlerts').on('click', (event) =>
			event.preventDefault()
			@getAlerts @updateListing
			return
		)

		# Check for new alerts on a schedule (for the listing page only)
		@pollForAlerts() if @pollTime > 0

		$('.deleteAlertButton').on('click', (event) =>
			event.preventDefault()
			deleteButton = $(this)

			@deleteAlert(deleteButton.data("alert-id"), (data) =>
				if data.success
					deleteButton.parents("tr").get(0).remove()

					if data.template
						$("#latestAlertsListing").html(data.template)
				else
					console.error data.error

				return
			)
			return
		)
		# Need to wire up our dropdown. Maybe create a dropdown class? IDK what's happening with the MyBB core for 1.8 in terms of their dropdowns...
		return

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

	pollForAlerts : () =>
		setTimeout(@getAlerts(@updateListing), @pollTime * 1000)
		return

	updateTitle : (alertCount = 0) ->
		alertCount = 0 if alertCount < 0

		if alertCount isnt 0
			document.title = "(#{alertCount}) #{document.title}"

		return
