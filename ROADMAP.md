#Roadmap

This branch holds unstable code for future versions of MyAlerts. It will be merged with Master when we reach a version number milestone.

##Planned changes

The version currently under development is a major milestone. Below are the planned changes:

* Restructuring of JavaScript to be class based and more usable. This approach will make maintenance more bearable in the future and will also allow developers to extend the JavaScript as they extend the core.
* Restructuring of alerts_settings table.
    * Adding of "enabled column"
    * Opt-out of alerts rather than opt in - enable all alerts by default
    * No more need to add settings to the myalerts setting group for developers
* Addition of Admin Control Panel (ACP) module to control the alert types available and prune alerts.
* Weekly "alert digest" email task to send out emails to users with more than _x_ alerts (where x is a configurable setting) that are unread in their inbox.
* Easier developer API
    * AlertManager class to allow devs to easily add, edit and remove their alerts
    * Methods within AlertManager to check if a user has a type of alert enabled (and its enabled in the system)
    * Registering of language strings so that developers can optionally hook in to affect the alert output rather than being forcd to in order to output their alerts.

##Contributing

As ever, my development is patchy. Rather than concentrate on one thing, I have started many. The key priority right now, however, is sorting out and testing the new JavaScript. This is a significant change that must be tested fully.

We also need to restructure the documentation to reflect the new changes.
