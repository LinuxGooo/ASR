# ASR league
The advanced study room is a go league played on the kgs go server. You can find information about our league in our [Website] .

This is the league software used by the ASR league to get the games players have played, display results and manage the league.

It's plain php/mysql.

You can find some more informations about this software [here][l1].

# Wordpress integration
This software is partly integrated inside a wordpress.
Basicly, we do a check on wordpress roles to see what a user can do.

For now, we use iframe inside wordpress page to display league data.

We have a draft of a wordpress plugins that calls this software function to avoid iframing.


# Files

You can test php files in www.advancedstudyroom.org/asrleague/ but most of them require you to be logged in the wordpress as a league admin.

For instance try www.advancedstudyroom.org/asrleague/archive.php
  - **scraper.php**: look for games inside kgs archive. run every 5 mins by cron on the server.
  - **archive.php**: This is where players can browse the sgf collection.
  - **league.php**: Display league results
  - **event.php**: main admins tool. Display list of events and allow admins to add user or assign player to a division.
  - **rooms.php**: edit rooms (divisions) of an event.
  - **cachelib.php**: cache the results.
  - all ***-iframe.php**: are just some display adapts from original file to help iframing inside wordpress page.
  - **action.php**: downlod a game to the server and ad pull request for the scraper.
  - **wplib.php**: allow this app to check wordpress roles and capabilitys.
   


# Licence
For now, it's **copirighted to akisora corporation**.
I have approval to do whatever I want about this.
I am considering GNU GPL but would appreciated some advice.

Climu 

[//]: # (references)

[website]:<http://www.advancedstudyroom.org/>
   [l1]: <http://www.advancedstudyroom.org/league-coder-guide/>
