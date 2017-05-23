// IMPORTANT - polyfill must happen before all imports
require('./classic/tmlp-polyfill')

import React from 'react'
import ReactDOM from 'react-dom'
import { Provider } from 'react-redux'
import { Router } from 'react-router'

import { LiveScoreboard } from './live_scoreboard'
import { SubmissionFlow } from './submission'
import QuarterAccountabilitiesEmbed from './submission/next_qtr_accountabilities/embed'
import AdminFlow from './admin/flow'
import ReportsFlow from './reports/flow'
import { store, history } from './store'

function _routedFlow() {
    return (
        <Provider store={store}>
            <Router history={history}>
                {SubmissionFlow()}
                {AdminFlow()}
                {ReportsFlow()}
            </Router>
        </Provider>
    )
}

var _components = [
    ['#live-scoreboard', function(elem) { ReactDOM.render(<Provider store={store}><LiveScoreboard/></Provider>, elem) }],
    ['#react-routed-flow', function(elem) { ReactDOM.render(_routedFlow(), elem) }],
    ['#cr3-accountabilities', function(elem) { ReactDOM.render(<Provider store={store}><QuarterAccountabilitiesEmbed /></Provider>, elem)}]
]


_components.forEach(function(c) {
    var elem = document.querySelector(c[0])
    if (elem) {
        c[1](elem)
    }
})

require('./classic')
