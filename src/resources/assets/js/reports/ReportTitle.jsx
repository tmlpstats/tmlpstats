import React from 'react'
import PropTypes from 'prop-types'
import ReportTokenLink from './ReportTokenLink'

export default class ReportTitle extends React.PureComponent {
    static propTypes = {
        nav: PropTypes.object,
        reportToken: PropTypes.string,
        title: PropTypes.string
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
