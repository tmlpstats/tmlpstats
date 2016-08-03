
// objectAssign "ponyfill" uses Object.assign when it can, otherwise falls back to a compatible ponyfill. (IE and safari as of this writing)
export const objectAssign = require('object-assign')


// Promise is an ES6 primitive not available in IE
export { Promise } from 'es6-promise'
