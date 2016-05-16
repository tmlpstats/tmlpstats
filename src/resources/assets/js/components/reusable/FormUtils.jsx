import React from 'react'
import { Field } from 'react-redux-form'


export class SimpleField extends React.Component {
    render() {
        var id='blah-' + this.props.id
        var field;
        if (this.props.customField) {
            field = this.props.children
        } else {
            field = <input type="text" className="form-control" />
        }
        return (
            <Field model={this.props.model}>
                <div className="form-group">
                    <label className="col-md-2 control-label">{this.props.label}</label>
                    <div className="col-md-8">{field}</div>
                </div>
            </Field>
        )
    }
}
