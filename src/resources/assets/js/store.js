import { createStore, combineReducers, applyMiddleware, compose } from 'redux'
import { browserHistory } from 'react-router'
import { syncHistoryWithStore, routerReducer } from 'react-router-redux'
import { createResponsiveStateReducer, createResponsiveStoreEnhancer } from 'redux-responsive'
import thunk from 'redux-thunk'

import { objectAssign } from './reusable/ponyfill'
import baseReducers from './storeBaseReducers'

const responsiveBreakpoints = {
    extraSmall: 480,
    small: 768,
    medium: 992,
    large: 1200,
    huge: 1600
}

const reducer = combineReducers(objectAssign(
    {
        browser: createResponsiveStateReducer(responsiveBreakpoints),
        routing: routerReducer
    },
    baseReducers
))

const responsive = createResponsiveStoreEnhancer({performanceMode: true})

var _enhancers = compose(responsive, applyMiddleware(thunk.withExtraArgument({Api: window.Api})))

// This trick will remove Redux devtools in production
if (process.env.NODE_ENV != 'production') {
    _enhancers = compose(_enhancers, window.devToolsExtension ? window.devToolsExtension() : f => f)
}

export const store = createStore(reducer, undefined, _enhancers)

export const history = syncHistoryWithStore(browserHistory, store)

// To prevent circular issues, set the store here
require('./storeProxy').default.setStore(store)
