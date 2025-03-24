import '/resources/css/composables.css';

export default async function useRemoveFormatInputError() {
    try {
        const pOld = document.querySelectorAll('.input-error-message');
        const inputsOld = document.querySelectorAll('.input-error');

        if (pOld) {
            pOld.forEach((el) => {
                el.remove();
            });
        }
        if (inputsOld) {
            inputsOld.forEach((el) => {
                el.classList.remove('input-error');
            });
        }
    } catch (error) {
        console.error(error)
    }
}
