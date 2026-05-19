export const Validator = {
    isEmail: email => {
        const regEx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        return regEx.test(email)
    },
    minLength: (value, min) => value.length >= min,
    isRequired: value => value.trim().length > 0,
    matches: (val1, val2) => val1 === val2
}