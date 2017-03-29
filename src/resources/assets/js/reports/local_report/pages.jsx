/**
 * Pages for the local report
 *
 * Each page must have an ID exactly equal to the report ID from the reports-generated
 */

import React, { PureComponent, PropTypes } from 'react'

export class NextQtrAccountabilities extends PureComponent {
    static propTypes = {
        initialData: PropTypes.object
    }

    render() {
        const { initialData: { nqas } } = this.props

        let rows = nqas.map((nqa) => {
            return (
                <tr key={nqa.id}>
                    <td>{nqa.accountability.display}</td>
                    <td>{nqa.name || 'N/A'}</td>
                    <td>{nqa.phone || 'N/A'}</td>
                    <td>{nqa.email || 'N/A'}</td>
                </tr>
            )
        })

        return (
            <div className="table-responsive">
                <table className="table table-condensed table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Accountability</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                    </tr>
                    </thead>
                    <tbody>
                        {rows}
                    </tbody>
                </table>
            </div>
        )
    }
}
