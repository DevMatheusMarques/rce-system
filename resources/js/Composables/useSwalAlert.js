import Swal from "sweetalert2";

export default function useSwalAlert(options = {}, timer = 3000) {
    if (Object.keys(options).length === 0) {
        options = {
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        };
    }

    return Swal.mixin(options);
}
