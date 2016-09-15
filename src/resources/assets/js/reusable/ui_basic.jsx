import React, { PropTypes } from 'react'

import { getErrMessage } from './ajax_utils'

/**
 * ModeSelectButtons renders side-by-side buttons used for mode selector, with appropriate active state.
 *
 * Typical props:
 *   - items: array of objects describing the item. Typically, the item must have props 'key' and 'label'
 *   - current: The key value of the currently selected item. string or number.
 *   - onClick: a callback which will be called with the selected key on item change.
 *
 * Optional props:
 *   - ariaGroupDesc: Used to provide an ARIA label to the button group which describes what it does.
 */
export class ModeSelectButtons extends React.Component {
    static defaultProps = {
        classes: 'btn btn-default',
        activeClasses: 'btn btn-default active',
        keyProp: 'key',
        getLabel: (v) => v.label
    }
    static propTypes = {
        items: PropTypes.arrayOf(PropTypes.object).isRequired,
        current: PropTypes.oneOfType([
            PropTypes.string,
            PropTypes.number
        ]),
        ariaGroupDesc: PropTypes.string,
        onClick: PropTypes.func
    }

    render() {
        var buttons = []
        this.props.items.forEach((item) => {
            var key = item[this.props.keyProp]
            const classes = (key == this.props.current) ? this.props.activeClasses : this.props.classes
            var cb = () => this.props.onClick(key, item)
            buttons.push(
                <button key={key} type="button" className={classes} onClick={cb}>
                    {this.props.getLabel(item)}
                </button>
            )
        })

        return (
            <div className="btn-group" role="group" aria-label={this.props.ariaGroupDesc}>
                {buttons}
            </div>
        )
    }
}

/**
 * LoadStateFlip is a really simple experimental component for choosing what's shown based on load state.
 *
 * This should
 */
export class LoadStateFlip extends React.PureComponent {
    render() {
        var loadState = this.props.loadState
        if (loadState.state == 'loading') {
            return <div><span className="glyphicon glyphicon-send"></span>TODO spinner here...</div>
        } else if (loadState.state == 'failed') {
            var error = 'FAILED '
            if (loadState.error) {
                error += getErrMessage(loadState.error)
            }
            return <div className="bg-danger">{error}</div>
        } else {
            return <div>{this.props.children}</div>
        }
    }
}

export class SubmitFlip extends React.PureComponent {
    static defaultProps = {
        buttonClasses: 'btn btn-primary',
        offset: 'col-md-offset-2 col-md-8',
        wrapGroup: true
    }
    static propTypes = {
        loadState: PropTypes.object.isRequired,
        offset: PropTypes.string,
        wrapGroup: PropTypes.bool
    }

    render() {
        const { wrapGroup, offset, loadState, buttonClasses } = this.props

        const body = (
            <LoadStateFlip loadState={loadState}>
                <button className={buttonClasses} type="submit">{this.props.children}</button>
            </LoadStateFlip>
        )

        if (wrapGroup) {
            return (
                <div className="form-group">
                    <div className={offset}>
                        {body}
                    </div>
                </div>
            )
        } else {
            return body
        }
    }
}

/**
 * MessagesComponent formats an array of messages for display.
 *
 * Use with error/warning messages
 */
export class MessagesComponent extends React.Component {
    render() {
        const messages = this.props.messages

        if (!messages) {
            return <div></div>
        }

        const errors = []
        const warnings = []

        messages.forEach((message) => {
            let item = <li key={message.reference+message.id} className={message.type}>{message.message}</li>

            if (message.type == 'error') {
                errors.push(item)
            } else if (message.type == 'warning') {
                warnings.push(item)
            }
        })

        let errorString = ''
        if (errors.length > 0) {
            errorString = (
                <div>
                    <h5 className="error">Errors:</h5>
                    <ul>
                        {errors}
                    </ul>
                </div>
            )
        }

        let warningString = ''
        if (warnings.length > 0) {
            warningString = (
                <div>
                    <h5 className="warning">Warnings:</h5>
                    <ul>
                        {warnings}
                    </ul>
                </div>
            )
        }

        return (
            <div>
                {errorString}
                {warningString}
            </div>
        )
    }
}

export class Alert extends React.PureComponent {
    static defaultProps = {
        alert: 'info',
        icon: 'info-sign'
    }
    render() {
        const { alert, icon, children } = this.props

        return (
            <div className={'alert alert-' + alert}>
                <span className={'glyphicon glyphicon-' + icon} aria-hidden="true"></span>
                {children}
            </div>
        )
    }
}
