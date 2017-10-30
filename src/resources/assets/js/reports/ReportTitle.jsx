import React, { PropTypes } from 'react'
import ReportTokenLink from './ReportTokenLink'

export default class ReportTitle extends React.Component {
    static propTypes = {
        title: PropTypes.string,
        reportToken: PropTypes.string,
        nav: PropTypes.object
    }

    render() {
        const { title, reportToken, nav } = this.props

        let reportTokenLink
        if (reportToken) {
            reportTokenLink = <ReportTokenLink token={reportToken} />
        }

        return (
            <div className="topLevelTitle">
                <h2>{title} {reportTokenLink}</h2>
                {nav}
            </div>
        )
    }
}
