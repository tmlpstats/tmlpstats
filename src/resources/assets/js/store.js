import { createStore, combineReducers, applyMiddleware, compose } from 'redux'
import { browserHistory } from 'react-router'
import { syncHistoryWithStore, routerReducer } from 'react-router-redux'
import thunk from 'redux-thunk'

import { submissionReducer } from './submission/reducers'

const reducer = combineReducers({
    routing: routerReducer,
    submission: submissionReducer
})

const _middlewares = compose(
    applyMiddleware(thunk.withExtraArgument({Api: window.Api})),
    window.devToolsExtension ? window.devToolsExtension() : f => f
)

export const store = createStore(reducer, undefined, _middlewares)

export const history = syncHistoryWithStore(browserHistory, store)
