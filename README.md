== Usage ==
=== Creating Custom Logs ===
* Create the page MediaWiki:Customlogs. It should be a comma-separated list of log names.
* For this example we'll say you want to create a log named "kittens"
* Create the page MediaWiki:Log-name-kittens with the name of the log, for example "Kitten Log"
* Create the page MediaWiki:Logentry-custom-kittens with the display text for the log
** You may specify parameters $1, $2, etc
** $1 is User (with markup)
** $2 is User
** $3 is Page
** $4 etc may be specified by you, the limit is given by $wgCustomLogsMaxCustomParams (default 3, so $4 through $6)
=== Writing Custom Logs ===
* This can be done via the api action customlogswrite
* Ability to write custom logs is governed by the right writecustomlogs, default available to anyone
* For different logs, parameters custom-1, custom-2, etc may have different meanings, but this numbering system was chosen to avoid retaining deprecated API parameters for logs that are no longer in use
* Can decide to publish the log in recent changes or not

== Development ==
Planned is eventually to have an interface similar to Tags where you can add and remove logs, and stop using MediaWiki namespace for things; this would also allow for a right for creating and removing logs separate from sysop. However the extension is functional as-is so that's not high priority currently.