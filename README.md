# TMLP Statistics Web Application

**Table Of Contents**

* [Installing/Setup](#installing)
* [Code Walkthrough](https://github.com/pdarg/tmlpstats/wiki/Walkthrough)

## Thank you:
<!-- The following is added at the request of BrowserStack for providing free use of their tools for opensource projects -->
Thank you to BrowserStack for providing us with tools to test in every browser.

## Installing:
This repo includes a docker-compose file and docker images setup to streamline our development process. This is a relatively new feature but the intent is 

### Clone Repo
If using a command-line:
```
$ cd ~/dev
$ git clone https://github.com/tmlpstats/tmlpstats
```
You can also use a GUI client to clone the repo like [Github Desktop](https://desktop.github.com/)

### Setup for Database Seeding
If you have an export of the database, pace it at the location 
`<tmlpstats folder>/docker/mysql/sql/030-dump.sql`.
This will be used


### Download and install Docker

#### Mac
All you should have to do is [Install Docker For Mac](https://docs.docker.com/docker-for-mac/install/)
The default configuration should be good enough to get you started. You will need to be somewhat familiar with the terminal

1. open a Terminal and go to your git checkout folder.. e.g. `cd ~/dev/tmlpstats`

#### Windows
(prerequisite): to use Docker for Windows you need Windows 10 Professional/Enterprise (Home will not work!)

1. [Install Docker For Windows](https://docs.docker.com/docker-for-windows/install/) (Make sure only to get the Stable Channel)
2. Docker Icon -> Preferences -> Shared Drives ; share the drive that you have your git files checked out on (Usually C: drive, but it could be)
3. Open up a command prompt window and go to the correct folder. If you checked out your files into your My Documents folder, `cd Documents\GitHub\tmlpstats`

### After docker install (all OSes)

1. Run the command `docker-compose up local` . The first time you do this, it is going to download and build a few things. This can take 10-20 minutes on a typical broadband.
2. When you see the line like `Command line: 'apache2 -D FOREGROUND'` you know the service is ready.
3. Open your favorite web browser (We recommend Chrome for the good developer tools) and go to the URL http://localhost:8080 - This should now show you a website login.


### Usage Tips

* using `ctrl-c` on your terminal will stop the running container.
* The command `docker-compose stop` will stop the background containers too, like MySQL.
  * The command `docker-compose rm --force` will also remove the container's state. We don't recommend this unless you are trying to chase down a specific issue.
  * By default, your database will reset to its original state every time you stop the containers or your docker machine, so stopping your MySQL container can be problematic.

## Troubleshooting issues

Sometimes ```composer install``` will fail or not complete, asking for an oauth token. Github has rate-limiting for
unauthenticated cloning. You can get around this by going to your github account settings and generating a new "Personal
access token" for your laptop. Re run ```composer install``` and paste the token when asked. 

## Next Steps

 * [Code Walkthrough](https://github.com/pdarg/tmlpstats/wiki/Walkthrough)
