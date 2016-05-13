import React from 'react'
import ReactDOM from 'react-dom'
import { createStore, combineReducers, applyMiddleware, compose } from 'redux'
import { Provider } from 'react-redux'
import { Router, Route, browserHistory } from 'react-router'
import { syncHistoryWithStore, routerReducer } from 'react-router-redux'

import LiveScoreboard from './components/LiveScoreboard';
import SubmissionFlow from './components/SubmissionFlow';
import { submissionReducer } from './states/SubmissionStates'

const reducer = combineReducers({
	routing: routerReducer,
	submission: submissionReducer
})


const store = createStore(reducer, undefined, compose(applyMiddleware(), window.devToolsExtension ? window.devToolsExtension() : f => f))

const history = syncHistoryWithStore(browserHistory, store)

function _wrapProvider(v) {
	return (
		<Provider store={store}>
			<Router history={history}>
				{v}
			</Router>
		</Provider>
	);
}

var _components = [
	['#live-scoreboard', function(elem) { ReactDOM.render(<LiveScoreboard/>, elem); }],
	['#submission-flow', function(elem) { ReactDOM.render(_wrapProvider(SubmissionFlow()), elem); }]
]


_components.forEach(function(c) {
	var elem = document.querySelector(c[0]);
	if (elem) {
		c[1](elem);
	}
});
