import React from 'react'
import PropTypes from 'prop-types'
import { Link } from 'react-router'

import { rebind } from '../../reusable/dispatch'
import { Form, formActions } from '../../reusable/form_utils'
import { FormTypeahead } from '../../reusable/typeahead'
import { ButtonStateFlip, Alert } from '../../reusable/ui_basic'

export class CenterList extends React.PureComponent {
    static propTypes = {
        centers: PropTypes.array.isRequired,
        linkPrefix: PropTypes.string.isRequired,
    }
    render() {
        const { centers, linkPrefix } = this.props

        const dispCenters = centers.map((center) => {
            const href = `${linkPrefix}/${center.abbreviation}`
            return <div key={center.id}><Link to={href}>{center.name}</Link></div>
        })
        return (
            <div>
                <h4>Please select a center:</h4>
                {dispCenters}
            </div>
        )
    }
}

export class CenterUpdateSelector extends React.PureComponent {
    static propTypes = {
        buttonLabel: PropTypes.string.isRequired,
        centerId: PropTypes.string.isRequired,
        centers: PropTypes.array.isRequired,
        children: PropTypes.node,
        data: PropTypes.object.isRequired,
        dispatch: PropTypes.func.isRequired,
        model: PropTypes.string.isRequired,
        onCompleteUrl: PropTypes.string.isRequired,
        onSubmit: PropTypes.func.isRequired,
        router: PropTypes.object.isRequired,
        saveState: PropTypes.object.isRequired,
    }
    static defaultProps = {
        buttonLabel: 'Save',
    }

    constructor(props) {
        super(props)
        rebind(this, 'onSubmit', 'onSelectAll')
    }

    render() {
        const { buttonLabel, centerId, centers, children, data, model, saveState } = this.props
        const otherCenter = data.applyCenter.length != 1 || data.applyCenter[0] != centerId

        let acWarn
        if (otherCenter) {
            acWarn = (
                <div className="col-md-5">
                <Alert alert="warning" icon="warning-sign">
                    Applying to a different center or more than one center
                    copies these locks to those center(s), overwriting
                    what was there.
                </Alert>
                </div>
            )
        }

        return (
            <Form model={model} onSubmit={this.onSubmit}>
                <div className="row">
                    {children}
                    <div className="form-group">
                        <div className="col-md-2">
                            <label>Apply to center(s)</label>
                            <br />
                            <button type="button" className="btn btn-default" onClick={this.onSelectAll}>Select All Centers</button>
                        </div>
                        <div className="col-md-5">
                            <FormTypeahead
                                    model={model+'.applyCenter'} items={centers}
                                    keyProp="abbreviation" labelProp="name"
                                    multiple={true} rows={8} />
                        </div>
                        {acWarn}
                    </div>
                </div>

                <ButtonStateFlip buttonClass="btn btn-primary btn-lg"
                                 loadState={saveState}
                                 wrapGroup={true}>{buttonLabel}</ButtonStateFlip>
            </Form>
        )
    }

    onSelectAll() {
        const { centers, dispatch, model } = this.props

        dispatch(formActions.change(`${model}.applyCenter`, centers.map(x => x.abbreviation)))
    }

    onSubmit(data) {
        const { dispatch, model, onCompleteUrl, onSubmit, router } = this.props

        dispatch(onSubmit(data.applyCenter[0], data.quarterId, data)).then(() => {
            const applyCenter = data.applyCenter.slice(1)
            if (applyCenter.length > 0) {
                dispatch(formActions.change(`${model}.applyCenter`, applyCenter))
                setTimeout(() => { this.onSubmit({...data, applyCenter}) }, 200)
            } else {
                router.push(onCompleteUrl)
            }
        })
    }
}
