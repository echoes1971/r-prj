
/*
Example:
sleep(200).then(() => {
    ...
})
*/
export function sleep(duration) {
    return new Promise(resolve => {
        setTimeout(resolve, duration)
    })
}

export function randomBetween(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
}

/**
 * Creates a cache for heavy functions like Fibonacci
 * @param {*} cb 
 * @returns 
 */
export function memoize(cb) {
    const cache = new Map()
    return (...args) => {
        const key = JSON.stringify(args)
        if(cache.has(key)) return cache.get(key)

        const result = cb(...args)
        cache.set(key,result)
        return result
    }
}
