import React from 'react'
import { Provider } from 'react-redux'
import { createStore, combineReducers, applyMiddleware } from 'redux'
import thunk from 'redux-thunk'

import baseReducers from '../storeBaseReducers'
import storeProxy from '../storeProxy'

const DEFAULT_BROWSER = {
    "_responsiveState":true,
    "width":1436,
    "height":1048,
    "lessThan":{"extraSmall":false,"small":false,"medium":false,"large":false,"huge":false,"infinity":true},
    "greaterThan":{"extraSmall":true,"small":true,"medium":true,"large":true,"huge":false,"infinity":false},
    "mediaType":"huge",
    "orientation":"landscape",
    "breakpoints":{"extraSmall":480,"small":768,"medium":992,"large":1200,"huge":1600,"infinity":null}
}

const fakeReducers = {
    browser(state, action) {
        return DEFAULT_BROWSER
    }
}

const reducer = combineReducers(Object.assign(fakeReducers, baseReducers))

export function newTestingStore() {
    const store = createStore(reducer, undefined, applyMiddleware(thunk))
    function Wrap(props) {
        return (
            <Provider store={store}>{props.children}</Provider>
        )
    }
    store.Wrap = Wrap
    storeProxy.setStore(store)
    return store
}

export const store = newTestingStore()
export const Wrap = store.Wrap
//export const history = syncHistoryWithStore(browserHistory, store)

export { Provider, thunk, applyMiddleware, createStore }
