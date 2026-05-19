import { Validator } from "./modules/validator.js"

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form')
    if (form) {
        form.addEventListener('submit', (e) => {
            if (!validateForm(form)) {
                e.preventDefault()
            }
        })
    }
    
    // Qui puoi mettere altre cose specifiche di login/register
    // Es: Mostra password, animazioni, ecc.
})

function validateForm(formElement) {
    const inputs = formElement.querySelectorAll('[data-rules]');
    let hasErrors = false;

    inputs.forEach(input => {
        const rules = input.dataset.rules.split('|')
        const value = input.value
        const label = input.previousElementSibling?.innerText || input.name

        for(const rule of rules) {
            const [ruleName, ruleValue] = rule.split(':')

            if(ruleName === 'required' && !Validator.isRequired(value)) {
                showAlert('error', `Field ${label} is required.`)
                hasErrors = true
                break
            }

            if(ruleName === 'email' && !Validator.isEmail(value)) {
                showAlert('error', 'Invalid email address.')
                hasErrors = true
                break
            }

            if(ruleName === 'min' && !Validator.minLength(value, ruleValue)) {
                showAlert('error', `Field ${label} must contain at least ${ruleValue} characters.`)
                hasErrors = true
                break
            }

            if(ruleName === 'match') {
                const target = document.getElementById(ruleValue);
                if (!Validator.match(value, target.value)) {
                    showAlert('error', 'Passwords must match')
                    hasErrors = true
                    break
                }
            }
        }
    })

    return !hasErrors
}