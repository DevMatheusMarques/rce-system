<script>
import ModalRight from "@/Components/ModalRight.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";

export default {
    components: {ModalRight},
    emits: ['close', 'save'],
    name: "UpdateEmail",
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    props: {
        visible: Boolean,
        authUser: Object
    }, methods: {
        async emailUpdate() {
            const formLogin = document.getElementById("form-email");
            const formData = new FormData(formLogin);
            const endpointRoute = '/' + this.authUser.level + '/user/email/update';

            try {
                const response = await axios.put(endpointRoute, {
                    email: formData.get('email'),
                    email_confirmation: formData.get('email_confirmation'),
                    current_password: formData.get('current_password'),
                });
                await this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                setTimeout(async () => {
                    this.$emit('close');
                }, 600);
                window.location.href = response.data.redirect;

            } catch (errors) {
                if (errors.response.status === 422) {
                    const inputsIds = errors.response.data.errors;
                    await useFormatInputError(inputsIds)
                }

                this.errors = errors.response.data.message;
                await this.toast.fire({
                    icon: 'error',
                    title: this.errors
                });
            }
        },
        removeStyleInputError(event) {
            const input = event.target;
            if (input.classList.contains('input-error')) {
                input.classList.remove('input-error');
            }
        }
    }
}
</script>

<template>
    <ModalRight
        :visible="visible"
        @close="$emit('close')"
        @save="emailUpdate"
        title="Atualizar email"
    >
        <form action="" class="form__password form" method="post" id="form-email">
            <div class="form__item">
                <label class="form__label" for="email">
                    Novo Email<span class="field-required">*</span></label>
                <input class="form__input"
                       @keydown.enter="emailUpdate"
                       @keydown="removeStyleInputError"
                       type="email" id="email" name="email" required placeholder="Insira seu novo e-mail"/>
            </div>
            <div class="form__item">
                <label class="form__label" for="email_confirmation">
                    Confirmação de email<span class="field-required">*</span>
                </label>
                <input class="form__input"
                       @keydown.enter="emailUpdate"
                       @keydown="removeStyleInputError"
                       type="email" id="email_confirmation" name="email_confirmation" required
                       placeholder="Confirme seu novo e-mail"/>
            </div>
            <div class="form__item">
                <label class="form__label" for="current_password">
                    Senha atual<span class="field-required">*</span>
                </label>
                <input class="form__input"
                       @keydown.enter="emailUpdate"
                       @keydown="removeStyleInputError"
                       type="password" name="current_password" required
                       placeholder="Informe sua senha atual"/>
            </div>
        </form>
    </ModalRight>
</template>

<style scoped>
.form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
.form__item:last-of-type {
    margin-top: 25%;
    padding-top: .5rem;
    border-top: solid 1px var(--gray-dark);
}
</style>
