// POLYFILLS FIRST - required before importing further modules

// EVERYTHING ELSE
import React from 'react'
import ReactDOM from 'react-dom'
import { Provider } from 'react-redux'
import { Router } from 'react-router'

import { LiveScoreboard } from './live_scoreboard'
import { SubmissionFlow } from './submission'
import { store, history } from './store'


function _wrapProvider(v) {
    return (
        <Provider store={store}>
            <Router history={history}>
                {v}
            </Router>
        </Provider>
    )
}

var _components = [
    ['#live-scoreboard', function(elem) { ReactDOM.render(<Provider store={store}><LiveScoreboard/></Provider>, elem) }],
    ['#submission-flow', function(elem) { ReactDOM.render(_wrapProvider(SubmissionFlow()), elem) }]
]


_components.forEach(function(c) {
    var elem = document.querySelector(c[0])
    if (elem) {
        c[1](elem)
    }
})
