import React from 'react'
import renderer from 'react-test-renderer'

import { Provider, store, Wrap } from '../../testing-store'
import { setReportingDate } from '../../../submission/core/actions'
import { initializeScoreboard } from '../../../submission/scoreboard/actions'
import SubmissionScoreboard, { ScoreboardRow } from '../../../submission/scoreboard/components'

const GAMES = [
    {"rating":"Marginally Effective","games":{"cap":{"promise":2,"actual":2,"percent":100,"points":8},"cpc":{"promise":1,"actual":-4,"percent":0,"points":0},"t1x":{"promise":1,"actual":0,"percent":0,"points":0},"t2x":{"promise":1,"actual":0,"percent":0,"points":0},"gitw":{"promise":75,"actual":70,"percent":93,"points":3},"lf":{"promise":5,"actual":3,"percent":60,"points":0}},"meta":{"weekNum":1,"canEditPromise":false,"canEditActual":false},"week":"2017-02-24"},
    {"rating":"Effective","games":{"cap":{"promise":6,"actual":6,"percent":100,"points":8},"cpc":{"promise":2,"actual":3,"percent":150,"points":4},"t1x":{"promise":1,"actual":0,"percent":0,"points":0},"t2x":{"promise":1,"actual":0,"percent":0,"points":0},"gitw":{"promise":75,"actual":78,"percent":104,"points":4},"lf":{"promise":12,"actual":3,"percent":25,"points":0}},"meta":{"weekNum":2,"canEditPromise":false,"canEditActual":false},"week":"2017-03-03"},
    {"rating":"Marginally Effective","games":{"cap":{"promise":19,"actual":18,"percent":95,"points":6},"cpc":{"promise":4,"actual":5,"percent":125,"points":4},"t1x":{"promise":1,"actual":0,"percent":0,"points":0},"t2x":{"promise":1,"actual":0,"percent":0,"points":0},"gitw":{"promise":75,"actual":57,"percent":76,"points":1},"lf":{"promise":25,"actual":6,"percent":24,"points":0}},"meta":{"weekNum":3,"isClassroom":true,"canEditPromise":false,"canEditActual":false},"week":"2017-03-10"},
    {"rating":"Marginally Effective","games":{"cap":{"promise":26,"actual":22,"percent":85,"points":4},"cpc":{"promise":5,"actual":4,"percent":80,"points":2},"t1x":{"promise":5,"actual":0,"percent":0,"points":0},"t2x":{"promise":2,"actual":0,"percent":0,"points":0},"gitw":{"promise":75,"actual":81,"percent":108,"points":4},"lf":{"promise":30,"actual":9,"percent":30,"points":0}},"meta":{"weekNum":4,"canEditPromise":false,"canEditActual":false},"week":"2017-03-17"},
    {"rating":"Ineffective","games":{"cap":{"promise":33,"actual":24,"percent":73,"points":0},"cpc":{"promise":6,"actual":4,"percent":67,"points":0},"t1x":{"promise":8,"actual":0,"percent":0,"points":0},"t2x":{"promise":2,"actual":0,"percent":0,"points":0},"gitw":{"promise":75,"actual":69,"percent":92,"points":3},"lf":{"promise":35,"actual":11,"percent":31,"points":0}},"meta":{"weekNum":5,"canEditPromise":false,"canEditActual":false},"week":"2017-03-24"},
    {"rating":"Ineffective","games":{"cap":{"promise":39,"actual":26,"percent":67,"points":0},"cpc":{"promise":7,"actual":4,"percent":57,"points":0},"t1x":{"promise":8,"actual":0,"percent":0,"points":0},"t2x":{"promise":2,"actual":2,"percent":100,"points":4},"gitw":{"promise":75,"actual":83,"percent":111,"points":4},"lf":{"promise":40,"actual":18,"percent":45,"points":0}},"meta":{"weekNum":6,"canEditPromise":false,"canEditActual":false},"week":"2017-03-31"},
    {"rating":"Marginally Effective","games":{"cap":{"promise":52,"actual":43,"percent":83,"points":4},"cpc":{"promise":44,"actual":34,"percent":77,"points":1},"t1x":{"promise":8,"actual":-1,"percent":0,"points":0},"t2x":{"promise":2,"actual":2,"percent":100,"points":4},"gitw":{"promise":75,"actual":71,"percent":95,"points":3},"lf":{"promise":53,"actual":20,"percent":38,"points":0}},"meta":{"weekNum":7,"isClassroom":true,"canEditPromise":false,"canEditActual":false},"week":"2017-04-07"},
    {"rating":"Ineffective","games":{"cap":{"promise":49,"actual":null,"percent":0,"points":0},"cpc":{"promise":34,"actual":null,"percent":0,"points":0},"t1x":{"promise":8,"actual":null,"percent":0,"points":0},"t2x":{"promise":2,"actual":null,"percent":0,"points":0},"gitw":{"promise":75,"actual":null,"percent":0,"points":0},"lf":{"promise":22,"actual":null,"percent":0,"points":0}},"meta":{"weekNum":8,"canEditPromise":false,"canEditActual":false},"week":"2017-04-14"},
    {"rating":"Ineffective","games":{"cap":{"promise":49,"actual":null,"percent":0,"points":0},"cpc":{"promise":34,"actual":null,"percent":0,"points":0},"t1x":{"promise":8,"actual":null,"percent":0,"points":0},"t2x":{"promise":2,"actual":null,"percent":0,"points":0},"gitw":{"promise":75,"actual":null,"percent":0,"points":0},"lf":{"promise":26,"actual":null,"percent":0,"points":0}},"meta":{"weekNum":9,"canEditPromise":false,"canEditActual":false},"week":"2017-04-21"},
    {"rating":"Ineffective","games":{"cap":{"promise":55,"actual":null,"percent":0,"points":0},"cpc":{"promise":34,"actual":null,"percent":0,"points":0},"t1x":{"promise":8,"actual":null,"percent":0,"points":0},"t2x":{"promise":3,"actual":null,"percent":0,"points":0},"gitw":{"promise":75,"actual":null,"percent":0,"points":0},"lf":{"promise":30,"actual":null,"percent":0,"points":0}},"meta":{"weekNum":10,"canEditPromise":false,"canEditActual":false},"week":"2017-04-28"},
    {"rating":"Ineffective","games":{"cap":{"promise":61,"actual":null,"percent":0,"points":0},"cpc":{"promise":34,"actual":null,"percent":0,"points":0},"t1x":{"promise":8,"actual":null,"percent":0,"points":0},"t2x":{"promise":3,"actual":null,"percent":0,"points":0},"gitw":{"promise":75,"actual":null,"percent":0,"points":0},"lf":{"promise":34,"actual":null,"percent":0,"points":0}},"meta":{"weekNum":11,"isClassroom":true,"canEditPromise":false,"canEditActual":false},"week":"2017-05-05"},
    {"rating":"Ineffective","games":{"cap":{"promise":67,"actual":null,"percent":0,"points":0},"cpc":{"promise":34,"actual":null,"percent":0,"points":0},"t1x":{"promise":8,"actual":null,"percent":0,"points":0},"t2x":{"promise":3,"actual":null,"percent":0,"points":0},"gitw":{"promise":75,"actual":null,"percent":0,"points":0},"lf":{"promise":38,"actual":null,"percent":0,"points":0}},"meta":{"weekNum":12,"canEditPromise":false,"canEditActual":false},"week":"2017-05-12"},
    {"rating":"Ineffective","games":{"cap":{"promise":74,"actual":"","percent":0,"points":0},"cpc":{"promise":35,"actual":"","percent":0,"points":0},"t1x":{"promise":8,"actual":null,"percent":0,"points":0},"t2x":{"promise":3,"actual":null,"percent":0,"points":0},"gitw":{"promise":75,"actual":null,"percent":0,"points":0},"lf":{"promise":45,"actual":null,"percent":0,"points":0}},"meta":{"localChanges":true,"weekNum":13,"canEditPromise":false,"canEditActual":true},"week":"2017-05-19"},
    {"rating":"Ineffective","games":{"cap":{"promise":84,"actual":null,"percent":0,"points":0},"cpc":{"promise":35,"actual":null,"percent":0,"points":0},"t1x":{"promise":8,"actual":null,"percent":0,"points":0},"t2x":{"promise":4,"actual":null,"percent":0,"points":0},"gitw":{"promise":75,"actual":null,"percent":0,"points":0},"lf":{"promise":47,"actual":null,"percent":0,"points":0}},"meta":{"weekNum":14,"canEditPromise":false,"canEditActual":false},"week":"2017-05-26"},
    {"rating":"Ineffective","games":{"cap":{"promise":86,"actual":null,"percent":0,"points":0},"cpc":{"promise":35,"actual":null,"percent":0,"points":0},"t1x":{"promise":10,"actual":null,"percent":0,"points":0},"t2x":{"promise":4,"actual":null,"percent":0,"points":0},"gitw":{"promise":88,"actual":null,"percent":0,"points":0},"lf":{"promise":50,"actual":null,"percent":0,"points":0}},"meta":{"weekNum":15,"canEditPromise":false,"canEditActual":false},"week":"2017-06-02"}
]

describe('SubmissionScoreboard', () => {

    it('Should Render As Snapshot', () => {
        store.dispatch(setReportingDate('2017-04-07'))
        store.dispatch(initializeScoreboard(GAMES))
        const params = {centerId: 'ABC', reportingDate: '2017-04-07'}
        const tree = renderer.create(
            <Provider store={store}>
                <SubmissionScoreboard
                    params={params}
                    />
            </Provider>
        ).toJSON()
        expect(tree).toMatchSnapshot()
    })
})

describe('ScoreboardRow', () => {
    it('Renders no heading when week is out', () => {
        const tree = renderer.create(
            <Wrap>
                <ScoreboardRow currentWeek='2017-04-07' weeks={GAMES.slice(0, 3)} />
            </Wrap>
        ).toJSON()
        expect(tree).toMatchSnapshot()
        const theadTH = tree.children[0].children[0]
        expect(theadTH.children.length).toEqual(5)
        expect(theadTH.children[1].props.className).toEqual('dayHead')
    })

    it('Renders a heading when week is in the set', () => {
        const tree = renderer.create(
            <Wrap>
                <ScoreboardRow currentWeek='2017-02-24' weeks={GAMES.slice(0, 3)} />
            </Wrap>
        ).toJSON()
        expect(tree).toMatchSnapshot()
        const theadTH = tree.children[0].children[0]
        expect(theadTH.children.length).toEqual(5)
        expect(theadTH.children[1].props.className).toEqual('dayHead currentWeek')
    })
})
