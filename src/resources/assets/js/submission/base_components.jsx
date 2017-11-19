import React from 'react'
import { routerShape } from 'react-router/lib/PropTypes'

import { getErrMessage } from '../reusable/ajax_utils'
import { Alert } from '../reusable/ui_basic'

export { React }

export class SubmissionBase extends React.Component {
    static contextTypes = { router: routerShape }

    baseUri() {
        const params = this.props.params
        return `/center/${params.centerId}/submission/${params.reportingDate}`
    }

    reportingDateString() {
        return this.props.params.reportingDate
    }

    renderBasicLoading(loadState) {
        if (!loadState) {
            loadState = this.props.loading
        }
        if (loadState && loadState.state == 'failed') {
            return (
                <Alert alert="danger" icon="exclamation-sign">
                    {getErrMessage(loadState.error || 'error')}
                </Alert>
            )
        }
        return <div>Loading....</div>
    }
}
