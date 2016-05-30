import React from 'react'

export { React }

export class SubmissionBase extends React.Component {
    baseUri() {
        var centerId = this.props.params.centerId
        return `/center/${centerId}/submission`
    }
}
