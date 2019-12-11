import { Schema, arrayOf } from 'normalizr'
import moment from 'moment'

import { LoadingMultiState } from '../../reusable/reducers'
import SimpleReduxLoader from '../../reusable/redux_loader/simple'
import Api from '../../api'


// The steps key lists the steps in the submission flow, used for building navigation
export const PAGES_CONFIG = [
    {key: 'scoreboard', name: 'Scoreboard', className: 'Scoreboard'},
    {key: 'applications', name: 'Team Expansion', className: 'TeamApplication'},
    {key: 'team_members', name: 'Team Members', className: 'TeamMember'},
    {key: 'courses', name: 'Courses', className: 'Course'},
    {key: 'next_qtr_accountabilities', name: 'Accountabilities', className: 'NextQtrAccountability'},
    {key: 'program_leaders', name: 'Program Leaders', className: 'ProgramLeader', hide_nav: true},
    {key: 'review', name: 'Review'},
]

export const SET_REPORTING_DATE = 'submission.setReportingDate'

export const coreInit = new LoadingMultiState('core/coreInit')


class CenterQuarterData extends SimpleReduxLoader {
    constructor() {
        super({
            prefix: 'submission/centerQuarters',
            loader: Api.LocalReport.getCenterQuarter,
            setLoaded: true,
            successHandler(data, {loader}) {
                return loader.replaceItem(data.quarterId, data)
            }
        })
    }

    getLabel(cq) {
        return `${cq.quarter.t1Distinction} ${cq.quarter.year} (starting ${cq.startWeekendDate})`
    }

    getMonthDistinctionLabel(cq) {
        let month = moment(cq.startWeekendDate).format('MMMM')
        return `${month} - ${cq.quarter.t1Distinction} Quarter`
    }
}

export const centerQuarterData = new CenterQuarterData()


/// Some schema definitions


const CenterQuarter = new Schema('quarters', {idAttribute: 'quarterId'})
const Accountability = new Schema('accountabilities')

export const cqResponse = new Schema('c')
cqResponse.define({
    validRegQuarters: arrayOf(CenterQuarter),
    validStartQuarters: arrayOf(CenterQuarter),
    accountabilities: arrayOf(Accountability),
    currentQuarter: CenterQuarter
})
