newsNoys
========

PHP code relating to the auto-tweeting NewsNoys system.

As NewsNoys creates its tweets from content taken from a database, a little setup is required. The basic database structure is:

Table: beginning
  Columns:
    pkey (integer, auto-increment)
    beginning (string)
    
Table: subject
  Columns:
    pkey (integer, auto-increment)
    firstname (string)
    surname (string)
    sex (string, but should either be male or female - sorry trans-gender individuals, you're not supposed in NewsNoys yet)
    alive (string, but should either be yes or no)
    popularity (integer)

Table: action
  Columns:
    pkey (integer, auto-increment)
    action (string)
    
Table: objects
  Columns:
    pkey (integer, auto-increment)
    objects (string)

Matt Harris' OAuth library is also not included, can be found https://github.com/themattharris/tmhOAuth (versions newer than 0.7 may require some NewsNoys code changes).
