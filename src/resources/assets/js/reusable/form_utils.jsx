import React from 'react'
import { Form, Field, actions as formActions } from 'react-redux-form'
import { Link } from 'react-router'

export { Form, Field, formActions }

export class SimpleField extends React.Component {
    render() {
        var field
        if (this.props.customField) {
            field = this.props.children
        } else {
            field = <input type="text" className="form-control" />
        }

        var labelSize = this.props.labelSize || 'col-md-2'
        var divSize = this.props.labelSize || 'col-md-8'

        return (
            <Field model={this.props.model}>
                <div className="form-group">
                    <label className={labelSize + " control-label"}>{this.props.label}</label>
                    <div className={divSize}>{field}</div>
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
