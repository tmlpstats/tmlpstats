import React from 'react';
import ReactDOM from 'react-dom';

import LiveScoreboard from './components/LiveScoreboard';

var liveScoreboardEl = document.querySelector('#live-scoreboard');
if (liveScoreboardEl) {
    ReactDOM.render(<LiveScoreboard/>, liveScoreboardEl);
}
