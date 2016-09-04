
// objectAssign "ponyfill" uses Object.assign when it can, otherwise falls back to a compatible ponyfill. (IE and safari as of this writing)
export const objectAssign = require('object-assign')

// arrayFind ponyfill uses Array.prototype.find when it can, otherwise ponyfill (IE and Opera as of this writing.)
export const arrayFind = require('array-find')


// Promise is an ES6 primitive not available in IE
import { Promise } from 'es6-promise'

const { fetch, Request, Response, Headers } = require('fetch-ponyfill')({Promise})

export { Promise, fetch, Request, Response, Headers }
