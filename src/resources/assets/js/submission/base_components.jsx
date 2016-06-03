import React from 'react'

export { React }

export class SubmissionBase extends React.Component {
    baseUri() {
        var params = this.props.params
        return `/center/${params.centerId}/submission/${params.reportingDate}`
    }

    reportingDateString() {
        return this.props.params.reportingDate
    }

    renderBasicLoading() {
        return <div>Loading....</div>
    }
}
