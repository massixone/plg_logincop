# plg_logincop - Joomla! plugin to log and email site logins
This is security monitoring plugin gor Joomla! 
The plugin is triggered by any login attempt and can email a selected Joomla! User Group ether for successful or unsuccesfu login.

# Summary
Are you sure that who is logging in to your Joomla! website is allowed to do so? Well, whith this plugin you can monitor and keep track of any login attempt to your web site.

# Features
* Watch any frontend and backend logins
* Email to a specific Joomla! User Group on login
* Records successful and unsuccessful login

# Installation
The installation of the plugin is the same as with any other Joomla! extention. Go to your site's back-end Extensions -> Manage and click on Browse. Locate the ZIP package and click on Upload and Install.

After you install it for the first time, make sure you configure and activate the plugin, if it is not already activated. 

# Uninstallation
You can uninstall the plugin just like any other Joomla! component.

* In your site's back-end, just go to Extensions Manager and click on Uninstall.
* In the Filter area type "Login Cop" and click on Search.
* The login entry appear. Select the entry related to the plugin.
* Now click on Uninstall. This will completely remove extension including all related data.

# Configuration
## Section - Plugin
### Send notification by email
Here you can setup whether you want your site send notification emails. 
If you set "On", email notifictions are sent to the Joomla! User Group defined below
If you set "Off", no email notification is sent whatsoever.

##  Successful Login
By answering "On", the plugin will send notification emails for any Successgul login.
By answering "Off", the plugin will prevent sending notification emails for any successful login.

##  Unsuccesful Login
By answering "On", the plugin will send notification emails for any Unsuccessgul login.
By answering "Off", the plugin will prevent sending notification emails for any Unsuccessful login.

##  User Group
Select the Joomla! User Group where notification email have to be sent.

##  Watch Super Users
By answering "On", the plugin will send notification emails also for administrator login attempt.
By answering "Off", Administrator login will not be watched.

## Section - Database Settings
### Enable DB Recording
By answering "On", database recording will be activated. All login attempts (either successful or unsuccessful) will be recorded into the database.
By answering "Off", database recording will not be activated. No login attempt (neither successful nor unsuccessful) will be recorded in the database.

### Timed Limit
This is the limit in time of the database records being recorded into the database itself. This means that you can keep in your database this amount of days of login records.
Please note that, when "Timed limit" is reached oldest records will be reoved from the database table, if they are oldest that this number of days.
Default: 30 (days).

### Record Limit
This is the limit in terms of number of database records being recorded into the database table. This means that you can keep in your database this number of login records. 
Please note that, when "Record limit" is reached, for any new record inserted into the database table oldest record will be reoved from the database table.
Default: 3000 (table records)

### Successful logins
By answering "On", Successful logins will be recorded into the database table.
By answering "Off", Successful logins will NOT be recorded into the database table.

### Unsuccessful logins
By answering "On", Unsuccessful logins will be recorded into the database table.
By answering "Off", Unsuccessful logins will NOT be recorded into the database table.

## Section - Email Settings
### Email content format 
By answering "Html", The notification email body will be composed in Html format.
By answering "Plain-Text", The notification email will be composed in plain/text format.

### Add login Username
By answering "On", The notification email body will contain the username used in the login form.
By answering "Off", The notification email body will NOT contain the username used in the login form.

### Add User-ID
By answering "On", The notification email body will contain the Joomla User-ID corresponding to the username used in the login form.
By answering "Off", The notification email body will NOT contain the Joomla User-ID corresponding to the username used in the login form.

### Add Login Timestamp
By answering "On", The notification email body will contain the login timestamp of the time when the login happened.
By answering "Off", The notification email body will NOT contain the login timestamp of the time when the login happened.

### Add IP Address
By answering "On", The notification email body will contain th IP Address of the Client which attempted the login.
By answering "Off", The notification email body will NOT contain the email body will IP Address of the Client which attempted the login.
