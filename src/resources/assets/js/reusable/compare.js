
// shallowArrayCompare does the fastest thing to do direct comparisons of elements in an array.
// This only compares reference equality, and will not succeed for non-reference scenarios.
// This means it's mostly useful in situations like Redux store comparisons and react prop comparisons.
export function shallowArrayElementsEqual(a, b) {
    if (a === b) {
        // If the two arrays are the same reference, don't do any more work.
        return true
    }
    const aLength = a.length
    if (aLength != b.length) {
        // If the lengths don't equal, don't do any more work.
        return false
    }
    for (var i = 0; i < aLength; i++) {
        if (a[i] !== b[i]) {
            // If any element isn't reference equal, we are not equal
            return false
        }
    }
    // If we get here, the arrays must be shallow-equal.
    return true
}
