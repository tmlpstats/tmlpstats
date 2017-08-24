

let browsers = {
    ie: {
        os: 'Windows',
        os_version: '10',
        browser: 'IE',
        browser_version: '11.0',
        resolution: '1280x800'
    },
    firefox: {
        os: 'Windows',
        os_version: '10',
        browser: 'Firefox',
        browser_version: '50.0',
        resolution: '1366x768'
    },
    chrome: {
        // This will randomly pick among Chrome versions. Gives more variety.
        // So far, I haven't found a failing Chrome version, but we may have
        // to do similar to Safari in the future.
        browser: 'Chrome',
        resolution: '1280x1024',
        chromeOptions: {
            args: [ '--disable-password-bubble' ]
        }
    },
    chrome_old: {
        // This specifies Chrome 40, which came out in 2014. It should still work.
        _excludeDefault: true,
        os: 'Windows',
        os_version: '7',
        browser: 'Chrome',
        browser_version: '40.0',
        resolution: '1280x800'
    },
    safari_old: {
        // Oldest Safari we support right now is Safari 7 (OSX Mavericks).
        // Browserstack has browsers like OSX Lion with Safari 6, which we do not support.
        _excludeDefault: true,
        os: 'OS X',
        os_version: 'Mavericks',
        browser: 'Safari',
        browser_version: '7.1',
        resolution: '1600x1200',
    },
    safari: {
        os: 'OS X',
        os_version: 'Sierra',
        browser: 'Safari',
        browser_version: '10.0',
        resolution: '1600x1200'
    },
    ipad: {
        // ipad takes a very long time to boot up. This ensures we run it intentionally
        _excludeDefault: true,
        browserName: 'iPad',
        platform: 'MAC',
        device: 'iPad Mini 4'
    }
}

let selectedBrowsers
if (process.env.SELECTED_BROWSERS) {
    selectedBrowsers = process.env.SELECTED_BROWSERS.split(',').map(x => x.trim())
} else {
    selectedBrowsers = Object.keys(browsers).filter(k => !browsers[k]._excludeDefault)
}

let capabilities = selectedBrowsers.map(k => browsers[k])


let baseConfig = {

    //
    // =================
    // Service Providers
    // =================
    // WebdriverIO supports Sauce Labs, Browserstack, and Testing Bot (other cloud providers
    // should work too though). These services define specific user and key (or access key)
    // values you need to put in here in order to connect to these services.
    //
    user: process.env.BROWSERSTACK_USER,
    key: process.env.BROWSERSTACK_KEY,


    //
    // ==================
    // Specify Test Files
    // ==================
    // Define which test specs should run. The pattern is relative to the directory
    // from which `wdio` was called. Notice that, if you are calling `wdio` from an
    // NPM script (see https://docs.npmjs.com/cli/run-script) then the current working
    // directory is where your package.json resides, so `wdio` will be called from there.
    //
    specs: [
        'tests/browser/**/*.spec.js'
    ],
    // Patterns to exclude.
    exclude: [
        // 'path/to/excluded/files'
    ],
    //
    // ============
    // Capabilities
    // ============
    // Define your capabilities here. WebdriverIO can run multiple capabilities at the same
    // time. Depending on the number of capabilities, WebdriverIO launches several test
    // sessions. Within your capabilities you can overwrite the spec and exclude options in
    // order to group specific specs to a specific capability.
    //
    // First, you can define how many instances should be started at the same time. Let's
    // say you have 3 different capabilities (Chrome, Firefox, and Safari) and you have
    // set maxInstances to 1; wdio will spawn 3 processes. Therefore, if you have 10 spec
    // files and you set maxInstances to 10, all spec files will get tested at the same time
    // and 30 processes will get spawned. The property handles how many capabilities
    // from the same test should run tests.
    //
    maxInstances: 1,
    //
    // If you have trouble getting all important capabilities together, check out the
    // Sauce Labs platform configurator - a great tool to configure your capabilities:
    // https://docs.saucelabs.com/reference/platforms-configurator
    //
    capabilities: capabilities,
    //
    // ===================
    // Test Configurations
    // ===================
    // Define all options that are relevant for the WebdriverIO instance here
    //
    // By default WebdriverIO commands are executed in a synchronous way using
    // the wdio-sync package. If you still want to run your tests in an async way
    // e.g. using promises you can set the sync option to false.
    sync: true,
    //
    // Level of logging verbosity: silent | verbose | command | data | result | error
    logLevel: 'command',
    //
    // Enables colors for log output.
    coloredLogs: true,
    //
    // If you only want to run your tests until a specific amount of tests have failed use
    // bail (default is 0 - don't bail, run all tests).
    bail: 1,
    //
    // Saves a screenshot to a given path if a command fails.
    screenshotPath: './storage/tests_output/errorShots',
    //
    // Set a base URL in order to shorten url command calls. If your url parameter starts
    // with '/', then the base url gets prepended.
    baseUrl: 'http://localhost:8000',
    //
    // Default timeout for all waitFor* commands.
    waitforTimeout: 30000,
    //
    // Default timeout in milliseconds for request
    // if Selenium Grid doesn't send response
    connectionRetryTimeout: 90000,
    //
    // Default request retries count
    connectionRetryCount: 3,
    //
    // Initialize the browser instance with a WebdriverIO plugin. The object should have the
    // plugin name as key and the desired plugin options as properties. Make sure you have
    // the plugin installed before running any tests. The following plugins are currently
    // available:
    // WebdriverCSS: https://github.com/webdriverio/webdrivercss
    // WebdriverRTC: https://github.com/webdriverio/webdriverrtc
    // Browserevent: https://github.com/webdriverio/browserevent
    // plugins: {
    //     webdrivercss: {
    //         screenshotRoot: 'my-shots',
    //         failedComparisonsRoot: 'diffs',
    //         misMatchTolerance: 0.05,
    //         screenWidth: [320,480,640,1024]
    //     },
    //     webdriverrtc: {},
    //     browserevent: {}
    // },
    //
    // Test runner services
    // Services take over a specific job you don't want to take care of. They enhance
    // your test setup with almost no effort. Unlike plugins, they don't add new
    // commands. Instead, they hook themselves up into the test process.
    services: ['browserstack'],
    //
    // Framework you want to run your specs with.
    // The following are supported: Mocha, Jasmine, and Cucumber
    // see also: http://webdriver.io/guide/testrunner/frameworks.html
    //
    // Make sure you have the wdio adapter package for the specific framework installed
    // before running any tests.
    framework: 'mocha',
    //
    // Test reporter for stdout.
    // The only one supported by default is 'dot'
    // see also: http://webdriver.io/guide/testrunner/reporters.html
    reporters: ['spec'],
    //
    // Options to be passed to Mocha.
    // See the full list at http://mochajs.org/
    mochaOpts: {
        ui: 'bdd',
        timeout: 600 * 1000 // 10 minutes
    },
    //
    // =====
    // Hooks
    // =====
    // WebdriverIO provides several hooks you can use to interfere with the test process in order to enhance
    // it and to build services around it. You can either apply a single function or an array of
    // methods to it. If one of them returns with a promise, WebdriverIO will wait until that promise got
    // resolved to continue.
    //
    // Gets executed once before all workers get launched.
    // onPrepare: function (config, capabilities) {
    // },
    //
    // Gets executed just before initialising the webdriver session and test framework. It allows you
    // to manipulate configurations depending on the capability or spec.
    // beforeSession: function (config, capabilities, specs) {
    // },
    //
    // Gets executed before test execution begins. At this point you can access all global
    // variables, such as `browser`. It is the perfect place to define custom commands.
    before: function(capabilities, specs) {
        // inject 'expect' from jest so they match our other tests
        global.expect = require('jest-matchers')
    },
    //
    // Hook that gets executed before the suite starts
    // beforeSuite: function (suite) {
    // },
    //
    // Hook that gets executed _before_ a hook within the suite starts (e.g. runs before calling
    // beforeEach in Mocha)
    // beforeHook: function () {
    // },
    //
    // Hook that gets executed _after_ a hook within the suite starts (e.g. runs after calling
    // afterEach in Mocha)
    // afterHook: function () {
    // },
    //
    // Function to be executed before a test (in Mocha/Jasmine) or a step (in Cucumber) starts.
    // beforeTest: function (test) {
    // },
    //
    // Runs before a WebdriverIO command gets executed.
    // beforeCommand: function (commandName, args) {
    // },
    //
    // Runs after a WebdriverIO command gets executed
    // afterCommand: function (commandName, args, result, error) {
    // },
    //
    // Function to be executed after a test (in Mocha/Jasmine) or a step (in Cucumber) starts.
    // afterTest: function (test) {
    // },
    //
    // Hook that gets executed after the suite has ended
    // afterSuite: function (suite) {
    // },
    //
    // Gets executed after all tests are done. You still have access to all global variables from
    // the test.
    // after: function (result, capabilities, specs) {
    // },
    //
    // Gets executed right after terminating the webdriver session.
    // afterSession: function (config, capabilities, specs) {
    // },
    //
    // Gets executed after all workers got shut down and the process is about to exit. It is not
    // possible to defer the end of the process using a promise.
    // onComplete: function(exitCode) {
    // }
    // Code to start browserstack local before start of test
}

if (process.env.BROWSERSTACK_LOCAL) {
    const browserstack = require('browserstack-local')

    //baseConfig.baseUrl = 'http://bs-local.com:8080'
    baseConfig.capabilities.forEach((capability) => {
        capability['browserstack.local'] = true
    })

    baseConfig.onPrepare = function (config, capabilities) {
        console.log('Connecting local')
        return new Promise(function(resolve, reject) {
            const local_conf = {
                key: baseConfig.key,
                logFile: 'storage/tests_output/local.log'
            }
            exports.bs_local = new browserstack.Local()
            exports.bs_local.start(local_conf, function(error) {
                if (error) {
                    return reject(error)
                }
                console.log('Connected. Now testing...')
                resolve()
            })
        })
    }

    // Code to stop browserstack local after end of test
    baseConfig.onComplete = function (capabilties, specs) {
        exports.bs_local.stop(function() {});
    }
}

exports.config = baseConfig
