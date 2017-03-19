Browser Automation Testing
==========================

NOTE this is very new, and will take some time to get better integrated
into our codebase, but it does work given you have a working local dev,
working database dump, and the right credentials for browserstack.

In the future we may make some modifications to easily drop in local selenium 
driver config for faster development of test suites.

## Basic Setup

Here's a quick guide to get ready for this test run.

### Installation/Readiness

* Make sure `npm install` done
* Start up dev VM. Probably without the 'watch' addon
* Helps to compile JS assets using the minified assets: `npm run production`
  (all these browsers start with an empty cache, so downloading JS that is
  1/3 the size speeds up the initial loads and such)

### Vars File

copy `tests/browser/vars.example.js` to simply `vars.js` in the same directory.
Edit the file to have valid credentials following the directives.

### Local Machine Env

Set in your local machine env:

```
export BROWSERSTACK_USER=user>
export BROWSERSTACK_KEY=<API key>
export BROWSERSTACK_LOCAL=y
```


## Running the tests

The basic command:
```
npm run browsertest
```

Also, take a look at the top of `wdio.conf.js`, you'll see browser profiles. You can run one or more of these profiles knowing the key by doing e.g.:

```
SELECTED_BROWSERS=ie,chrome npm run browsertest
```