import React from 'react'
import { Form, Field, actions as formActions } from 'react-redux-form'
import { Link } from 'react-router'

export { Form, Field, formActions }

export class SimpleField extends React.Component {
    static defaultProps = {
        labelSize: 'col-md-2',
        divSize: 'col-md-8'
    }
    render() {
        var field
        if (this.props.customField) {
            field = this.props.children
        } else {
            field = <input type="text" className="form-control" />
        }

        return (
            <Field model={this.props.model}>
                <div className="form-group">
                    <label className={this.props.labelSize + ' control-label'}>{this.props.label}</label>
                    <div className={this.props.divSize}>{field}</div>
                </div>
            </Field>
        )
    }
}

export class AddOneLink extends React.Component {
    render() {

        var label = this.props.label
        if (!label) {
            label = '+ Add One'
        }

        return (
            <Link to={this.props.link}>{label}</Link>
        )
    }
}
