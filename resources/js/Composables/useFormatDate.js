export default function useFormatDate(dateFromLaravel, useIn) {
    try {
        if (useIn === 'table') {
            const data = new Date(dateFromLaravel)
            return data.toLocaleString('pt-BR');
        } else if (useIn === 'input') {
            const date = new Date(dateFromLaravel);

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Mês é 0-indexado
            const day = String(date.getDate()).padStart(2, '0');
            const hour = String(date.getHours()).padStart(2, '0');
            const minute = String(date.getMinutes()).padStart(2, '0');

            return `${year}-${month}-${day}T${hour}:${minute}`;
        }

    } catch (error) {
        console.log(error)
    }
}
