import React from 'react'
import { connect } from 'react-redux'

import * as actions from './actions'

const settings = window.settings

class GameFieldView extends React.Component {
    setGameOp(op) {
        this.props.dispatch(actions.setGameOp(this.props.game, op))
    }

    submitUpdates(gameValue) {
        if (this.props.gameData.op != 'updating') {
            // Tell LiveScoreboard it's time to update this value
            const center = settings.center.abbreviation
            const { game, field } = this.props
            this.props.dispatch(actions.submitUpdates(center, game, field, gameValue))
        }
    }

    handleChange(e) {
        const center = settings.center.abbreviation
        const value = e.target.value
        const { game, field } = this.props
        // Pass the updated value to LiveScoreboard who owns the state object
        this.props.dispatch(actions.changeGameFieldPotentialUpdate(center, game, field, value))
    }

    handleKeyPress(e) {
        if (e.key == 'Enter') {
            this.submitUpdates(this.props.gameValue)
        }
    }

    renderEditable() {
        var after = <span className="glyphicon glyphicon-pencil" data-toggle="tooltip" title="Use the field to the left and hit enter to set a score."></span>
        if (this.props.suffix) {
            after = this.props.suffix
        }
        var container = ''
        switch (this.props.gameData.op) {
        case 'updating':
            after = <span className="glyphicon glyphicon-refresh"></span>
            break
        case 'success':
            container = 'has-success'
            after = <span className="glyphicon glyphicon-ok" style={{color: 'green'}}></span>
            break
        case 'failed':
            container = 'has-failure'
            after = <span className="glyphicon glyphicon-remove" style={{color: 'red'}}></span>
            break
        }

        return (
            <div className={'input-group live-scoreboard-group ' + container}>
                <input
                    type="text"
                    value={this.props.gameValue}
                    onChange={this.handleChange.bind(this)}
                    onKeyPress={this.handleKeyPress.bind(this)}
                    className="form-control"
                />
                <span className="input-group-addon" aria-hidden="true">
                    {after}
                </span>
            </div>
        )
    }

    renderNotEditable() {
        const { gameValue, suffix } = this.props
        return (
            <span>{gameValue}{suffix}</span>
        )
    }

    render() {
        if (this.props.editable) {
            return this.renderEditable()
        } else {
            return this.renderNotEditable()
        }
    }
}

const GameField = connect()(GameFieldView)

export default GameField
