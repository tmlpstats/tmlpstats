import React from 'react'
import { connect } from 'react-redux'

import * as actions from './actions'

const settings = window.settings

class GameFieldView extends React.Component {
    setGameOp(op) {
        this.props.dispatch(actions.setGameOp(this.props.game, op))
    }
    submitUpdates() {
        // Tell LiveScoreboard it's time to update this value
        this.setGameOp('updating')
        var revert = () => {
            const op = this.props.gameData.op
            if (op == 'success' || op == 'failed') {
                this.setGameOp('default')
            }
        }
        const center = settings.center.abbreviation
        const { game, field, gameValue } = this.props
        this.props.dispatch(actions.postGameValue(center, game, field, gameValue)).done(() => {
            this.setGameOp('success')
            setTimeout(revert, 5000)
        }).fail(() => {
            this.setGameOp('failed')
            setTimeout(revert, 8000)
        })
    }

    handleChange(e) {
        const value = e.target.value
        const { game, field } = this.props
        // Pass the updated value to LiveScoreboard who owns the state object
        this.props.dispatch(actions.changeGameField(game, field, value))
    }

    handleBlur(e) {
        e.target.value = this.props.gameValue
    }

    handleKeyPress(e) {
        if (e.key == 'Enter') {
            this.submitUpdates()
        }
    }

    renderEditable() {
        var after = <span className="glyphicon glyphicon-pencil" data-toggle="tooltip" title="Use the field to the left and hit enter to set a score."></span>
        if (this.props.suffix) {
            after = this.props.suffix
        }
        var disabled = false
        var container = ''
        switch (this.props.gameData.op) {
        case 'updating':
            disabled = true
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
                    onBlur={this.handleBlur.bind(this)}
                    onKeyPress={this.handleKeyPress.bind(this)}
                    disabled={disabled}
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
