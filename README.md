# TMLP Statistics Web Application

**Table Of Contents**

* [Installing/Setup](#installing)
* [Code Walkthrough](https://github.com/tmlpstats/tmlpstats/wiki/Walkthrough)

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
`<tmlpstats folder>/docker/mysql/sql/030-dump.sql.gz`. (or `030-dump.sql` if it's not a `.gz` file)
This will be used later when building your local mysql image.


### Download and install Docker

#### Mac
All you should have to do is [Install Docker For Mac](https://docs.docker.com/docker-for-mac/install/)
The default configuration should be good enough to get you started. You will need to be somewhat familiar with the Terminal to use the commands.

1. open a Terminal and go to your git checkout folder.. e.g. `cd ~/dev/tmlpstats`

#### Windows
(prerequisite): to use Docker for Windows you need Windows 10 Professional/Enterprise (Home will not work!)

1. [Install Docker For Windows](https://docs.docker.com/docker-for-windows/install/) (Make sure only to get the Stable Channel)
2. Docker Icon -> Preferences -> Shared Drives ; share the drive that you have your git files checked out on (Usually C: drive, but it could be different if you have multiple drives and put your code somewhere else.)
3. Open up a command prompt window and go to the correct folder. If you checked out your files into your My Documents folder, `cd Documents\GitHub\tmlpstats`

### After docker install (all OSes)

1. Run the command `docker-compose up local` . The first time you do this, it is going to download and build a few things. This can take 10-20 minutes on a typical broadband.
2. When you see the line like `[BS] Watching files...` you know the service is ready.
3. Open your favorite web browser (We recommend Chrome for the good developer tools) and go to the URL http://localhost:8030 - This should now show you a website login.


### Usage Tips

* using `ctrl-c` on your terminal will stop the running container.
* The command `docker-compose stop` will stop the background containers too, like MySQL.
  * The command `docker-compose rm --force` will also remove the container's state. We don't recommend this unless you are trying to chase down a specific issue.
  * By default, your database will reset to its original state every time you `docker-compose rm` the container, so consider that if you have made extensive local changes. Of course, this can be used to your advantage when testing new features to reset to a known state.

## Troubleshooting issues

Sometimes ```composer install``` will fail or not complete, asking for an oauth token. Github has rate-limiting for
unauthenticated cloning. You can get around this by going to your github account settings and generating a new "Personal
access token" for your laptop. Re run ```composer install``` and paste the token when asked. 

## Next Steps

 * [Code Walkthrough](https://github.com/tmlpstats/tmlpstats/wiki/Walkthrough)

## Thank you:
<!-- The following is added at the request of BrowserStack for providing free use of their tools for opensource projects -->

<img src="https://www.browserstack.com/images/layout/browserstack-logo-600x315.png" width="200" height="105" />
<div>
Thank you to BrowserStack for providing us with tools to test in every browser.
</div>
