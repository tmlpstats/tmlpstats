import React from 'react'
import { Link, withRouter } from 'react-router'
import { delayDispatch, rebind, connectRedux } from '../../reusable/dispatch'
import { Form, Field, SimpleSelect, SimpleField, DateInput, SimpleDateInput } from '../../reusable/form_utils'
import { SubmitFlip } from '../../reusable/ui_basic'

import RegionBase from './RegionBase'
import * as actions from './actions'


@connectRedux()
export class QuarterDates extends RegionBase {
    static mapStateToProps(state) {
        return state.admin.regions
    }

    componentWillMount() {
        rebind(this, 'onSubmit')
    }

    render() {
        if (!this.checkRegions()) {
            return <div>Loading...</div>
        }
        const { region, centers } = this.regionCenters()
        const MODEL = 'admin.regions.quarterDates'

        return (
            <Form model={MODEL} onSubmit={this.onSubmit} className="form-horizontal">
                <h2>Editing blah for {region.name}</h2>
                <SimpleDateInput label="Test Date" model={MODEL+'.foo'} />
                <SimpleField label="Hello" model={MODEL+'.bar'} />
            </Form>
        )
    }

    onSubmit() {
        // TODO
    }
}
