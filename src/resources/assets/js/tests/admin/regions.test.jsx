import * as selectors from '../../admin/regions/selectors'

const ISODATE = 'YYYY-MM-DD'

describe('admin -> regions -> selectors', () => {
    const basicRegionQuarter = {
        id: '1/37',
        quarterId: 37,
        regionId: 1,
        startWeekendDate: '2017-08-18',
        endWeekendDate: '2017-11-17',
        classroom1Date:'2017-09-08',
        classroom2Date:'2017-10-06',
        classroom3Date:'2017-11-03',
        firstWeekDate: '2017-08-25'
    }

    describe('quarterReportingDates', () => {
        it('should work fine for a proper quarter', () => {
            let dates = selectors.quarterReportingDates(basicRegionQuarter)
            expect(dates.length).toBe(13)
            expect(dates[0].format(ISODATE)).toBe('2017-08-25')
            expect(dates[12].format(ISODATE)).toBe('2017-11-17')
        })

        it('Should save itself when given a super-long quarter', () => {
            const badQuarter = Object.assign({}, basicRegionQuarter, {endWeekendDate: '2028-05-01'})
            let dates = selectors.quarterReportingDates(badQuarter)
            expect(dates.length).toBe(49)
        })

        it('Should save itself when given a broken quarter', () => {
            const badQuarter = Object.assign({}, basicRegionQuarter, {endWeekendDate: null})
            let dates = selectors.quarterReportingDates(badQuarter)
            expect(dates.length).toBe(0)
        })

    })


    describe('annotatedRegionQuarter', () => {
        it('should annotate the whole quarter', () => {
            let rq = selectors.annotatedRegionQuarter(basicRegionQuarter)
            expect(rq).toBeDefined()

            expect(rq.reportingDates.length).toBe(13)
            expect(rq.reportingDates[12].format(ISODATE)).toBe('2017-11-17')
            expect(rq.annotatedDates.size).toBe(13)
            const firstWeekAnnotated = rq.annotatedDates.get('2017-08-25')
            expect(firstWeekAnnotated).toMatchObject({dateString: '2017-08-25', isMilestone: false})
            expect(rq.annotatedDates.get('2017-10-06')).toMatchObject({dateString: '2017-10-06', isMilestone: true})
        })

    })
})
