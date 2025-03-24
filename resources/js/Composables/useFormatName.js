export default function useFormatName(name) {
    try {
        const partsName = name.split(' ');
        if (partsName.length === 1) {
            return partsName[0];
        }
        return `${partsName[0]} ${partsName[partsName.length - 1]}`;
    } catch (error) {
        console.log(error)
    }
}
