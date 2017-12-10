import React from 'react'
import renderer from 'react-test-renderer'
import { Provider, newTestingStore } from '../testing-store'

import * as actions from '../../reusable/tabular/actions'
import reducer from '../../reusable/tabular/reducer'
import { ColumnSort } from '../../reusable/tabular/types'
import { buildTable } from '../../reusable/tabular'

describe('Reducer', () => {
    const TABLE_NAME = 'mytable_x'

    function getTable(result) {
        return result.tables.get(TABLE_NAME)
    }

    test('Defaults', () => {
        expect(reducer(undefined, {type: 'ignoreme'})).toBeDefined()
        expect(reducer(undefined, actions.clickColumn(TABLE_NAME, 'abc'))).toBeDefined()
        expect(reducer(undefined, {type: 'tabular/bogus'})).toBeDefined()
    })

    describe('Column Click', () => {
        it('Should assign a sort column when there is none', () => {
            const result = reducer(undefined, actions.clickColumn(TABLE_NAME, 'abc'))
            expect(result.tables.size).toBe(1)
            const t = getTable(result)
            expect(t.sort_by.size).toBe(1)
            const firstSort = t.sort_by.get(0)
            expect(firstSort.column).toBe('abc')
            expect(firstSort.direction).toBe('asc')
        })

        const singleSort = reducer(undefined, actions.clickColumn(TABLE_NAME, 'def'))

        it('Should flip sort on double-sort of same column', () => {
            const r1 = reducer(singleSort, actions.clickColumn(TABLE_NAME, 'def'))
            const t1 = getTable(r1)
            expect(t1.sort_by.size).toBe(1)
            expect(t1.sort_by.get(0).direction).toBe('desc')
            const r2 = reducer(r1, actions.clickColumn(TABLE_NAME, 'def', true))
            const t2 = getTable(r2)
            expect(t2.sort_by.size).toBe(1)
            expect(t2.sort_by.get(0).direction).toBe('asc')
        })

        it('Should replace on different columnn click', () => {
            const r1 = reducer(singleSort, actions.clickColumn(TABLE_NAME, 'ghi'))
            const t = getTable(r1)
            expect(t.sort_by.size).toBe(1)
            expect(t.sort_by.get(0).column).toBe('ghi')
            expect(t.sort_by.get(0).direction).toBe('asc')
        })

        it('Should compound sort on different columnn click', () => {
            const r1 = reducer(singleSort, actions.clickColumn(TABLE_NAME, 'ghi', true))
            const t = getTable(r1)
            expect(t.sort_by.size).toBe(2)
            expect(t.sort_by.get(0).column).toBe('def')
            expect(t.sort_by.get(1).column).toBe('ghi')
        })
    })

    describe('Replace Sorts', () => {
        const initial = reducer(undefined, actions.clickColumn(TABLE_NAME, 'ignoreme'))

        it('Should replace sorts', () => {
            const input = [
                {'column': 'aaa'},
                {'column': 'bbb', direction: 'desc'},
                new ColumnSort({column: 'ccc', direction: 'desc'})
            ]
            const r = reducer(initial, actions.overrideSort(TABLE_NAME, input))
            const { sort_by } = getTable(r)
            expect(sort_by.size).toBe(3)
            expect(sort_by.map(x => [x.column, x.direction]).toJS()).toEqual([
                ['aaa', 'asc'], ['bbb', 'desc'], ['ccc', 'desc']
            ])
            expect(sort_by.get(1)).toBeInstanceOf(ColumnSort)
            expect(sort_by.get(2)).toBe(input[2]) // Record should be unmodified

        })

    })
})

describe('Tables', () => {
    const store = newTestingStore()

    const NAME = 'abc'
    const MyTable = buildTable({
        name: NAME,
        columns: [
            {key: 'name', label: 'Name'},
            {key: 'uName', label: 'Upper Name', selector: x => x.name.toUpperCase()},
            {key: 'age', sorter: 'number'}
        ]
    })

    const data1 = [
        {id: 1, name: 'Bob', age: 55},
        {id: 2, name: 'Mary', age: 49},
        {id: 3, name: 'Joan', age: 38}
    ]

    it('Should Render Snapshot', () => {
        const tree = renderer.create(
            <Provider store={store}>
                <MyTable data={data1} />
            </Provider>
        ).toJSON()

        expect(tree).toMatchSnapshot()
    })


    const getHeadings = tree => tree.children[0].children[0].children // thead -> tr -> children
    const getTbodyRows = tree => tree.children[1].children // [thead, tbody] .. children
    const getNameOnly = row => row.children[0].children[0] // first td -> text node

    it('Should respond to sorts', () => {
        function renderAndFetchNames() {
            const tree = renderer.create(
                <Provider store={store}>
                    <MyTable data={data1} />
                </Provider>
            ).toJSON()

            const rows = getTbodyRows(tree)
            const names = rows.map(getNameOnly)
            return {tree, rows, names}
        }

        store.dispatch(actions.clickColumn(NAME, 'name'))
        expect(renderAndFetchNames().names).toEqual(['Bob', 'Joan', 'Mary'])

        store.dispatch(actions.clickColumn(NAME, 'name')) // Flips to descending
        const { tree: tree1, names: names1 } = renderAndFetchNames()
        expect(names1).toEqual(['Mary', 'Joan', 'Bob'])

        // Instead of using store.dispatch let's use an action handler simulating a real click
        getHeadings(tree1)[2].props.onClick({persist: function() {}})
        expect(renderAndFetchNames().names).toEqual(['Joan', 'Mary', 'Bob'])

    })

})
