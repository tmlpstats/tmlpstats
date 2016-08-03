import { createStore, combineReducers, applyMiddleware, compose } from 'redux'
import { browserHistory } from 'react-router'
import { syncHistoryWithStore, routerReducer } from 'react-router-redux'
import { responsiveStateReducer, responsiveStoreEnhancer } from 'redux-responsive'
import thunk from 'redux-thunk'

import { submissionReducer } from './submission/reducers'

const reducer = combineReducers({
    browser: responsiveStateReducer,
    routing: routerReducer,
    submission: submissionReducer
})

var _enhancers = compose(responsiveStoreEnhancer, applyMiddleware(thunk.withExtraArgument({Api: window.Api})))

// This trick will remove Redux devtools in production
if (process.env.NODE_ENV != 'production') {
    _enhancers = compose(_enhancers, window.devToolsExtension ? window.devToolsExtension() : f => f)
}

export const store = createStore(reducer, undefined, _enhancers)

export const history = syncHistoryWithStore(browserHistory, store)
