/*
MyAlerts core class
*/

(function() {
  var MyAlerts,
    __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

  MyAlerts = (function() {
    MyAlerts.prototype.pollTime = 5;

    MyAlerts.prototype.dropdownAnchor = "#myalerts_dropdown";

    MyAlerts.prototype.numAlertsInDropdown = 5;

    MyAlerts.prototype.xmlhttp = "xmlhttp.php";

    function MyAlerts() {
      this.pollForAlerts = __bind(this.pollForAlerts, this);
      this.markRead = __bind(this.markRead, this);
      this.updateListing = __bind(this.updateListing, this);
      this.updateDropdown = __bind(this.updateDropdown, this);
      this.getAlerts = __bind(this.getAlerts, this);
      var _this = this;
      $('#getUnreadAlerts').on('click', function(event) {
        event.preventDefault();
        _this.getAlerts(_this.updateListing);
      });
      if (this.pollTime > 0) {
        this.pollForAlerts();
      }
      $('.deleteAlertButton').on('click', function(event) {
        var deleteButton;
        event.preventDefault();
        deleteButton = $(this);
        _this.deleteAlert(deleteButton.data("alert-id"), function(data) {
          if (data.success) {
            deleteButton.parents("tr").get(0).remove();
            if (data.template) {
              $("#latestAlertsListing").html(data.template);
            }
          } else {
            console.error(data.error);
          }
        });
      });
      return;
    }

    MyAlerts.prototype.getAlerts = function(callback) {
      if (typeof callback !== "function") {
        callback = new Function(callback);
      }
      $.ajax(this.xmlhttp, {
        cache: false,
        dataType: "json",
        type: "POST",
        data: {
          action: "getAlerts",
          limit: this.numAlertsInDropdown
        },
        success: callback,
        error: this.ajaxErrorHandler
      });
    };

    MyAlerts.prototype.updateDropdown = function(data) {
      $(document).trigger("onUpdateDropdown", [data, this]);
    };

    MyAlerts.prototype.updateListing = function(data) {
      $(document).trigger("onUpdateListing", [data, this]);
      $('#latestAlertsListing').prepend(data.template);
    };

    MyAlerts.prototype.deleteAlert = function(alertId, callback) {
      if (alertId == null) {
        alertId = 0;
      }
      alertId = parseInt(alertId, 10);
      if (alertId < 1) {
        return;
      }
      if (typeof callback !== "function") {
        callback = new Function(callback);
      }
      $.ajax(this.xmlhttp, {
        cache: false,
        dataType: "json",
        type: "POST",
        data: {
          id: alertId
        },
        success: callback,
        error: this.ajaxErroHandler
      });
    };

    MyAlerts.prototype.markRead = function(alertIds, callback) {
      if (alertIds == null) {
        alertIds = [];
      }
      if (!alertIds instanceof Array) {
        alertIds = parseInt(alertIds, 10);
        if (alertIds < 1) {
          return;
        }
      } else if (alertIds.length < 1) {
        return;
      }
      if (typeof callback !== "function") {
        callback = new Function(callback);
      }
      $.ajax(this.xmlhttp, {
        cache: false,
        dataType: "json",
        type: "POST",
        data: {
          toMarkRead: alertIds
        },
        success: callback,
        error: this.ajaxErrorHandler
      });
    };

    MyAlerts.prototype.ajaxErrorHandler = function(jqXHR, textStatus, errorThrown) {
      console.error(textStatus);
      console.error(errorThrown);
    };

    MyAlerts.prototype.pollForAlerts = function() {
      setTimeout(this.getAlerts(this.updateListing), this.pollTime * 1000);
    };

    MyAlerts.prototype.updateTitle = function(alertCount) {
      if (alertCount == null) {
        alertCount = 0;
      }
      if (alertCount < 0) {
        alertCount = 0;
      }
      if (alertCount !== 0) {
        document.title = "(" + alertCount + ") " + document.title;
      }
    };

    return MyAlerts;

  })();

}).call(this);
