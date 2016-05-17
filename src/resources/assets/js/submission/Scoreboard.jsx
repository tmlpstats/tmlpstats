import React from 'react'
import { connect } from 'react-redux'

class SubmissionScoreboard extends React.Component {
    render() {
        return (
            <div>SCOREBOARD TAB {this.props.scoreboard.games}</div>
        );
    }
}

function mapStateToProps(state) {
    return {
        scoreboard: state.submission.scoreboard
    }
}

export default connect(mapStateToProps)(SubmissionScoreboard)
