import React from 'react'
import { Link } from 'react-router'
import PropTypes from 'prop-types'
import { defaultMemoize } from 'reselect'

import { arrayFind, objectAssign } from '../../reusable/ponyfill'

import { Form, NullableTextControl, BooleanSelectView, SimpleSelect, connectCustomField, AddOneLink } from '../../reusable/form_utils'
import { ModeSelectButtons, ButtonStateFlip } from '../../reusable/ui_basic'
import { delayDispatch, rebind, connectRedux } from '../../reusable/dispatch'
import { createKeyBasedMemoizer } from '../../reusable/selectors'
import { buildTable } from '../../reusable/tabular'
import { ProgramLeadersIndex } from '../program_leaders/components'

import { TEAM_MEMBERS_COLLECTION_FORM_KEY } from './reducers'
import { teamMembersData, teamMemberText } from './data'
import * as actions from './actions'
import { TeamMembersBase, GITW_LABELS, YES_NO, TDO_OPTIONS } from './components-base'
import {actions as formActions} from "react-redux-form";

const STATE_UPDATING = 'Updating'
const STATE_NOTHING = 'Nothing'
const STATE_SAVED = 'Saved'

const rowProps = {
    [STATE_NOTHING]: {},
    [STATE_UPDATING]: {className: 'bg-warning'},
    [STATE_SAVED]: {className: 'bg-success'}
}

function getRowProps(teamMember) {
    return rowProps[teamMember._updating]
}

function NameColumn(props) {
    const tm = props.data
    return (
        <td>
            <Link to={`${props.columnContext.baseUri}/edit/${tm.id}`}>
                {teamMemberText(tm)}
            </Link>
        </td>
    )
}
NameColumn.propTypes = {
    data: PropTypes.object,
    columnContext: PropTypes.object
}

const NAME_SORT = [{'column': 'name'}]

const TABLE_DEFAULT_COLS = [
    {
        key: 'name', label: 'Name', selector: x => x, sortSelector: teamMemberText,
        component: NameColumn
    },
    {
        key: 'year', label: 'Year',
        selector: row => `T${row.teamYear} Q${row.quarterNumber}`
    },
    {key: 'renderedAccountabilities', label: 'Accountabilities', default: ''
    /*, selector: row => row.map((accId) => accountabilities[accId].display).join(', ')*/},
]

const ClassListDefaultTable = buildTable({
    name: 'submission_team_members_default',
    getRowProps: getRowProps,
    defaultSorts: NAME_SORT,
    columns: TABLE_DEFAULT_COLS.concat([
        {
            key: 'gitw', label: 'GITW', default: '',
            sorter: 'number', selector: 'IDENT',
            sortSelector(tm) {
                if (tm.gitw === true) {
                    return 3
                } else if (tm.gitw === false) {
                    return 2
                } else {
                    return 1
                }
            },
            component: function GitwRow(props) {
                const { modelKey } = props.data
                return <td><BooleanLiveSelect model={modelKey+'.gitw'} emptyChoice=" " labels={GITW_LABELS} /></td>
            }
        },
        {
            key: 'tdo', label: 'TDO', default: '',
            sorter: 'number', sortSelector: 'KEY', selector: 'IDENT',
            component: function TdoRow (props) {
                const { modelKey } = props.data
                return (
                    <td>
                        <SimpleSelect model={modelKey+'.tdo'} emptyChoice=" "
                              items={TDO_OPTIONS} keyProp='k' labelProp='k'
                              changeAction={actions.selectChangeAction} />
                    </td>
                )
            },
        }
    ])
})

const ClassListTravelTable = buildTable({
    name: 'submission_team_members_travel',
    getRowProps: getRowProps,
    defaultSorts: NAME_SORT,
    columns: TABLE_DEFAULT_COLS.concat([
        {
            key: 'travel', label: 'Travel', default: '',
            sorter: 'number', sortSelector: 'KEY', selector: 'IDENT',
            component: function TravelRow(props) {
                const { modelKey } = props.data
                return <td><BooleanLiveSelect model={modelKey+'.travel'} labels={YES_NO} /></td>
            }
        },
        {
            key: 'room', label: 'Rooming', default: '',
            sorter: 'number', sortSelector: x => x.travel,
            selector: x => x,
            component: function RoomRow(props) {
                const { modelKey } = props.data
                return <td><BooleanLiveSelect model={modelKey+'.room'} labels={YES_NO} /></td>
            }
        },
    ])
})

function rppComponent(field) {
    return function RppRow(props) {
        const { modelKey } = props.data
        return <td><NullableTextControl model={modelKey+'.'+field} style={{maxWidth: 'em'}} changeAction={actions.rppChangeAction} /></td>
    }
}

const ClassListRppTable = buildTable({
    name: 'submission_team_members_rpp',
    getRowProps: getRowProps,
    defaultSorts: NAME_SORT,
    columns: TABLE_DEFAULT_COLS.slice(0, 2).concat([
        {
            key: 'rppCap', label: 'CAP Registrations', default: '',
            sorter: 'number', sortSelector: 'KEY',
            selector: 'IDENT',
            component: rppComponent('rppCap')

        },
        {
            key: 'rppCpc', label: 'CPC Registrations', default: '',
            sorter: 'number', 'sortSelector': 'KEY',
            selector: 'IDENT',
            component: rppComponent('rppCpc')
        },
        {
            key: 'rppLf', label: 'LF Registrations', default: '',
            sorter: 'number', 'sortSelector': 'KEY',
            selector: 'IDENT',
            component: rppComponent('rppLf')
        },
    ])
})

const TABLES = [
    {key: 'submission_team_members_default', label: 'GITW/TDO', table: ClassListDefaultTable},
    {key: 'submission_team_members_travel', label: 'Travel/Room', table: ClassListTravelTable},
    {key: 'submission_team_members_rpp', label: 'Registrations', table: ClassListRppTable},
]

@connectRedux()
export class TeamMembersIndex extends TeamMembersBase {
    static mapStateToProps() {
        return (state) => {
            const submission = state.submission
            const { weeklySave, weeklyReporting, teamMembers } = submission.team_members
            const { lookups } = state.submission.core
            return { weeklySave, weeklyReporting, teamMembers, lookups }
        }
    }
    constructor(props) {
        super(props)
        rebind(this, 'saveWeeklyReporting', 'changeTableFormat', 'markDefault')
        const keyMemoizer = createKeyBasedMemoizer()
        this.preprocess = defaultMemoize((teamMembers, accountabilities, wr, weeklySave) => {
            // Phase1: The keyMemoizer only re-annotates team members if they've changed.
            const annotatedMembers = keyMemoizer(teamMembers, (tm) => {
                let toSet = {
                    modelKey: `${TEAM_MEMBERS_COLLECTION_FORM_KEY}.${tm.id}`,
                    _updating: wr.changed[tm.id]? STATE_UPDATING: STATE_NOTHING
                }

                // accountabilities lookup
                const acc = tm.accountabilities
                if (acc && acc.length) {
                    toSet.renderedAccountabilities = acc.map((accId) => accountabilities[accId].display).join(', ')
                }
                return objectAssign({}, tm, toSet)
            })

            // Phase2: separate out withdrawn
            const withdrawn = [], current = []
            for (let k in annotatedMembers) {
                let tm = annotatedMembers[k]
                if (tm.withdrawCode || tm.wbo || tm.xferOut) {
                    withdrawn.push(tm)
                } else {
                    if (wr.changed[k] && weeklySave.loaded && wr.working && wr.working[k] >= wr.changed[k]) {
                        tm = objectAssign({}, tm, {_updating: STATE_SAVED})
                    }
                    current.push(tm)
                }
            }

            return {current, withdrawn}
        })
        this.makeColumnContext = defaultMemoize((baseUri) => {
            return { baseUri }
        })
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }

        const baseUri = this.teamMembersBaseUri()
        const { weeklySave, weeklyReporting: wr, teamMembers, lookups } = this.props
        const { current, withdrawn } = this.preprocess(teamMembers.data, this.props.lookups.accountabilities, wr, weeklySave)
        const columnContext = this.makeColumnContext(baseUri)

        const withdraws = withdrawn.map(tm => {
            return (
                <TeamMemberWithdrawnRow
                        key={tm.id} teamMember={tm} baseUri={baseUri}
                        lookups={lookups} />
            )
        })
        let withdrawTable
        if (withdraws.length) {
            withdrawTable = (
                <div>
                <br/>
                    <h4>Withdraws/Transfers</h4>
                    <table className="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Year</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>{withdraws}</tbody>
                    </table>
                </div>
            )
        }

        const format = teamMembers.meta.get('format')
        const currentTablePrefs = arrayFind(TABLES, table => table.key === format)
        const TableClass = currentTablePrefs.table

        return (
            <Form model={TEAM_MEMBERS_COLLECTION_FORM_KEY} onSubmit={this.saveWeeklyReporting}
                  onChange={modelValue => console.log(modelValue)}
            >
                <h3>Team Members</h3>
                <div style={{position: 'relative'}}>
                <ModeSelectButtons
                        items={TABLES} current={teamMembers.meta.get('format')}
                        onClick={this.changeTableFormat} ariaGroupDesc="Sort Preferences" />
                    {/*{
                        currentTablePrefs.label === 'GITW/TDO' &&
                        <button style={{float: 'right'}}
                                   onClick={this.markDefault}>
                            Mark all as default
                        </button>
                    }
                    {
                        currentTablePrefs.label === 'Travel/Room' &&
                        <button style={{float: 'right'}}
                                onClick={this.markDefault}>
                            Mark all as default
                        </button>
                    }*/}
                </div>
                <TableClass data={current} columnContext={columnContext} />
                <ButtonStateFlip loadState={weeklySave}>Save {currentTablePrefs.label} changes</ButtonStateFlip>
                <br />
                <AddOneLink link={`${baseUri}/add`} />
                {withdrawTable}
                <br />
                <ProgramLeadersIndex params={this.props.params} />
            </Form>
        )
    }

    markDefault() {
        let table = document.querySelector('[id^="tbl-submission_team_members_"]').getElementsByTagName("tbody")[0];
        let rows = table.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            let row = rows[i];
            let cols = row.getElementsByTagName('td');

            let gitwCol = cols[3], tdoCol = cols[4];

            let gitwSelect = gitwCol.getElementsByTagName('select')[0];
            let tdoSelect = tdoCol.getElementsByTagName('select')[0];

            this.props.dispatch(formActions.change(gitwSelect.name, true))
            this.props.dispatch(formActions.change(tdoSelect.name, tdoSelect.name.endsWith('tdo') ? 0 : true))

            this.props.dispatch(actions.markWeeklyReportingFromModel(gitwSelect.name));
        }
    }

    saveWeeklyReporting(data) {
        if (this.props.weeklySave.state == 'new') {
            const { centerId, reportingDate } = this.props.params
            delayDispatch(this, actions.weeklyReportingSubmit(
                centerId, reportingDate,
                this.props.weeklyReporting, data
            ))
        }
    }

    changeTableFormat(newFormat) {
        this.props.dispatch(teamMembersData.setMeta('format', newFormat))
    }
}

class TeamMemberWithdrawnRow extends React.PureComponent {
    static propTypes = {
        baseUri: PropTypes.string.isRequired,
        lookups: PropTypes.object.isRequired,
        teamMember: PropTypes.object.isRequired
    }

    render() {
        const { baseUri, teamMember, lookups } = this.props
        let reason = 'Transfered to another team'
        if (teamMember.withdrawCode) {
            reason = lookups.withdraw_codes_by_id[teamMember.withdrawCode].display
        } else if (teamMember.wbo) {
            reason = 'Well-being Issue'
        }
        return (
            <tr>
                <td>
                    <Link to={`${baseUri}/edit/${teamMember.id}`}>
                        {teamMemberText(teamMember)}
                    </Link>
                </td>
                <td>T{teamMember.teamYear} Q{teamMember.quarterNumber}</td>
                <td>{reason}</td>
            </tr>
        )
    }
}

@connectCustomField
class BooleanLiveSelect extends BooleanSelectView {
    onChange(e) {
        console.log('change')
        super.onChange(e)
        this.props.dispatch(actions.markWeeklyReportingFromModel(this.props.model))
    }
}
