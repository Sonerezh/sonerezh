Changes with 1.1.0
------------------
Improvements:
* #148: Upgrade CakePHP to 2.7
* Upgrade Twitter Bootstrap to 3.3.6

Changes with 1.0.0
------------------
New features:
* Docker compatibility
* The first row on the album view shows the 6 latest albums added

Improvements:
* Code readability

Bug / Security fixes:
* Fix #142: Mp3 files without Id3 tags all showns as "Unknown"
* Fix #122: Songs are duplicated when i attempt to update database

Changes with 1.0.0-beta2
------------------------
New features:
* French translation (using the browser language)

Improvements:
* Improve queue behavior on the "search" view

Changes with 1.0.0-beta
-----------------------
New features:
* #61: Set multiple root folders
* #46: Forgot my password system
* #45: Email notifications system
* #43: 'Remember Me' feature
* Sonerezh now works with PostgreSQL and SQlite (not recommended)

Improvements:
* Improvements on queue behavior
* #117: Port option on database installation
* #82: Improve Raspberry Pi compatibility (Thanks to kletellier)
* #72: Improve ID3v2 support
* #35: Help for get root path on shared hosting
* #32: Improve import process
* #15: Added the pointer cursor on music titles clickable lines (Thanks to maximelebastard)
* #7: Improve OGG metadata tags support
* Performance improvement in algorithm sorting

Bug / Security fixes:
* Fix #120: Error 500 on settings page
* Fix #73: Newly created playlists aren't listed
* Fix #48 #64: Ask for password twice when trying to update it) (Thanks to FoxiesCuties)
* Fix #34: XSS vulnerability on search form
* Fix #33: A "Listener" can update any account
* Fix #31: Do not hotlink images from flattr.com, paypalobjects.com
* Fix #23: End space in directory name
* Fix #22: Invalid search query when using history back button
* Fix #19: Unable to add songs to playlist from search results screen (Thanks to Cr33p)
* Fix #8 #71: Length path limitation (Thanks to maximelebastard)
* Fix #3: Avconv and FFmpeg
