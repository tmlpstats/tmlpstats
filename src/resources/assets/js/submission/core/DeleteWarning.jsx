import _ from 'lodash'
import PropTypes from 'prop-types'
import React, { Component } from 'react'
import { Form, Control, actions as formActions } from 'react-redux-form'

import { loadStateShape } from '../../reusable/shapes'
import { Panel, ButtonStateFlip } from '../../reusable/ui_basic'
import { rebind, connectRedux } from '../../reusable/dispatch'

const extraConfirmProp = '_extraConfirm'

@connectRedux()
export default class DeleteWarning extends Component {
    static mapStateToProps(state, ownProps) {
        const v = {}
        if (ownProps.extraConfirm) {
            v.extraConfirmValue = _.get(state, `${ownProps.model}.${extraConfirmProp}`)
        }
        return v
    }
    static propTypes = {
        model: PropTypes.string.isRequired,
        noun: PropTypes.string.isRequired,
        buttonState: loadStateShape,
        extraConfirm: PropTypes.string,
        extraConfirmValue: PropTypes.string,
        onSubmit: PropTypes.func,
        spiel: PropTypes.node,
    }

    constructor(props) {
        super(props)
        rebind(this, 'preSubmit')
    }

    render() {

        const { spiel, noun, model, buttonState, extraConfirm, extraConfirmValue } = this.props
        let button, extra

        const renderSpiel = (typeof spiel == 'string')? <p>{spiel}</p> : spiel

        if (buttonState) {
            button = <ButtonStateFlip buttonClass="btn btn-danger" loadState={buttonState}>Delete</ButtonStateFlip>
        } else {
            button = <button className="btn btn-danger" type="submit">Delete</button>
        }

        if (extraConfirm) {
            extra = <Control.text model={`${model}.${extraConfirmProp}`} className="form-control" />
            if (extraConfirmValue != extraConfirm) {
                button = undefined
            }
        }

        return (
            <Panel heading={`Delete ${noun}`}>
                <Form model={model} onSubmit={this.preSubmit}>
                    {renderSpiel}
                    {extra}
                    {button}
                </Form>
            </Panel>
        )
    }

    preSubmit(data) {
        if (!confirm(`Are you SURE you want to delete this ${this.props.noun}?`)) {
            return // stop action continuing
        }
        if (this.props.extraConfirm) {
            // Probably not needed since we hide the button until the text is entered, but not a big deal to check
            if (data[extraConfirmProp] != this.props.extraConfirm) {
                return alert('Must match names')
            }
        }
        this.props.onSubmit(data)
    }
}
