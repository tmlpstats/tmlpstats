import React from 'react'
import renderer from 'react-test-renderer'
import Immutable from 'immutable'
import moment from 'moment'

import { Wrap, store } from '../../testing-store'

import { LocalReport } from '../../../reports/local_report/components'
import { reportConfigData } from '../../../reports/data'
import { LocalKey, reportData } from '../../../reports/local_report/data'
import { lookupsData } from '../../../lookups'


const regionCentersData = lookupsData.scopes.region_centers


describe('LocalReport', () => {
    const baseParams = {centerId: 'abc', reportingDate: '2017-04-07'}
    const key = new LocalKey(baseParams)
    let reportConfig = {
        'statsReportId':12345,
        'globalRegionId':'na',
        'flags':{'canReadContactInfo':true, 'firstWeek': true, 'nextQtrAccountabilities': false},
        'capabilities':{'reportToken':true,'reportNavLinks':true},
        'centerInfo':{'id':16,'name':'ABC','abbreviation':'ABC','teamName':null,'regionId':2},
        'reportToken':'http:\/\/localhost\/report\/abc'
    }
    store.dispatch(regionCentersData.replaceItem('na', Immutable.OrderedMap([
        [1, {id: 1, name: 'AAA', 'abbreviation': 'aaa'}],
        [2, {id: 2, name: 'ABC', 'abbreviation': 'abc'}],
        [3, {id: 3, name: 'DEF', 'abbreviation': 'def'}]
    ])))

    test('Basic', () => {
        store.dispatch(reportConfigData.replaceItem(key, reportConfig))
        store.dispatch(reportData.replaceItem(key.set('page', 'Summary'), 'Summary Page Here'))
        const params = Object.assign({tab1: 'Summary'}, baseParams)
        const tree = renderer.create(
            <Wrap>
                <LocalReport params={params} />
            </Wrap>
        ).toJSON()

        expect(tree).toMatchSnapshot()
    })

    test('Next Qtr Accountabilities', () => {
        reportConfig.flags.nextQtrAccountabilities = true
        store.dispatch(reportConfigData.replaceItem(key, reportConfig))
        store.dispatch(reportData.replaceItem(key.set('page', 'NextQtrAccountabilities'), {nqas: [
                {id: 123, accountability: {display: 'Boss'}, name: 'Person Name', phone: '12345', email: 'abc@def.com', meta: {updatedAt: moment('2017-01-01T02:03:04').utc().format()}}
        ]}))
        const params = Object.assign({tab1: 'NextQtrAccountabilities'}, baseParams)
        const tree = renderer.create(
            <Wrap>
                <LocalReport params={params} />
            </Wrap>
        ).toJSON()
        expect(tree).toMatchSnapshot()
    })
})
