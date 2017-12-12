export const CLICK_COLUMN = 'tabular/click-column'
export const CLICK_ITEM = 'tabular/click-item'
export const OVERRIDE_SORT = 'tabular/override-sort'


// clickColumn is used when we click a column, maybe with a modifier key.
export function clickColumn(table, column, modKey) {
    return {type: CLICK_COLUMN, table, column, modKey}
}

export function overrideSort(table, sorts) {
    return {type: OVERRIDE_SORT, table, sorts}
}
