import React from 'react'
import ReactDOM from 'react-dom'
import { Provider } from 'react-redux'
import { Router } from 'react-router'

import { LiveScoreboard } from './live_scoreboard'
import { SubmissionFlow } from './submission'
import AdminFlow from './admin/flow'
import { store, history } from './store'

function _routedFlow() {
    return (
        <Provider store={store}>
            <Router history={history}>
                {SubmissionFlow()}
                {AdminFlow()}
            </Router>
        </Provider>
    )
}

var _components = [
    ['#live-scoreboard', function(elem) { ReactDOM.render(<Provider store={store}><LiveScoreboard/></Provider>, elem) }],
    ['#submission-flow', function(elem) { ReactDOM.render(_routedFlow(), elem) }]
]


_components.forEach(function(c) {
    var elem = document.querySelector(c[0])
    if (elem) {
        c[1](elem)
    }
})

require('./classic')
