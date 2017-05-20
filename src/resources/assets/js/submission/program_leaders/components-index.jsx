import { Link } from 'react-router'
import { React } from '../base_components'
import { connectRedux } from '../../reusable/dispatch'

import { ProgramLeadersBase, ACCOUNTABILITY_DISPLAY } from './components-base'

@connectRedux()
export class ProgramLeadersIndex extends ProgramLeadersBase {

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }

        const { data } = this.props.programLeaders

        return (
            <div>
                <h3>Program Leaders</h3>
                <table className="table programLeadersTable">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Attending Weekend</th>
                        </tr>
                    </thead>
                    <tbody>{this.renderLeaders(data)}</tbody>
                </table>
            </div>
        )
    }

    renderLeaders(data) {
        let leaders = []
        ACCOUNTABILITY_DISPLAY.forEach((display, accountability) => {
            const id = data.meta[accountability]

            let leaderData = data[id]
            if (!leaderData) {
                leaderData = {
                    firstName: '-',
                    lastName: '',
                    phone: '-',
                    email: '-',
                }
            }

            let attendingWeekend = leaderData.attendingWeekend ? 'yes' : 'no'
            if (leaderData.attendingWeekend == undefined) {
                attendingWeekend = 'Not Reported'
            }
            leaders.push(
                <tr key={accountability}>
                    <td>
                        <Link to={`${this.programLeadersBaseUri()}/edit/${accountability}`}>
                            {display}
                        </Link>
                    </td>
                    <td>{leaderData.firstName} {leaderData.lastName}</td>
                    <td>{leaderData.phone}</td>
                    <td>{leaderData.email}</td>
                    <td>{attendingWeekend}</td>
                </tr>
            )
        })
        return leaders
    }
}
