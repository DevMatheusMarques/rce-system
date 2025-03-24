import useSwalAlert from "@/Composables/useSwalAlert.js";
import '/resources/css/composables.css';
import useRemoveFormatInputError from "@/Composables/useRemoveFormatInputError.js";

export default async function useFormatInputError(inputIds = {}, seconds = 3000) {
    // const toast = useSwalAlert();
    try {
        await useRemoveFormatInputError();
        for (let id in inputIds) {
            const input = document.getElementById(id);
            input.classList.add('input-error');
            const p = document.createElement('p');
            p.classList.add('input-error-message');
            p.textContent = inputIds[id];
            input.insertAdjacentElement('afterend', p);
        }

    } catch (error) {
        console.log(error)
        // await toast.fire({
        //     icon: 'error',
        //     title: error
        // });
    }
}
