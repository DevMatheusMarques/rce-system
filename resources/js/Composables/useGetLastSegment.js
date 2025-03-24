export default function useGetLastSegment(urlComplete) {
    try {
        urlComplete = new URL(urlComplete);
        const pathArray = urlComplete.pathname.split("/");
        return pathArray[pathArray.length - 1];
    } catch (error) {
        console.log(error)
    }
}
