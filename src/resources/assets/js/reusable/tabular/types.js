import Immutable from 'immutable'

const baseColumn = Immutable.Record({
    key: null,         // Required; key of the table.
    label: null,       // Basically required: heading.
    sortable: true,   // If true, allow sorting on this column.
    component: null,   // if set, a react component.
    selector: null,    // If set, a selector to get this column's value. Defaults to a generated selector.
    sorter: 'string',  // What to use for sorts
    sortSelector: null,// if set, a selector to get the sort value.
    default: null,
    headingClasses: null,

    // Internal, used by us
    _columnClick: null,
    _sort: null
})

export class Column extends baseColumn {
    constructor(input) {
        if (!input.selector) {
            input.selector = makeKeySelector(input.key)
        } else if (input.selector === 'IDENT') {
            input.selector = identitySelector
        }
        if (input.sortSelector === 'KEY') {
            input.sortSelector = makeKeySelector(input.key)
        }
        super(input)
    }
}

function identitySelector(x) { return x }

function makeKeySelector(key) {
    return (obj) => obj[key]
}

export const TableState = Immutable.Record({
    sort_by: Immutable.List(),
})

export const ColumnSort = Immutable.Record({
    column: '',
    direction: 'asc',
})

export function adaptColumnSorts(sorts) {
    return Immutable.List(sorts.map(x => new ColumnSort(x)))
}
