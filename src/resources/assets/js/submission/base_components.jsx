import React from 'react'

import { routerShape } from 'react-router/lib/PropTypes'

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
            return <div className="bg-danger">{loadState.error || 'error'}</div>
        }
        return <div>Loading....</div>
    }
}
