
// objectAssign "ponyfill" uses Object.assign when it can, otherwise falls back to a compatible ponyfill. (IE and safari as of this writing)
export const objectAssign = require('object-assign')

// arrayFind ponyfill uses Array.prototype.find when it can, otherwise ponyfill (IE and Opera as of this writing.)
export const arrayFind = require('array-find')


// Promise is an ES6 primitive not available in IE. Unlike other polyfills, this one
// does not test for a global one, so I wrote a simple test to see which to use.
const Promise = (function() {
    let testPromise = window.Promise
    if (!testPromise || !testPromise.resolve || !testPromise.all || !testPromise.race) {
        return require('es6-promise').Promise
    }
    return testPromise
}())

const { fetch, Request, Response, Headers } = require('fetch-ponyfill')({Promise})

export { Promise, fetch, Request, Response, Headers }
