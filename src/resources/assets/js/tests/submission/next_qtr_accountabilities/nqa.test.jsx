import React from 'react'
import renderer from 'react-test-renderer'

import { Provider, newTestingStore } from '../../testing-store'
import { setReportingDate } from '../../../submission/core/actions'
import { initializeApplications } from '../../../submission/applications/actions'
import { initializeTeamMembers } from '../../../submission/team_members/actions'
import { teamMembersData } from '../../../submission/team_members/data'
import { QuarterAccountabilities, QuarterAccountabilitiesTable } from '../../../submission/next_qtr_accountabilities/components'
import { qtrAccountabilitiesData } from '../../../submission/next_qtr_accountabilities/data'

import { defaultSubmissionLookups } from '../core-lookups'

describe('NQA', () => {
    const store = newTestingStore()
    const params = {centerId: 'ABC', reportingDate: '2017-04-07'}

    it('Should display loading screen', () => {
        const tree = renderer.create(
            <Provider store={store}>
                <QuarterAccountabilities
                    params={params}
                    />
            </Provider>
        ).toJSON()
        expect(tree).toMatchSnapshot()
    })


    it('Should Render As Snapshot', () => {
        store.dispatch(setReportingDate('2017-04-07'))
        store.dispatch(initializeApplications({123: {id: 123, firstName: 'Person1', lastName: 'Person1Last'}}))
        store.dispatch(initializeTeamMembers({
            '789': {id: 789, firstName: 'TM1', lastName: 'TM1Last', email: 'EMAIL', 'phone': 'PHONE'}
        }))
        store.dispatch(teamMembersData.loadState('loaded'))
        store.dispatch(defaultSubmissionLookups())
        window.apiMocks['Submission.NextQtrAccountability.allForCenter'] = () => {
            return Promise.resolve([
                {id: 6, 'teamMember': 789}, // T1TL checks team member path
                {id: 4, 'application': 123}, // statistician checks application path
                {id: 5, 'name': 'Person Name', email: 'Person@email.com', phone: '111-222-3333'}, // stats apprentice checks the "other" path
            ])
        }
        return store.dispatch(qtrAccountabilitiesData.load({center: 'DEN', reportingDate: '2017-04-07'})).then(() => {
            const component = renderer.create(
                <Provider store={store}>
                    <QuarterAccountabilitiesTable
                        params={params}
                        />
                </Provider>
            )
            let tree = component.toJSON()
            expect(tree).toMatchSnapshot()
        })
    })

})
