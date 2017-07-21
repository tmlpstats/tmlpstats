import React from 'react'
import { Provider } from 'react-redux'
import { createStore, combineReducers, applyMiddleware } from 'redux'
import thunk from 'redux-thunk'

import baseReducers from '../storeBaseReducers'
import storeProxy from '../storeProxy'

const reducer = combineReducers(baseReducers)


export const store = createStore(reducer, undefined, applyMiddleware(thunk))

storeProxy.setStore(store)

//export const history = syncHistoryWithStore(browserHistory, store)

export function Wrap(props) {
    return (
        <Provider store={store}>{props.children}</Provider>
    )
}

export { Provider, thunk, applyMiddleware, createStore }
