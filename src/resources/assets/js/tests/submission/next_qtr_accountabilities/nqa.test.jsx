import React from 'react'
import renderer from 'react-test-renderer'

import { Provider, newTestingStore } from '../../testing-store'
import { setReportingDate } from '../../../submission/core/actions'
import { initializeApplications } from '../../../submission/applications/actions'
import { QuarterAccountabilitiesTable } from '../../../submission/next_qtr_accountabilities/components'
import { qtrAccountabilitiesData } from '../../../submission/next_qtr_accountabilities/data'

import { defaultSubmissionLookups } from '../core-lookups'

describe('NQA', () => {
    const store = newTestingStore()

    it('Should Render As Snapshot', () => {
        store.dispatch(setReportingDate('2017-04-07'))
        store.dispatch(initializeApplications({123: {id: 123, firstName: 'Person1', lastName: 'Person1Last'}}))
        store.dispatch(defaultSubmissionLookups())
        window.apiMocks['Submission.NextQtrAccountability.allForCenter'] = () => {
            return Promise.resolve([{id: 1}])
        }
        return store.dispatch(qtrAccountabilitiesData.load({center: 'DEN', reportingDate: '2017-04-07'})).then(() => {
            const params = {centerId: 'ABC', reportingDate: '2017-04-07'}
            const tree = renderer.create(
                <Provider store={store}>
                    <QuarterAccountabilitiesTable
                        params={params}
                        />
                </Provider>
            ).toJSON()
            expect(tree).toMatchSnapshot()
        })
    })
})
